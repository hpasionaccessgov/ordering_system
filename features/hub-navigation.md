---
slug: hub-navigation
title: Centralized browser navigation
kind: hub
domain: public
status: active
summary: Single map of route keys to controller routes and pretty URL patterns.
implicit: [public, auth, catalog, cart, orders, kitchen, inventory, loyalty, notifications, reports, admin, staff, customer]
exposed_as: [Navigation::url, Navigation::urlRules]
files:
  - services/Navigation.php
  - config/web.php
tests:
  - tests/Unit/Services/NavigationTest.php
  - tests/Functional/NotificationCest.php
updated: 2026-11-27
---

# Centralized browser navigation

## Summary

Navigation holds every browser-facing route key, its controller route ID and its pretty URL pattern. `config/web.php` builds `urlManager.rules` from the same map. Views, widgets and redirects should call `Navigation::url()` instead of hardcoding paths.

## Contract

- Route keys are constants on `Navigation`; `Navigation::route()` rejects unknown keys.
- A pattern of `null` keeps the legacy query-style URL for that route.
- Every pattern must resolve to an existing controller action with matching HTTP verbs.
- GET links must never point at POST-only actions.
- Changing a pattern changes generated links across every role and layout.

## Example use

`Navigation::url(Navigation::CART_INDEX)`

`vendor/bin/codecept run Unit --filter NavigationTest`

## Gotchas

- Yii converts an unknown action from a matching URL rule into a 404, not an `InvalidRouteException` in the browser.
- `VerbFilter` returns 405 for GET links aimed at POST-only actions.
- Unused constants can still generate dead URLs; remove them when no action exists.
- Pattern parameter names must match the action signature, including `$report` and `$format`.

## Changelog

- 2026-11-27 (fix, local): stale notification fixture failed -> old selectors and retention cache -> assertions and cache reset updated. Test: NotificationCest
- 2026-11-27 (feat, local): API routes needed JSON paths -> browser map cannot own them -> merged Module::urlRules into urlManager while Navigation still owns browser links. Test: NavigationTest
- 2026-11-27 (fix, local): cart form and notification links reached dead or POST-only routes -> stale map entries and wrong constants -> corrected cart route, added GET view key and removed dead rules. Test: NavigationTest
