<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2021
 * @license Webasyst
 */
declare(strict_types=1);

/**
 * Class shopSyrattachPluginViewHelper
 */
class shopSyrattachPluginViewHelper extends waPluginViewHelper
{
    /**
     * @param int|string $product_id
     * @param bool|int $force_on_empty
     */
    public function render($product_id, $force_on_empty = false): string
    {

    }

    /**
     * Helper method.
     * Returns an array of attached files
     *
     * array(
     *     array(
     *        'id'
     *        'name'
     *        'ext'
     *        'description',
     *        'size',
     *        'url'
     *     )
     * )
     *
     * @param int|string $product_id
     * @return array
     */
    public function getList($product_id): array
    {
        if (!($product_id = (int)$product_id)) return [];
        if (!($files = (new shopSyrattachFileModel())
            ->select("`id`,`name`, `ext`, `description`, `size`")
            ->where('product_id=i:id', ['id' => $product_id])
            ->order('`sort` ASC')
            ->fetchAll())) return [];

        array_walk($files, function (&$file) use ($product_id) {
            try {
                $file['url'] = shopSyrattachPlugin::getFileUrl($file + ['product_id' => $product_id]);
            } catch (waException $e) {
                waLog::log("Exception when processing list of files: " . $e->getMessage());
                $file = null;
            }
        });
        $files = array_filter($files);

        return array_values($files);
    }
}
