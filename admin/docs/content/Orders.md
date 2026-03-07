# Orders

## Order List

The order list shows all orders with their status, customer, date, and total. Use the Search tab to filter by order number, customer name, status, or date range.

| Column | Description |
| --- | --- |
| Order ID | Click to edit. Icons indicate if the order has notes or customer comments. |
| Customer | Customer name with an icon showing registered or guest status. Click to view the customer record. |
| Status | Colour-coded order status. |
| Date / Updated | When the order was placed and last modified. |
| Total | Order total in store currency. |

### Bulk Actions

Select multiple orders using checkboxes, then choose an action:

- **Status change** -- Update the status of all selected orders.
- **Print** -- Generate invoices for the selected orders.
- **Pin / Unpin** -- Add or remove orders from the dashboard.
- **Delete** -- Permanently remove the selected orders.

## Edit Order

### Order Summary

The main overview showing addresses, items, totals, and contact details.

| Field | Description |
| --- | --- |
| Order Status | Change the order status. When set to dispatched and no ship date exists, today's date is used automatically. |
| Skip Email | Toggle to prevent sending a status change notification to the customer. |
| Dashboard | Pin this order to the dashboard for easy access (only for orders with status 3 or above). |

The items table shows each product with its quantity, options, product code, unit price, and line total. Digital products include download links and a reset option.

### Billing Address

Edit the billing address. Use the customer lookup to search by name, or select a saved address from the customer's address book.

### Delivery Address

Edit the delivery address. Use **Copy from Billing** to duplicate the billing address. Shipping details can also be edited here:

| Field | Description |
| --- | --- |
| Shipping Date | The date the order was dispatched. |
| Shipping Method | The carrier name (e.g. Royal Mail). |
| Shipping Product | The service level (e.g. Special Delivery). |
| Tracking | Tracking numbers or URLs. Multiple entries can be added on separate lines. |

### Inventory

Add, edit, or remove products from the order. All amounts are editable including subtotal, discount, shipping, tax, and the order total. Use the **Refresh** button to recalculate.

| Field | Description |
| --- | --- |
| Discount Type | Choose between a fixed amount or percentage discount. |
| Add Tax | Select a tax rate from the dropdown and enter the amount to add a tax row. |

### Order Notes

Two types of notes can be added:

- **Private note** -- Visible to admin staff only.
- **Public note** -- Sent to the customer by email.

### Order History

A read-only log of all status changes, showing the new status, date/time, and who made the change.

### Transaction Logs

Payment transactions for this order, showing the transaction ID, status, amount, gateway, and any notes from the payment processor. Refund and capture actions appear here if supported by the payment gateway.

## Create Order

Use the **Create New Order** tab to manually create an order. Add products, set addresses, apply taxes and shipping, then save. A new order ID is generated automatically.

## Print Invoice

Click the print icon to generate an invoice. Multiple orders can be printed together using bulk select. The invoice includes store details, order items, tax breakdown, shipping information, and any notes marked for printing.

## GDPR

The GDPR tab allows you to purge orders older than a specified number of months.
