<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2021
 * @license Webasyst
 */
declare(strict_types=1);

/**
 * Class shopSyrattachPluginBackendAttachmentsAction
 */
class shopSyrattachPluginBackendAttachmentsAction extends waViewAction
{
    /**
     * @throws waException
     */
    public function execute()
    {
        $id = waRequest::param('id', 0, waRequest::TYPE_INT);
        $product = new shopProduct($id);

        $this->view->assign('product', $product);
        $this->view->assign('plugin_version', wa('shop')->getPlugin('syrattach')->getVersion());
        $this->view->assign('attachments', $id ? array_values((new shopSyrattachFileModel())->getByProductId($id, true)) : []);
        $this->view->assign('max_upload_size', (int)waRequest::getUploadMaxFilesize());
        $this->view->assign('l10n', [
            'GB'                                                          => _wp('GB'),
            'MB'                                                          => _wp('MB'),
            'KB'                                                          => _wp('KB'),
            'bytes'                                                       => _wp('bytes'),
            'of'                                                          => _wp('of'),
            'Attached files'                                              => _wp('Attached files'),
            'Upload or drag & drop files here'                            => _wp('Upload or drag & drop files here'),
            'Max. file size for upload:'                                  => _wp('Max. file size for upload:'),
            'Drag & drop files here or click this area to upload files.'  => _wp('Drag & drop files here or click this area to upload files.'),
            'Loading files'                                               => _wp('Loading files'),
            'Add file description'                                        => _wp('Add file description'),
            'Save'                                                        => _wp('Save'),
            'Cancel'                                                      => _wp('Cancel'),
            'Delete'                                                      => _wp('Delete'),
            'File deletion confirmation'                                  => _wp('File deletion confirmation'),
            'Do you really want to delete this file?'                     => _wp('Do you really want to delete this file?'),
            "You can't upload files to a new product. First, save the product." => _wp("You can't upload files to a new product. First, save the product."),
            'Size of %name% exceeds maximum upload size limit'            => _wp('Size of %name% exceeds maximum upload size limit'),
            'Upload error'                                                => _wp('Upload error'),
        ]);

        $this->setLayout(new shopBackendProductsEditSectionLayout([
            'product'    => $product,
            'content_id' => 'attachments'
        ]));
    }

    /**
     * @return string
     */
    protected function getTemplate(): string
    {
        return $this->getPluginRoot() . 'templates/actions/attachments/index.html';
    }
}
