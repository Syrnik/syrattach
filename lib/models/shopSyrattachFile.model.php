<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright (c) 2014-2026, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

declare(strict_types=1);

class shopSyrattachFileModel extends waModel
{
    protected $table = 'shop_syrattach_files';

    /**
     * Upload a file and attach it to an entity.
     *
     * Files are stored in central storage keyed by file id, so one file record
     * can later be linked to multiple entities without path conflicts.
     *
     * Returns a flat array suitable for JSON responses; `id` is the LINK id
     * (used for subsequent delete/description operations).
     *
     * @throws waException
     */
    public function add(int $entity_id, waRequestFile $file, string $entity_type = 'product', bool $copy = false): array
    {
        if (!$entity_id) {
            throw new waException(_wp("Entity ID missing while saving file"));
        }

        $file_data = [
            'product_id'      => null,
            'name'            => $file->name,
            'ext'             => $file->extension,
            'upload_datetime' => date('Y-m-d H:i:s'),
            'size'            => $file->size,
        ];

        $file_data['id'] = $this->insert($file_data);
        if (!$file_data['id']) {
            throw new waException(_wp('Database error'));
        }

        $target_dir = shopSyrattachPlugin::getDirectory(null, $file_data['id']);

        try {
            $this->ensureDirectory($target_dir);
            if ($copy) {
                $file->copyTo($target_dir, $file->name);
            } else {
                $file->moveTo($target_dir, $file->name);
            }
        } catch (waException $e) {
            $this->deleteById($file_data['id']);
            throw $e;
        }

        $link_model = new shopSyrattachLinkModel();
        try {
            $link_id = $link_model->insert([
                'file_id'     => $file_data['id'],
                'entity_type' => $entity_type,
                'entity_id'   => $entity_id,
                'sort'        => $link_model->getSortValue($entity_type, $entity_id),
                'description' => '',
            ]);
            if (!$link_id) {
                throw new waException(_wp('Database error creating file link'));
            }
        } catch (waException $e) {
            $this->deleteById($file_data['id']);
            waFiles::delete($target_dir);
            throw $e;
        }

        return [
            'id'          => (int)$link_id,
            'file_id'     => (int)$file_data['id'],
            'name'        => $file_data['name'],
            'ext'         => $file_data['ext'],
            'size'        => (int)$file_data['size'],
            'sort'        => 0,
            'description' => '',
            'product_id'  => null,
            'url'         => shopSyrattachPlugin::getFileUrl($file_data + ['file_id' => $file_data['id']]),
        ];
    }

    /**
     * Files attached to entity, ordered by sort.
     * Each row has `id` = link id (for delete/description operations).
     *
     * @throws waException
     */
    public function getByEntity(string $entity_type, int $entity_id, bool $with_urls = false): array
    {
        $sql = "SELECT l.`id`, l.`file_id`, l.`sort`, l.`description`,
                       f.`name`, f.`ext`, f.`size`, f.`product_id`
                FROM `shop_syrattach_links` l
                JOIN `{$this->table}` f ON f.`id` = l.`file_id`
                WHERE l.`entity_type` = s:type AND l.`entity_id` = i:eid
                ORDER BY l.`sort` ASC";

        $rows = $this->query($sql, [
            'type' => $entity_type,
            'eid'  => $entity_id,
        ])->fetchAll();

        foreach ($rows as &$row) {
            $row['id']      = (int)$row['id'];
            $row['file_id'] = (int)$row['file_id'];
            $row['sort']    = (int)$row['sort'];
            $row['size']    = (int)$row['size'];
            if ($with_urls) {
                $row['url'] = shopSyrattachPlugin::getFileUrl($row);
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * @deprecated use getByEntity('product', $product_id)
     * @throws waException
     */
    public function getByProductId($product_id, bool $file_urls = false): array
    {
        return $this->getByEntity('product', (int)$product_id, $file_urls);
    }

    /**
     * Detach file from entity (delete link).
     * If no links remain and the file is new-style (product_id IS NULL),
     * also deletes the physical file and the file record.
     *
     * @param int|string $link_id  ID from shop_syrattach_links
     * @throws waException
     */
    public function delete($link_id, bool $delete_file = true): void
    {
        $link_id    = (int)$link_id;
        $link_model = new shopSyrattachLinkModel();
        $link       = $link_model->getById($link_id);

        if (!$link) {
            throw new waException(sprintf_wp("Cannot find a link record ID#%d", $link_id));
        }

        $file_id = (int)$link['file_id'];
        $file    = $this->getById($file_id);

        if (!$link_model->deleteById($link_id)) {
            throw new waException(_wp("Delete error"));
        }

        if (!$delete_file) {
            return;
        }

        if ($link_model->countByFile($file_id) > 0) {
            return;
        }

        // No remaining links — remove file record
        $this->deleteById($file_id);

        if (!$file) {
            return;
        }

        // Physical cleanup
        try {
            if ($file['product_id'] === null) {
                // New-style: delete the whole per-file directory
                waFiles::delete(shopSyrattachPlugin::getDirectory(null, $file_id));
            } else {
                // Old-style: delete the specific file (product dir cleanup owned by Shop)
                waFiles::delete(shopSyrattachPlugin::getFilePath($file));
            }
        } catch (waException $e) {
            waLog::log(
                sprintf_wp("SyrAttach cannot delete file for record %d: %s", $file_id, $e->getMessage()),
                shopSyrattachPlugin::LOG
            );
        }
    }

    /**
     * Detach all files from entity and clean up orphaned new-style files.
     * Used by the product_delete hook; for old-style files the product
     * directory is cleaned up by Shop-Script automatically.
     *
     * @throws waException
     */
    public function deleteByEntity(string $entity_type, int $entity_id): void
    {
        $link_model = new shopSyrattachLinkModel();
        $file_ids   = $link_model->getFileIdsForEntity($entity_type, $entity_id);

        $link_model->deleteByEntity($entity_type, $entity_id);

        foreach ($file_ids as $file_id) {
            if ($link_model->countByFile($file_id) > 0) {
                continue;
            }

            $file = $this->getById($file_id);
            $this->deleteById($file_id);

            // New-style files are not inside the product directory, so we delete them
            if ($file && $file['product_id'] === null) {
                try {
                    waFiles::delete(shopSyrattachPlugin::getDirectory(null, $file_id));
                } catch (waException $e) {
                    waLog::log(
                        sprintf_wp("SyrAttach cannot delete dir for file %d: %s", $file_id, $e->getMessage()),
                        shopSyrattachPlugin::LOG
                    );
                }
            }
        }
    }

    /**
     * @throws waException
     */
    private function ensureDirectory(string $dir): void
    {
        if (file_exists($dir) && !is_writable($dir)) {
            throw new waException("Error saving file: directory not writable.");
        }
        if (!file_exists($dir) && !waFiles::create($dir, true)) {
            throw new waException("Error saving file: cannot create directory.");
        }
    }
}
