# Dashboard

The dashboard is the landing page of the admin panel. It provides an overview of your store's activity, quick access to common tasks, and alerts about items requiring attention.

## Sales Chart

A monthly comparison chart showing sales for the current year against the previous year. Only confirmed orders (processing or complete) with a value greater than zero are included. The chart data is cached for the duration of your session. The chart adapts to light and dark colour schemes automatically.

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

Plugins can add custom quick tasks via the `admin.dashboard.custom_quick_tasks` and `admin.dashboard.default_quick_tasks` hooks.

## Last 5 Orders

Shows the five most recent orders with their order number and customer name. Click an order number to open it for editing.

## My Notes

A personal notepad for each admin user. Notes are saved to your admin account and are not visible to other administrators.

## Latest News

Displays an RSS feed (up to five items) from the URL configured in store settings (`default_rss_feed`). By default this shows posts from the CubeCart community forums. Each item links to the original article. The feed is cached for the session.

## Recent Extensions

Shows recently published extensions fetched from cubecart.com. Each extension displays its name, image, and price, and links to the marketplace page within the admin panel.

## Unsettled Orders

Lists orders that need attention -- those with a pending or processing status, or orders manually flagged for dashboard review.

### Per-Page Selector

A dropdown above the table lets you choose how many orders to display per page (25, 50, 100, 250, or 500).

### Columns

All column headers are sortable by clicking the column name.

| Column | Description |
| --- | --- |
| Checkbox | Select orders for bulk actions. |
| Order Number | Click to edit the order. A comment icon appears if the customer left a comment. |
| Customer Type | Icon indicating whether the customer is registered or a guest. |
| Customer | Name linked to the customer record. |
| Status | Colour-coded order status. |
| Date | When the order was placed. |
| Total | Order total in store currency. |

### Row Actions

Each order row has action icons on the right:

| Icon | Action |
| --- | --- |
| Notes | Appears if internal admin notes exist for this order. Hover to preview note content. Links to the order's notes tab. |
| Print | Opens a printable version of the order in a new tab. |
| Edit | Opens the order for editing. |
| Delete | Deletes the order (with confirmation prompt). |

### Bulk Actions

Select multiple orders using the checkboxes, then use the footer controls to: change status, and then optionally print or delete. Click **Go** to apply.

## Pending Reviews

Lists product reviews awaiting approval.

| Column | Description |
| --- | --- |
| Title | Review title with an excerpt of the review text below. Click to edit. |
| Product | The associated product name, linked to its edit page. |
| Rating | Star rating out of five. |
| Name | Reviewer name. If the review was submitted anonymously, an "(Anonymous)" indicator is shown. Below the name, the reviewer's **email address** (linked) and **IP address** are displayed. |
| Date | When the review was submitted. |
| Approved | Toggle to approve or reject the review. |
| Actions | Edit and delete icons. |

Click **Update** to save approval changes.

## Stock Warnings

Products whose stock level has fallen below the configured warning threshold. The threshold can be set globally in Settings > Stock, or individually per product.

### Per-Page Selector

A dropdown above the table lets you choose how many warnings to display per page (25, 50, 100, 250, or 500).

### Columns

Columns are sortable by clicking the column header.

| Column | Description |
| --- | --- |
| Product Name | Links to the product edit page. If the warning is for an option-matrix variant, the link goes directly to the Options tab. |
| Product Code | The product's SKU. |
| Stock Level | Current stock level. For option-matrix items, the matrix stock level is shown instead of the main product stock, along with the cached option combination name. |

Each row has edit and delete action icons.

## Store Overview

A summary of your store's data and server environment, split into two sections:

### Inventory Data

| Item | Description |
| --- | --- |
| Customers | Total customer count. |
| Orders | Total order count. |
| Products | Total product count. |
| Categories | Total category count. |

### Technical Data

| Item | Description |
| --- | --- |
| CubeCart Version | Your installed version number. |
| PHP Version | Server PHP version. |
| MySQL Version | Database server version. |
| Image Folder Size | Click **Calculate** to compute disk usage for the images directory. |
| Download Folder Size | Click **Calculate** to compute disk usage for the files directory. |
| Max Upload Size | The maximum file upload size allowed by your PHP configuration. |
| Browser | Your current browser's user agent string. |
| Server | The web server software string (e.g. Apache, Nginx). |

## Security Checks

The dashboard automatically checks for common issues on each page load:

- **Setup folder present** -- The `/setup` folder should be removed after installation.
- **Admin folder/file present** -- If you have renamed your admin folder or file, the originals (`/admin` directory and `/admin.php` file) are automatically deleted. A warning is shown if automatic deletion fails.
- **Config file writable** -- `global.inc.php` should be read-only (chmod 0444). On non-Windows systems, the dashboard attempts to set this automatically.
- **Caching disabled** -- The cache system should be enabled for performance.
- **MySQL root user** -- Using the root database account is a security risk.
- **MySQL strict mode** -- A warning is shown if MySQL's `sql_mode` includes `STRICT_TRANS_TABLES` or similar strict modes, which can cause query failures.
- **Version update available** -- Checks the latest CubeCart release on GitHub and warns if a newer version is available, with a link to upgrade instructions.
