# Product Statistics

A per-product view of sales performance — revenue, units, orders, stock cover, top variants, and the customers buying it. Open it from the bar-chart icon on any row in **Products** &rarr; **Inventory**, or by drilling into a row in the **Popular Products** table on the main Statistics page.

## Date Range Filter

A highlighted bar at the top sets the window for every metric on the page.

| Control | Behaviour |
| --- | --- |
| From / To | Date inputs. Click **Go** to apply a custom range. |
| 7d / 30d / 90d | Quick presets — last 7, 30, or 90 days ending today. |
| All time | Removes the date filter entirely. |

The page defaults to the **last 30 days**. The currently active preset is highlighted.

## Summary Cards

Four headline cards summarise the key numbers for the active window.

| Card | Shows |
| --- | --- |
| Revenue | Total takings from orders within the window. If the product has a Cost Price set, the card also shows estimated **Profit** (revenue minus cost &times; units). When a date filter is active, a green/red trend pill compares against the previous equal-length period. |
| Quantity Sold | Units sold within the window. The number is a link to the orders index pre-filtered to every order containing this product. Includes a trend pill against the previous period. |
| Orders | Distinct orders containing this product. The sub-line shows the average units per order. |
| Days of Stock | Estimated days of stock remaining at the current sales rate (`stock level / (units sold / days in window)`). The number turns red when below 14 days. Only shown when the product has stock control enabled. |

## Overview

Below the cards, an Overview table lists the supporting detail.

| Field | Description |
| --- | --- |
| Created | When the product was added to the catalogue. |
| Updated | When the product was last edited. |
| First Sale | Date of the earliest order in the window. |
| Last Sale | Date of the most recent order in the window. |
| Avg. Sale Interval | Average time between sales over the window (last sale &minus; first sale, divided by the number of orders). |
| Refunded / Cancelled | Lost units, lost orders, and lost revenue from Declined or Cancelled orders. Only shown when there are any. |

## Top Variants

For products with options (sizes, colours, etc.), this table shows the top five variants by units sold within the window.

| Column | Description |
| --- | --- |
| Top Variants | The variant's product code (or `options_identifier` if no code is set on the variant). |
| Quantity Sold | Units sold for this variant within the window. |
| Revenue | Revenue contribution from this variant. |

The table is hidden when the product has no orders against any variant.

## Top Customers

Customers who have bought the most of this product within the window, ranked by units.

| Column | Description |
| --- | --- |
| Customer Name | First and last name, linked to the customer account editor. |
| Email | Customer email address as a `mailto:` link. |
| Purchases | Total quantity of this product purchased by the customer. Click the number to open the orders index pre-filtered to that customer's orders containing this product. |

Guest checkouts are not listed because they are not associated with a customer record.

## Which Orders Count?

Sales totals (Revenue, Quantity Sold, Orders, Top Variants, Top Customers, First/Last Sale, Sale Interval) include orders with a status of **Processing** or **Complete** only.

The **Refunded / Cancelled** row counts orders with a status of **Declined** or **Cancelled**.

Pending orders (where the customer hasn't paid) and Failed/Fraudulent orders are excluded from every total on the page.

## Tips

- Set a **Cost Price** on each product to enable the Profit figure on the Revenue card.
- Turn on **Use Stock Level** for products you actually count — that's the only way Days of Stock can compute.
- Trend pills only appear when a date filter is active; they need a "before" and an "after" of equal length to compare.
- Click any underlined number on the page to drill into the underlying orders.
