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
 */

$log = 'shop/plugins/syrattach.log';

try {
    $model = new waModel();

    // 1. Make product_id nullable so new-style records can use NULL
    $model->exec("ALTER TABLE `shop_syrattach_files` MODIFY `product_id` INT(11) NULL DEFAULT NULL");
} catch (Exception $e) {
    waLog::log('SyrAttach 3.0.0 migration: ALTER product_id failed: ' . $e->getMessage(), $log);
}

try {
    // 2. Create the links table
    $model->exec("
        CREATE TABLE IF NOT EXISTS `shop_syrattach_links` (
            `id`          INT(11)     NOT NULL AUTO_INCREMENT,
            `file_id`     INT(11)     NOT NULL,
            `entity_type` VARCHAR(64) NOT NULL,
            `entity_id`   INT(11)     NOT NULL,
            `sort`        INT(11)     NOT NULL DEFAULT 0,
            `description` TEXT,
            PRIMARY KEY (`id`),
            UNIQUE KEY `file_entity_unique` (`file_id`, `entity_type`, `entity_id`),
            KEY `entity` (`entity_type`, `entity_id`, `sort`),
            KEY `file_id` (`file_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
} catch (Exception $e) {
    waLog::log('SyrAttach 3.0.0 migration: CREATE TABLE failed: ' . $e->getMessage(), $log);
}

try {
    // 3. Populate links from existing file records (INSERT IGNORE respects the UNIQUE key)
    $model->exec("
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
    waLog::log('SyrAttach 3.0.0 migration: INSERT links failed: ' . $e->getMessage(), $log);
}
