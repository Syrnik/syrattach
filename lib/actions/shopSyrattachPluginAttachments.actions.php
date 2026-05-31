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
            $this->response = 'Saved';
        } catch (waException $exc) {
            $this->errors[] = [$exc->getMessage()];
        }
    }

    protected function preExecute(): void
    {
        parent::preExecute();
        $this->Attachment = new shopSyrattachFileModel();
    }
}
