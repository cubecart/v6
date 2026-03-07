# Coupons & Gift Certificates

## Coupon List

The coupon list shows all coupons (excluding gift certificates) in a sortable table. Columns are sortable by clicking the header.

| Column | Description |
| --- | --- |
| Status | Toggle to enable or disable the coupon directly from the list. |
| Code | The coupon code. Click to edit. |
| Value | The discount amount (formatted currency) or percentage. |
| Starts | The date the coupon becomes valid, or a dash if no start date is set. |
| Expires | The expiry date, or "Never" if no expiry. |
| Times Used | Shows the usage count vs. allowed uses (e.g. `3 / 10`). Unlimited is shown as an infinity symbol. |

Each row has edit and delete action icons.

## Add / Edit Coupon

The coupon form has two tabs: **General** and **Assigned Products**.

### Coupon Details

| Field | Description |
| --- | --- |
| Code | The code customers enter at checkout. Alphanumeric characters, dashes, and underscores only (required). |
| Description | Internal description for your reference. |

### Coupon Value

| Field | Description |
| --- | --- |
| Discount Type | Fixed amount or percentage off. |
| Discount Value | The amount or percentage to discount. |

### Limits

| Field | Description |
| --- | --- |
| Start Date | Date (YYYY-MM-DD) when the coupon becomes valid. Leave blank for no restriction. |
| Expiry Date | Date (YYYY-MM-DD) when the coupon expires. Leave blank for no expiry. |
| Allowed Uses | Maximum number of times the coupon can be used in total. Set to 0 for unlimited. |
| Times Used | Read-only count of how many times the coupon has been redeemed. Only shown when editing an existing coupon. |
| Minimum Subtotal | The order subtotal must reach this amount before the coupon applies. |
| Apply to Shipping | Toggle to include shipping costs in the discount calculation. |
| Free Shipping | Toggle to grant free shipping when this coupon is used. |
| Free Shipping Excluded | Toggle to exclude this coupon from the free shipping benefit. When enabled, this coupon will not grant free shipping even if other conditions would allow it. |
| Exclude Sale Items | Toggle to exclude products already on sale from the discount. |
| Per Customer Limit | Maximum uses per individual customer. |

### Restrictions

Optionally restrict the coupon to specific manufacturers, categories, or shipping methods using the multi-select dropdowns:

| Field | Description |
| --- | --- |
| Manufacturer Limit | Only apply to products from selected manufacturers. |
| Category Limit | Only apply to products in selected categories. |
| Shipping Limit | Only apply when selected shipping methods are used. |

### Convert to Gift Certificate

Link the coupon to an order number to convert it into a gift certificate. The coupon must use a fixed amount (not percentage).

| Field | Description |
| --- | --- |
| Order Number | The order ID to associate with. Accepts both traditional and custom order IDs. |

### Product Assignment

On the **Assigned Products** tab, assign specific products to the coupon. Search for products and add them to the list.

The **Product List** dropdown controls how assigned products are treated:

| Option | Description |
| --- | --- |
| Include | The coupon only applies to the listed products. |
| Exclude | The coupon applies to everything except the listed products. |
| Shipping Only | The coupon only applies to shipping costs for orders containing the listed products. |

> [!TIP]
> When no products are assigned, the coupon applies to the entire order (subject to any manufacturer, category, or shipping restrictions).

## Gift Certificates

Gift certificates are automatically created when a customer purchases a gift certificate product. They appear in the Gift Certificates tab showing:

| Column | Description |
| --- | --- |
| Status | Toggle to enable or disable the certificate. |
| Code | The gift certificate code. |
| Value | The remaining balance. |
| Expires | The expiry date, or "Never" if no expiry. |
| Order Number | The originating order. Click to view the order. |

> [!TIP]
> A coupon can be manually converted to a gift certificate by linking it to an order number on the coupon edit form. The coupon must use a fixed amount (not percentage).
