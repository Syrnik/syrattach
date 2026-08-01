<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright (c) 2014-2026, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

declare(strict_types=1);

/**
 * Class shopSyrattachPluginAttachmentsActions
 * @Controller attachments
 */
class shopSyrattachPluginAttachmentsActions extends waJsonActions
{
    /** @var string */
    protected $template_folder = 'templates/Attachments';

    /** @var shopSyrattachFileModel */
    private $Attachment;

    /**
     * Delete a link (detach file from entity).
     * POST id = shop_syrattach_links.id
     *
     * @throws waException
     */
    public function deleteAction(): void
    {
        $id = waRequest::post('id', null, waRequest::TYPE_INT);
        try {
            $this->Attachment->delete($id, true);
            $this->response = _wp('Deleted');
        } catch (Exception $exc) {
            $this->errors[] = [$exc->getMessage()];
        }
    }

    /**
     * List of files attached to entity.
     * GET entity_type, entity_id  (or legacy product_id for products)
     *
     * @throws waException
     */
    public function listAction(): void
    {
        $entity_type = waRequest::get('entity_type', 'product', waRequest::TYPE_STRING_TRIM);
        $entity_id   = waRequest::get('entity_id', 0, waRequest::TYPE_INT);

        // Backward compat: product_id used in old JS calls
        if (!$entity_id && $entity_type === 'product') {
            $entity_id = waRequest::get('product_id', 0, waRequest::TYPE_INT);
        }

        if (!$entity_id) {
            $this->errors[] = [_wp('Unknown entity')];
            return;
        }

        try {
            $attachments                   = $this->Attachment->getByEntity($entity_type, $entity_id, true);
            $this->response['attachments'] = $attachments;
            $this->response['count']       = count($attachments);
        } catch (waException $ex) {
            $this->errors[] = [$ex->getMessage()];
        }
    }

    /**
     * Save description for a link.
     * POST id = shop_syrattach_links.id, data[description]
     *
     * @throws waException
     */
    public function descriptionsaveAction(): void
    {
        $id   = waRequest::post('id', 0, waRequest::TYPE_INT);
        $data = waRequest::post('data', [], waRequest::TYPE_ARRAY);

        if (!$id) {
            $this->errors[] = [_wp('Unknown attachment ID')];
            return;
        }

        if (!is_array($data) || !isset($data['description'])) {
            $this->errors[] = [_wp('Description is not set')];
            return;
        }

        try {
            (new shopSyrattachLinkModel())->updateById($id, ['description' => $data['description']]);
            $this->response = _wp('Saved');
        } catch (waException $exc) {
            $this->errors[] = [$exc->getMessage()];
        }
    }

    /**
     * Search files not yet linked to entity.
     * GET query, entity_type, entity_id
     *
     * @throws waException
     */
    public function searchAction(): void
    {
        $query       = waRequest::get('query', '', waRequest::TYPE_STRING_TRIM);
        $entity_type = waRequest::get('entity_type', 'product', waRequest::TYPE_STRING_TRIM);
        $entity_id   = waRequest::get('entity_id', 0, waRequest::TYPE_INT);

        if (!$entity_id) {
            $this->errors[] = [_wp('Invalid parameters')];
            return;
        }

        $this->response['files'] = $this->Attachment->search($query, $entity_type, $entity_id);
    }

    /**
     * Link an existing file to entity.
     * POST file_id, entity_type, entity_id
     *
     * @throws waException
     */
    public function linkAction(): void
    {
        $file_id     = waRequest::post('file_id', 0, waRequest::TYPE_INT);
        $entity_type = waRequest::post('entity_type', 'product', waRequest::TYPE_STRING_TRIM);
        $entity_id   = waRequest::post('entity_id', 0, waRequest::TYPE_INT);

        if (!$file_id || !$entity_id) {
            $this->errors[] = [_wp('Invalid parameters')];
            return;
        }

        $file = $this->Attachment->getById($file_id);
        if (!$file) {
            $this->errors[] = [_wp('File not found')];
            return;
        }

        $link_model = new shopSyrattachLinkModel();
        $link_id    = $link_model->link($file_id, $entity_type, $entity_id);
        $link       = $link_model->getById($link_id);

        $this->response = [
            'id'          => (int)$link_id,
            'file_id'     => (int)$file_id,
            'name'        => $file['name'],
            'ext'         => $file['ext'],
            'size'        => (int)$file['size'],
            'sort'        => (int)($link['sort'] ?? 0),
            'description' => $link['description'] ?? '',
            'product_id'  => $file['product_id'],
            'url'         => shopSyrattachPlugin::getFileUrl($file),
        ];
    }

    /**
     * Save a new sort order for entity's attachments.
     * POST entity_type, entity_id, order[] — link_ids in desired display order.
     *
     * @throws waException
     */
    public function sortAction(): void
    {
        $entity_type = waRequest::post('entity_type', 'product', waRequest::TYPE_STRING_TRIM);
        $entity_id   = waRequest::post('entity_id', 0, waRequest::TYPE_INT);
        $order       = waRequest::post('order', [], waRequest::TYPE_ARRAY);

        if (!$entity_id || !$order) {
            $this->errors[] = [_wp('Invalid parameters')];
            return;
        }

        (new shopSyrattachLinkModel())->updateSort($entity_id, $entity_type, array_map('intval', $order));
        $this->response = 'OK';
    }

    protected function preExecute(): void
    {
        parent::preExecute();
        $this->Attachment = new shopSyrattachFileModel();
    }
}
