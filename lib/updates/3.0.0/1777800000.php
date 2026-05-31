<?php
/**
 * Migration to v3.0.0
 *
 * Separates physical file storage from entity links:
 * - shop_syrattach_files stores the file on disk and its metadata.
 *   product_id becomes nullable: NULL means new-style central storage,
 *   non-null means old-style path inside the product directory.
 * - shop_syrattach_links is a new join table that maps one file to
 *   multiple entities (products, orders, etc.) each with its own
 *   sort order and description.
 *
 * Existing rows in shop_syrattach_files are migrated non-destructively:
 * product_id and description remain intact, and a matching link row is
 * inserted for each one. No files on disk are moved.
 *
 * @author Serge Rodovnichenko <serge@syrnik.com>
 *
 * @var shopSyrattachPlugin $this
 */

$log = 'shop/plugins/syrattach.log';
$schema_file = $this->path . '/lib/config/db.php';

try {
    $model = new waModel();

    if (file_exists($schema_file) && is_file($schema_file) && is_readable($schema_file)) {
        $schema = include($schema_file);

        // 1. Make product_id nullable so new-style records can use NULL
        $shop_syrattach_links_model = new shopSyrattachLinkModel();
        $shop_syrattach_links_model->modifyColumn('product_id', $schema);

        // 2. Create the links table
        $model->createSchema(['shop_syrattach_links' => $schema['shop_syrattach_links']]);
    }

    // 1. Make product_id nullable so new-style records can use NULL
    $model->exec("ALTER TABLE `shop_syrattach_files` MODIFY `product_id` INT(11) NULL DEFAULT NULL");

    // 3. Populate links from existing file records (INSERT IGNORE respects the UNIQUE key)
    $model->exec(
        "
        INSERT IGNORE INTO `shop_syrattach_links`
            (`file_id`, `entity_type`, `entity_id`, `sort`, `description`)
        SELECT
            `id`,
            'product',
            `product_id`,
            `sort`,
            COALESCE(`description`, '')
        FROM `shop_syrattach_files`
        WHERE `product_id` IS NOT NULL
    ");
} catch (Exception $e) {
    waLog::log('SyrAttach 3.0.0 migration failed: ' . $e->getMessage(), $log);
}
