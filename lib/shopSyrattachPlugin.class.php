<?php
/**
 * @package Syrattach
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2014, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
class shopSyrattachPlugin extends shopPlugin
{

    const SYRATTACH_ATTACHMENTS_FOLDER = "attachments";

    /** @var shopSyrattachFileModel */
    private $Attachments;

    public function __construct($info)
    {
        parent::__construct($info);
        $this->Attachments = new shopSyrattachFileModel();
    }

    /**
     * Hook 'backend_product'
     *
     * @param array $product
     * @return array
     */
    public function backendProduct($product)
    {
        $template = $this->path . '/templates/backend_product.html';
        $view = waSystem::getInstance()->getView();
        $count = $this->Attachments->countByField('product_id', $product['id']);

        $view->assign(compact('count', 'product'));
        $html = $view->fetch($template);

        return array('edit_section_li' => $html);
    }

    /**
     * Handler for 'product_delete' hook
     *
     * We don't care about attached files because they will be deleted by
     * Shopscript with other public files such as images that belongs to
     * products
     *
     * @param array $product_ids
     */
    public function productDelete($product_ids)
    {
        $this->Attachments->deleteByField('product_id', $product_ids['ids']);
    }

    public static function getDirectory($product_id)
    {
        return shopProduct::getPath($product_id, self::SYRATTACH_ATTACHMENTS_FOLDER, TRUE);
    }

    public static function getFileUrl($attachment, $absolute=FALSE)
    {
        $path = shopProduct::getFolder($attachment['product_id']) .
                "/" .
                "{$attachment['product_id']}" .
                "/" .
                self::SYRATTACH_ATTACHMENTS_FOLDER .
                "/{$attachment['name']}";

        return waSystem::getInstance()->getDataUrl($path, TRUE, 'shop', $absolute);
    }

}
