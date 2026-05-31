<?php
/**
 * @package Syrattach
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright (c) 2014-2026, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */

class shopSyrattachPlugin extends shopPlugin
{
    const SYRATTACH_ATTACHMENTS_FOLDER = 'attachments';
    const LOG = 'shop/plugins/syrattach.log';

    /**
     * Directory where a file's physical data lives.
     *
     * Old-style (product_id set): inside the product's own directory.
     * New-style (product_id null): central plugin storage keyed by file id.
     */
    public static function getDirectory(?int $product_id, int $file_id = 0): string
    {
        if ($product_id !== null) {
            return shopProduct::getPath($product_id, self::SYRATTACH_ATTACHMENTS_FOLDER, true);
        }

        return waSystem::getInstance()->getDataPath(
            self::SYRATTACH_ATTACHMENTS_FOLDER . '/files/' . $file_id,
            true,
            'shop'
        );
    }

    /**
     * Absolute filesystem path to a file.
     * Accepts a row from shop_syrattach_files (needs `product_id`, `name`)
     * or a joined row (also has `file_id`).
     */
    public static function getFilePath(array $attachment): string
    {
        $product_id = $attachment['product_id'] ?? null;
        $name       = $attachment['name'];

        if ($product_id === null) {
            $file_id = $attachment['file_id'] ?? $attachment['id'];
            return waSystem::getInstance()->getDataPath(
                self::SYRATTACH_ATTACHMENTS_FOLDER . '/files/' . $file_id . '/' . $name,
                true,
                'shop'
            );
        }

        return shopProduct::getPath(
            $product_id,
            self::SYRATTACH_ATTACHMENTS_FOLDER . DIRECTORY_SEPARATOR . $name,
            true
        );
    }

    /**
     * Public URL to a file.
     * Dual-mode: old-style uses the product directory, new-style uses central storage.
     *
     * @throws waException
     */
    public static function getFileUrl(array $attachment, bool $absolute = false): string
    {
        $product_id = $attachment['product_id'] ?? null;

        if ($product_id === null) {
            $file_id = $attachment['file_id'] ?? $attachment['id'];
            $path    = self::SYRATTACH_ATTACHMENTS_FOLDER . '/files/' . $file_id . '/' . $attachment['name'];
            return waSystem::getInstance()->getDataUrl($path, true, 'shop', $absolute);
        }

        $path = shopProduct::getFolder($product_id)
            . '/' . $product_id . '/'
            . self::SYRATTACH_ATTACHMENTS_FOLDER
            . '/' . $attachment['name'];

        return waSystem::getInstance()->getDataUrl($path, true, 'shop', $absolute);
    }

    /**
     * @param string $param
     * @param array $settings
     * @return string
     */
    public static function templateControl(string $param, array $settings): string
    {
        try {
            $control_template_path = 'plugins/syrattach/templates/settings/template_control.html';
            $control_template      = waSystem::getInstance()->getAppPath($control_template_path, 'shop');
            $view                  = waSystem::getInstance()->getView();
            $template_path         = 'plugins/syrattach/templates/frontend_product.html';
            $original_template     = waSystem::getInstance()->getAppPath($template_path, 'shop');
            $modified_template_path = waSystem::getInstance()->getDataPath($template_path, false, 'shop', false);
        } catch (waException $exception) {
            waLog::log($exception->getMessage(), self::LOG);
            return '';
        }

        $original_template  = file_get_contents($original_template);
        $modified_template  = null;
        $template_modified  = false;

        if (file_exists($modified_template_path)) {
            $modified_template = file_get_contents($modified_template_path);
            $template_modified = true;
        }

        $view->assign(compact('settings', 'modified_template', 'original_template', 'template_modified'));

        try {
            return $view->fetch($control_template);
        } catch (Exception $e) {
            waLog::log($e->getMessage(), self::LOG);
            return '';
        }
    }

    /**
     * @param array $route
     * @return array
     * @throws waException
     */
    public function routing($route = array())
    {
        if (wa()->getEnv() === 'backend') {
            return ['products/<id>/attachments/?' => 'backend/attachments'];
        }
        return parent::routing($route);
    }

    /**
     * Hook 'backend_product'
     *
     * @param array|shopProduct $product
     * @return array
     * @throws SmartyException
     * @throws waException
     */
    public function backendProduct($product): array
    {
        $template       = $this->path . '/templates/backend_product.html';
        $view           = waSystem::getInstance()->getView();
        $count          = (new shopSyrattachLinkModel())->countByField([
            'entity_type' => 'product',
            'entity_id'   => $product['id'],
        ]);
        $shop_version   = wa('shop')->getVersion();
        $hints_allowed  = (bool)version_compare($shop_version, '7.5', '>=');

        $view->assign(compact('count', 'product', 'hints_allowed'));
        $html = $view->fetch($template);

        return array('edit_section_li' => $html);
    }

    /**
     * @param $params
     * @return array
     * @throws waException
     */
    public function handlerBackendProd(&$params): array
    {
        $wa_app_url = wa()->getAppUrl('shop', true);
        $id         = (int)$params['product']->getId();

        if (!$id) {
            $id    = 'new';
            $total = 0;
        } else {
            $total = (new shopSyrattachLinkModel())->countByField([
                'entity_type' => 'product',
                'entity_id'   => $id,
            ]);
        }

        return [
            'sidebar_item' => "<li id=\"s-syrattach-plugin-menuitem\"><a href='{$wa_app_url}products/$id/attachments/'><span>" .
                _wp('Attached files') .
                "</span>" . ($total ? "<span class=\"count\">$total</span>" : "") . "</a></li>"
        ];
    }

    /**
     * @return string[]
     */
    public function handlerBackendProdLayout(): array
    {
        return ['bottom' => '<script>$(\'#wa-app\').on(\'wa_loaded\', ()=>{$.wa_shop_products.router.routes["/products/\\\\d+/attachments/"]={id:"products", content_selector: ".s-product-page .js-page-content"}});</script>'];
    }

    /**
     * @return array
     * @throws waException
     */
    public function productCustomFields(): array
    {
        return ['product' => ['file' => _wp('Attached File')]];
    }

    /**
     * Hook 'product_delete'
     *
     * Removes links and orphaned new-style files. Old-style files reside
     * inside the product directory which Shop-Script deletes automatically.
     *
     * @param array $product_ids
     * @throws waException
     */
    public function productDelete(array $product_ids): void
    {
        $ids   = (array)($product_ids['ids'] ?? []);
        $model = new shopSyrattachFileModel();
        foreach ($ids as $product_id) {
            $model->deleteByEntity('product', (int)$product_id);
        }
    }

    /**
     * Hook 'product_save' — import files from CSV
     *
     * @param array|mixed $params
     * @throws waException
     */
    public function productSave($params): void
    {
        if (!array_key_exists('syrattach_plugin', $params['data'])) {
            return;
        }

        if (empty($params['data']['id'])) {
            if (wa()->getConfig()->isDebug()) {
                waLog::log(sprintf(_wp('No ID given for product "%s"'), ifset($params['data']['name'], '')), self::LOG);
            }
            return;
        }

        $data_path = wa()->getDataPath('syrattach', true, 'site', false);
        $files     = (array)ifset($params, 'data', 'syrattach_plugin', 'file', []);
        if (!$files) {
            return;
        }

        $model = new shopSyrattachFileModel();
        foreach ($files as $file) {
            if ((strpos($file, '/') !== false) || (strpos($file, '\\') !== false)) {
                waLog::log(sprintf(_wp('Wrong file name "%s" for product "%s". File not saved.'), $file, ifset($params['data']['name'])), self::LOG);
                continue;
            }

            $full_path = $data_path . DIRECTORY_SEPARATOR . $file;
            if (!file_exists($full_path) || !is_file($full_path) || !is_readable($full_path)) {
                waLog::log(sprintf(_wp('File named "%s" not exists or not readable. File not saved.'), $file), self::LOG);
                continue;
            }

            $model->add(
                $params['data']['id'],
                new waRequestFile([
                    'name'     => $file,
                    'type'     => 'application/binary',
                    'size'     => filesize($full_path),
                    'tmp_name' => $full_path,
                    'error'    => 0,
                ], true),
                'product',
                true
            );
        }
    }

    /**
     * Hook 'frontend_product'
     *
     * @param shopProduct $product
     * @return array
     */
    public function frontendProduct(shopProduct $product): array
    {
        $placement = $this->getSettings('frontend_product_hook');
        if (($placement !== 'block') && ($placement !== 'block_aux')) {
            return [];
        }

        return [$placement => (new shopSyrattachPluginViewHelper($this, 'syrattach'))->render($product->id)];
    }

    /**
     * @param int|string $product_id
     * @param bool|int $force_on_empty
     * @return string
     * @deprecated since 2.0.0
     */
    public static function render($product_id, $force_on_empty = false): string
    {
        $product_id     = (int)$product_id;
        $force_on_empty = (bool)$force_on_empty;

        try {
            $plugin = wa('shop')->getPlugin('syrattach');
        } catch (waException $e) {
            return '';
        }

        return (new shopSyrattachPluginViewHelper($plugin, 'syrattach'))
            ->render($product_id, $force_on_empty);
    }

    /**
     * @param int|string $product_id
     * @return array
     * @deprecated since 2.0.0
     */
    public static function getList($product_id): array
    {
        try {
            $plugin = wa('shop')->getPlugin('syrattach');
        } catch (waException $e) {
            return [];
        }

        return (new shopSyrattachPluginViewHelper($plugin, 'syrattach'))
            ->getList($product_id);
    }
}
