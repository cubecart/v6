# Admin Users

Manage administrator accounts, permissions, and two-factor authentication.

## Admin List

The main view shows all admin users in a table. Each row includes:

- **Status Toggle** -- enable or disable the admin account directly from the list.
- **Edit** and **Delete** action links.

## Admin Details

| Field | Description |
| --- | --- |
| Name | The admin's display name. |
| Username | Login username for the admin panel. |
| Email | Email address for notifications and password recovery. |
| Language | Preferred language for the admin panel. |
| Super User | Super users have unrestricted access to all areas (only visible to other super users). |
| Notifications | Receive email notifications for new orders. |
| Link Account | Link this admin to a customer account for testing. |
| Tour Shown | Reset the welcome tour for this admin. |

### Notes

A free-text field for internal notes about this admin account.

## Password

Set a new password for the admin. Changing a password will log the admin out of all active sessions.

## Overview

Displays read-only account statistics for existing admin users:

| Field | Description |
| --- | --- |
| Login Count | Total number of times this admin has logged in. |
| Last Login | Date and time of the most recent login. |

## Two-Factor Authentication

Add an extra layer of security to admin logins. Two methods are available:

| Method | Description |
| --- | --- |
| Email | A one-time code is sent to the admin's email address on each login. |
| Authenticator App (TOTP) | Use an authenticator app (Google Authenticator, Authy, etc.) to generate time-based codes. Scan the QR code or enter the secret key manually, then verify with a code to complete setup. |

### Backup Codes

When 2FA is enabled, 8 single-use backup codes are generated. Save these securely -- they can be used to log in if you lose access to your email or authenticator app. Use **Regenerate Backup Codes** to create a new set (this invalidates the old codes).

## Permissions

For non-super-user admins, permissions control access to each section of the admin panel. Super users can set granular permissions per section.

> [!NOTE]
> At least one super user account must exist. The last super user cannot be demoted or deleted.
