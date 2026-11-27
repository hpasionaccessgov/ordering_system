---
slug: hub-audit-columns
title: Audit user columns
kind: hub
domain: admin
status: active
summary: Nullable created_by and updated_by columns with BlameableBehavior on app models.
implicit: []
exposed_as: [created_by, updated_by]
files:
  - migrations/m261119_120000_add_audit_user_columns.php
  - migrations/m261127_000000_add_missing_audit_user_columns.php
  - models/
  - services/ShoppingCart.php
tests:
  - tests/Unit/Models/AuditColumnsTest.php
updated: 2026-11-27
---

# Audit user columns

## Summary

Audit columns record which user created and last changed a row. BlameableBehavior fills them on insert and update. Nullable integers allow rows written by system tasks with no signed-in user.

## Contract

- Application tables have nullable integer `created_by` and `updated_by`.
- ActiveRecord models with those columns attach `BlameableBehavior`.
- `InventoryTransaction` keeps its explicit `created_by`; the behavior fills only `updated_by`.
- RBAC tables store the columns, but Yii `DbManager` does not populate them.
- The `migration` table is framework bookkeeping and stays unchanged.

## Example use

`php yii migrate`

`vendor/bin/codecept run --env php-builtin`

## Gotchas

- Add columns idempotently; existing databases may already have one of the two.
- `safeDown()` must not drop the pre-existing `created_by` on `inventory_transactions`.
- BlameableBehavior overwrites an explicitly set `created_by` unless `createdByAttribute` is false.

## Changelog

- 2026-11-27 (migration, local): 14 app tables lacked audit user columns -> original audit migration only covered six tables -> added idempotent migration, BlameableBehavior and tests. Test: AuditColumnsTest
