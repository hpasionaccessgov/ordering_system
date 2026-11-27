---
slug: rbac-role-matrix
title: RBAC role matrix
kind: feature
domain: auth
status: active
summary: Role editor labels recent RBAC permissions; loyalty report access is enforced.
aliases: []
entry:
  - screen /admin/roles
files:
  - views/admin-user/roles.php
  - services/RbacPermissionPolicy.php
tests:
  - tests/Unit/Services/RbacPermissionPolicyTest.php
data: []
config: []
connections:
  - report-filters: ReportController::actionLoyalty and actionExport enforce the loyalty.report permission defined here.
hubs:
  - hub-role-navigation: sidebar entries stay gated by the permissions rendered in this matrix.
updated: 2026-11-27
---

# RBAC role matrix

## Summary

The admin role editor groups and describes every available permission, including loyalty, rewards, reports and the order board. The loyalty report now checks its own permission at the controller, not only in the sidebar.

## Example use

http://localhost:8080/admin/roles

`vendor/bin/codecept run Unit --filter RbacPermissionPolicyTest`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| screen /admin/roles | `AdminUserController::actionRoles` | screen, user.assignRole | Renders the permission matrix. |
| screen /reports/loyalty | `ReportController::actionLoyalty` | screen, loyalty.report | Shared report action with an extra guard. |

## Chain

- entry: `views/admin-user/roles.php` reads permissions and roles from `authManager`.
- logic: `RbacPermissionPolicy` normalizes module index dependencies before saving.
- data: DbManager stores permissions and role children.
- presentation: permission groups, descriptions and role presets render in the matrix.
- config: no new config source; built-in permissions are seeded by migrations.

## Flow

1. The role editor loads every permission and built-in role from `authManager`.
2. `permissionModuleLabels` groups the new loyalty, reward and report modules.
3. `permissionDescriptions` explains each permission instead of showing its raw name.
4. `RbacPermissionPolicy` keeps dependent permissions in sync when an index checkbox changes.
5. Saving a role normalizes the selected permission names.
6. `ReportController::requireLoyaltyReportAccess` blocks direct loyalty report access without `loyalty.report`.

## Gotchas

- Admin already holds every permission; staff reward grants come from `m261127_130000_grant_staff_reward_permissions`.
- `reports.view` opens the shared report pages but does not imply `loyalty.report`.
- Permissions without an `.index` name rely on the module toggle, not `expandIndexPermission`.
- Descriptions in the view can improve labels without a migration; RBAC descriptions in the database remain raw names.

## Tests

The focused test checks role-matrix labels and descriptions, policy behavior and the loyalty report guard. Live QA confirms the role editor has no uncategorized `other` group and role counts are unchanged.

## Changelog

- 2026-11-27 (feat, local): staff could not manage rewards -> reward.* permissions were admin-only -> granted staff index, view, create and update, not delete. Test: RewardRedemptionTest
- 2026-11-27 (fix, local): recent permissions showed raw names under `other` -> matrix lacked metadata -> added labels, descriptions and a loyalty report guard. Test: RbacPermissionPolicyTest
