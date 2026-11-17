---
name: history_
description: Maintain recent Cafébara Yii2 project and RBAC implementation context across workspaces.
argument-hint: A task, bug report, question, or feature request for the ordering system.
---

# Cafébara recent conversation history

## Agent purpose

Continue work on the existing Cafébara Yii2 ordering system. Inspect the current implementation before changing it, preserve unrelated worktree changes, and use focused fixes that match the existing Yii2, AdminLTE, Bootstrap, and PHP conventions.

## Repository

- Repository: `hpasionaccessgov/ordering_system`
- Workspace: `C:\laragon\www\ordering_system`
- Framework: Yii2 Basic
- Runtime: PHP 8.4 in Laragon
- Database: MySQL/MariaDB with Yii2 `DbManager` RBAC
- Main UI: Bootstrap 5, AdminLTE, Bootstrap Icons, custom Cafébara theme

## Recent conversation outcomes

### Configurable role permissions

- The role editor is intended to let administrators configure permissions on built-in and custom roles.
- Every permission checkbox remains editable, including `admin.access` and `user.assignRole`; do not reintroduce a blanket lock on these permissions.
- Permission saves are handled through `AdminRoleController` and validate that each submitted permission exists.
- The role editor displays permissions grouped by module.
- Granting `admin.access` or `user.assignRole` to a role is intentionally powerful and must remain clearly warned about in the UI.

### Separate list and record-detail access

- `*.index` is the permission for a module's list/table page.
- `*.view` is the permission for opening an individual record's details.
- These remain separate permissions at runtime. The role editor treats `*.index` as the module-level toggle: checking it selects the module's operational permissions, and unchecking it clears them. `*.view` remains the individual-record/detail permission.
- Implemented index permissions:
  - `order.index` for `OrderController::actionIndex()`
  - `product.index` for `ProductController::actionIndex()`
  - `user.index` for `AdminUserController` index/list actions
- Detail access is enforced by `order.view`, `product.view`, and `user.view`. Product detail remains publicly readable by guests as an existing behavior.
- `AdminUserController::actionView()` provides the user detail endpoint. User update requires both `user.update` and `user.view`.
- List, detail, and permitted action links should only be rendered when the matching permission allows the destination.

### Permission dependencies

The role editor synchronizes module permissions when a module-index permission is toggled. The same normalization runs server-side when role permissions are saved, so disabling JavaScript cannot retain operational permissions without the corresponding index permission:

- `user.view` synchronizes `user.index`, `user.create`, `user.delete`, and `user.update`.
- `product.view` synchronizes `product.index`, `product.create`, `product.update`, `product.delete`, and `product.archive`.
- `order.view` synchronizes `order.index`, `order.create`, `order.update`, `order.cancel`, `order.process`, and `order.receipt.print`.

Individual permissions remain editable after the module view checkbox is enabled. Selecting a dependent in the UI also enables its matching view permission. Keep the dependency map in `services/RbacPermissionPolicy.php` synchronized with controller checks.

The role editor also enables the module's `*.index` permission when an action permission such as `user.update` or `product.delete` is selected, so the corresponding module remains visible in navigation. Permission inputs expose descriptions through hover tooltips. The built-in admin role displays a recovery warning when both `admin.access` and `user.assignRole` are unchecked.

The role editor treats `admin.access`, `staff.access`, and `customer.access` as master switches. Enabling one selects its configured feature bundle; disabling one clears that bundle. The built-in admin role's bundle includes administrator, staff, customer, product, order, and user permissions, while customer and staff bundles remain narrower.

### Admin access recovery

- A new migration, `m261117_090000_add_resource_index_permissions.php`, adds the three index permissions, preserves existing list access based on prior permissions, and restores essential permissions on the built-in admin role.
- The migration has been applied in the local development database.
- `services/AdminAccessRecovery.php` restores `admin.access` and `user.assignRole` on the admin role.
- Run this console command from the project root if the role editor becomes inaccessible:

  ```powershell
  php yii rbac/restore-admin-access
  ```

- The recovery helper is also used by `RbacController::actionMakeAdmin()`, admin account creation/role assignment, and `commands/seeders/UserSeeder.php`.
- Recovery restores the admin master permission and its configured administrator bundle, including `user.assignRole`, so the role editor remains usable after accidental lockout.
- The role editor warns administrators about the recovery command. Avoid adding protections that silently re-add every checkbox selection; explicit role configuration remains supported.

## Important files for this work

- `controllers/OrderController.php`: order list, detail, receipt, and update permission enforcement
- `controllers/ProductController.php`: product list/detail and product edit permission enforcement
- `controllers/AdminUserController.php`: user list/detail/edit permission enforcement and admin account assignment
- `controllers/AdminRoleController.php`: role creation, permission validation, dependency normalization, and saves
- `services/RbacPermissionPolicy.php`: role permission dependencies and staff-access checks
- `services/AdminAccessRecovery.php`: recovery of admin role access
- `commands/RbacController.php`: admin recovery and make-admin console commands
- `commands/seeders/UserSeeder.php`: seeded admin recovery
- `migrations/m261117_090000_add_resource_index_permissions.php`: new list permissions and admin recovery migration
- `views/admin-user/roles.php`: grouped role checkboxes and dependency behavior
- `views/admin-user/index.php`, `view.php`, and `update.php`: permission-aware user table and detail/edit pages
- `views/order/index.php` and `view.php`: permission-aware order list and detail links
- `views/product/index.php`, `view.php`, `_form.php`, and `views/shared/_product_card.php`: permission-aware product navigation and actions
- `views/layouts/_sidebar.php`: permission-aware module links

## Recent validation

- Focused Codeception RBAC policy tests passed: 3 tests, 8 assertions.
- Changed PHP files passed `php -l`; `git diff --check` passed.
- `php yii migrate --interactive=0` reported no pending migrations after the new migration was applied.
- `php yii rbac/restore-admin-access` completed successfully.
- Browser QA confirmed role/order/product pages return HTTP 200, role permission checkboxes are not disabled, admin core permissions are present after recovery, and a user detail route returns HTTP 403 without `user.view`.
- Browser QA also confirmed unchecking `order.view` clears dependent `order.update` and `order.receipt.print` checkboxes; the test was reloaded without saving to preserve seeded role data.

## Working notes

- Users who can access a list but not details need `*.index` but not `*.view`; the list should hide detail links and direct detail routes must return forbidden.
- Preserve the separation between `index` and `view` when modifying role checkboxes or controller access rules.
- Role configuration changes can affect every account assigned to that role. Avoid testing by saving changes to shared seeded roles unless their original permissions are recorded and restored.
- Keep the console recovery command available even if all browser-accessible admin permissions are removed.
- `history_1.agent.md` contains broader Cafébara architecture, setup, and legacy project context.
