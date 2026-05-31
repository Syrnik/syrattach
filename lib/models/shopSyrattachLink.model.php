<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright (c) 2024-2026, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

declare(strict_types=1);

class shopSyrattachLinkModel extends waModel
{
    protected $table = 'shop_syrattach_links';

    /**
     * Next sort value for entity (MAX+1, or 0 if entity has no files yet)
     */
    public function getSortValue(string $entity_type, int $entity_id): int
    {
        $info = $this->select('MAX(`sort`) + 1 AS `max`, COUNT(1) AS `cnt`')
            ->where('`entity_type` = s:type AND `entity_id` = i:id', [
                'type' => $entity_type,
                'id'   => $entity_id,
            ])
            ->fetch();

        return $info['cnt'] ? (int)$info['max'] : 0;
    }

    /**
     * How many entities still reference this file
     */
    public function countByFile(int $file_id): int
    {
        return (int)$this->countByField('file_id', $file_id);
    }

    /**
     * All file_ids linked to entity (used before batch-deleting links)
     *
     * @return int[]
     */
    public function getFileIdsForEntity(string $entity_type, int $entity_id): array
    {
        $rows = $this->select('`file_id`')
            ->where('`entity_type` = s:type AND `entity_id` = i:id', [
                'type' => $entity_type,
                'id'   => $entity_id,
            ])
            ->fetchAll();

        return array_map('intval', array_column($rows, 'file_id'));
    }

    /**
     * Delete all links for entity
     */
    public function deleteByEntity(string $entity_type, int $entity_id): void
    {
        $this->deleteByField([
            'entity_type' => $entity_type,
            'entity_id'   => $entity_id,
        ]);
    }

    /**
     * Create a link between file and entity.
     * Ignores duplicate (file already linked to same entity).
     *
     * @return int link id (0 if duplicate)
     */
    public function link(int $file_id, string $entity_type, int $entity_id): int
    {
        $existing = $this->select('`id`')
            ->where('`file_id` = i:fid AND `entity_type` = s:type AND `entity_id` = i:eid', [
                'fid'  => $file_id,
                'type' => $entity_type,
                'eid'  => $entity_id,
            ])
            ->fetch();

        if ($existing) {
            return (int)$existing['id'];
        }

        return (int)$this->insert([
            'file_id'     => $file_id,
            'entity_type' => $entity_type,
            'entity_id'   => $entity_id,
            'sort'        => $this->getSortValue($entity_type, $entity_id),
            'description' => '',
        ]);
    }
}
