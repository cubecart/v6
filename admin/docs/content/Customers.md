# Customers

## Customer List

The customer list shows all registered and guest customers. Use the search sidebar to find customers by name, email, or customer ID.

| Column | Description |
| --- | --- |
| Status | Toggle a customer active or inactive. Deactivating a customer ends any active sessions. |
| Type | Icon showing registered or guest customer. |
| Name | Customer name with group memberships shown in brackets. |
| Email | Click to copy the email address. |
| Registered | Date the account was created. |
| Orders | Number of orders placed. Click to view them. |

> [!NOTE]
> Customers with orders cannot be deleted. Use the GDPR tools to purge old customer data.

## Add / Edit Customer

### General

| Field | Description |
| --- | --- |
| Status | Enable or disable the customer account. |
| First Name / Last Name | Customer's name. Automatically capitalised. |
| Type | Registered or Unregistered (guest). |
| Language / Currency | Preferred language and currency for this customer. |
| Notes | Private admin notes. Not visible to the customer. |
| Credit | Store credit balance available for the customer to use at checkout. |
| Email | Must be unique across all customers. |
| Phone / Mobile | Contact numbers. |
| Newsletter | Subscription status: No, Yes, or Yes with Double Opt-In confirmation. |
| Password | Leave blank to keep unchanged. Enter a new password in both fields to reset. |

### Addresses

Manage the customer's saved addresses. Each address has a description (e.g. "Home", "Work"), and can be set as the default billing or delivery address.

| Field | Description |
| --- | --- |
| Description | A label for the address (e.g. Home, Office). |
| First Name / Last Name | Recipient name for this address. |
| Company | Optional company name. |
| Address Lines | Street address (line 1 is required). |
| Town / State / Postcode | Location details. State options update based on the selected country. |
| Billing / Default | Set this address as the default billing or delivery address. |

### Orders

Quick link to view all orders placed by this customer.

### Cookie Consent

A log of the customer's cookie consent records, showing the consent type, IP address, and timestamp.

### Sign In As

Allows an administrator to sign in to the storefront as this customer. Useful for troubleshooting. The customer's basket is cleared when an admin signs in as them.

### Groups

Manage the customer's group memberships. A customer can belong to multiple groups. Groups are used for access control, pricing rules, and category discounts.

## Customer Groups

Create and manage customer groups from the Customer Groups tab. Each group has a name and description. Groups can be used for:

- Category-level discounts (set in Categories > Customer Group Discounts)
- Product-level quantity pricing
- Category access restrictions

> [!NOTE]
> Deleting a group removes all memberships and associated pricing rules.
