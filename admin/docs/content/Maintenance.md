# Maintenance

System maintenance tools for cache management, backups, and upgrades.

## Rebuild

### Catalogue

Reset all product view counters to zero.

### SEO URLs

Truncate custom SEO URLs or auto-generated URLs. Useful if URLs have become inconsistent or after a bulk import.

> [!NOTE]
> Truncating SEO URLs removes all friendly URLs. They will be regenerated automatically when pages are next visited, but external links and search engine indexes will break temporarily.

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

Creates a ZIP archive of all store files. Options to skip images and/or digital download files to reduce the archive size.

### Database Backup

| Option | Description |
| --- | --- |
| Include DROP TABLE | Add DROP TABLE statements so restoring replaces existing tables. |
| Include Structure | Include CREATE TABLE statements. |
| Include Data | Include INSERT statements with table data. |
| Compress | Compress the backup as a .gz file. |

### Existing Backups

Lists all backup files with their size. Actions available: download, delete, restore, or compress.

> [!NOTE]
> Restoring a database backup overwrites your current data. Always verify you are restoring the correct file.

## Upgrade

Checks your CubeCart version against the latest release. If an update is available, click the upgrade button to begin the automatic update process.
