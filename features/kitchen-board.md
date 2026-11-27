---
slug: kitchen-board
title: Kitchen order board
kind: feature
domain: kitchen
status: active
summary: Four ticket rails with live timers and internal scrolling for kitchen staff.
aliases: []
entry:
  - GET /sales/kitchen-board
files:
  - controllers/KitchenController.php
  - views/kitchen/index.php
  - views/kitchen/_order_card.php
  - web/css/cafebara-theme.css
tests:
  - tests/Unit/Frontend/KitchenBoardTest.php
data: [orders.confirmed_at, orders.status]
config: [Navigation::KITCHEN_BOARD]
connections: []
hubs:
  - hub-navigation: The board route comes from Navigation::KITCHEN_BOARD.
  - hub-theme: Rail and ticket colors use Cafébara tokens with dark overrides.
updated: 2026-11-27
---

# Kitchen order board

## Summary

Staff see pending, confirmed, processing and ready orders as four rails of tickets. Each rail scrolls its own ticket container so the page header and rail headings stay put when a queue grows. Timers advance from `confirmed_at` in the browser.

## Example use

http://localhost:8080/sales/kitchen-board

`vendor/bin/codecept run Unit --filter KitchenBoardTest`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| GET /sales/kitchen-board | `KitchenController::actionIndex` | screen, order.process | Loads the four status rails. |

## Chain

- entry: `KitchenController::actionIndex` groups orders by status and passes `columns`, `counts` and stats.
- logic: `Order::queueElapsedSeconds` and `Order::priorityLevel` seed the server-rendered timer.
- data: `orders.confirmed_at` drives the live browser timer; `orders.status` selects the rail.
- presentation: `views/kitchen/index.php` renders rails, `views/kitchen/_order_card.php` renders tickets.
- config: `Navigation::KITCHEN_BOARD` supplies the pretty URL.

## Flow

1. `KitchenController::actionIndex` builds `$columns` for the four active statuses.
2. `views/kitchen/index.php` renders one `.kitchen-rail` per status with a fixed header.
3. The `.kitchen-rail-body` container holds the tickets and scrolls when they exceed its viewport cap.
4. `_order_card.php` prints quantity, item names, total and the next-status action.
5. The registered JS updates `.kitchen-ticket-timer` every second from `data-confirmed-at`.
6. Priority colors change in place; no status is advanced automatically.

## Gotchas

- The scroll cap lives on `.kitchen-rail-body`; styling `.kitchen-rail` alone will not contain the cards.
- The body is focusable with `tabindex="0"` so keyboard users can scroll a rail.
- `overscroll-behavior: contain` stops a rail scroll from chaining to the page.
- Dark mode uses the `.kitchen-board` token overrides, not raw light backgrounds.

## Tests

The focused unit test checks the scroll container CSS and the focusable rail markup. Live browser scrolling at every breakpoint is not covered.

## Changelog

- 2026-11-27 (feat, local): long ticket queues scrolled the whole kitchen page -> the rail body had no height cap -> added internal overflow scrolling and a focusable rail. Test: KitchenBoardTest
