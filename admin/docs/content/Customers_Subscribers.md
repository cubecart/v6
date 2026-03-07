# Newsletter Subscribers

> [!NOTE]
> If newsletters are disabled in store settings, a notice is displayed at the top of the subscriber list.

## Subscriber List

View and manage all newsletter subscribers. The list is paginated (20 per page) and sorted by date (newest first).

| Column | Description |
| --- | --- |
| Checkbox | Select individual subscribers for bulk removal. |
| Email | Subscriber email. Linked to the customer edit page if they have an account. |
| IP Address | The IP address recorded at signup. |
| Date | When the subscription was created. |
| Subscribed | Whether the subscription is active (check or cross icon). |
| Imported | Whether the subscriber was added via the admin import tool. |
| Double Opt-In | Whether the subscriber confirmed via double opt-in. |
| Actions | **Log** link (opens the subscriber's consent/activity log in a lightbox) and **Delete** icon. |

### Bulk Select and Delete

Use the checkboxes in the first column to select subscribers. Click the **Check/Uncheck** link in the footer to select or deselect all. Choose "Remove" from the dropdown and click **Go** to delete the selected subscribers.

### Email Filter

Use the filter fieldset at the top of the list to search for subscribers by email address (supports partial matches). Click **Go** to apply, or **Reset** to clear the filter.

### Log Search

The **Log Search** fieldset below the subscriber list lets you look up the subscription history for any email address. Enter an email and click **Go** to view the log in a lightbox popup.

### Purge Invalid and Empty List

Two action buttons appear below the subscriber list:

- **Purge Invalid** -- Validates all subscriber email addresses and removes invalid ones. Also unsubscribes addresses that fail validation. A summary shows how many were deleted and unsubscribed.
- **Empty List** -- Removes all subscribers from the list entirely. Requires confirmation.

## Import Subscribers

Paste a comma-separated list of email addresses to bulk-add subscribers. Duplicate and invalid emails are automatically skipped (with individual error messages for invalid addresses). Imported subscribers are linked to existing customer accounts where the email matches, and are flagged as imported.

## Export Mailing List

Export your subscriber list as a text or CSV file.

| Field | Description |
| --- | --- |
| Format Template | Use placeholders to control the output format: `{$EMAIL_ADDRESS}`, `{$FIRST_NAME}`, `{$LAST_NAME}`, `{$FULL_NAME_LONG}`, `{$FULL_NAME_SHORT}`. Leave blank to export email addresses only. |
| File Extension | Choose .txt (comma-separated) or .csv (one per line). |
| Double Opt-In Only | Tick to only export subscribers who have confirmed via double opt-in. Pre-checked if double opt-in is enabled in store settings. |

> [!TIP]
> Name placeholders only work for subscribers who have a linked customer account.

## GDPR

Use the **Delete Single Opt-In Subscribers** button to remove all subscribers who have not completed double opt-in confirmation. This helps ensure GDPR compliance by keeping only explicitly confirmed subscriptions.
