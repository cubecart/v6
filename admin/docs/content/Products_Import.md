# Product Import

Import products in bulk from a CSV file.

## Step 1: Upload

| Field | Description |
| --- | --- |
| Source File | Select a CSV file to upload. The maximum file size is shown (based on your server's `upload_max_filesize` setting). |
| Format | Select a known format if available, or leave as unknown for manual column mapping. |
| Delimiter | The separator used in your file: comma (`,`), semicolon (`;`), or tab. |

## Step 2: Column Mapping

After uploading, map each CSV column to a product field. A sample value from the first row of your CSV is shown alongside each column to help identify the data. Select a target field from the dropdown, or choose "Ignore this column" to skip it.

### Mappable Fields

- `available` -- Available for Purchase (toggle)
- `status` -- Status (enabled/disabled)
- `name` -- Product Name
- `image` -- Images (comma-separated filenames)
- `product_code` -- Product Code
- `cat_id` -- Master Category ID (numeric ID or breadcrumb path e.g. `Clothing/Shirts`)
- `description` -- Description
- `description_short` -- Short Description
- `manufacturer` -- Manufacturer (name or ID)
- `price` -- Price
- `sale_price` -- Sale Price
- `cost_price` -- Cost Price
- `product_weight` -- Weight
- `use_stock_level` -- Use Stock Level
- `stock_level` -- Stock Level
- `stock_warning` -- Stock Level Warning
- `digital` -- Digital (yes/no)
- `digital_path` -- Digital File Path (legacy custom path)
- `tax_type` -- Tax Class
- `tax_inclusive` -- Tax Inclusive
- `featured` -- Featured
- `latest` -- Latest
- `seo_path` -- SEO Path
- `seo_meta_title` -- Meta Title
- `seo_meta_description` -- Meta Description
- `condition` -- Condition (new, refurbished, used)
- `upc` -- UPC Code
- `ean` -- EAN Code
- `jan` -- JAN Code
- `isbn` -- ISBN Code
- `brand` -- Brand
- `gtin` -- GTIN Code
- `mpn` -- MPN Code
- `product_width` -- Product Width
- `product_height` -- Product Height
- `product_depth` -- Product Depth
- `dimension_unit` -- Dimension Unit (cm or in)

### Options

| Field | Description |
| --- | --- |
| CSV has headers | Tick if the first row contains column names rather than data. |
| Replace existing data | Tick to delete all existing products before importing. Use with caution. |

## Step 3: Processing

The import processes in batches of 50 rows, with a progress page shown between each batch. Product codes are auto-generated if not provided. Manufacturers and categories are created automatically if they don't already exist. SEO URLs are generated for new products.

## Revert Import

Previous imports are listed in a separate tab showing their date and product count. Tick one or more import batches and save to delete all products from those imports.

> [!NOTE]
> The replace option permanently deletes all existing products, images, options, categories, reviews, translations, quantity pricing, group pricing, and SEO URLs. Always export first as a backup.
