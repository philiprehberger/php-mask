# Changelog

All notable changes to `php-mask` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
