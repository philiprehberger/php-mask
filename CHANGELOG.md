# Changelog

All notable changes to `php-mask` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2026-03-31

### Added
- URL masking via `Mask::url()` with query parameter and credential masking
- JWT masking via `Mask::jwt()` preserving header for debugging
- Declarative `RedactionPolicy` for defining reusable masking rules with field paths, patterns, and wildcards

## [1.1.3] - 2026-03-31

### Changed
- Standardize README to 3-badge format with emoji Support section
- Update CI checkout action to v5 for Node.js 24 compatibility
- Add GitHub issue templates, dependabot config, and PR template

## [1.1.2] - 2026-03-23

### Changed
- Standardize README requirements format per template guide

## [1.1.1] - 2026-03-23

### Fixed
- Remove decorative dividers from README for template compliance

## [1.1.0] - 2026-03-22

### Added
- `iban()` method for IBAN masking (shows country code and last 4 digits)
- `custom()` method for flexible masking with configurable visible start/end lengths
- `arrayRecursive()` method for masking values in nested arrays using dot notation paths

## [1.0.3] - 2026-03-17

### Changed
- Standardized package metadata, README structure, and CI workflow per package guide

## [1.0.2] - 2026-03-16

### Changed
- Standardize composer.json: add type, homepage, scripts

## [1.0.1] - 2026-03-15

### Changed
- Standardize README badges

## [1.0.0] - 2026-03-15

### Added
- Email masking with local and domain part redaction
- Phone number masking with country code and last-4 preservation
- Credit card masking showing first 4 and last 4 digits
- IP address masking for IPv4 and IPv6
- Generic string masking with configurable visible characters
- Deep array masking with recursive key matching
- JSON masking with parse, mask, and re-encode round-trip
- Configurable mask character via `MaskConfig`
