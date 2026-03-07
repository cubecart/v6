# Orders

## Order List

The order list shows all orders with their status, customer, date, and total. Use the Search tab to filter by order number, customer name, status, or date range.

| Column | Description |
| --- | --- |
| Order ID | Click to edit. A sticky-note icon appears if the order has notes. A comment icon appears if the customer left comments (hover to preview). A pin icon appears if the order is pinned to the dashboard. |
| Customer | Customer name. An icon indicates registered or guest status. Click to view the customer record (guests are not linked). |
| Status | Colour-coded order status. |
| Date | When the order was placed. |
| Updated | When the order was last modified. |
| Total | Order total in store currency. |
| Actions | Per-row icons to print, edit, or delete the individual order. |

### Per-Page Selector

A dropdown above the order list lets you choose how many orders to display per page. Options are 25, 50, 100, 250, or 500 items per page (default 25).

### Bulk Actions

Select multiple orders using checkboxes, then use the two dropdowns at the bottom of the list:

1. **Status dropdown** -- Optionally change the status of all selected orders (or leave as "No Change").
2. **Action dropdown** -- Choose an additional action to perform:
   - **Nothing** -- No additional action.
   - **Print** -- Generate invoices for the selected orders.
   - **Pin** -- Add selected orders to the dashboard.
   - **Unpin** -- Remove selected orders from the dashboard.
   - **Delete** -- Permanently remove the selected orders (shown in red).

Both dropdowns are applied together when you click **Go**. For example, you can change status to Dispatched and print invoices in one operation.

## Edit Order

### Order Summary

The main overview tab showing addresses, items, totals, and contact details.

| Field | Description |
| --- | --- |
| Order Status | Change the order status. When set to Dispatched and no ship date exists, today's date is used automatically. |
| Skip Email | Toggle to prevent sending a status change notification to the customer. |
| Dashboard | Pin this order to the dashboard for easy access. Only shown for orders with status 3 (Dispatched) or above. |

> [!NOTE]
> If the customer has notes on their customer record, they are displayed as a quoted paragraph at the top of the summary tab.

#### Customer Comments

If the customer left comments during checkout, they appear in a highlighted note box below the status controls, attributed to the customer's name.

#### Addresses

The summary displays both the delivery address and billing address side by side. Each address block has a click-to-copy button. If what3words is enabled, the what3words link is shown below the address.

#### Order Identifiers and Currency

| Field | Description |
| --- | --- |
| Standard Order ID | The system-generated order ID (always shown). |
| Custom Order ID | Shown only if the store uses a custom/incremental order ID scheme. |
| Order Date | The date and time the order was placed. |
| Currency | The currency used for the order (shown if recorded). |

#### Items Table

Each product row shows quantity, item name (linked to the product editor), product code, unit price, and line total. Product options and custom fields (e.g. gift certificate method/value) are shown below the item name. Digital products include a download link, download count, expiry date, and a **Reset** link to restore the download counter.

#### Totals

| Row | Description |
| --- | --- |
| Subtotal | Sum of all line items. |
| Discount | Discount amount. If the discount is percentage-based, the percentage is shown in brackets. |
| Shipping | Shipping cost. |
| Tax | Individual tax lines are shown if the order has itemised tax records (e.g. "VAT @ 20%"). Otherwise a single total tax row is displayed. |
| Credit | Shown only if store credit was applied. Displayed in brackets to indicate it reduces the total. |
| Total | Final order total (double-underlined). |

#### Contact Details

| Field | Description |
| --- | --- |
| Email | Customer email address (click-to-copy, clickable mailto link). |
| Phone | Customer phone number (click-to-copy). |
| Mobile | Customer mobile number (click-to-copy). Only shown if provided. |
| IP Address | The IP address used when placing the order (click-to-copy). |
| Language | The language used when placing the order, shown as a flag icon. |

#### Shipping and Gateway

| Field | Description |
| --- | --- |
| Shipping Date | The date the order was dispatched. |
| Shipping Method | The carrier name (e.g. Royal Mail). |
| Shipping Product | The service level (e.g. Special Delivery). |
| Shipping Tracking | Tracking numbers or URLs (URLs are rendered as clickable links). |
| Gateway | The payment gateway used for the order. This field is editable inline. |

### Billing Address

Edit the billing address fields. Use the customer lookup to search by name and auto-fill the address, or select a saved address from the customer's address book dropdown.

Fields: First Name, Last Name, Company Name, Address Line 1, Address Line 2, Town, Country, State, Postcode, and what3words (if enabled).

The contact details section below the address contains editable Email, Phone, and Mobile fields.

### Delivery Address

Edit the delivery address. Use **Copy from Billing** to duplicate the billing address. A saved address can also be selected from the dropdown.

Fields: First Name, Last Name, Company Name, Address Line 1, Address Line 2, Town, Country, State, Postcode, and what3words (if enabled).

#### Shipping Details

| Field | Description |
| --- | --- |
| Shipping Date | The date the order was dispatched. |
| Shipping Method | The carrier name (e.g. Royal Mail). |
| Shipping Product | The service level (e.g. Special Delivery). |
| Shipping Tracking | Tracking numbers or URLs. Multiple entries can be added on separate lines. |
| Weight | The order weight, shown with the store's configured weight unit. |

### Inventory

Add, edit, or remove products from the order. Existing products are listed with editable quantity, name (editable inline), unit price, and line total fields.

To add a product, use the inline row at the bottom of the product list: type to search for a product, set the quantity and price, then click the add (+) button.

The totals section below the product list contains these editable fields:

| Field | Description |
| --- | --- |
| Subtotal | Calculated sum of line items. |
| Discount Type | Choose between a fixed amount or percentage discount. |
| Discount | The discount value (shows a % symbol when percentage is selected). |
| Shipping | The shipping amount. |
| Tax rows | Each tax line shows the tax class and rate name, with an editable amount. Individual tax rows can be removed. |
| Add Tax | Select a tax rate from the grouped dropdown (organised by country) and enter an amount to add a new tax row. |
| Total Tax | The combined tax total. |
| Credit | Store credit applied to the order. |
| Total | The order total. Click the **Refresh** button to recalculate. |

### Order Notes

Two types of notes can be added:

- **Private note** -- Visible to admin staff only. Multiple private notes accumulate as a list, each showing the author and timestamp. Individual notes can be deleted.
- **Public note** -- Sent to the customer by email. This is a single field that is included with the next status update email.

### Order History

A read-only log of all status changes, showing the status, date/time, and initiator (e.g. System, Admin, or Customer).

### Transaction Logs

Payment transactions for this order, showing the transaction ID, status, amount, gateway, date/time, and any notes from the payment processor. An Action column with refund/capture buttons appears if the payment gateway supports those operations.

### Card Details

This tab only appears for orders placed via offline payment capture. It displays the stored card fields (card type, number, expiry, valid from, issue number, CVV) as editable text inputs. A **Delete** link permanently removes the stored card data.

## Create Order

The **Create New Order** tab in the order list navigates to the order editor in "add" mode. This uses the same Billing, Delivery, and Inventory tabs as the edit view. Add products, set addresses, configure taxes and shipping, then save. A new order ID is generated automatically. The Summary, Notes, History, and Transaction tabs are not shown until the order is saved.

## Print Invoice

Click the print icon on an individual order row, or use bulk select with the Print action, to generate a printable invoice. The invoice is rendered as a standalone HTML page that triggers the browser's print dialog automatically. It includes store details, order items, tax breakdown, shipping information, and any notes marked for printing.

## GDPR

The GDPR tab allows you to purge orders older than a specified number of months. Enter the number of months and click **Go** to permanently delete all matching orders.
