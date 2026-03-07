# Maintenance

System maintenance tools for cache management, backups, database management, and upgrades.

## Rebuild

### Catalogue

Reset all product view counters to zero.

### SEO URLs

Two separate options for truncating SEO URLs:

- **Truncate Custom SEO URLs** -- removes manually-set friendly URLs.
- **Truncate Auto-Generated SEO URLs** -- removes system-generated friendly URLs.

> [!NOTE]
> Truncating SEO URLs removes friendly URLs. They will be regenerated automatically when pages are next visited, but external links and search engine indexes will break temporarily.

### Cache

- **Clear Main Cache** -- removes cached language files, configuration, and compiled templates.
- **Clear Image Cache** -- removes resized/cached image thumbnails. They will be regenerated on demand.

### Clear Logs

| Log | Description |
| --- | --- |
| Admin Access Logs | Admin login attempt records. |
| Error Logs | System and admin error entries. |
| Email Logs | Sent email records. |
| Request Logs | HTTP request/response logs. |
| Transaction Logs | Payment gateway transaction records. |
| Search Terms | Customer search query history. |
| Cookie Consent | Cookie consent acknowledgement records. |
| Sessions | Active session data. |

### Miscellaneous

**Generate XML Sitemap** -- creates a sitemap.xml file for search engines.

## Backup

### File Backup

Creates a ZIP archive of all store files. Options:

| Option | Description |
| --- | --- |
| Skip Images | Exclude the images directory to reduce archive size. |
| Skip Downloads | Exclude the digital downloads directory to reduce archive size. |

### Database Backup

| Option | Description |
| --- | --- |
| Include DROP TABLE | Add DROP TABLE statements so restoring replaces existing tables. |
| Include Structure | Include CREATE TABLE statements. |
| Include Data | Include INSERT statements with table data. |
| Compress | Compress the backup as a .gz file. |
| Include 3rd Party Tables | Include database tables that do not use the CubeCart prefix (e.g. tables from other applications sharing the database). |

### Existing Backups

Lists all backup files with their size. Actions available: download, delete, restore, or compress.

> [!NOTE]
> Restoring a database backup overwrites your current data. Always verify you are restoring the correct file.

## Upgrade

Checks your CubeCart version against the latest release. If an update is available, click the upgrade button to begin the automatic update process.

If automatic upgrades have been disabled by your hosting provider, a message is shown with a contact email address.

### Upgrade History

A table listing all previously installed CubeCart versions and the date each was applied. Version numbers link to their release notes when available.

| Column | Description |
| --- | --- |
| CubeCart Version | The version number. Links to release notes if available. |
| Date | When that version was installed or upgraded to. |

## Database

The database tab provides direct management of your CubeCart database tables.

### Missing Tables, Columns, and Indexes

If any expected CubeCart tables, columns, or indexes are missing, warning messages are displayed at the top of the tab. Suggested SQL fix queries are shown in a copyable text area that can be run via the SQL Tools page.

### Table List

| Column | Description |
| --- | --- |
| Checkbox | Select tables for bulk actions. |
| Name | The database table name. |
| Records | Number of rows in the table. |
| Engine | The storage engine (e.g. InnoDB, MyISAM). |
| Collation | The character set collation of the table. |
| Size | The data size of the table. |
| Overhead | Wasted space that can be reclaimed by optimising. |
| Indexes | Shows **OK** if all expected indexes are present, or a warning icon with details if indexes are missing or incorrect. |

### Table Actions

Select one or more tables using the checkboxes, then choose an action:

| Action | Description |
| --- | --- |
| Rebuild | Optimise and rebuild the selected tables. |
| Check | Run a consistency check on the selected tables. |
| Analyze | Update index statistics for the query optimiser. |

After running an action, results are displayed showing the table name, operation performed, message type, and message text.

## Elasticsearch

If Elasticsearch is enabled in your store settings, this tab shows:

- **Document Count** -- the number of indexed documents.
- **Index Size** -- the size of the Elasticsearch index.
- A **Build** or **Rebuild** button to (re)index all products.

If Elasticsearch is not enabled, an informational page explains the feature and how to enable it in store settings, with a demo video.
