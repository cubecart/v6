# API Keys

Manage REST API access to your store. API keys allow external applications to read and write store data over HTTPS.

## Overview

The CubeCart REST API is available at `/api/v1/` and provides access to 10 resources: Products, Categories, Orders, Customers, Coupons, Shipping, Tax, Settings, Reviews, and File Manager.

Each API key is linked to an admin user and has scoped permissions per resource.

## Creating a Key

Click the **Create New** tab and fill in:

| Field | Description |
| --- | --- |
| Label | A descriptive name (e.g. "ERP Integration"). |
| Owner | The admin user this key is linked to. |
| Rate Limit | Max requests per minute (default 60). |
| Expires | Optional expiry date. |
| IP Whitelist | One IP per line. Blank allows all. |

### Permissions

Set access per resource:

- **Read** -- GET requests only.
- **Read/Write** -- GET, POST, and PUT.
- **Read/Write/Delete** -- Full access.
- **None** -- No access.

After creating a key, the **API Key** and **API Secret** are shown once. Copy them immediately -- the secret cannot be retrieved later.

## Authentication

All API requests require a Bearer token:

```
Authorization: Bearer {api_key}:{api_secret}
```

Example:

```
curl -H "Authorization: Bearer ccapi_abc:secret" https://yourstore.com/api/v1/products
```

## Endpoints

All endpoints return JSON:

```
{"success": true, "data": {...}, "meta": {"page": 1, "per_page": 20, "total": 150}}
```

### Products

- `GET /products` -- List (filter: `status`, `cat_id`, `manufacturer`, `featured`, `search`)
- `GET /products/{id}` -- Detail with categories, images, options
- `POST /products` -- Create (required: `name`, `price`)
- `PUT /products/{id}` -- Update
- `DELETE /products/{id}` -- Delete with related data
- `GET /products/{id}/images` -- List images
- `POST /products/{id}/images` -- Assign image (`file_id`)
- `DELETE /products/{id}/images/{file_id}` -- Remove image
- `GET /products/{id}/options` -- List options
- `POST /products/{id}/options` -- Assign option (`option_id`)
- `GET /products/{id}/reviews` -- List reviews

### Categories

- `GET /categories` -- List (filter: `status`, `parent_id`, `search`)
- `GET /categories/tree` -- Nested tree
- `GET /categories/{id}` -- Detail with product count
- `POST /categories` -- Create (required: `cat_name`)
- `PUT /categories/{id}` -- Update
- `DELETE /categories/{id}` -- Delete (children reassigned)
- `GET /categories/{id}/products` -- Products in category

### Orders

- `GET /orders` -- List (filter: `status`, `customer_id`, `date_from`, `date_to`, `search`)
- `GET /orders/{id}` -- Detail with items, notes, taxes (accepts numeric ID or cart_order_id)
- `POST /orders` -- Create (required: `first_name`, `last_name`, `email`)
- `PUT /orders/{id}` -- Update
- `PUT /orders/{id}/status` -- Change status (required: `status` 1-7)
- `GET /orders/{id}/items` -- Line items
- `GET /orders/{id}/notes` -- Notes
- `POST /orders/{id}/notes` -- Add note (required: `content`)

Status values: 1=Pending, 2=Processing, 3=Complete, 4=Declined, 5=Failed, 6=Refunded, 7=Cancelled.

### Customers

- `GET /customers` -- List (filter: `status`, `customer_group_id`, `search`)
- `GET /customers/{id}` -- Detail (passwords never exposed)
- `POST /customers` -- Create (required: `first_name`, `last_name`, `email`)
- `PUT /customers/{id}` -- Update
- `DELETE /customers/{id}` -- Anonymize (GDPR)
- `GET /customers/{id}/addresses` -- List addresses
- `POST /customers/{id}/addresses` -- Add address (required: `line1`, `town`, `postcode`, `country`)
- `PUT /customers/{id}/addresses/{id}` -- Update address
- `GET /customers/{id}/orders` -- Order history

### Coupons

- `GET /coupons` -- List (filter: `status`, `search`)
- `GET /coupons/{id}` -- Detail
- `POST /coupons` -- Create (required: `code`)
- `PUT /coupons/{id}` -- Update
- `DELETE /coupons/{id}` -- Delete
- `GET /coupons/validate/{code}` -- Validate code

### Shipping

- `GET /shipping` -- Methods overview
- `GET /shipping/methods` -- List modules
- `GET /shipping/rates` -- List rates
- `POST /shipping` -- Create rate
- `PUT /shipping/{id}` -- Update rate
- `DELETE /shipping/{id}` -- Delete rate

### Tax

- `GET /tax` -- Classes and rates overview
- `GET /tax/classes` -- List classes
- `GET /tax/rates` -- List rates
- `GET /tax/{id}` -- Rate with details
- `POST /tax` -- Create rate (required: `name`)
- `PUT /tax/{id}` -- Update rate
- `DELETE /tax/{id}` -- Delete rate and details
- `POST /tax/calculate` -- Calculate (required: `price`, `tax_type`)

### Settings

- `GET /settings` -- All settings (sensitive keys excluded)
- `GET /settings/{key}` -- Single setting
- `GET /settings/store` -- Store name, URL, version, currency, timezone
- `GET /settings/currencies` -- Currencies
- `GET /settings/countries` -- Countries
- `GET /settings/zones/{country_id}` -- States/zones
- `GET /settings/languages` -- Languages
- `PUT /settings` -- Batch update

### Reviews

- `GET /reviews` -- List (filter: `product_id`, `approved`)
- `GET /reviews/{id}` -- Detail
- `PUT /reviews/{id}` -- Update/approve
- `DELETE /reviews/{id}` -- Delete

### Files

- `GET /files` -- List (filter: `type`, `filepath`, `search`)
- `GET /files/{id}` -- Metadata and URL
- `GET /files/directories` -- Image directories
- `POST /files` -- Upload (multipart or base64 JSON)
- `DELETE /files/{id}` -- Delete from disk and DB

## Query Parameters

All list endpoints support:

- `page` -- Page number (default 1)
- `per_page` -- Items per page (default 20, max 100)
- `sort` -- Field to sort by
- `order` -- `asc` or `desc`
- `fields` -- Comma-separated sparse fieldsets
- `search` -- LIKE search across relevant fields

## Rate Limiting

Every response includes:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 42
X-RateLimit-Reset: 1711400460
```

Exceeding the limit returns **429 Too Many Requests** with a `Retry-After` header.

## Error Responses

```
{"success": false, "error": {"code": "NOT_FOUND", "message": "Product not found", "status": 404}}
```

Common codes: 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, 409 Conflict, 422 Validation Error, 429 Rate Limited, 500 Internal Error.

## API Log

The **API Log** tab shows all requests with method, endpoint, status, response time, key, and IP. Sortable and filterable by key.

Logs older than 30 days are automatically purged. Clear all logs from Maintenance or the Clear Log button on the API Log tab.

## Security

- HTTPS required for all requests.
- Secrets are bcrypt-hashed and cannot be retrieved.
- Optional IP whitelisting per key.
- Keys inherit the permission ceiling of their admin owner.
- Sensitive settings (DB credentials, SMTP passwords) are never exposed.
