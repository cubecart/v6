# Product Import

Import products in bulk from a CSV file.

## Step 1: Upload

| Field | Description |
| --- | --- |
| Source File | Select a CSV file to upload. |
| Delimiter | The separator used in your file: comma, semicolon, or tab. |

## Step 2: Column Mapping

After uploading, map each CSV column to a product field. A sample of the data is shown to help identify columns. Available fields include:

- name, product_code, description, description_short
- price, sale_price, cost_price
- stock_level, use_stock_level, stock_warning
- cat_id (category ID or breadcrumb path)
- manufacturer, image (comma-separated filenames)
- tax_type, tax_inclusive, product_weight
- seo_path, seo_meta_title, seo_meta_description
- status, featured, latest, digital, condition
- upc, ean, jan, isbn, brand, gtin, mpn
- product_width, product_height, product_depth, dimension_unit

### Options

| Field | Description |
| --- | --- |
| CSV has headers | Tick if the first row contains column names rather than data. |
| Replace existing data | Tick to delete all existing products before importing. Use with caution. |

## Step 3: Processing

The import processes in batches of 50 rows. Product codes are auto-generated if not provided. Manufacturers and categories are created automatically if they don't already exist. SEO URLs are generated for new products.

## Revert Import

Previous imports are listed with their date and product count. Select an import batch and delete to remove all products from that import.

> [!NOTE]
> The replace option permanently deletes all existing products, images, options, categories, and translations. Always export first as a backup.
