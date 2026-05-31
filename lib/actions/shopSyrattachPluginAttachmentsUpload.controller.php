<?php
/**
 * File Upload controller
 *
 * @package Syrattach/controller
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright (c) 2014-2026, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

declare(strict_types=1);

class shopSyrattachPluginAttachmentsUploadController extends shopUploadController
{
    /** @var shopProductModel */
    private $Product;

    /** @var shopSyrattachFileModel */
    private $SyrattachFile;

    public function __construct()
    {
        $this->Product       = new shopProductModel();
        $this->SyrattachFile = new shopSyrattachFileModel();
    }

    /**
     * @param waRequestFile $file
     * @return array
     * @throws waException
     */
    protected function save(waRequestFile $file): array
    {
        // Accept entity_type/entity_id for generic entities.
        // Falls back to syrattach_product_id for backward compatibility with existing JS.
        $entity_type = waRequest::post('entity_type', 'product', waRequest::TYPE_STRING_TRIM);
        $entity_id   = waRequest::post('entity_id', 0, waRequest::TYPE_INT);

        if (!$entity_id) {
            $entity_id   = waRequest::post('syrattach_product_id', 0, waRequest::TYPE_INT);
            $entity_type = 'product';
        }

        if (!$entity_id) {
            throw new waException(_wp("Entity ID required"));
        }

        if ($entity_type === 'product') {
            $this->checkProductRights($entity_id);
        }

        return $this->SyrattachFile->add($entity_id, $file, $entity_type);
    }

    /**
     * @param int $product_id
     * @throws waException
     */
    private function checkProductRights(int $product_id): void
    {
        if (!$this->Product->checkRights($product_id)) {
            throw new waException(_wp('Access denied'));
        }
    }
}
