<?php
/**
 * Description of shopSyrattachPluginAttachments
 *
 * @package Syrattach/controller
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * @copyright (c) 2014-2021, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

declare(strict_types=1);

/**
 * Class shopSyrattachPluginAttachmentsActions
 */
class shopSyrattachPluginAttachmentsActions extends waJsonActions
{
    /** @var string */
    protected $template_folder = 'templates/Attachments';

    /** @var shopProductModel */
    private $Product;

    /** @var shopSyrattachFileModel */
    private $Attachment;

    /**
     * @throws waException
     */
    public function deleteAction()
    {
        $errors = array();
        $response = _wp("Deleted");
        $id = waRequest::post('id', null, waRequest::TYPE_INT);

        try {
            $this->Attachment->delete($id, true);
            $this->response = $response;
        } catch (Exception $exc) {
            $this->errors[] = [$exc->getMessage()];
        }
    }

    /**
     * Список всех аттачей
     *
     * @throws waException
     */
    public function listAction()
    {
        if (!($product_id = waRequest::get('product_id', 0, waRequest::TYPE_INT))) {
            $this->errors[] = [_wp('Unknown product')];
            return;
        }

        try {
            $this->response['attachments'] = $this->Attachment->getByProductId($product_id, true);
            $this->response['count'] = count($this->response['attachments']);
        } catch (waException $ex) {
            $this->errors[] = [$ex->getMessage()];
        }
    }

    /**
     * Сохранение описания вложения
     * @throws waException
     */
    public function descriptionsaveAction()
    {
        if (!($id = waRequest::post('id', 0, waRequest::TYPE_INT))) {
            $this->errors[] = [_wp('Unknown attachment ID')];
            return;
        }

        if (!($data = waRequest::post('data', array(), waRequest::TYPE_ARRAY)) || !is_array($data) || !isset($data['description'])) {
            $this->errors[] = [_wp('Description is not set')];
            return;
        };

        try {
            $this->Attachment->updateById($id, array('description' => $data['description']));
            $this->response = "Saved";
        } catch (waException $exc) {
            $this->errors[] = [$exc->getMessage()];
        }
    }

    /**
     * Initialize controller-wide variables
     */
    protected function preExecute()
    {
        parent::preExecute();
        $this->Product = new shopProductModel();
        $this->Attachment = new shopSyrattachFileModel();
    }
}
