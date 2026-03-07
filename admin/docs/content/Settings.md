# Store Settings

Core configuration for your store, organised into tabs.

## General

### Store Details

| Field | Description |
| --- | --- |
| Store Name | Your store's name, shown in emails and page titles. |
| Address | Store postal address, shown on invoices and contact pages. |
| Country / State / Postcode | Store location, used as the default for tax and shipping calculations. |

### Domain Settings

| Field | Description |
| --- | --- |
| Store URL | The full URL of your store (e.g. https://www.example.com/store). |
| Cookie Domain | The domain scope for cookies (e.g. .example.com). Set this if sessions are lost between www and non-www. |

### Locale Settings

| Field | Description |
| --- | --- |
| Default Weight Unit | Metric (kg) or Imperial (lb). |
| Default Size Unit | Centimetres (cm) or Inches (in). |
| VAT Registration Number | Your tax registration number, shown on invoices. Hidden for US, Canada, and Australia. |
| Tax Customer By | Calculate tax based on the customer's billing or delivery address. |

### Social Accounts

Enter usernames or URLs for social media accounts. These are displayed in the storefront footer: Bluesky, Facebook, Flickr, Instagram, LinkedIn, Pinterest, Vimeo, WordPress, YouTube, Reddit, Tumblr, X.

## Features

### Reviews

Enable product reviews: Disabled, Enabled, or Enabled without Gravatar images.

### Orders

| Field | Description |
| --- | --- |
| Expire Pending Orders | Automatically cancel pending orders after this many days. Blank to disable. |
| Minimum Order | Minimum order value required to checkout. Blank to disable. |
| Maximum Order | Maximum order value allowed. Blank to disable. |
| Order ID Mode | How order numbers are displayed: Internal ID, Incremental (with prefix/postfix, zero-padding, and start number), or Random. |

### Sales

| Field | Description |
| --- | --- |
| Sale Mode | How sale prices are calculated. |
| Sale Percentage | Global discount percentage for sale items. |
| Start / Expiry Date | Date range for the global sale. |
| Sale Items Count | Number of sale items to display on the sale page. |

### Bot Protection (reCAPTCHA)

| Field | Description |
| --- | --- |
| Enable | Select reCAPTCHA version (v2 Checkbox, v2 Invisible, v3, or hCaptcha). |
| Site Key | Your reCAPTCHA public site key. |
| Secret Key | Your reCAPTCHA secret key. |

### Newsletter

| Field | Description |
| --- | --- |
| Status | Enable or disable the newsletter signup form. |
| Exit Modal | Show a newsletter signup popup when visitors attempt to leave the site. |
| Double Opt-In | Require email verification before adding subscribers. |

### Miscellaneous

| Field | Description |
| --- | --- |
| Admin Order Status Notification | Email admin on order status changes. |
| Skip Processing Check | Allow orders to skip the Processing status. |
| Hide Prices | Hide product prices from the storefront (admin view unaffected). |
| Catalogue Mode | Disable the basket and checkout entirely. |
| Allow No Shipping | Allow checkout without selecting a shipping method. |
| Disable Shipping Groups | Turn off shipping groups feature. |
| Shipping Defaults | Pre-select cheapest or most expensive shipping option. |
| Force Completed | Automatically mark paid orders as Completed. |
| Disable Estimates | Hide shipping cost estimates before checkout. |
| Different Delivery Address | Allow customers to ship to an address different from their billing address. |
| Email Confirmation | Require customers to confirm their email address during registration. |
| Admin Login Notify | Email notification when an admin logs in from a new device. |

## Layout

### Display Settings

| Field | Description |
| --- | --- |
| Default Product Sort | How products are sorted by default (name, price, date, popularity) and direction. |
| Show Empty Categories | Display categories that contain no products. |
| Product Precis | Character limit for the short description shown in product listings. |
| Expand Category Tree | Expand the full category navigation tree by default. |
| After Add to Basket | Where to redirect customers after adding a product: stay on page, go to basket, or go to checkout. |
| Disable Checkout Terms | Remove the Terms & Conditions checkbox from checkout. |
| Show Basket Weight | Display total weight in the basket. |
| Default RSS Feed | URL for the RSS feed shown on the storefront. |

### Popular & Latest Products

| Field | Description |
| --- | --- |
| Latest Products | How to determine "latest": by date added or by the Latest flag. |
| Latest Products Count | Number of latest products to display. |
| Popular Products Count | Number of popular products to display. |
| Popular Source | Rank by sales volume or page views. |

### Skin Settings

| Field | Description |
| --- | --- |
| Default Storefront Skin | Select the active storefront skin and colour style. |
| Default Admin Skin | Select the admin panel skin. |
| Allow Skin Change | Let customers switch between available skins. |
| Mobile Skin | Select a separate skin for mobile devices (if available). |

## Stock

### Digital Downloads

| Field | Description |
| --- | --- |
| Download Expiry | Days until download links expire. Blank to disable. |
| Update Existing | Apply the new expiry to existing unexpired downloads. |
| Download Attempts | Maximum download attempts per file. Blank for unlimited. |

### General Stock Settings

| Field | Description |
| --- | --- |
| Show Stock Level | Display stock quantities on the storefront. |
| Allow Out of Stock Purchase | Let customers buy products that are out of stock. |
| Stock Warning Method | How to warn about low stock: email, admin notification, or both. |
| Stock Warning Level | Threshold below which a stock warning is triggered. |
| Reduce Stock | When stock is reduced: on order placement or when order status changes to Processing. |
| Hide Out of Stock | Hide out-of-stock products from the storefront entirely. |
| Update Main Stock | Deduct from the main stock level when option matrix stock is used. |
| Delete Images with Products | Remove associated images when a product is deleted. |
| Image Upload Format | Convert uploaded images to: WebP (recommended), JPEG, PNG, or keep the original format. |

## SEO (Search Engines)

### Global Meta Data

| Field | Description |
| --- | --- |
| Browser Title | Default page title shown in browser tabs and search results. |
| Meta Description | Default meta description for pages without a specific one. |
| Product SEO Path | Include category path in product URLs, or use product name only. |
| Category SEO Path | Include parent category path in category URLs. |
| SEO Extension | URL suffix: .html, /, or none. |

### Meta Data Behaviour

Control how meta tags are generated when not explicitly set: auto-generate from content, use global defaults, or leave blank.

## Offline

Take the store offline for maintenance. While offline, visitors see a custom message instead of the store. Admins can still access the admin panel.

## Logos

Upload and manage store logos. Each logo can be scoped to a specific skin and style. Multiple logos can be uploaded and assigned to different skins.

## Advanced Settings

### Email

| Field | Description |
| --- | --- |
| Email Method | PHP mail() function or SMTP. |
| Sender Name | The "From" name on outgoing emails. |
| Sender Address | The "From" email address. |
| SMTP Host | SMTP server hostname (when using SMTP). |
| SMTP Port | SMTP server port (typically 587 or 465). |
| SMTP Authentication | None, SSL, or TLS. |
| SMTP Username / Password | Credentials for SMTP authentication. |

Use the **Test** button to send a test email and verify your settings.

### Performance

| Field | Description |
| --- | --- |
| Debug | Enable debug mode to show error details. Restrict to specific IPs for production use. |
| Debug IP Addresses | Comma-separated list of IPs that can see debug output. |
| Cache | Enable caching for improved performance. Shows the active cache method (File, Redis, etc.). |

### Elasticsearch

| Field | Description |
| --- | --- |
| Enable | Activate Elasticsearch for fast search-as-you-type. |
| Only Index In Stock | Exclude out-of-stock products from search results. |
| Host | Elasticsearch server URL. |
| Auth Type | Basic (username/password), API key, or None. |
| Index Name | The Elasticsearch index name (defaults to your database name). |
| SSL Verification | Verify SSL certificates for the Elasticsearch connection. |
| CA Certificate | Path to a custom CA certificate file. |

> [!NOTE]
> After enabling Elasticsearch, you must build the search index from the Maintenance page.

### Proxy

Configure an HTTP proxy for outgoing requests (for servers behind a corporate firewall). Set the proxy host and port.

### Time & Date

| Field | Description |
| --- | --- |
| Fuzzy Time Format | Short date format used in lists (PHP date format). |
| Time Format | Full date/time format (PHP date format). |
| Dispatch Date Format | Date format for dispatch/shipping dates. |
| UTC Offset | Hours offset from UTC for time display. |
| Timezone | Server timezone selection. |

### Log Retention

Set the number of days to retain each type of log before automatic cleanup:

- Admin Activity Log
- Admin Error Log
- Email Log
- Request Log
- Access Log
- System Error Log

### Other

| Field | Description |
| --- | --- |
| Feed Access Key | Secret key required to access data feeds (e.g. product feeds for Google Shopping). |
| Hide Chat | Hide the support chat widget in the admin panel. |

## Copyright

Custom copyright text displayed in the store footer. Supports HTML formatting via the rich text editor.

## Cart Recovery

| Field | Description |
| --- | --- |
| Enabled | Enable abandoned cart recovery emails. |
| Delay | How long after cart abandonment before sending the recovery email. |
| Notify Cooldown | Minimum time between recovery emails to the same customer. |
| Order Window | Only send if no order has been placed within this period. |
| Coupon | Optionally include a coupon code in the recovery email to incentivise return. |

## Scheduled Tasks

Configure automated tasks that run via cron. Each task has a status toggle, frequency setting, and shows when it last ran and the result.

Set up a cron job on your server to call the provided URL at regular intervals (every 5 minutes recommended). Examples are provided for cron and wget.

## Extra

### Product Clone

| Field | Description |
| --- | --- |
| Status | Enable or disable the product clone feature. Can also hide the clone button. |
| Images | Include product images when cloning. |
| Product Options | Include product options when cloning. |
| Options Matrix | Include option matrix and pricing when cloning. |
| Additional Categories | Include additional category assignments when cloning. |
| Main Stock Level | Include the main stock level in the cloned product. |
| Product Code | Generate a new product code or clone the existing one. |
| Translations | Include product translations when cloning. |
| Redirect | Automatically redirect to the cloned product after creation. |

### GDPR

**Cookie Compliance Dialogue** -- display a cookie consent message to visitors, as required by GDPR and ePrivacy regulations.
