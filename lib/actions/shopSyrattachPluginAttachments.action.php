<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright (c) 2014-2026, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

declare(strict_types=1);

class shopSyrattachPluginAttachmentsAction extends waViewAction
{
    /** @var shopSyrattachPlugin */
    protected $plugin;

    /**
     * @throws waException
     */
    protected function preExecute(): void
    {
        parent::preExecute();
        $this->plugin = wa('shop')->getPlugin('syrattach');
    }

    /**
     * @throws waException
     */
    public function execute(): void
    {
        $product_id = waRequest::get('id', null, waRequest::TYPE_INT);
        if (!$product_id) {
            throw new waException(_wp('Product ID required'));
        }

        $product     = new shopProduct($product_id);
        $file_model  = new shopSyrattachFileModel();
        $attachments = $file_model->getByEntity('product', $product_id, true);

        $this->view->assign([
            'attachments'   => $attachments,
            'count'         => count($attachments),
            'max_file_size' => (int)waRequest::getUploadMaxFilesize(),
            'product'       => $product,
        ]);
    }
}
