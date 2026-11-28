---
slug: hub-vendor-assets
title: Composer-installed front-end assets
kind: hub
domain: public
status: active
summary: Shared front-end libraries are installed by Composer and published from vendor paths.
implicit: [public, auth, catalog, cart, orders, kitchen, inventory, loyalty, notifications, reports, admin, staff, customer]
exposed_as: [AssetBundle::sourcePath]
files:
  - assets/AdminLteAsset.php
  - assets/BootstrapJsAsset.php
  - assets/BootstrapIconsAsset.php
  - assets/ChartJsAsset.php
  - assets/OverlayScrollbarsAsset.php
  - composer.json
  - composer.lock
tests: []
updated: 2026-11-28
---

# Composer-installed front-end assets

## Summary

Layouts register asset bundles that publish AdminLTE, Bootstrap, Bootstrap Icons, Chart.js and OverlayScrollbars from Composer-managed `vendor/` directories. `node_modules` is not a runtime dependency.

## Contract

- Asset bundles use `@app/vendor/...` source paths that exist after `composer install`.
- npm libraries come from asset-packagist packages under `vendor/npm-asset/...`.
- Do not point a bundle at `node_modules` or an unmanaged copied directory.
- A new front-end library needs a Composer `require` entry plus a bundle path update in the same change.

## Example use

`composer install`

`http://localhost:8080/`

## Gotchas

- `vendor/` is ignored by Git, so a bundle path without a matching Composer requirement works only on machines with manual copies.
- `fxp-asset` is disabled in `composer.json`; asset-packagist installs npm packages as `npm-asset/*`.
- `AdminLteAsset` still accepts an optional `@app/almasaeed2010/adminlte/dist` fallback for legacy local installs.

## Changelog

- 2026-11-28 (fix, local): asset publishing failed on fresh install -> bundles referenced unmanaged vendor/asset-libs copies -> required Composer packages and repointed bundles. Test: BootstrapIconsAsset
