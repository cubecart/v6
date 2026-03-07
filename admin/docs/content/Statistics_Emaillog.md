# Email Log

A record of all emails sent by the store, with the ability to view content and resend.

## Log Entries

| Column | Description |
| --- | --- |
| Sent | Success or failure indicator. |
| Subject | The email subject line. |
| To | Recipient name and email address. |
| From | Sender address. |
| HTML / Plain Text | Click to view the email content as it was sent. |
| Method | The sending method used (e.g. SMTP, mail). |
| Date | When the email was sent. |
| Attachments | Any files attached to the email, with download links. |

## Actions

| Action | Description |
| --- | --- |
| Filter | Filter the log by recipient email address (full or partial match). |
| Resend | Re-send an email to the original recipient. A new log entry is created for the resend attempt. |
| Edit | Edit the email template content (if linked to a content template). |
| Clear All | Delete the entire email log. |

> [!TIP]
> Failed emails show the error reason. Use the resend button to retry after fixing the underlying issue (e.g. SMTP configuration).
