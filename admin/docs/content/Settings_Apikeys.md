# API Keys

Manage REST API access to your store. API keys allow external applications to read and write store data over HTTPS.

## Creating a Key

Click the **Create New** tab and fill in:

| Field | Description |
| --- | --- |
| Label | A descriptive name (e.g. "ERP Integration"). |
| Owner | The admin user this key is linked to. The key can never exceed this admin's permissions. |
| Rate Limit | Max requests per minute (default 60, max 1000). |
| Expires | Optional expiry date. Leave blank for no expiry. |
| IP Whitelist | One IP address per line. Leave blank to allow all. |

### Permissions

Set access per resource using the radio buttons:

- **Read** -- GET requests only.
- **Read/Write** -- GET, POST, and PUT requests.
- **Read/Write/Delete** -- Full access including DELETE.
- **None** -- No access to this resource.

After creating a key, the **API Key** and **API Secret** are shown once. Copy them immediately -- the secret cannot be retrieved later.

## Managing Keys

The key list shows all API keys with their label, masked key, owner, status, last used date, and expiry.

- **Edit** -- Change label, permissions, rate limit, IP whitelist, expiry, or enable/disable.
- **Regenerate** -- Create a new key+secret pair. The old credentials stop working immediately.
- **Delete** -- Permanently remove the key.

A maximum of 20 API keys can exist at any time.

## API Log

The **API Log** tab shows all API requests with method, endpoint, status code, response time, key used, and IP address. Logs are sortable and filterable by API key.

Logs older than 30 days are automatically purged. Use the **Clear Log** button to remove all entries, or clear from Maintenance > Rebuild > Clear Logs.

## API Documentation

For full API documentation including endpoints, authentication, code examples, and query parameters, visit the [CubeCart Knowledge Base](https://kb.cubecart.com/api-guide).
