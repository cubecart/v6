# Sales Reports

Generate filtered sales reports with CSV export.

## Filter

| Field | Description |
| --- | --- |
| Date Range | From and To dates. Defaults to the first of the current month through today. |
| Order Status | Select which order statuses to include (multi-select). Processing and Completed are selected by default. |

## Report Columns

| Column | Description |
| --- | --- |
| Order Number | Click to open the order. Respects your order ID display setting. |
| Subtotal | Order subtotal before shipping and tax. |
| Discount | Any coupon or discount applied. |
| Shipping | Shipping charge. |
| Tax | Tax amount for the order. |
| Total | Final order total. |
| Name | Customer name, linked to their account. |
| Country | Billing country. |
| State | Billing state/region. |
| Status | Order status at time of report. |
| Date/Time | When the order was placed. |

A summary row at the bottom shows the order count and totals for subtotal, discount, shipping, tax, and order total across all matching orders.

## Export

Click **Export** to download the report as a CSV file. If external reporting modules are installed (e.g. Sage, Xero), additional export buttons appear alongside the main export button.

> [!NOTE]
> The CSV export includes additional fields not shown on screen, such as billing and delivery addresses, phone, mobile, email, and payment gateway.

> [!TIP]
> Use reports to reconcile sales with your accounting software. Filter by date range and completed orders for accurate revenue figures.
