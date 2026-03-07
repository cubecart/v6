# Admin & Access Logs

Track login attempts and administrative actions across the store.

## Admin Access Logs

Records every admin login attempt.

| Column | Description |
| --- | --- |
| Username | The admin username used. |
| Date | When the attempt occurred. |
| IP Address | The source IP. Click for WHOIS lookup. |
| Success | Whether the login succeeded or failed. |

## Admin Activity Logs

Records actions taken by administrators (creating, editing, or deleting items).

| Column | Description |
| --- | --- |
| Username | The admin who performed the action. |
| Description | What was done. |
| Item | The affected item (order, product, category, etc.). Click to view it. |
| Date | When the action occurred. |
| IP Address | Source IP address. |

Use the **Clear Log** button to purge all activity log entries.

## Customer Access Logs

Records customer login attempts on the storefront.

| Column | Description |
| --- | --- |
| Username | The customer email used to log in. |
| Date | When the attempt occurred. |
| IP Address | Source IP. Click for WHOIS lookup. |
| Success | Whether the login succeeded or failed. |

All columns are sortable. Click a column header to sort ascending or descending.

> [!TIP]
> Monitor failed login attempts for signs of brute-force attacks. Repeated failures from the same IP address may indicate an unauthorised access attempt.
