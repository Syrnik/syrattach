# Attached Files for Shop Products — Shop-Script plugin

[Русская версия](README.md)

The plugin lets you attach any number of files with descriptions to each product. The file list is displayed to customers on the product page.

Typical use cases: user manuals and guides, drivers and firmware, certificates and datasheets, supplementary product materials.

## Features

**File management in admin panel**

- Upload files via drag-and-drop or standard file dialog
- Progress bar during upload
- Custom description for each file
- Supports both new and legacy product editors
- Manual drag-and-drop reordering of attached files
- Attach an already-uploaded file to multiple products without re-uploading

**Storefront display**

- File block is rendered via one of the hooks: `frontend_product.block` or `frontend_product.block_aux`
- Alternatively, insert into any template location using the built-in helper
- **Design theme template (recommended):** create a file named `plugin.syrattach.attachments.html` in your active design theme folder — the plugin will automatically find and use it
- If the theme file is absent, the built-in default template is used
- The template has access to the `$attachments` variable — an array of files with fields: `id`, `name`, `ext`, `description`, `size`, `url`

**CSV import**

- Files can be attached to products during CSV import
- Upload the files to `wa-data/public/site/syrattach` beforehand and reference them by filename in the CSV

## Requirements

- PHP 7.4 or higher
- Webasyst Framework 3.0
- Shop-Script 10.0 or higher

## Links

- [Plugin page on Webasyst Market](https://www.webasyst.ru/store/plugin/shop/syrattach/)
- [Changelog](CHANGELOG.md)
- [License](LICENSE)

## Developer

Sergey Rodovnichenko — serge@syrnik.com
