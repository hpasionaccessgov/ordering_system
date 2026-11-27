---
slug: notification-navigation
title: Notification navigation
kind: feature
domain: notifications
status: active
summary: Notification links mark rows read and redirect customers to the notification target.
aliases: []
entry:
  - GET /notification/index
  - GET /notification/view
  - POST /notification/read
files:
  - controllers/NotificationController.php
  - views/notification/index.php
  - views/layouts/_notifications_link.php
  - views/customer/index.php
tests:
  - tests/Functional/NotificationCest.php
data: [notifications.read_at]
config: [Navigation::NOTIFICATION_VIEW, Navigation::NOTIFICATION_READ]
connections: []
hubs:
  - hub-navigation: View links use NOTIFICATION_VIEW while the read form uses POST-only NOTIFICATION_READ.
  - hub-audit-columns: Notification rows carry created_by and updated_by.
updated: 2026-11-27
---

# Notification navigation

## Summary

The notification list, header dropdown and customer dashboard link to a GET view action. Opening a notification marks it read and follows its stored target URL. Marking read without following the target is a separate POST action.

## Example use

http://localhost:8080/notifications

`vendor/bin/codecept run Functional --env php-builtin --filter NotificationCest`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| GET /notification/index | `NotificationController::actionIndex` | route, notification.index | Lists notifications owned by the caller. |
| GET /notification/view | `NotificationController::actionView` | route, notification.read | Marks read then redirects to the target. |
| POST /notification/read | `NotificationController::actionRead` | route, notification.read | Marks read without redirecting. |

## Chain

- entry: `NotificationController::actionView` resolves the caller's notification.
- logic: `Notification::getTargetUrl` converts the stored target into a browser URL.
- data: `notifications.read_at` records the first read.
- presentation: list, dropdown and dashboard links call `Navigation::url`.
- config: `Navigation::NOTIFICATION_VIEW` keeps the GET link distinct from `NOTIFICATION_READ`.

## Flow

1. `NotificationController::actionIndex` renders the notification list for the signed-in user.
2. The list renders a POST form for `Mark as read` and a GET `View` link.
3. `NotificationController::actionView` loads the notification with `visibleNotifications()`.
4. The controller sets `read_at` when it is null and saves that column.
5. `Notification::getTargetUrl` supplies the destination and the controller redirects there.

## Gotchas

- `notification/read` is POST-only; a GET link to it returns 405.
- The view route intentionally has a null pattern so generated hrefs stay encoded query URLs.
- Notification targets are raw stored routes and can point to any module the caller can access.

## Tests

The functional test proves unread scoping, header dropdown hrefs and the 405 on GET `notification/read`. It does not render every target route.

## Changelog

- 2026-11-27 (refactor, local): customer dashboard moved its showcase into the shared highlights partial -> the old section block was removed -> notification links stay on GET NOTIFICATION_VIEW. Test: CustomerDashboardViewTest
- 2026-11-27 (fix, local): notification `View` links pointed at a POST-only action -> callers used NOTIFICATION_READ -> links now use GET NOTIFICATION_VIEW. Test: NotificationCest
