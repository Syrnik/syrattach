<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of shopSyrattachPluginAttachments
 *
 * @author serge
 */
class shopSyrattachPluginAttachmentsActions extends waViewActions
{
    /** @var string */
    protected $template_folder = 'templates/Attachments';

    /** @var shopProductModel */
    private $Product;

    /** @var shopSyrattachFileModel */
    private $Attachment;

    /**
     * Shows the tab content
     *
     * @throws waException
     */
    public function defaultAction()
    {
        $product_id = waRequest::get('id', NULL, waRequest::TYPE_INT);
        $product = $this->Product->getById($product_id);
        if(!$product) {
            throw new waException(_wp("Unknown product"));
        }

        $attachments = $this->Attachment->getByProductId($product_id, TRUE);
        $count = count($attachments);

        $this->view->assign(compact('attachments', 'count', 'product'));
    }

    public function deleteAction()
    {
        $errors = array();
        $response = _wp("Deleted");

        $this->getResponse()->addHeader('Content-type', 'application/json');

        $id = waRequest::post('id', NULL, waRequest::TYPE_INT);

        try {
            $this->Attachment->delete($id, TRUE);
        } catch (waException $exc) {
            $errors[] = $exc->getMessage();
        }

        $this->view->assign(compact('response', 'errors'));
    }

    /**
     * Returns full path to the template file to render
     *
     * @return string
     */
    protected function getTemplate()
    {
        $pluginRoot = $this->getPluginRoot();

        if ($this->template === NULL) {
            if($this->getResponse()->getHeader('Content-type') === 'application/json') {
                return "{$pluginRoot}templates/json.tpl";
            }
            $template = ucfirst($this->action);
        } else {
            // If path contains / or : then it's a full path to template
            if (strpbrk($this->template, '/:') !== false) {
                return $this->template;
            }

            // otherwise it's a template name and we need to figure out its directory
            $template = $this->template;
        }

        $match = array();
        preg_match("/[A-Z][^A-Z]+/", get_class($this), $match);
        $template = "{$pluginRoot}{$this->template_folder}/$template".$this->view->getPostfix();
        return $template;
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
