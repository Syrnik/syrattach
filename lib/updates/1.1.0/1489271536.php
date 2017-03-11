<?php
$plugin_path = wa('shop')->getConfig()->getPluginPath('syrattach');
waFiles::delete( $plugin_path . '/css');
