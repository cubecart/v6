# URL Redirects

Manage URL redirects and review the storefront 404 log. Three tabs: **Redirects**, **404 Log**, and **Ignored**.

## Redirects

### Add a redirect

Use the *Add Redirect* fieldset at the top of the tab:

| Field | Description |
| --- | --- |
| Status Code | *301 (Permanent)* — preferred for permanent moves; transfers SEO value. *302 (Temporary)* — for temporary moves. |
| Page | Group the destination by *Dynamic Pages* (Product, Category, Document) or *Static Pages* (Sale Items, Gift Certificates, Contact, Search, Login, Register). |
| Redirect from Path | The old URL path. Paths shown in the table are site-root relative (e.g. `/store/foo.html`); the install prefix is stripped automatically when stored. |
| Redirect to Destination | For Dynamic Pages, type-to-search and click a result to choose the destination — there's no need to know the numeric ID. The name appears once chosen; click it to switch back to the search box. For Static Pages this field is hidden. |

Click **Add** when complete. If the path already has a redirect or matches a live page, the form returns an error and links to the conflicting object so you can review it.

### Filter

Above the redirects table is a *Filter* fieldset:

| Filter | Effect |
| --- | --- |
| Path | Substring match on the *Redirect from Path* column. |
| Redirect | Restrict to *301* or *302*. |
| Page | Restrict to one of the page types. |

**Go** applies the filter; **Reset** appears when at least one filter is active and clears them all.

### List columns

| Column | Description |
| --- | --- |
| Action | Trash icon to delete the redirect rule. |
| Status Code | *301* or *302*. |
| Page | The destination page type. |
| Redirect from Path | Site-root-relative path (the visitor URL). |
| Item ID | Internal ID of the destination object, or `-` for static pages. |
| Redirect to Destination | The current resolved URL of the target — updates automatically if the SEO slug of the underlying object changes. |
| Hits | Number of times the redirect has fired. Lets you spot rules that no longer get used. |
| Last seen | Timestamp of the most recent fire, or `—` if it has never fired. |

## 404 Log

URIs that visitors hit and got a 404 for, in the last 90 days.

The logger automatically **skips** the following so the table only contains actionable entries:

- Asset extensions (`.png`, `.jpg`, `.gif`, `.webp`, `.svg`, `.css`, `.js`, `.map`, `.woff`/`.woff2`, `.ttf`, `.eot`, fonts/audio/video/`.pdf`/`.json`/`.xml`).
- Hidden / dotfile segments (`.DS_Store`, `.env`, `.git/HEAD`, `.htaccess`, etc.) — almost always bot recon.
- Anything pointing at the admin folder/file (uses your configured `adminFolder` / `adminFile` values).
- The literal `undefined` URI produced by storefront JavaScript bugs.

### List columns

| Column | Description |
| --- | --- |
| (checkbox) | Select rows for the With Selected dropdown at the foot of the table. |
| ID | Database record ID. |
| URI | Site-root-relative URL. |
| Hits | Number of times the URI has been requested. |
| Created | When the 404 was first logged. |
| Done | Green tick = resolved, red cross = unresolved. Auto-flips to green when you create a matching redirect. A warning triangle appears next to the green tick if the same URI is hit *after* being marked done — click it to acknowledge. |
| Ignore | Move the URI to the *Ignored* tab. Asks for confirmation. |
| Add | Click the <i class="fa fa-plus-circle"></i> to jump to the Redirects tab with this URI prefilled in the *Redirect from Path* field. |

### Bulk action

The footer of the table holds a *Check/Uncheck all* link plus a *With Selected* dropdown:

| Action | Effect |
| --- | --- |
| Ignore | Move all ticked rows to the *Ignored* tab. |
| Delete | Permanently remove the ticked rows from the log. |

## Ignored

URIs you've chosen to suppress. Hits keep accumulating in the background but they don't clutter the 404 Log. Each row has a Remove (trash) icon to send it back to the 404 Log; the **Clear All Ignored** button at the foot of the tab purges every ignored row in one click.

> [!TIP]
> Regularly review the 404 log to identify broken links and create redirects. This improves user experience and preserves search engine rankings.
