<?php

return array(
    'name' => _wp('Product Attachments'),
    'icon' => 'img/syrattach.gif',
    'version' => '1.0.0',
    'vendor' => '670917',
    'handlers' =>
    array(
        'backend_product' => 'backendProduct',
        'product_delete' => 'productDelete'
    ),
);
