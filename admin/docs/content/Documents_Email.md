# Email Templates

Manage the content and layout of all automated emails sent by the store.

## Email Contents

Each email type has editable content with support for multiple languages. Click an email type to edit its subject line and HTML body.

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
| Newsletter Verify | Email address verification for newsletter signup. |
| Tell a Friend | Customer shares a product via email. |
| Abandoned Cart | Reminder sent to customers who left items in their basket. |

### Editing Email Content

| Field | Description |
| --- | --- |
| Subject | The email subject line. Supports macros. |
| Language | Which language this version is for. |
| Content | HTML body of the email. Use the code editor to insert macros. |

### Macros

Macros are placeholders that are replaced with real data when the email is sent. A reference table of available macros is shown below the editor, varying by email type. Common macros include:

- **Order data:** cart_order_id, custom_oid, order_date, shipping_method, discount
- **Customer data:** first_name, last_name, company_name, email
- **Product loops:** `{$product.name}`, `{$product.quantity}`, `{$product.price}`
- **Tax loops:** `{$tax.tax_name}`, `{$tax.tax_percent}`, `{$tax.tax_amount}`

## Email Templates

Templates control the outer layout (header, footer, logo) that wraps all email content. The `{$EMAIL_CONTENT}` macro is required and is where the email body content is inserted.

| Field | Description |
| --- | --- |
| Template Name | Internal name for this template. |
| Default | Set this template as the default for all outgoing emails. |
| Content | HTML template layout. Must include `{$EMAIL_CONTENT}`. |

Use the **Preview** button to see how the template looks. Templates can be cloned to create variations.

> [!NOTE]
> You must have at least one email template. The last remaining template cannot be deleted.
