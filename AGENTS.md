# Agent Guide

Read `docs/CODEBASE_CONVENTIONS.md` before editing. This is a Yii2 Basic app (PHP 8.2+, MariaDB/MySQL, Bootstrap 5, AdminLTE/Cafebara theme).

## Quick rules

- Reuse existing widgets/partials/services before adding files. No unrequested abstraction.
- Action views: `views/<controller>/<action>.php` (no underscore). Partials/subviews: `_name.php`. Shared cross-view partials: `views/shared/_name.php`. Reusable behavior+assets: `widgets/`.
- Confirm destructive actions with ConfirmModal: add `data-confirm-modal="..."` on POST submit/link; do not use browser `confirm()`.
- Keep controllers thin; status/business writes go through services with transactions where needed.
- Use pretty URLs from `config/web.php` for browser links; route IDs remain `controller/action`.
- Bootstrap 5 buttons: primary=main action, outline-primary=secondary, outline-secondary=neutral, outline-danger=destructive confirmed.
- RBAC via `AccessControl` + `Yii::$app->user->can()`. CSRF on POST forms.
- Verify: `php -l` on touched PHP files, `git diff --check`. Don’t commit/reset unrelated work. Use absolute Windows paths in commands.
