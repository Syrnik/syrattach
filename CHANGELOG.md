# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

### Changed
- Minimum requirements updated: PHP 7.4+, Webasyst Framework 3.0, Shop-Script 10.0+ (strict)
- Improved JavaScript and CSS loading mechanism
- Refactored file upload handling for old product editor (#34.3)
- Enhanced Premium version compatibility
- Reorganized source assets into proper directory structure
- Added LICENSE files to exclude configuration for distribution
- Delete confirmation now uses built-in $.wa.confirm() UI 2.0 dialog (#34.8)

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
