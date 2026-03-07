# File Manager

Browse, upload, and manage product images and digital download files.

## File Browser

Navigate through folders using breadcrumb links. Files can be sorted by name, size, or date. For images, toggle between list view and thumbnail sizes (small, medium, large, extra large).

### File Actions

| Action | Description |
| --- | --- |
| Edit | Update the filename, description, and alt text for an image. |
| Delete | Remove the file. Select multiple files using checkboxes for bulk deletion. |
| Download | Download the file to your computer. |

### Search

Search for images by filename. Available in the image manager mode only.

## Upload

Drag and drop files or use the file picker to upload. Multiple files can be uploaded at once. The maximum upload size is determined by your server's PHP configuration.

## Create Folder

Create a new subdirectory within the current folder to organise your files.

## Rebuild Database

Scans the filesystem and rebuilds the file manager database. Use this if files have been added via FTP or if the database is out of sync with the actual files on disk.

> [!TIP]
> When editing a file, the description field is used for image tooltips on the storefront, and the alt text is used for the HTML alt attribute (important for accessibility and SEO).
