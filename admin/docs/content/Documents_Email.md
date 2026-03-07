# Email Templates

Manage the content and layout of all automated emails sent by the store.

## Email Contents

Each email type has editable content with support for multiple languages. Click a translation flag to edit that language version.

The email contents list has the following columns:

| Column | Description |
| --- | --- |
| Email Type | The name/description of the email. |
| Translations | Language flags for each existing translation. Click a flag to edit. |
| Status | Toggle to enable or disable this email type. |
| Add Translation | Icon to add a translation (shown when not all languages are covered). |

### Email Types

| Type | Sent When |
| --- | --- |
| Order Confirmation | Customer places an order. |
| Order Complete | Order status is set to Complete. |
| Order Cancelled | Order is cancelled. |
| Payment Received | Payment is confirmed. |
| Payment Fraud | Payment flagged as potentially fraudulent. |
| Digital Download | Digital product download link delivery. |
| Gift Certificate | Gift certificate code delivery. |
| Password Recovery | Customer requests a password reset. |
| Admin Password Recovery | Admin requests a password reset. |
| Admin Order Received | Notification to admin of new order. |
| Review Added | Notification to admin of new product review. |
| Two-Factor Code | 2FA verification code for admin login. |
| New Device Login | Notification to admin when login occurs from a new device/IP. |
| Newsletter Verify | Email address verification for newsletter signup. |
| Newsletter Remove Request | Confirmation when a user requests newsletter removal. |
| Tell a Friend | Customer shares a product via email. |
| Abandoned Cart | Reminder sent to customers who left items in their basket. |

### Editing Email Content

| Field | Description |
| --- | --- |
| Subject | The email subject line. Supports macros. |
| Language | Which language this version is for. When editing, shown as a flag; when creating, a dropdown of available languages. |
| Content | HTML body of the email. Uses the Ace code editor with Smarty syntax highlighting. |

### Macros

Macros are placeholders that are replaced with real data when the email is sent. A reference table of available macros is shown below the editor, varying by email type. Common macros include:

- **Order data:** `{$DATA.cart_order_id}`, `{$DATA.custom_oid}`, `{$DATA.order_date}`, `{$DATA.ship_method}`, `{$DATA.discount}`
- **Customer data:** `{$DATA.first_name}`, `{$DATA.last_name}`, `{$BILLING.company_name}`, `{$BILLING.email}`
- **Product loops:** `{$product.name}`, `{$product.quantity}`, `{$product.price}`
- **Tax loops:** `{$tax.tax_name}`, `{$tax.tax_percent}`, `{$tax.tax_amount}`

## Email Templates

Templates control the outer layout (header, footer, logo) that wraps all email content. The `{$EMAIL_CONTENT}` macro is required and is where the email body content is inserted.

### Template List

The template list has the following columns:

| Column | Description |
| --- | --- |
| Default | Radio button to set this template as the default for all outgoing emails. |
| Template Name | The internal name. Click to edit the template. |
| Clone | Duplicate this template. |
| Edit | Open the template editor. |
| Delete | Delete this template. |

Below the list, a **Create Template** link opens a blank template editor.

### Template Macros

| Macro | Description | Required |
| --- | --- | --- |
| `{$EMAIL_CONTENT}` | Placeholder where the email body content is inserted. | Yes |
| `{$DATA.logoURL}` | URL to the store logo image. | No |
| `{$DATA.store_name}` | The store name. | No |
| `{$DATA.storeURL}` | URL to the store homepage. | No |
| `{$DATA.unsubscribeURL}` | Newsletter unsubscribe link. | No |

Use the **Preview** button to see how the template looks. Templates can be cloned to create variations.

> [!NOTE]
> You must have at least one email template. The last remaining template cannot be deleted.
