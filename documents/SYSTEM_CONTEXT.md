# Cafébara Ordering System — Implementation Context

Handoff document describing what is implemented in this repository so far. Written for an AI continuing work here. Treat it as a map, not as a guarantee that every browser flow was verified.

## 1. Stack and layout

- Yii2 **basic** app, PHP >= 8.3, MySQL (`config/db.php`, values from `.env` via phpdotenv).
- Bootstrap 5.3 + Bootstrap Icons + AdminLTE (admin/staff shell). Chart.js for order statistics.
- Codeception suites under `tests/{Acceptance,Functional,Unit}` (cannot currently run in this environment — see §10).
- Key config: `config/web.php` (components, bootstrap), `config/params.php` (mail), `config/console.php`.
- One codebase serves two shells:
  - Public/customer: `SiteController`, `views/site/*`, `views/layouts/_public_header.php`.
  - Admin/staff: `AdminController`, `StaffController`, grid CRUD controllers, `views/layouts/_header.php` + `_sidebar.php`.

## 2. Data model (migrations live in `migrations/`)

| Area | Tables / notes |
|---|---|
| Identity | `users`, RBAC `auth_*` tables (Yii `DbManager`) |
| Catalog | `products`, `categories`; every product requires an active category; product images stored in DB (`image_data`) or as path, served by `/product/image?id=` |
| Cart | Session cart plus `cart_items` persistence for logged-in users; max quantity 99 |
| Inventory | `inventory` (name, description, unit string, current/minimum stock, status, `low_stock_notified` dedupe flag, timestamps) and `inventory_transactions` (signed quantity, type, remarks, `created_by`, `created_at`) |
| Orders | `orders`, `order_items`, `order_status_history`; snapshots of `product_name`/`subtotal`; order numbers `CB-YYYYMMDD-000001`; `checkout_token` for confirmation |
| Notifications | `notifications` (renamed from `order_notifications`) with `type`, `message`, `target_route`, `target_id`; `order_id`/`status` kept nullable for legacy rows |
| Audit | `created_by`, `updated_by`, `created_at`, `updated_at` on user/category/order; sortable via `User::usernameSortExpression()` correlated expressions |
| Logging | `activity_log` with `event`, `route`, `user_id`, `created_at` |
| Settings | `application_settings` single row (`id = 1`) |

Order statuses: `pending → confirmed → processing → completed`, plus `cancelled`.

## 3. RBAC and permissions

- Roles: `admin`, `staff`, `customer`, plus access flags `admin.access`, `staff.access`, `customer.access`.
- Permission families: `user.*`, `category.*`, `inventory.*`, `product.*` (includes `archive`, `delete`), `order.*` (includes `receipt.print`, `process`, `cancel`), `cart.*`, `activityLog.*`, `notification.*`, `settings.*`, `user.assignRole`.
- Inventory: `inventory.index/view/create/update/delete` granted to `admin` + `seed`; only `index/view/update` to `staff`; none to `customer`. The sidebar item is hidden from customers.
- `services/RbacPermissionPolicy.php` is the single source for permission dependencies:
  - Selecting `*.index` auto-selects that module's child permissions.
  - Removing `*.index` or `*.view` strips its dependents.
  - Used by `AdminRoleController::actionUpdateMatrix()` and the role UI at `/admin-user/roles`.
- `AdminAccessRecovery` restores admin access when migrations rewire roles.
- **Important live-role detail**: staff has `category.view` again, granted by the applied migration `m261120_200000_grant_staff_category_view.php`. Without it `CategoryController::actionIndex()` shows an empty list and `/category/view` 403s. `RbacPermissionPolicy` also re-adds `category.view` whenever `category.index` is selected, but an admin can still remove it manually from the matrix.
- Customers browse products and use the cart/notification inbox; staff/admin get management permissions.

## 4. Functionality by module

### Public site (`SiteController`, `views/site`)
- Home with featured products (3-column cards, paginated), menu page (6 cards/page), about, contact form that sends mail, register, login, logout.
- Profile, change password, account deactivate, role-based dashboard landing.
- Login throttling, lockout, and idle timeout come from `ApplicationSettings` + `services/SessionUser.php`; `SessionController` exposes `status`/`activity` endpoints.
- Public header (`_public_header.php`) collapses on mobile; its toggler now uses `bi bi-list` inside a `btn btn-primary`, matching the admin sidebar toggle. Bootstrap's `navbar-toggler` class is retained so it hides on desktop.

### Products (`ProductController`, `views/product`, `shared/_product_card.php`)
- Index with search/filter/sort, detail, create, update, archive, restore, permanent delete.
- Image upload stored in DB and streamed by `actionImage`.
- Cards use `Product::getImageUrl()` with a cup-icon fallback, hover lift/zoom overlay, and a stretched link to the detail page.
- Audit info shown only to admins/staff.
- Notifications on create/update/archive/restore/delete (see §5).

### Categories (`CategoryController`, `views/category`)
- Index, view, create, update, delete with permission gates and audit columns.
- Create/update notifications deep-link to `/category/view?id=…`; delete notifications point to `/category/index` because the record is gone.

### Inventory (`InventoryController`, `views/inventory`, `services/InventoryService.php`)
- CRUD for stock items: list with search/status filter, view with stock history, create (admin), update (staff/admin), delete (admin) with a guard: items with transactions cannot be deleted, set Inactive instead.
- `low_stock_notified` flag prevents duplicate alerts; it is re-armed when stock recovers above the minimum. Alerts use `NotificationService::notifyManagers()` with type `inventory_low_stock`, target `/inventory/view?id=`, and text `Low stock alert: Milk has only 3 liters remaining.`
- `InventoryService::create/update/recordMovement()` wrap stock changes and transaction inserts in a DB transaction. Recorded types: `STOCK_IN` (initial stock), `ADJUSTMENT` (CRUD stock change), `DEDUCT`, `WASTAGE` (via `recordMovement()` for future order/wastage integration).
- Low stock is `current_stock <= minimum_stock`; flagged rows get a warning badge and `table-danger` row, and dashboards render `views/shared/_inventory_alert.php`.
- `InventoryQuery` adds `active()`, `lowStock()`, `outOfStock()` scopes. No UI exists yet for posting DEDUCT/WASTAGE movements — call the service.

### Cart (`CartController`, `views/cart/index.php`)
- Add, batch add, update quantity, remove, checkout; unavailable products pruned.
- Cart page renders the same image-first card feel as the catalog: `.cart-item-media` wrapper, real `<a href="/product/view?id=…">`, `ToolTip` on the link, and a click-through zoom overlay on hover/focus.
- Batch cart is a static pill in the top-right of product listings.

### Orders (`OrderController`, `views/order`)
- Create from cart or direct product add, view, print receipt, confirmation page, status update, process, cancel.
- `OrderAccessPolicy` restricts customers to their own orders while staff/admins can view all.
- Status transitions and side effects in `services/OrderStatusService.php` and `OrderService.php`; status history recorded.
- Order views mark related unread notifications as read.

### Notifications (generic, not order-only)
- `models/Notification.php`, `NotificationSearch.php`, `views/notification/{index,_search}.php`, `NotificationController.php`, `services/NotificationService.php`.
- Dropdown in `views/layouts/_notifications_link.php`: unread only, max 5, theme-aware vars, hidden below 576px (bell then links straight to the inbox). Clicking an item marks it read and redirects through `Notification::targetUrl()`.
- Inbox `/notification/index` is the full history with search/filter; `actionDelete` is POST-only with confirmation; `notification.delete` permission enforced.
- `actionUnreadCount()` is polled by the bell every 30s.
- `getLabel()` (not `getMessage()` — the DB column shadows a getter) renders the stored message or legacy order status text.
- `Notification::visibleTo()` limits customers to their own orders plus non-order alerts; managers see all.

### Users and roles (`AdminUserController`, `AdminRoleController`)
- User list/create/view/update/delete, role assignment, role CRUD, permission matrix with description tooltips.
- `views/admin-user/roles.php` uses the shared `ToolTip` widget for permission labels; its old inline bootstrap-tooltip init was removed.

### Activity log (`ActivityLogController`, `views/activity-log`)
- Logs login, logout, and GET page visits with route (`ActivityLogger` as app bootstrap). Login/logout Page is stamped from `$app->controller?->route`; no historical backfill.
- Admin-only index/view with filters, sorting, pagination. `/admin/activity` redirects to `/activity-log/index`.

### Settings (`SettingsController`, `views/settings`)
- Card and table page sizes, idle timeout, failed-login threshold/reset/lockout windows, activity-log retention, notification retention (1–3650 days).
- `ApplicationSettings::pagination()` pins `pageSizeLimit` to the configured size so users cannot change it via query string.

### Admin/staff dashboards
- Role-specific dashboards with counts and Chart.js order-status chart (`views/shared/_order_status_chart.php`).
- Admin/staff dashboards also show Inventory items, Low stock items and Out of stock cards plus the low-stock alert partial.

## 5. Notification producers and audiences

`NotificationService::notifyAll()` sends to active users with `notification.index`; `notifyManagers()` additionally requires `admin.access` or `staff.access`. The acting user is always excluded; inserts are batched.

| Event | Audience | Target |
|---|---|---|
| `product_created` | all eligible users | `/product/view?id=` |
| `product_updated`, `product_archived`, `product_restored`, `product_deleted` | staff/admin | `/product/view?id=` |
| `category_created`, `category_updated` | staff/admin | `/category/view?id=` |
| `category_deleted` | staff/admin | `/category/index` |
| `inventory_low_stock` | staff/admin (actor excluded) | `/inventory/view?id=` |
| order status events (from `OrderStatusService`) | order participants/managers | `/order/view?id=` |

Legacy rows were backfilled with `type='order'`, `target_route='/order/view'`, `target_id=order_id`.

## 6. UI conventions and widgets

- `widgets/ToolTip.php` is a helper: `app\widgets\ToolTip::options($text, $htmlOptions, $placement)` adds `data-bs-toggle="tooltip"`, `title`, placement, and registers the one-time Bootstrap init JS. Applied to theme toggle, sidebar toggle, bell icon, cart image link, category/user row actions, and role permission labels.
- `color-mode.js` calls Bootstrap's `setContent()` so the theme-toggle tooltip text follows light/dark state.
- Other widgets: `AdminGridView`, `AdminActiveForm`, `Alert`, `ConfirmModal` (triggers are `data-confirm-modal="…"`).
- AdminLTE sidebar groups are collapsible and highlight the parent when a child is active (`.menu-open.has-active-child`).
- `admin-panel` cards have internally scrolling bodies; list/detail views share a header + `DetailView` card-table look.
- Light/dark theme via CSS variables in `web/css/cafebara-theme.css`; notification dropdown and tooltip colors adapt.

## 7. Services (`services/`)

| Service | Purpose |
|---|---|
| `ActivityLogger` | Login/logout/visit logging and daily cached cleanup of expired logs and notifications |
| `AdminAccessRecovery` | Repairs admin role access after RBAC migrations |
| `InventoryService` | Transactional stock changes, movement history, low-stock notification dedupe |
| `NotificationService` | Recipient selection and batched notification inserts |
| `OrderAccessPolicy` | Own-orders vs all-orders access |
| `OrderItemSelection` | Product selection/validation for order forms |
| `OrderService`, `OrderStatusService` | Checkout/order creation and status workflow + notifications |
| `OrderStatistics` | Dashboard counts/chart data |
| `ProductCatalogPolicy` | Catalog visibility rules |
| `RbacPermissionPolicy` | Permission dependency normalization |
| `SessionUser` | Idle timeout and login throttle integration |
| `ShoppingCart` | Session/DB cart operations |

## 8. Migrations worth knowing

- `m261120_190000_generalize_notifications.php` renames `order_notifications` → `notifications` and adds generic columns. Its rollback is guarded and refuses to discard generic rows.
- Index and FK names intentionally retain the **old** `order_notifications` prefix so rollback and retention migrations keep working.
- `m261120_180000_add_notification_retention.php` detects the table dynamically; it is currently **untracked** in git.
- `m261119_000536_add_staff_category_permissions.php` already intended the `category.view` grant; `m261120_200000_grant_staff_category_view.php` corrects live-role drift and is reversible.
- `m261123_083800_create_inventory_table.php` created the base table; `m261124_000000_refine_inventory_schema.php` changes `unit` to a string, adds `description`, `low_stock_notified`, and creates `inventory_transactions`; `m261124_010000_add_inventory_permissions.php` creates and revokes the `inventory.*` permissions. The pair is reversible (`migrate/down 2` → `migrate/up` verified).
- Locally applied: schema migrated up/down/up successfully; DB currently has the `notifications` table with 27 backfilled order rows and an empty inventory catalog.

## 9. Tests

- Acceptance: `AboutCest`, `ContactCest`, `HomeCest`, `LoginCest`.
- Functional: `ActivityLogCest`, `CatalogNotificationCest` (product/category notification audience + deep-link assertions), `CheckoutCest`, `ContactFormCest`, `ListingFiltersCest`, `LoginFormCest`, `NotificationCest`, `ProfileCest`, `SettingsCest`.
- Unit: extensive coverage under `tests/Unit/{Controllers,Functional,Models,Services,widgets}`, including `CategoryAccessTest`, `RbacPermissionPolicyTest`, `OrderWorkflowTest`, `ShoppingCartTest`, `AdminGridPaginationTest`.
- `CatalogNotificationCest` was edited to assert `/category/view` targets for create/update. `tests/Unit/Models/InventoryTest.php` covers low-stock thresholds and the alert text.

## 10. Verification status and constraints

- Passed here: `php -l` on all touched PHP files, `git diff --check`, migration up/down/up, DB-backed CLI assertions for all eight product/category notification types, a CLI render of the cart page confirming the image anchor/tooltip/overlay, and CLI checks of inventory create/update/dedupe/restock/movement plus rendered inventory index/view and both dashboards.
- **Could not run here**: Codeception (CURL/PHP DLL policy) and any real browser check. Visual hover/zoom, dropdown behavior, and dark-mode appearance still need manual confirmation.
- Tooltips depend on the global `window.bootstrap` being loaded; if a page drops the Bootstrap JS asset, tooltips silently do nothing.
- All work is **uncommitted**. Unrelated pre-existing user edits must be preserved, including `models/LoginForm.php`, `services/ActivityLogger.php`, `views/site/about.php`, `views/login.php`, and untracked migrations/widgets.

## 11. First files to read when continuing

`config/web.php`, `services/RbacPermissionPolicy.php`, `services/NotificationService.php`, `services/InventoryService.php`, `models/Inventory.php`, `controllers/InventoryController.php`, `models/Notification.php`, `controllers/NotificationController.php`, `views/layouts/_notifications_link.php`, `widgets/ToolTip.php`, `web/css/cafebara-theme.css`, `models/ApplicationSettings.php`, `services/ActivityLogger.php`, `tests/Functional/CatalogNotificationCest.php`.
