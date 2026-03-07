# Email Log

A record of all emails sent by the store, with the ability to view content, edit templates, and resend.

## Filter

Enter a full or partial recipient email address and click **Go** to filter the log. The filter is session-persistent -- it remains active until you clear it or your session ends. Click **Reset** next to the filter field to remove the current filter and show all entries.

## Log Entries

| Column | Description |
| --- | --- |
| Sent | Success (check mark) or failure (cross) indicator. |
| Subject | The email subject line. |
| To | Recipient name and email address, linked to a customer search. |
| From | Sender name and email address. |
| HTML / Plain Text | Click either link to view the email content in a lightbox as it was sent. |
| Method | The sending method used (e.g. SMTP, mail). |
| Date | When the email was sent. |
| Attachments | Any files attached to the email, with download links. |
| Edit | Pencil icon to edit the linked email content template (only shown when the email is associated with a content template). |
| Resend | Paper-plane icon to re-send the email to the original recipient. A new log entry is created for the resend attempt. |

If an email failed to send, a sub-row appears below the entry displaying the fail reason (e.g. SMTP connection error, authentication failure).

## Actions

| Action | Description |
| --- | --- |
| Clear All | **Clear Log** button at the top right deletes the entire email log. Requires confirmation. |

> [!TIP]
> Failed emails show the error reason in the sub-row beneath the entry. Use the resend button to retry after fixing the underlying issue (e.g. SMTP configuration).
