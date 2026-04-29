# Products

## Product List

The product list shows all products in a sortable table. Use the filters at the top to narrow results by category, status (enabled/disabled), or alphabetical range. Search by product name or product ID using the search sidebar.

### List Columns

| Column | Description |
| --- | --- |
| Name | Product name with primary category shown below. Click to edit. |
| Image | Thumbnail preview. Click for full-size lightbox. |
| Product ID | The internal database ID for the product. |
| Digital/Physical | An icon indicating whether the product is digital (download) or physical (archive). |
| Product Code | The SKU or reference code. Click-to-copy supported. |
| Price | The formatted selling price. |
| Stock Level | Current stock quantity if stock tracking is enabled, or a dash if not. |
| Updated | Date the product was last modified. Shows "Unknown" if never updated. |
| Translations | Flag icons linking to each translated version of the product. |
| Status | Toggle to enable or disable the product directly from the list. |

Actions per row include clone, preview (opens storefront), statistics, edit, and delete. Use the row checkboxes plus the **Check/Uncheck all** link at the bottom of the table, then choose an action from the **With Selected** dropdown and click **Go**:

| Action | Effect |
| --- | --- |
| Add to Bulk Price Change | Sends the ticked products to the Bulk Price Update screen. The list accumulates across multiple trips — pick more products and choose this action again to append. See *Bulk Price Update* in the Product Assignment docs. |
| Delete | Bulk-deletes the ticked products. |

The list page also has shortcut tabs to **Add Product**, **Category Assignment** (bulk assign products to categories), **Option Set Assignment** (bulk assign option sets to products), and a **Search** sidebar.

## Add / Edit Product

When editing a product, the form is split into tabs: General, Description, Specification, Pricing, Categories, Options, Images, Digital, SEO, Reviews, and Translations.

### General

#### Basic Information

| Field | Description |
| --- | --- |
| Status | Toggle to enable or disable the product on the storefront. |
| Product Name | The display name shown on the storefront (required). |
| Manufacturer | Select a manufacturer/brand from the dropdown. Manufacturers are managed separately under Products > Manufacturers. |
| Condition | The product condition: New, Refurbished, or Used. |
| Product Code | A unique SKU or reference code. |
| Auto-Generate Product Code | Toggle on to have the system automatically generate a product code from the product name. When enabled, the manual product code field is overridden. |
| Product Weight | Weight value used for shipping calculations. |
| Dimension Unit | Unit of measurement for dimensions: Centimetres (cm) or Inches (in). Defaults to the store-wide setting. |
| Product Width | Width of the product in the selected dimension unit. |
| Product Height | Height of the product in the selected dimension unit. |
| Product Depth | Depth of the product in the selected dimension unit. |
| Country of Manufacture | The country where the product is manufactured. Selected from the full country list. |
| Featured | Toggle to include the product in the featured products section. |
| Latest | Toggle to include the product in the latest products section. |
| Available for Purchase | Toggle to control whether the product can be added to the cart. When off, the product page is visible but cannot be purchased. Defaults to on. |
| Live From | Schedule the product to become visible at a future date and time (e.g. `25 December 2026 09:00:00`). Leave blank for immediate visibility. |

#### Stock Control

| Field | Description |
| --- | --- |
| Use Stock Level | Toggle to enable inventory tracking for this product. |
| Stock Level | Current quantity in stock. When an option matrix is active, stock is managed per combination and a note is displayed here. |
| Stock Warning | Threshold at which a low stock warning appears on the dashboard. |

#### Miscellaneous

| Field | Description |
| --- | --- |
| UPC | Universal Product Code. Max 20 characters. |
| EAN | European Article Number. Max 20 characters. |
| JAN | Japanese Article Number. Max 20 characters. |
| ISBN | International Standard Book Number. Max 20 characters. |
| GTIN | Global Trade Item Number. Max 20 characters. |
| MPN | Manufacturer Part Number. Max 70 characters. |
| Google Category | Select from the Google product taxonomy list, or enter manually if the taxonomy file is not loaded. |
| Brand | The product brand (from the import/export field set). |

### Description

| Field | Description |
| --- | --- |
| Description | Full product description with rich text (WYSIWYG) editor. |
| Parse Content | Toggle to enable server-side parsing of Smarty template syntax and PHP within the description. Leave off for standard HTML content. |
| Short Description | A brief summary used in product listings and search results. Optional. |

### Specification

The specification tab allows you to define structured product attributes and freeform copy.

- **Key-Value Pairs**: Add rows of Name / Value pairs (e.g. "Material" / "Cotton"). Click **+** to add rows, **-** to remove. Each value field is a textarea supporting multi-line content.
- **Freeform Copy**: A rich text editor below the key-value pairs for additional specification narrative or formatted content.

### Pricing

Use the dropdown at the top to switch between **Standard Pricing** and any **Customer Group** pricing.

#### Standard Pricing

| Field | Description |
| --- | --- |
| Standard Price | The regular selling price. |
| Sale Price | A discounted price. When set, the standard price is shown with a strikethrough on the storefront. |
| Cost Price | Your cost for the product. Used for profit reporting only -- not displayed to customers. |
| Tax Class | Which tax rate to apply to this product. Select from configured tax classes. |
| Tax Inclusive | Toggle on if the entered price already includes tax. |
| Minimum Quantity | The minimum number of this product a customer must order. Defaults to 1. |
| Maximum Quantity | The maximum number of this product a customer can order. Leave blank to disable. |

#### Quantity Discounts

Offer tiered pricing based on the quantity ordered. Each row specifies a **Quantity** threshold and a **Price** that applies when that quantity is reached.

#### Group Pricing

When customer groups are configured, select a group from the dropdown to set group-specific pricing:

| Field | Description |
| --- | --- |
| Standard Price | Override price for this customer group. |
| Sale Price | Override sale price for this customer group. |
| Tax Class | Tax class override for this customer group. |
| Tax Inclusive | Whether this group's price includes tax. |

Each group also has its own quantity discount tiers.

### Categories

Assign the product to one or more categories. Each category row has:

| Column | Description |
| --- | --- |
| Primary | Radio button to select which category is the primary (used for breadcrumbs and canonical URL). |
| Additional | Checkbox to include the product in this category. |
| Category Name | The full category path. |

Use the check/uncheck all toggle at the bottom to select or deselect all categories.

### Options

Assign option groups, individual option values, or option sets to the product. Each assigned option value shows these columns:

| Column | Description |
| --- | --- |
| Status | Toggle to enable or disable this option value for the product. |
| Matrix | Toggle to include this option value in the option matrix for variant generation. |
| Name | The option group and value name (e.g. "Size: Large"). |
| Option Set | The set this option belongs to, if any. |
| Default | Checkbox to pre-select this option value when the product page loads. |
| Negative | Checkbox to subtract (rather than add) the price/weight adjustment. |
| Price | The price adjustment for this option value. Click to edit inline. |
| Absolute Price | Checkbox -- when ticked, the price value replaces the product price entirely rather than adjusting it. |
| Weight | The weight adjustment for this option value. Click to edit inline. |
| Image | Assign a specific image to this option value (e.g. a colour swatch). Click the icon to open the file manager. |

To add a new option, use the dropdown and fields in the table footer, then click the add icon.

**Option Sets**: Assign pre-built option sets to the product. Select a set from the dropdown and click Add. Remove assigned sets with the delete icon.

#### Option Matrix

When one or more option values have the Matrix toggle enabled, the Option Matrix table appears. It shows every combination of matrix-included options with these columns:

| Column | Description |
| --- | --- |
| Combination | The option value names for this variant (e.g. "Red / Large"). |
| Use Stock | Toggle to track stock for this specific combination. |
| Stock Level | Stock quantity for this combination. |
| Product Code | A unique SKU for this variant. |
| UPC | UPC code for this variant. |
| EAN | EAN code for this variant. |
| JAN | JAN code for this variant. |
| ISBN | ISBN code for this variant. |
| GTIN | GTIN code for this variant. |
| Restock Note | A note displayed when this variant is out of stock. Max 255 characters. |

### Images

Upload and manage product images. The file manager shows available images; click to assign them to the product.

Image states are indicated by icons:
- **Star** -- main product image (used as the primary thumbnail and first gallery image).
- **1** -- included in the product gallery.
- **0** -- excluded from the product gallery.

Drag and drop files onto the upload zone to add new images. Use the search field to filter images by folder/filename. The main image preview is shown to the right.

### Digital

The Digital tab marks a product as downloadable. It is a separate tab, not part of the General or Shipping sections.

Use the file manager to select the downloadable file. Only files in the downloads area are shown. The file manager operates in single-select mode -- only one file can be assigned.

| Field | Description |
| --- | --- |
| File Path | A custom/external file path for the download. Use this when the file is hosted outside the standard downloads directory or served via an external URL. |

### SEO

| Field | Description |
| --- | --- |
| Meta Title | Browser title tag. A character counter is shown to the right. Aim for 50-60 characters. |
| SEO Path | The friendly URL slug for this product. Auto-generated from the product name if left blank. |
| Meta Description | Search engine description. A character counter is shown to the right. Aim for 150-160 characters. |

#### SEO Redirects

If the product's SEO path has been changed previously, old paths are listed in a Redirects table showing:

| Column | Description |
| --- | --- |
| Path | The old URL path. |
| Status Code | The HTTP redirect status code (e.g. 301). |
| Action | Delete the redirect. |

### Reviews

View and manage customer reviews for this product. Each review shows its title, content, star rating, date, reviewer email, and IP address. Toggle the approval status or click edit/delete.

### Translations

Add translated versions of the product name, description, short description, and specification for each language enabled in your store. Existing translations are listed with flag icons. Click to edit or delete.
