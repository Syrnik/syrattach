<?php

class shopSyrattachPlugin extends shopPlugin
{
    
    const SYRATTACH_ATTACHMENTS_FOLDER = "attachments";

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
        $Attachment = new shopSyrattachFileModel();
        $count = $Attachment->countByField('product_id', $product['id']);
        
        $view->assign(compact('count', 'product'));
        $html = $view->fetch($template);
        
        return array('edit_section_li' => $html);
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
