<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright (c) 2021-2026, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
declare(strict_types=1);

class shopSyrattachPluginViewHelper extends waPluginViewHelper
{
    /**
     * @param int|string $product_id
     * @param bool|int $force_on_empty
     * @param string|null $no_template
     * @return string
     */
    public function render($product_id, $force_on_empty = false, ?string $no_template = null): string
    {
        $product_id     = (int)$product_id;
        $force_on_empty = (bool)$force_on_empty;

        $attachments = $this->getList($product_id);
        if (!$attachments && !$force_on_empty) {
            return '';
        }

        if (($template_file = $this->getTemplate($no_template)) === null) {
            return '';
        }

        try {
            $view = wa('shop')->getView();
        } catch (waException $e) {
            waLog::log('Exception loading getView: ' . $e->getMessage());
            return '';
        }

        $view->assign('attachments', $attachments);
        waSystem::pushActivePlugin('syrattach');
        try {
            $result = $view->fetch($template_file);
        } catch (SmartyException $e) {
            waLog::log('Smarty exception on rendering attachments template: ' . $e->getMessage());
            $result = '';
        } catch (waException $e) {
            waLog::log('Webasyst system exception on rendering attachments template: ' . $e->getMessage());
            $result = '';
        }
        waSystem::popActivePlugin();

        return $result;
    }

    /**
     * Returns files attached to the product, with URLs.
     * Each item: id (link id), file_id, name, ext, description, size, url.
     *
     * @param int|string $product_id
     * @return array
     */
    public function getList($product_id): array
    {
        if (!($product_id = (int)$product_id)) {
            return [];
        }

        try {
            return (new shopSyrattachFileModel())->getByEntity('product', $product_id, true);
        } catch (waException $e) {
            waLog::log('Exception getting attachment list: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @param string|null $no_template
     * @return string|null
     */
    protected function getTemplate(?string $no_template): ?string
    {
        if ($no_template === null) {
            $no_template = $this->plugin->getSettings('no_template');
        }
        $file = null;

        try {
            if (wa()->getEnv() === 'frontend' && ($theme = waRequest::getTheme())) {
                $theme = new waTheme($theme);
                if ($theme->getFile('plugin.syrattach.attachments.html')) {
                    $file = $theme->getPath() . '/plugin.syrattach.attachments.html';
                }
            }
        } catch (waException $e) {
            waLog::log('Exception when loading theme template: ' . $e->getMessage());
            $file = null;
        }

        if ($file || ($no_template === 'off')) {
            return $file;
        }

        $template_path = 'plugins/syrattach/templates/frontend_product.html';

        try {
            $original_template = wa()->getAppPath($template_path, 'shop');
        } catch (waException $e) {
            waLog::log('Exception reading original template');
            return null;
        }

        try {
            $modified_template = wa()->getDataPath($template_path, false, 'shop', false);
        } catch (waException $e) {
            waLog::log('Exception reading old custom template');
            $modified_template = null;
        }

        if ($modified_template && file_exists($modified_template)) {
            return $modified_template;
        }

        return $original_template;
    }
}
