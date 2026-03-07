# URL Redirects

Manage URL redirects and monitor 404 errors.

## Redirects

Create redirects from old URLs to new destinations. Useful when products or pages are moved or renamed.

| Field | Description |
| --- | --- |
| Status Code | 301 (Permanent) or 302 (Temporary). Use 301 for permanent moves to transfer SEO value. |
| Page Type | The type of destination: Product, Category, Document, Sale Items, Gift Certificates, Contact, Search, Login, or Register. |
| From Path | The old URL path to redirect from. |
| Item ID | The ID of the destination item (e.g. the product ID or category ID). Not required for static page types. |
| Destination | Shows the resolved destination URL. |

## Missing URIs (404 Log)

URLs that visitors have tried to access but returned a 404 Not Found error.

| Column | Description |
| --- | --- |
| ID | The database record ID for this 404 entry. |
| URI | The URL path that was not found. |
| Hits | Number of times this URI has been requested. |
| Created | When the 404 was first logged. |
| Done | Mark a 404 as resolved (e.g. after creating a redirect for it). |
| Ignore | Move the URI to the ignored list so it no longer appears here. |

## Ignored URIs

URIs previously moved from the 404 log. These are still tracked for hit counts but hidden from the main list. Shows ID, URI, Hits, and a Remove action. Remove an ignored URI to move it back to the 404 log.

> [!TIP]
> Regularly review the 404 log to identify broken links and create redirects. This improves user experience and preserves search engine rankings.
