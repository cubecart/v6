# Sales Reports

Generate filtered sales reports with CSV export.

## Filter

| Field | Description |
| --- | --- |
| Date Range | From and To dates. Defaults to the first of the current month through today. |
| Order Status | Select which order statuses to include. Processing and Completed are selected by default. |

## Report Columns

| Column | Description |
| --- | --- |
| Order Number | Click to open the order. Respects your order ID display setting. |
| Subtotal | Order subtotal before shipping and tax. |
| Discount | Any coupon or discount applied. |
| Shipping | Shipping charge. |
| Total Tax | Tax amount for the order. |
| Total | Final order total. |
| Customer | Customer name, linked to their account. |
| Country | Billing country. |
| State | Billing state/region. |
| Status | Order status at time of report. |
| Date/Time | When the order was placed. |

A summary row at the bottom shows totals for subtotal, discount, shipping, tax, and order total across all matching orders.

## Export

Click **Download CSV** to export the report as a spreadsheet-compatible file. If external reporting modules are installed, additional export formats may be available.

> [!TIP]
> Use reports to reconcile sales with your accounting software. Filter by date range and completed orders for accurate revenue figures.
