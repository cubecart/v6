# Product Assignment & Bulk Pricing

## Category Assignment

Assign multiple products to one or more categories at once.

### Picking Products

The product list is **search-driven** — start typing in the search box (minimum 2 characters) and matching products appear in a dropdown. Click a result to add it to the list below. Use the **×** icon on the right of each row to remove a product.

Already-added products are skipped if you search for them again, so duplicates are not possible.

### Picking Categories

Tick the categories the selected products should be assigned to, then click **Save**.

## Bulk Price Update

Update prices across multiple products or entire categories in one operation.

### Building the Product List

The product list works the same way as Category Assignment — type to search, click to add. The list is held in your browser (`localStorage`) so it survives page refreshes and accumulates across visits:

- Pick a few products on the **Products** screen, choose **Add to Bulk Price Change** from the *With Selected* dropdown and click **Go** — they are added to your bulk-price list.
- Repeat as many times as you like; each trip appends to the existing list rather than replacing it.
- Click **×** to drop a product from the list.
- The list is cleared automatically when you click **Update**.

### Update Settings

| Field | Description |
| --- | --- |
| Apply | *Update selected products* (rows in the list) or *Update products in selected categories* (every product in the ticked categories). |
| Method | *By amount* (a fixed currency value) or *By percent*. |
| Action | *Subtract from*, *Add to*, or *Set to* — only shown when Method is *By amount*. *Set to* replaces the existing price. |
| Value | The amount or percentage to apply. |
| Price | Which prices to update — multi-select. Options below. |

#### Price field options

| Value | Affects |
| --- | --- |
| All | Every price column on every applicable row, including all customer groups. |
| Retail Price | The standard product price. |
| Sale Price | The product sale price. |
| Cost Price | The internal cost price (not shown to customers). |
| Quantity Discounts | All tier prices in the product's quantity-discount table. |
| Product Options | Per-option price adjustments. |
| All Customer Groups | All customer-group price/sale-price overrides for the selected products. |
| *Individual customer group* | When customer groups are configured, each group is listed separately so you can target one group at a time. |

The fields are revealed step by step — each step only enables once the previous one has a valid value, and the **Update** button only enables once at least one product (or one category, when Apply is set to categories) is in scope.

> [!NOTE]
> Bulk price changes cannot be undone. Consider exporting your products first as a backup.
