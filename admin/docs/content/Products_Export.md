# Product Export

Export your product catalogue as a CSV file.

## Export Settings

Use the **Products Per Page** dropdown to control how many products are included in each export file. Available values: 50, 100, 250, 500, 1,000, 5,000, 10,000, 25,000.

For large catalogues, the export is split into numbered pages. Each page is a separate download link.

## Export Formats

Formats are listed in a table with three columns:

| Column | Description |
| --- | --- |
| Format | The export format name (e.g. CubeCart). Additional formats may be added by plugins. |
| Parts | Numbered download links for each page of products. Click a number to download that page as a CSV file. |
| Feed/Access URL | A direct URL for automated access to the full export. This URL includes your store's feed access key and can be used by external services or feed aggregators to pull product data without logging in. |

## Exported Fields

The CubeCart CSV format includes the following fields in order:

Product Name, Status, Featured, Latest, Product Code, Weight, Description, Short Description, Price, Sale Price, Cost Price, Tax Class, Tax Inclusive, Images, Stock Level, Use Stock Level, Stock Level Warning, Master Category ID, Manufacturer, UPC, EAN, JAN, ISBN, Brand, MPN, GTIN, Meta Title, Meta Description, Condition, Digital, Digital Path (Legacy), Product Width, Product Height, Product Depth, Dimension Unit.

> [!NOTE]
> The Digital Path (Legacy) field exports any custom file path set on the product's Digital tab. This is separate from the Digital field which indicates the file manager assignment.

> [!TIP]
> Export your products before performing bulk price changes or imports with the replace option, so you have a backup to revert to.
