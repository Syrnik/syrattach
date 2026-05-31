<?php
/**
 * Save Settings controller
 *
 * @package Syrattach/controller
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2014, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
class shopSyrattachPluginSettingsSaveController extends waJsonController
{
    public function execute()
    {
        if (!$this->getUser()->getRights('shop', 'settings')) {
            throw new waException(_w('Access denied'));
        }

        $namespace = 'shop_syrattach';

        $plugin = waSystem::getInstance()->getPlugin('syrattach');
        $settings = (array)$this->getRequest()->post($namespace);

        try {
            $this->response = $plugin->saveSettings($settings);
            $this->response['message'] = _wp('Saved');
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }
}
