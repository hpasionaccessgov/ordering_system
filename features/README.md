# Feature Map — Cafébara (index)

Connections only. Each row is `features/<slug>.md`. Read this file first, then open ONLY the feature
file(s) for your task (at most 3), then the source files they list. `links` = features this one
depends on or feeds; the reason for each link, entry points, flow, tests and gotchas live in the file.
- Who links TO a feature:    grep -l "^  - <slug>:" features/*.md
- Which feature owns a path: grep -l "<path>" features/*.md
- Changing a feature? Follow AGENTS.md § Feature map. Format and caps: features/_guide.md.

## public
| slug | summary | links |
|---|---|---|

## auth
| slug | summary | links |
|---|---|---|
| rbac-role-matrix | Role editor labels recent RBAC permissions; loyalty report access is enforced. | report-filters |

## catalog
| slug | summary | links |
|---|---|---|
| product-highlights | Shared specials and featured-drinks sections for home and the three role dashboards. | |

## cart
| slug | summary | links |
|---|---|---|
| cart-add-feedback | Adding a product shows a toast with the new count and a direct View cart action. | cart-quantity |
| cart-quantity | Update or clear cart line quantities from the cart page and mini cart. | |

## orders
| slug | summary | links |
|---|---|---|
| rest-api | Bearer-token REST API for login, catalog browsing and customer orders. | rest-api-customer |

## kitchen
| slug | summary | links |
|---|---|---|
| kitchen-board | Four ticket rails with live timers and internal scrolling for kitchen staff. | |

## inventory
| slug | summary | links |
|---|---|---|

## loyalty
| slug | summary | links |
|---|---|---|
| loyalty-progress | Loyalty page shows tier progress from lifetime earned points in both color modes. | |
| reward-redemption | Redeeming points creates a free pending order for the reward's mapped menu product. | loyalty-progress, rbac-role-matrix |

## notifications
| slug | summary | links |
|---|---|---|
| notification-navigation | Notification links mark rows read and redirect customers to the notification target. | |

## reports
| slug | summary | links |
|---|---|---|
| product-report-ranking | Product report ranks completed orders in range, matching catalog best sellers. | report-tables, report-pdf-analytics |
| report-charts | Shared Chart.js builder renders report charts with theme-aware labels. | |
| report-filters | Shared report filter normalizes typed customer IDs and defaults report ranges to this week. | report-pdf-analytics |
| report-pdf-analytics | PDF exports embed browser-captured Chart.js images and summary statistics for every report. | report-filters, report-charts |
| report-tables | Report tables paginate with the shared settings page size and keep each table's page separate. | report-filters |

## admin
| slug | summary | links |
|---|---|---|

## staff
| slug | summary | links |
|---|---|---|

## customer
| slug | summary | links |
|---|---|---|
| rest-api-customer | Bearer-token APIs for cart, loyalty, rewards, notifications, favorites and reviews. | rest-api, reward-redemption, notification-navigation, cart-quantity |

## shared (hubs)
| slug | file | contract |
|---|---|---|
| hub-audit-columns | features/hub-audit-columns.md | nullable created_by/updated_by plus BlameableBehavior on app models |
| hub-best-sellers | features/hub-best-sellers.md | ranked best-sellers panel shared by home, menu, catalog and dashboards |
| hub-navigation | features/hub-navigation.md | route keys, controller routes and pretty URL patterns from one map |
| hub-role-navigation | features/hub-role-navigation.md | staff lose personal rewards shortcuts; customers/admins keep them; overview and RBAC stay |
| hub-theme | features/hub-theme.md | Bootstrap color-mode tokens for surfaces, text, borders and charts |
| hub-vendor-assets | features/hub-vendor-assets.md | Composer-installed front-end assets under vendor paths; no node_modules runtime |

## Aliases
