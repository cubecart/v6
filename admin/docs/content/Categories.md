# Categories

## Category List

The category list shows all top-level categories, or sub-categories when browsing into a parent. Each row displays:

| Column | Description |
| --- | --- |
| ID | The category's unique database identifier. |
| Drag handle | Drag rows to reorder categories. The display order on your storefront updates immediately on save. |
| Visible | Toggle whether the category appears on the storefront. Hidden categories remain accessible by direct URL. |
| Status | Enable or disable the category entirely. Disabled categories are not accessible at all. |
| Name | The category name. Click to browse into sub-categories if any exist. |
| Products | Number of products assigned. Shows total count, with primary and secondary counts displayed separately. |
| Translations | Flag icons for each language translation available. |

### Row Actions

| Icon | Action |
| --- | --- |
| Filter | View products filtered to this category. |
| External link | Preview the category on your storefront. |
| Plus | Add a new translation for this category. |
| Pencil | Edit the category. |
| Trash | Delete the category (only if it has no sub-categories or products). |

## Add / Edit Category

### General

| Field | Description |
| --- | --- |
| Status | Enable or disable the category. |
| Visible | Show or hide the category on the storefront while keeping it enabled. |
| Category Name | The display name shown on the storefront and in navigation. |
| Parent Category | Select a parent to nest this category. Choose **/** for a root-level category. A category cannot be its own parent. |

### Description

| Field | Description |
| --- | --- |
| Category Description | Rich text content displayed on the category page. Supports full HTML via the editor. |
| Parse Smarty Tags | When enabled, Smarty template tags such as `{$VARIABLE}` in the description will be processed. Useful for dynamic content. |

### Images

Drag and drop image files onto the upload zone to add images. You can enable or disable individual images using the toggles. The first enabled image becomes the primary category image displayed on the storefront.

### SEO

| Field | Description |
| --- | --- |
| Meta Title | Sets the browser title tag for the category page. Aim for 50-60 characters for best search engine results. |
| SEO URL Path | The friendly URL segment for this category. Auto-generated from the category name if left empty. Tick **Generate SEO** to regenerate from the current name. |
| Meta Description | The meta description tag for search engines. Aim for 150-160 characters. |
| Redirects | Lists any 301/302 redirects pointing to this category. You can delete old redirects here. |

### Customer Group Discounts

Set a percentage discount for each customer group. Products within this category will have the discount applied automatically for members of that group. Leave at 0.00 for no discount.

### Access Control

| Field | Description |
| --- | --- |
| Restrict to Customer Groups | Tick one or more groups to restrict visibility. If no groups are ticked, the category is visible to everyone. |
| Guest Access | When group restrictions are set, this controls whether visitors who are not logged in can still see the category. |

### Shipping

Only available when the Per Category shipping module is enabled.

| Field | Description |
| --- | --- |
| Shipping per Order | A flat shipping charge applied once per order containing products from this category. |
| Shipping per Item | An additional charge multiplied by the quantity of items ordered from this category. |
| International variants | Separate per-order and per-item rates for international shipping. |

### Translations

Add translations for each language enabled in your store. Each translation can have its own category name, description, meta title, meta description and SEO URL path.

## Tips

> [!TIP]
> Use the **Save & Reload** button to save your changes and continue editing on the same page.

> [!TIP]
> Categories must be empty (no products or sub-categories) before they can be deleted.

> [!NOTE]
> Changing a category's parent will update its SEO path automatically. Any old URLs will need manual redirects if you want to preserve search engine rankings.
