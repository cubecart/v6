# Dashboard

The dashboard is the landing page of the admin panel. It provides an overview of your store's activity, quick access to common tasks, and alerts about items requiring attention.

## Sales Chart

A monthly comparison chart showing sales for the current year against the previous year. Only confirmed orders (processing or complete) with a value greater than zero are included. The chart data is cached for the duration of your session.

## Quick Stats

| Statistic | Description |
| --- | --- |
| Total Sales | Sum of all completed orders (all time). |
| Average Order Value | Mean value across all completed orders. |
| This Month | Sales from the 1st of the current month to today (processing and complete orders). |
| Last Month | Sales for the entire previous calendar month. |

## Quick Tasks

Shortcuts to common actions:

- View today's orders
- View this week's orders (from Monday)
- View this month's orders
- Add a new product
- Add a new category

## Last 5 Orders

Shows the five most recent orders with their order number and customer name. Click an order number to open it for editing.

## My Notes

A personal notepad for each admin user. Notes are saved to your admin account and are not visible to other administrators.

## Unsettled Orders

Lists orders that need attention -- those with a pending or processing status, or orders manually flagged for dashboard review.

| Column | Description |
| --- | --- |
| Order Number | Click to edit the order. A comment icon appears if the customer left a note. |
| Customer | Name with an icon indicating whether they are a registered or guest customer. |
| Status | Colour-coded order status. |
| Date | When the order was placed. |
| Total | Order total in store currency. |

### Bulk Actions

Select multiple orders using the checkboxes, then choose an action: change status, print, or delete.

## Pending Reviews

Lists product reviews awaiting approval. Each review shows the title, an excerpt, star rating, reviewer details, and the associated product. Use the toggle to approve or reject individual reviews.

## Stock Warnings

Products whose stock level has fallen below the configured warning threshold. The threshold can be set globally in Settings > Stock, or individually per product. Columns are sortable by stock level, product name, or product code.

## Store Overview

A summary of your store's data and server environment:

| Item | Description |
| --- | --- |
| Total Customers / Orders / Products / Categories | Record counts across your store. |
| CubeCart Version | Your installed version number. |
| PHP / MySQL Version | Server software versions. |
| Image / Download Folder Size | Click to calculate disk usage for the images and files directories. |
| Max Upload Size | The maximum file upload size allowed by your PHP configuration. |

## Security Checks

The dashboard automatically checks for common issues on each page load:

- **Setup folder present** -- The /setup folder should be removed after installation.
- **Config file writable** -- global.inc.php should be read-only (chmod 0444).
- **Caching disabled** -- The cache system should be enabled for performance.
- **MySQL root user** -- Using the root database account is a security risk.
- **Version update available** -- A newer version of CubeCart is available.
