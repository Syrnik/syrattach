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

## For developers

### Database schema

The plugin uses two tables.

**`shop_syrattach_files`** — physical files

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | PK |
| `product_id` | int\|NULL | `NULL` — new-style (central storage); non-`NULL` — old-style (file inside product directory, legacy) |
| `name` | varchar(255) | Filename |
| `ext` | varchar(255) | Extension |
| `upload_datetime` | datetime | Upload timestamp |
| `size` | int | Size in bytes |
| `description` | text | *Deprecated* — moved to `shop_syrattach_links` |
| `sort` | int | *Deprecated* — moved to `shop_syrattach_links` |

**`shop_syrattach_links`** — file-to-entity bindings

| Column | Type | Description |
|--------|------|-------------|
| `id` | int | PK — **this is the "attachment ID"** used in API and JS |
| `file_id` | int | FK → `shop_syrattach_files.id` |
| `entity_type` | varchar(64) | Entity type (`'product'`, extensible) |
| `entity_id` | int | Entity ID |
| `sort` | int | Display order within the entity |
| `description` | text | Per-attachment description |

One file can be attached to multiple products without duplicating it on disk.

### File storage

**New-style** (`product_id IS NULL`) — central storage:

```
wa-data/public/shop/attachments/files/{file_id}/{filename}
```

**Old-style** (`product_id IS NOT NULL`, legacy) — inside the product directory:

```
wa-data/public/shop/products/{folder}/{product_id}/attachments/{filename}
```

where `{folder}` is computed via `shopProduct::getFolder($product_id)`. Old-style is legacy data created before 3.0.0 and is never produced again: `shopSyrattachFileModel::add()` unconditionally writes `product_id => null`, so both product editor uploads and files attached during CSV import land in central storage. When a product is deleted, Shop-Script cleans up its directory (and old-style files); new-style orphaned files are removed by the plugin itself.

Every file lives in its own directory named after its `id`, created fresh for the row that was just inserted. Name collisions are therefore impossible: files sharing a name coexist, and nothing is renamed or overwritten.

### Plugin helper methods

```php
// Absolute path to the file's directory
shopSyrattachPlugin::getDirectory(?int $product_id, int $file_id): string

// Absolute path to the file (DB row: needs product_id, name, [file_id/id])
shopSyrattachPlugin::getFilePath(array $attachment): string

// Public URL of the file
shopSyrattachPlugin::getFileUrl(array $attachment, bool $absolute = false): string
```

### Models

**`shopSyrattachFileModel`** (`shop_syrattach_files`):

- `add(int $entity_id, waRequestFile $file, string $entity_type = 'product'): array` — upload and attach a file; returns the link row data
- `getByEntity(string $entity_type, int $entity_id, bool $with_urls = false): array` — files attached to entity, ordered by `sort`
- `delete(int $link_id): void` — detach a file; deletes the physical file and record when no links remain
- `deleteByEntity(string $entity_type, int $entity_id): void` — remove all attachments for an entity (used in the `product_delete` hook)
- `search(string $query, string $entity_type, int $entity_id): array` — files not yet attached to the given entity

**`shopSyrattachLinkModel`** (`shop_syrattach_links`) — helper model for managing bindings.

> **Important:** in API responses and all JS code `id` always refers to `shop_syrattach_links.id` (link id), not `shop_syrattach_files.id`. This allows one file to be correctly attached to multiple products simultaneously.

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
