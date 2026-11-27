---
slug: reward-redemption
title: Reward redemption
kind: feature
domain: loyalty
status: active
summary: Redeeming points creates a free pending order for the reward's mapped menu product.
aliases: []
entry:
  - screen /rewards
  - POST /rewards/<id>/redeem
files:
  - migrations/m261127_120000_link_rewards_to_products.php
  - migrations/m261127_130000_grant_staff_reward_permissions.php
  - models/Reward.php
  - controllers/RewardController.php
  - views/reward/_form.php
  - views/reward/create.php
  - views/reward/index.php
  - views/reward/update.php
  - views/reward/view.php
  - controllers/LoyaltyController.php
  - services/LoyaltyService.php
  - services/OrderService.php
  - tests/Functional/LoyaltyCest.php
  - tests/Unit/Services/RewardRedemptionTest.php
tests:
  - tests/Functional/LoyaltyCest.php
  - tests/Unit/Services/RewardRedemptionTest.php
data: [reward.product_id, loyalty_point_transaction.order_id]
config: [Navigation::REWARD_REDEEM, Navigation::ORDER_VIEW]
connections:
  - loyalty-progress: the rewards overview shows the mapped product and gates the redeem button.
  - rbac-role-matrix: loyalty.redeem and reward.* permissions gate redemption and reward management for staff and admin.
hubs:
  - hub-navigation: redeem and order links use Navigation keys.
  - hub-role-navigation: staff gain the permission-gated Reward catalog link but keep no personal rewards shortcut.
updated: 2026-11-27
---

# Reward redemption

## Summary

Each reward maps to an active menu product selected by an admin. Redeeming points deducts the cost and creates a zero-total pending order for that product, so staff track and fulfil the treat like any other order.

## Example use

http://localhost:8080/rewards

`vendor/bin/codecept run Unit --filter RewardRedemptionTest`

## Entry points

| Entry | Handler (Symbol) | Kind / access | Note |
|---|---|---|---|
| screen /rewards | `LoyaltyController::actionIndex` | screen, loyalty.view | Lists active mapped rewards. |
| POST /rewards/<id>/redeem | `LoyaltyController::actionRedeem` | POST, loyalty.redeem | Calls `LoyaltyService::redeem`. |

## Chain

- entry: the rewards view posts a CSRF-protected redeem form.
- logic: `LoyaltyService::redeem` locks the user, checks balance, creates the order and writes the ledger row.
- data: `reward.product_id` selects the item; `loyalty_point_transaction.order_id` links the deduction.
- presentation: the reward card shows the mapped product photo and name; the notification links to the new order.
- config: `Navigation::REWARD_REDEEM` and `Navigation::ORDER_VIEW` own the URLs.

## Flow

1. A staff member or admin selects an active product for each reward in the reward form.
2. A customer opens the rewards page and redeems an affordable reward.
3. `LoyaltyService::redeem` re-reads the reward and rejects missing or inactive mappings.
4. It locks the user row and rechecks the spendable balance inside a transaction.
5. `OrderService::create` creates a pending one-item order with a zero unit price.
6. The REDEEM ledger row stores the returned order id and negative points.
7. A notification links the customer to the new order for tracking.
8. The order appears in the normal order list and kitchen board.

## Gotchas

- Reward orders must stay zero-total; a priced order would earn points back on completion.
- Inventory is validated and deducted when the reward order completes, like a normal order.
- A reward with a disabled or missing product is not redeemable.
- Existing redemption rows keep a null order id; only new redemptions are linked.
- Staff can create and edit mapped rewards but cannot delete them.
- A mapped product without an image falls back to a cup icon on the reward card.

## Tests

The focused unit test checks the schema, service and form contract. The functional test redeems a reward and asserts the ledger row, pending order, zero total and notification link.

## Changelog

- 2026-11-27 (feat, local): reward cards showed a gift icon; staff could not create rewards -> no image use or reward.create grant -> product photo and staff grants added. Test: RewardRedemptionTest
- 2026-11-27 (feat, local): redemption only wrote a ledger row -> no product or queue link -> rewards now map to menu products and create free pending orders. Test: LoyaltyCest
