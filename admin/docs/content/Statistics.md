# Statistics

Sales analytics, conversion data, product performance, and visitor activity. The page is split into nine tabs, each loaded on demand.

## Sales

Four charts at progressively finer granularity. Pick years/months/days from the dropdowns above each chart and click **Go**.

| Chart | Shows |
| --- | --- |
| Yearly | Total sales per year, from your first order to the current year. |
| Monthly | Sales by month for the selected year. |
| Daily | Sales by day for the selected month. |
| Hourly | Sales by hour (0-23) for the selected day. |

A status filter at the top of the tab lets you narrow the included orders by order status (Processing, Completed, etc.). All four charts and footer totals respect the current filter.

## Conversion Funnel

A 7-day funnel showing how visitors progress from arrival to purchase. Each stage row shows:

| Column | Description |
| --- | --- |
| Stage | The step name (Visited, Viewed Product, Added to Cart, Reached Checkout, Paid). |
| Count | Distinct sessions that reached this stage. |
| % of Total | Percentage of all visitors that reached this stage. |
| % of Previous | Drop-off relative to the immediately previous stage. |
| Bar | Visual width proportional to *% of Total*. |

The footer summarises subtotal value and paid value across the same 7-day window.

## Abandoned Carts

Carts that were created but never converted to an order, sorted by most recently active.

| Column | Description |
| --- | --- |
| Customer | Name (and email, if known); registered customers link to their account record. |
| Items | Number of distinct items in the abandoned basket. |
| Value | Cart total at the moment the basket was last touched. |
| Idle | Time since last activity. |
| IP Address | Linked to a WHOIS lookup. |
| ✉ | Send an email to the customer (only shown when an email address is captured). |

## Sales by Country

Order revenue broken down by shipping country. Pick a year from the dropdown and click **Go**.

| Column | Description |
| --- | --- |
| Rank | Position by revenue (descending). |
| Country | Country flag and name. |
| Orders | Number of orders shipped to this country in the selected period. |
| Revenue | Total revenue from those orders. |
| % of Total | Country's share of total revenue for the period. |

A horizontal-bar chart sits above the table.

## Popular Products

Products ranked by quantity sold within a chosen year (from Processing and Completed orders).

| Column | Description |
| --- | --- |
| Rank | Position by units sold (descending). |
| Product | Product name; click to drill into its detailed statistics. |
| Quantity | Units sold in the period. |
| Revenue | Revenue contribution from those units. |
| Percentage | Share of total units sold. |
| Stock | Current stock level; highlighted when low. |
| Trend | Up/down arrow with percentage change versus the previous period (only shown when comparable data exists). |

Pagination is shown above the table.

## Most Viewed Products

Products ranked by page-view count.

| Column | Description |
| --- | --- |
| Rank | Position by view count. |
| Product | Product name; click to drill into its detailed statistics. |
| Views | Total page views recorded. |
| Percentage | Share of all product views. |
| Stock | Current stock level; highlighted when low. |

## Search Terms

Keywords customers have searched for on the storefront.

| Column | Description |
| --- | --- |
| Rank | Position by hit count. |
| Search Term | The keyword or phrase searched. Clicks open the storefront search results in a new tab. |
| Hits | Number of times the term was searched. |
| Percentage | Proportion of all search activity. |

Click **Clear Log** in the tab header to delete all recorded search terms.

## Best Customers

Customers ranked by total spend in the chosen year (Completed orders only — status 3). Click a name to open their account record.

| Column | Description |
| --- | --- |
| Rank | Position by total spend. |
| Name | Customer name (last, first). |
| Orders | Number of completed orders. |
| Total Expenditure | Total amount spent. |
| Percentage | Share of total revenue for the period. |

## Users Online

Visitors with active sessions in the last 30 minutes. Two buttons in the tab header toggle bot traffic visibility and force an immediate refresh.

| Column | Description |
| --- | --- |
| Type | Admin icon, customer icon, or a *Bot* badge with the bot name. |
| User | Guest label or customer name (registered customers link to their account). |
| Location | The URL the visitor is on; an external-link icon opens the page in a new tab. |
| Cart | Current basket value (if any). |
| Active For | Duration since the session started. |
| Last Seen | Time of the most recent activity. |
| Country | Country derived from IP. |
| IP Address | Linked to a WHOIS lookup. |

Rows for visitors who are currently on the checkout page are highlighted. The footer summarises totals broken down into signed-in customers, guests, and bots.
