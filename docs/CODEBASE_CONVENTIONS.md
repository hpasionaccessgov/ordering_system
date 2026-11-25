# Cafébara / Yii2 Codebase Conventions

Reference for humans and agents. Follow Yii2 Basic layout, Bootstrap 5, AdminLTE/Cafebara theme conventions already used here.

## Stack / layout

- **Framework**: Yii2 Basic (`controllers/`, `models/`, `views/`, `widgets/`, `assets/`, `services/`, `migrations/`, `config/`).
- **UI**: Bootstrap 5 + AdminLTE vendor + `web/css/cafebara-theme.css`. Prefer Bootstrap utility/component classes; avoid custom CSS unless needed.
- **DB/RBAC**: MariaDB/MySQL, `DbManager` RBAC. Enforce access with `AccessControl`/`Yii::$app->user->can()`.

## Naming

| Kind | Convention | Example |
|---|---|---|
| Controller | Singular PascalCase + `Controller`, action methods `actionXxx` | `OrderController::actionIndex()` |
| Model/ActiveRecord | Singular PascalCase | `Order`, `OrderItem` |
| Form model | Purpose suffix | `LoginForm`, `ReportFilter` |
| Service | Singular PascalCase + `Service` | `OrderStatusService` |
| Widget | PascalCase class in `widgets/` | `ConfirmModal`, `AdminGridView` |
| Asset | PascalCase + `Asset` | `ConfirmModalAsset` |
| View file | lower-kebab/underscored as existing; action views no underscore | `views/order/index.php` |
| Partial/subview | **leading underscore**; rendered via `render('_name')`, not routable | `views/order/_cancel.php`, `_search.php` |
| Layout partial | leading underscore in `views/layouts/` | `_header.php`, `_sidebar.php` |
| Shared partial | reusable cross-view partial in `views/shared/` with leading underscore | `_product_card.php` |
| DB table | snake_case plural | `{{%orders}}`, `{{%order_status_history}}` |
| Route ID | `controller/action` | `order/view` |
| Pretty URL | sidebar-aligned, human path in `urlManager` rules | `/sales/orders`, `/sales/orders/<id>` |

## Views: `_` vs no `_`

- **No underscore**: controller action views (entry points): `index.php`, `view.php`, `create.php`, `update.php`, `board.php`. One per action/route.
- **Leading underscore**: partials included by `render()`/`renderPartial()`; not directly routable. Use for repeated or isolated chunks: `_form.php`, `_search.php`, `_order_card.php`.
- **`views/shared/`**: cross-domain reusable partials. Keep stateless; pass required data explicitly.
- **`widgets/`**: reusable UI + behavior with PHP class (and optional asset/JS). Use when partial needs logic, asset registration, options, or reuse across many views. Example: `ConfirmModal` registers JS asset once and provides modal markup.
- **Rule of thumb**: partial (`_*.php`) for simple markup reuse inside views; widget for reusable component with behavior/assets/config API.

## Confirmations / modals

- Use **ConfirmModal widget** (already in layouts) + `data-confirm-modal` attribute. JS: `web/js/confirm-modal.js`.
- Do **not** use browser `confirm()` / `data-confirm` for app actions.
- Cancel/order destructive actions: POST form + CSRF + `data-confirm-modal="..."`. Reuse existing partial when same permission + payload + label pattern repeats.

## Buttons (Bootstrap 5)

- **Primary**: main positive action (Save, View primary CTA, Advance status).
- **Outline primary**: secondary positive action (Open cart, Order Again, non-primary CTA).
- **Outline secondary**: neutral navigation/back.
- **Outline danger**: destructive action requiring confirm (Cancel order, Delete).
- Avoid `btn-success/warning/danger` filled for routine actions unless semantic status requires it; be consistent within same component type.

## URLs

- Browser-facing links should prefer pretty URL aliases from `config/web.php` that match sidebar IA.
- Internal route IDs remain valid for Yii routing/controller actions; tests may use route IDs.
- Keep `returnUrl` only for safe local paths (`/...`, no `//`).

## Services / controllers

- Controllers thin: auth, request/response, flash, redirect/render.
- Business/state transitions in services (`OrderStatusService`, etc.). Use DB transactions for multi-write/status changes.
- Reuse existing services before adding new abstractions. No new abstraction unless 2+ real uses or clear boundary.

## Before adding files

1. Can existing partial/widget/service be reused with a param?
2. Is this an action view, partial, widget, or service?
3. Does naming match adjacent files?
4. Does it follow RBAC, CSRF, Bootstrap/AdminLTE patterns?
5. Run `php -l` on touched PHP; `git diff --check`.
