# Documents (CMS Pages)

Create and manage static content pages for your store, such as About Us, Terms & Conditions, and Privacy Policy.

## Document List

All documents are listed with drag-and-drop handles to control their display order in the store navigation.

| Column | Description |
| --- | --- |
| Arrange | Drag handle to reorder documents. |
| Status | Toggle to enable or disable the page on the storefront. |
| ID | The unique document identifier. |
| Primary Language | Flag icon showing the primary language of the document. |
| Document Title | Click to edit the document. Titles with **Hide Title** enabled are shown with a strikethrough style. |
| Translations | Language flags showing which translations exist. Click a flag to edit that translation. |
| Terms | Radio button to designate this document as the store's Terms & Conditions. |
| Homepage | Radio button to designate this document as the store homepage content. |
| Privacy | Radio button to designate this document as the Privacy Policy. |

### Row Actions

Each document row has the following action icons:

| Action | Description |
| --- | --- |
| View | Opens the document on the storefront in a new tab. |
| Translate | Add a new translation for this document. |
| Edit | Open the document editor. |
| Delete | Delete the document and all its translations. |

## Editing a Document

### General Tab

| Field | Description |
| --- | --- |
| Title | The page heading displayed to customers. |
| Language | Which language this version of the document is for. |
| Status | Enable or disable the page. |
| Hide Title | Do not display the title heading on the page. |
| URL | Optional external URL. If set, clicking the navigation link goes to this URL instead of showing content. |
| Target | Open the URL in the same window or a new window. |
| Navigation Link | Show this page as a link in the store navigation menu. |

### Content Tab

Rich text editor for the page content. The **Parse Content** toggle allows Smarty template syntax within the content (for advanced users).

### SEO Tab

| Field | Description |
| --- | --- |
| Meta Title | Custom page title for search engines. Character count shown. |
| SEO Path | The URL-friendly path for this page. Auto-generated from the title if left blank. |
| Meta Description | Description shown in search engine results. Character count shown. |

#### SEO Redirects

If the SEO path has been changed previously, a redirects table is shown listing old paths that now redirect to the current path. Each redirect displays the old path, the HTTP status code, and a delete action to remove the redirect.

## Translations

Each document can have translations for every installed language. Use the translate action to create a version in another language. A document must have at least one translation and cannot be deleted if it is the only version.

## Special Document Types

Only one document at a time can be designated as Terms & Conditions, Homepage, or Privacy Policy. These are used by the checkout process and GDPR compliance features respectively.
