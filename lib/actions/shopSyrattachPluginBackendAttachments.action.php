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
        $this->view->assign('l10n', $this->getL10n());

        $this->setLayout(new shopBackendProductsEditSectionLayout([
            'product'    => $product,
            'content_id' => 'attachments'
        ]));
    }

    /**
     * Strings passed to the Vue app. Keys are the English source strings used
     * as `t()` arguments in the components — keep them in sync with `src/`.
     *
     * @return string[]
     */
    protected function getL10n(): array
    {
        return [
            'GB'                                      => _wp('GB'),
            'MB'                                      => _wp('MB'),
            'KB'                                      => _wp('KB'),
            'bytes'                                   => _wp('bytes'),
            'of'                                      => _wp('of'),
            'Attached files'                          => _wp('Attached files'),
            'Upload or drag & drop files here'        => _wp('Upload or drag & drop files here'),
            'Max. file size for upload:'              => _wp('Max. file size for upload:'),
            'Loading files'                           => _wp('Loading files'),
            'Add file description'                    => _wp('Add file description'),
            'Save'                                    => _wp('Save'),
            'Cancel'                                  => _wp('Cancel'),
            'Close'                                   => _wp('Close'),
            'Delete'                                  => _wp('Delete'),
            'File deletion confirmation'              => _wp('File deletion confirmation'),
            'Do you really want to delete this file?' => _wp('Do you really want to delete this file?'),
            'Size of %name% exceeds maximum upload size limit' => _wp('Size of %name% exceeds maximum upload size limit'),
            'Upload error'                            => _wp('Upload error'),
            'Server error'                            => _wp('Server error'),
            'Loading'                                 => _wp('Loading'),
            'Drag to reorder'                         => _wp('Drag to reorder'),
            'Attach existing file'                    => _wp('Attach existing file'),
            'Search by filename...'                   => _wp('Search by filename...'),
            'Attach'                                  => _wp('Attach'),
            'No files found'                          => _wp('No files found'),
            "You can't upload files to a new product. First, save the product."
                                                      => _wp("You can't upload files to a new product. First, save the product."),
        ];
    }

    /**
     * @return string
     */
    protected function getTemplate(): string
    {
        return $this->getPluginRoot() . 'templates/actions/attachments/index.html';
    }
}
