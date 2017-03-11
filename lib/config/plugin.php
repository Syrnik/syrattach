<?php
/**
 * @package Syrattach
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * @version 1.0.1
 * @copyright (c) 2014-2015, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
return array(
    'name'          => _wp('Attached Files'),
    'img'           => 'img/syrattach.png',
    'version'       => '1.0.1',
    'vendor'        => '670917',
    'shop_settings' => true,
    'handlers'      =>
        array(
            'backend_product'       => 'backendProduct',
            'frontend_product'      => 'frontendProduct',
            'product_delete'        => 'productDelete',
            'product_custom_fields' => 'productCustomFields'
        ),
);
