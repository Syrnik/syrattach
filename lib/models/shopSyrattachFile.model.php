<?php

/**
 * @package Syrattach/model
 * @author Serge Rodovnichenko <sergerod@gmail.com>
 * @version 1.0.0
 * @copyright (c) 2014, Serge Rodovnichenko
 * @license http://www.webasyst.com/terms/#eula Webasyst
 */
class shopSyrattachFileModel extends waModel
{
    protected $table = 'shop_syrattach_files';

    /**
     * Adds a new record to the database
     * If a file with the same name and extension exists the new name will
     * be given %name%_%counter%.%ext%, i,e if file.pdf exists, the
     * newly uploaded file with the same name will be renamed to file_1.ext
     *
     * @param int $product_id
     * @param waRequestFile $file
     * @return array
     * @throws waException
     */
    public function add($product_id, $file)
    {
        if (!intval($product_id)) {
            throw new waException(_wp("Product ID missing while file metadata saving"));
        }

        $target_dir = shopSyrattachPlugin::getDirectory($product_id);

        $this->checkDirectory($target_dir);

        $data = array(
            'product_id'      => intval($product_id),
            'name'            => $this->getUniqueFileName(intval($product_id), $file),
            'sort'            => $this->getSortValue(intval($product_id)),
            'upload_datetime' => date("Y-m-d H:i:s"),
            'size'            => $file->size,
            'ext'             => $file->extension
        );

        $data['id'] = $this->insert($data);

        if (!$data['id']) {
            throw new waException(_w('Database error'));
        }

        $file->moveTo($target_dir, $data['name']);

        return $data;
    }

    /**
     *
     * @param int|string $product_id
     * @param bool $file_urls
     * @return array
     */
    public function getByProductId($product_id, $file_urls = FALSE)
    {
        $attachments = $this->select("*")->
        where("product_id=i:product_id", array('product_id' => $product_id))->
        order("sort ASC")->
        fetchAll();

        if ($file_urls) {
            foreach ($attachments as $key => $value) {
                $attachments[$key]['url'] = shopSyrattachPlugin::getFileUrl($value);
            }
        }

        return $attachments;
    }

    /**
     * Deletes record and attached file
     *
     * @param int|string $id
     * @param bool $delete_file
     * @throws Exception
     * @throws waException
     */
    public function delete($id, $delete_file = TRUE)
    {
        $attachment = $this->getById($id);

        if (!$attachment) {
            throw new waException(sprintf(_wp("Cannot find a record for attachment ID#%d"), $id));
        }

        /** @todo We need our own getPath? */
        $file = shopProduct::getPath(
            $attachment['product_id'],
            shopSyrattachPlugin::SYRATTACH_ATTACHMENTS_FOLDER . DIRECTORY_SEPARATOR . $attachment['name'],
            TRUE);

        waLog::log("Try to delete '$file'", 'syrattach.log');

        if (!$this->deleteById($id)) {
            throw new waException(_wp("Delete error"));
        }

        try {
            waFiles::delete($file);
        } catch (waException $e) {
            waLog::log(sprintf(_wp("SyrAttach Plugin cannot delete file %s. Message: %s"), $file, $e->getMessage()));
        }
    }

    /**
     *
     * @param int $product_id
     * @return int
     */
    private function getSortValue($product_id)
    {

        $info = $this->select('MAX(`sort`)+1 AS `max`, COUNT(1) AS `cnt`')
            ->where($this->getWhereByField('product_id', $product_id))
            ->fetch();

        if ($info['cnt']) {
            return $info['max'];
        }

        return 0;
    }

    /**
     *
     * @param int $product_id
     * @param waRequestFile $file
     * @return string
     */
    private function getUniqueFileName($product_id, $file)
    {
        return $file->name;
    }

    /**
     *
     * @param $dir string
     * @throws waException
     */
    private function checkDirectory($dir)
    {

        if ((file_exists($dir) && !is_writable($dir)) || (!file_exists($dir) && !waFiles::create($dir, TRUE))) {
            throw new waException("Error saving file. Check write permissions.");
        }
    }
}
