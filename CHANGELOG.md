# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.0.0]

### Added
- File upload support in new product editor (#34.4)
- Progress bar for file uploads
- File upload via file input selection
- Support for drag-and-drop uploads in old product editor
- Automatic file addition to list after upload
- GitHub Actions workflows for automated release and PHP compatibility checks
- README.md (Russian) and README_en.md (English) with full feature descriptions and cross-links (#34.6)
- LICENSE and LICENSE_ru files with Webasyst EULA (#34.6)
- CHANGELOG.md following Keep a Changelog standard (#34.6)
- Migrated product editor frontend to Vue 3 + TypeScript + Vite (#34.8)
- Manual drag-and-drop reordering of attached files in the product editor (#34.5)
- "Attach existing file" button opens a side drawer with filename search — one file can be linked to multiple products without re-uploading (#34.9)
- `shopSyrattachLinkModel` — model for `shop_syrattach_links`.
- `shopSyrattachFileModel::getByEntity(entity_type, entity_id)` — generic entity file list.
- `shopSyrattachFileModel::deleteByEntity(entity_type, entity_id)` — batch detach for hook handlers.
- `shopSyrattachPlugin::getFilePath()` — filesystem path resolver (dual-mode: old/new storage).

### Changed
- Minimum requirements updated: PHP 7.4+, Webasyst Framework 3.0, Shop-Script 10.0+ (strict)
- Improved JavaScript and CSS loading mechanism
- Refactored file upload handling for old product editor (#34.3)
- Enhanced Premium version compatibility
- Reorganized source assets into proper directory structure
- Added LICENSE files to exclude configuration for distribution
- Delete confirmation now uses built-in $.wa.confirm() UI 2.0 dialog (#34.8)
- **Refactored storage architecture**: file storage is now separated from entity attachments.
  - New table `shop_syrattach_links` binds one file to multiple entities (products, orders, etc.) each with its own sort order and description.
  - New-style uploaded files are stored in a central location (`attachments/files/{id}/`) instead of inside the product directory. Old files remain in-place — no migration of files on disk.
  - `description` and `sort` are now stored per link (in `shop_syrattach_links`), not per file.
- `shopSyrattachFileModel::delete()` now removes the entity link; the physical file is deleted only when no links remain.
- `shopSyrattachFileModel::add()` accepts `entity_type` parameter (default `'product'`); upload controller also accepts `entity_type`/`entity_id` POST fields alongside the legacy `syrattach_product_id`.
- API responses use link `id` as the primary identifier instead of file `id` — transparent to existing JS.
- `productDelete` hook now removes only links (and orphaned new-style files); old-style files in the product directory continue to be cleaned up by Shop-Script.

## [2.0.0] - 2023-06-09

### Changed
- Complete rewrite with Vue 2 frontend (for Shop-Script 9)
- New admin interface redesign
- Improved file management system

## [1.2.0]

### Added
- Support for multiple file uploads

## [1.1.0]

### Added
- Initial release
