<?php
/**
 * @package Syrattach
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2014, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
return array(
    'shop_syrattach_files' => array(
        'id'              => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        // NULL for new-style files stored in central storage; old records keep product_id for path resolution
        'product_id'      => array('int', 11, 'null' => 1),
        'name'            => array('varchar', 255, 'null' => 0),
        'ext'             => array('varchar', 255, 'null' => 0),
        'upload_datetime' => array('datetime', 'null' => 0),
        'size'            => array('int', 11, 'null' => 0),
        'description'     => array('text'),  // deprecated: moved to shop_syrattach_links
        'sort'            => array('int', 11, 'null' => 0, 'default' => '0'),  // deprecated: moved to links
        ':keys'           => array(
            'PRIMARY'    => 'id',
            'product_id' => array('product_id', 'sort'),
        ),
    ),
    'shop_syrattach_links' => array(
        'id'          => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'file_id'     => array('int', 11, 'null' => 0),
        'entity_type' => array('varchar', 64, 'null' => 0),
        'entity_id'   => array('int', 11, 'null' => 0),
        'sort'        => array('int', 11, 'null' => 0, 'default' => '0'),
        'description' => array('text'),
        ':keys'       => array(
            'PRIMARY'     => 'id',
            'entity'      => array('entity_type', 'entity_id', 'sort'),
            'file_id'     => 'file_id',
            'file_entity' => array('file_id', 'entity_type', 'entity_id'),
        ),
    ),
);
