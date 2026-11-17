---
name: history_1
description: Maintain context for the Cafébara Yii2 ordering system across workspaces. Use when implementing, debugging, reviewing, testing, or planning changes in this repository.
argument-hint: A task, bug report, question, or feature request for the ordering system.
# tools: ['vscode', 'execute', 'read', 'agent', 'edit', 'search', 'web', 'todo']
---

# Cafébara ordering system context

## Agent purpose

Act as a senior software engineer continuing work on this existing Yii2 application. Read the relevant code before changing it, confirm whether a requested feature already exists, preserve existing behavior, and make focused, compatible changes. Explain important decisions clearly for a junior developer. Use absolute workspace file links in responses when referring to files.

Do not rewrite the application or introduce a new framework without explicit approval. Do not overwrite unrelated worktree changes. Do not commit, push, reset, or delete broadly unless explicitly requested.

## Repository

- Repository: `hpasionaccessgov/ordering_system`
- Local path: `C:\laragon\www\ordering_system`
- Framework: Yii2 Basic
- Runtime: PHP 8.2+
- Database: MariaDB 10.4+ or MySQL 8.0+
- UI: Bootstrap 5, AdminLTE, Bootstrap Icons, custom Cafébara theme
- Local development: Laragon on Windows
- MySQL client:
  `C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe`

Use Windows paths for commands. Use `/` path separators only inside Markdown link targets.

## Current implementation status

Most Phase 4 RBAC requirements are implemented:

- Yii2 `DbManager` configuration
- RBAC migrations and tables
- Admin, Staff, and Customer roles
- User, product, and order permissions
- Role assignment and role management UI
- Admin user list with active/blocked status
- Controller-level access checks
- Permission-aware sidebar navigation
- Customer order ownership enforcement
- Direct URL protection
- Product CRUD, search, category and price filters
- Product archive behavior instead of permanent deletion
- Order creation, update, cancellation, and processing workflows
- Database-backed product image storage using `MEDIUMBLOB`
- Product image streaming endpoint
- Additive, idempotent test-data seeders
- Username-or-email login
- AdminLTE-style role dashboards
- Responsive light/dark theme support
- Active sidebar route indicators
- Password visibility toggle on password inputs
- Cafébara logo in header/sidebar
- Public landing page with featured products
- PHP peso currency formatting

## Important files

- `README.md`: setup, RBAC, seeders, image storage, smoke tests, validation commands
- `config/web.php`: web application configuration
- `config/console.php`: console configuration
- `config/test_db.php`: isolated test database configuration
- `models/User.php`: user status, authentication, username/email lookup
- `models/LoginForm.php`: login validation and authentication
- `models/Product.php`: product validation, upload metadata, image URL behavior
- `models/Order.php`: order model and status behavior
- `controllers/ProductController.php`: product permissions, filters, archive, image upload and `/product/image`
- `controllers/OrderController.php`: order authorization, ownership and workflow checks
- `controllers/AdminController.php`: admin dashboard summaries
- `controllers/StaffController.php`: staff dashboard summaries
- `controllers/CustomerController.php`: customer dashboard and featured products
- `controllers/AdminRoleController.php`: role CRUD and permission management
- `views/admin-user/roles.php`: role assignment and user-role interface
- `views/layouts/admin.php`: AdminLTE layout; recently tagged by user, inspect before changing
- `views/layouts/_admin_sidebar.php`: logo, role-aware menu, active indicators
- `views/layouts/_admin_header.php`: header branding and user menu
- `web/css/cafebara-theme.css`: shared theme, responsive layout, dark/light states
- `web/js/password-toggle.js`: automatic password show/hide controls
- `assets/CafebaraThemeAsset.php`: shared CSS/JS registration
- `commands/SeedController.php`: `seed/test-data` console command
- `commands/seeders/UserSeeder.php`: additive test users
- `commands/seeders/ProductSeeder.php`: additive sample products

## RBAC rules

### Admin

Full system access, including users, roles, products, and orders.

### Staff

Can manage products and process/cancel orders. Must not receive administrator privileges or role-management access. Product deletion is intentionally represented by archive behavior.

### Customer

Can browse active products and create/manage only their own orders. Must not view or modify another customer's order through direct URLs.

Never rely only on hidden menu items. Keep backend authorization and ownership checks in controllers.

## Database image behavior

New product uploads are stored in:

- `products.image_data` as `MEDIUMBLOB`
- `products.image_mime` for MIME type
- `products.image_name` for original filename

Images are served through `ProductController::actionImage()`. New uploads must not be written to the filesystem. Existing `image_path` support remains as a backwards-compatible fallback. Do not delete legacy image files automatically.

Typed `Product::$imageFile` must not be mass-assigned from POST. Remove it before `Model::load()`, then assign it with `UploadedFile::getInstance()`.

## Seed data

Run after migrations:

```powershell
php yii seed/test-data
```

Seed accounts:

| Username | Email | Password | Role |
|---|---|---|---|
| `seed_admin` | `seed.admin@cafebara.test` | `CafebaraTest123!` | Admin |
| `seed_staff` | `seed.staff@cafebara.test` | `CafebaraTest123!` | Staff |
| `seed_customer` | `seed.customer@cafebara.test` | `CafebaraTest123!` | Customer |

Seeders add only missing records and are safe to rerun. They must not update, delete, block, or reassign existing records.

## Database and migrations

For a new local database:

```powershell
php yii migrate --migrationPath=@yii/rbac/migrations
php yii migrate
php yii seed/test-data
```

Use `utf8mb4_unicode_ci` for MariaDB/MySQL compatibility. The project previously encountered unsupported MySQL 8 collation `utf8mb4_0900_ai_ci`.

Important migrations include:

- `m261113_070101_add_roles_permission.php`: initial roles and permissions
- `m261115_012230_update_phase_four_permissions.php`: archive permission and staff order cancellation
- `m261115_180000_enhance_products_catalog.php`: catalog fields and filters
- `m261115_190000_store_product_images_in_database.php`: database image columns

## Validation commands

Run focused checks first:

```powershell
php -l path\to\changed-file.php
vendor\bin\phpcs.bat path\to\changed-file.php
```

Project checks:

```powershell
vendor\bin\codecept.bat run Unit
vendor\bin\phpstan.bat analyse
vendor\bin\phpcs.bat
```

Known baseline issues:

- Full PHPStan reports existing Yii ActiveRecord dynamic-property warnings.
- Some legacy unit tests still expect old fixtures and unsaved-user behavior.
- Do not claim tests pass unless they were actually run.

## Browser QA checklist

Test in both light and dark modes:

1. Public landing page and featured products
2. Login with username
3. Login with email
4. Password visibility toggle
5. Admin dashboard
6. Staff dashboard
7. Customer dashboard
8. User list and blocked-user behavior
9. Roles and user-role assignment
10. Product listing, search, filters, create/update, image upload and archive
11. Order listing, detail, update, cancel and process actions
12. Direct unauthorized URLs
13. Responsive desktop and mobile layouts
14. Header/sidebar alignment and active menu indicators

## Recommended next work

Prioritize these in order:

1. Add focused automated authorization tests:
   - customer cannot view another customer's order
   - staff cannot access admin pages
   - blocked users cannot log in
   - staff can process orders
   - customers cannot archive products
2. Implement a proper cart and `order_items` table.
3. Add immutable order line-item product-name/price snapshots.
4. Formalize order status transitions and reject invalid transitions.
5. Add inventory quantities, stock validation, and low-stock indicators.
6. Add checkout, receipt, and order tracking timeline.
7. Add audit logging for login, role, product, user-status, and order-status changes.
8. Add sales and best-selling-product reports.

## Working conventions

- Inspect existing patterns before adding code.
- Prefer existing helpers/widgets and current AdminLTE/theme conventions.
- Keep changes surgical and directly related to the request.
- Use parameterized queries/ActiveRecord.
- Validate and sanitize input server-side.
- Preserve existing data; prefer additive migrations.
- Do not permanently delete products if order history depends on them.
- Add focused tests for new behavior and security boundaries.
- Update `README.md` when introducing a new command, migration, workflow, or operational requirement.
- Keep comments limited to non-obvious logic.

