<?php
/**
 * @package Syrattach
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2014, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
return array(
    'name' => _wp('Product Attachments'),
    'icon' => 'img/syrattach.gif',
    'version' => '1.0.0',
    'vendor' => '670917',
    'handlers' =>
    array(
        'backend_product'   => 'backendProduct',
        'frontend_product'  => 'frontendProduct',
        'product_delete'    => 'productDelete'
    ),
);
