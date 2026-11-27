---
slug: hub-role-navigation
title: Role-aware app navigation
kind: hub
domain: auth
status: active
summary: Staff lose personal rewards shortcuts; customers and admins keep them, with staff overview and admin RBAC intact.
implicit: [public, customer, staff, admin]
exposed_as: [app header, app sidebar]
files:
  - views/layouts/_header.php
  - views/layouts/_sidebar.php
tests:
  - tests/Unit/Frontend/RoleNavigationTest.php
updated: 2026-11-27
---

# Role-aware app navigation

## Summary

The shared header and sidebar give customers and admins personal loyalty shortcuts, but staff do not get the personal badge or sidebar link. Staff keep the loyalty overview, and admin keeps every permission-gated management entry.

## Contract

- `isCustomer` means `customer.access` without `staff.access` or `admin.access`.
- The header loyalty snapshot requires `isCustomer` or `admin.access`, plus `loyalty.view`.
- Theme toggle and cart button render independently of loyalty; `_cart_button` still checks `cart.index`.
- Sidebar `My rewards` requires `isCustomer` or `admin.access`, plus `loyalty.view`.
- `Loyalty overview` remains visible on `loyalty.transaction.index` for staff and admin.
- Admin sections stay permission-gated; do not replace `$can(...)` checks with role-name hardcoding.
- Staff see the permission-gated Reward catalog link but still no personal rewards shortcut.

## Example use

http://localhost:8080/customer/dashboard

http://localhost:8080/staff/dashboard

`vendor/bin/codecept run Unit --filter RoleNavigationTest`

## Gotchas

- Staff and admin both satisfy `loyalty.view`; staff must stay excluded while admin keeps the personal dropdown.
- The admin role also holds `staff.access`, so a plain `!staff.access` check would hide admin rewards too.
- The theme toggle used to sit inside the loyalty block; moving the loyalty guard must not hide it.
- `My rewards` is a customer shortcut, not the staff loyalty overview or the admin reward catalog.

## Changelog

- 2026-11-27 (fix, local): mobile toggle sat right and profile overflowed -> shared right group and relative nested dropdown -> toggle sits beside brand. Test: RoleNavigationTest
- 2026-11-27 (feat, local): staff had no reward management entry -> reward.index was admin-only -> staff now see the Reward catalog link via RBAC. Test: RoleNavigationTest
- 2026-11-27 (fix, local): admin lost personal rewards dropdown -> staff.access guard excluded admin -> restored header and sidebar rewards for admin. Test: RoleNavigationTest
- 2026-11-27 (fix, local): staff saw personal rewards -> loyalty.view is shared -> gated badge and My rewards on customer-only role, kept overview. Test: RoleNavigationTest
