# Newsletter Subscribers

## Subscriber List

View and manage all newsletter subscribers. Use the email filter to search for specific addresses.

| Column | Description |
| --- | --- |
| Email | Subscriber email. Linked to the customer record if they have an account. |
| IP Address | The IP address recorded at signup. |
| Date | When the subscription was created. |
| Subscribed | Whether the subscription is active. |
| Imported | Whether the subscriber was added via the admin import tool. |
| Double Opt-In | Whether the subscriber confirmed via double opt-in. |

## Import Subscribers

Paste a comma-separated list of email addresses to bulk-add subscribers. Duplicate and invalid emails are automatically skipped. Imported subscribers are linked to existing customer accounts where the email matches.

## Export Mailing List

Export your subscriber list as a text or CSV file.

| Field | Description |
| --- | --- |
| Format Template | Use placeholders to control the output format: `{$EMAIL_ADDRESS}`, `{$FIRST_NAME}`, `{$LAST_NAME}`, `{$FULL_NAME_LONG}`, `{$FULL_NAME_SHORT}`. Leave blank to export email addresses only. |
| File Extension | Choose .txt (comma-separated) or .csv (one per line). |
| Double Opt-In Only | Tick to only export subscribers who have confirmed via double opt-in. |

> [!TIP]
> Name placeholders only work for subscribers who have a linked customer account.

## GDPR

Use the **Delete Single Opt-In Subscribers** button to remove all subscribers who have not completed double opt-in confirmation. This helps ensure GDPR compliance by keeping only explicitly confirmed subscriptions.
