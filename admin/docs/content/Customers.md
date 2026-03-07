# Customers

## Customer List

The customer list shows all registered and guest customers. The list is sorted by registration date (newest first) by default. Click any column header to sort by that column.

| Column | Description |
| --- | --- |
| Checkbox | Select individual customers for bulk actions. |
| Status | Toggle a customer active or inactive. Deactivating a customer ends any active sessions. |
| Type | Icon showing registered or guest (unregistered) customer. |
| Name | Customer name (Last, First) with group memberships shown in brackets. Click to edit. |
| Language | Flag icon for the customer's preferred language. Click to view that language's settings. |
| Email | Customer email address. Click the copy icon to copy it to the clipboard. |
| Registered | Date the account was created. |
| Orders | Number of orders placed. Click the count to view that customer's orders. |
| Actions | **Sign In As** (opens storefront as this customer in a new tab), **Edit**, and **Delete** icons. |

### Bulk Actions

Use the checkboxes in the first column to select customers. Click the **Check/Uncheck** link in the footer to select or deselect all. Choose an action from the dropdown (e.g. delete) and click **Go**.

### Per-Page Selector

A dropdown at the top of the list lets you choose how many customers to display per page (25, 50, 100, 250, or 500).

> [!NOTE]
> Customers with orders cannot be deleted. Use the GDPR tools to purge old customer data.

### Search Sidebar

The **Search Customers** sidebar tab lets you find customers by keyword or customer ID. Enter a name or email address to search across both fields, or enter a numeric customer ID to jump directly to that customer's edit page.

## Add / Edit Customer

### General

| Field | Description |
| --- | --- |
| Status | Enable or disable the customer account. |
| First Name / Last Name | Customer's name. Automatically capitalised on save. |
| Type | Registered or Unregistered (guest). Selected from a dropdown. |
| Language | Preferred language for this customer, chosen from enabled languages. |
| Currency | Preferred currency for this customer, chosen from active currencies. |
| Notes | Private admin notes. Not visible to the customer. |
| Credit | Store credit balance available for the customer to use at checkout. Enter a numeric amount (min 0, max 100000, step 0.01). |
| Email | Must be unique across all customers. |
| Phone / Mobile | Contact numbers. |
| Newsletter | Subscription status dropdown: No, Yes, or Yes (Double Opt-In). |
| IP Address | The IP address recorded for this customer (display only, not editable). |
| Password | Leave blank to keep unchanged. Enter a new password in both fields to reset. |

### Addresses

Manage the customer's saved addresses. Click **Add Address** to create a new one, or click the edit icon on an existing address to modify it. Addresses can be deleted individually via the trash icon.

The address list table shows:

| Column | Description |
| --- | --- |
| Description | A label for the address (e.g. Home, Office). |
| Name | Recipient name, with company name in brackets if set. |
| Address Line 1 | Street address (line 2 appended if present). |
| Town | Town or city. |
| State | State or county (auto-populated from country selection when applicable). |
| Postcode | Postal/ZIP code (displayed uppercase). |
| Country | Country name (resolved from the stored country code). |
| Actions | Edit and Delete icons. |

When adding or editing an address, the following fields are available:

| Field | Description |
| --- | --- |
| Description | A label for the address (e.g. Home, Office). |
| First Name / Last Name | Recipient name for this address. |
| Company | Optional company name. |
| Address Line 1 / Line 2 | Street address (line 1 is required). |
| Town | Town or city. |
| Country | Dropdown of all countries. State options update based on the selected country. |
| State | State or county. Becomes a dropdown when states are available for the selected country. |
| Postcode | Postal/ZIP code (auto-uppercased on save). |
| what3words | A what3words address. Only shown when what3words is enabled in store settings. |
| Billing Address | Toggle to set this as the customer's billing address. |
| Default Delivery Address | Toggle to set this as the default delivery address. |

### Orders

Tab linking to all orders placed by this customer. The tab label shows the order count.

### Cookie Consent

A log of the customer's cookie consent records, showing the consent description, IP address, and timestamp. Paginated in sets of 50.

### Sign In As

A tab that opens the storefront in a new browser tab, signed in as this customer. Useful for troubleshooting. The customer's basket is cleared when an admin signs in as them.

### Groups

Manage the customer's group memberships. A customer can belong to multiple groups. Use the dropdown to add a membership, and the trash icon to remove one.

## Customer Groups

Create and manage customer groups from the Customer Groups tab. Each group has an editable name and description (click the text to edit inline). Groups can be used for:

- Category-level discounts (set in Categories > Customer Group Discounts)
- Product-level quantity pricing
- Category access restrictions

> [!NOTE]
> Deleting a group removes all memberships, quantity pricing rules, and group pricing rules associated with it.
