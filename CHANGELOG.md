# Changelog

All notable changes to this module will be documented in this file.

## [1.1.0] - Unreleased

### Changed

- Keep the public Twig `page`, `theme`, and Vite URL behavior compatible with the current `Hippo.Core` multisite runtime, but add explicit fallback to the default CMS behavior when the helper is unavailable so the integration remains temporary and removable.

### Fixed

- Scope CMS theme template cache keys to the theme filesystem path to prevent Redis cache collisions between themes with identical layout or partial names.

## [1.0.0] - 2026-04-18

### Added

- Fork this module from Winter CMS `1.2.12` since commit `ea0d979e9e` as the initial Hippo baseline.
