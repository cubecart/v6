# File Manager

Browse, upload, and manage product images and digital download files.

## File Browser

Navigate through folders using breadcrumb links. Files can be sorted by name (A-Z or Z-A), size (low-high or high-low), or date added (ascending or descending). For images, toggle between list view and thumbnail sizes (small, medium, large, extra large).

### File Actions

| Action | Description |
| --- | --- |
| Edit | Opens the file editor (see Edit File below). |
| Delete | Remove the file. Select multiple files using checkboxes for bulk deletion. |
| Download | For non-image files, click the filename to download. For images, clicking opens the full-size image. |

### Search

Search for images by filename. Available in the image manager mode only.

## Edit File

When editing a file, the following fields are available:

| Field | Description |
| --- | --- |
| Image Preview | For image files, a preview is shown with the current pixel dimensions (width x height). |
| File Name | Rename the file. |
| Subfolder / Move | Move the file to a different subdirectory using the dropdown. |
| Alt Text | The HTML `alt` attribute for the image (important for accessibility and SEO). Only shown for image files. |
| Title | The HTML `title` attribute for the file. |
| Description | Text used for image tooltips on the storefront. Only shown when the file is streamable. |
| Stream | Toggle to enable or disable streaming for this file. Only shown for streamable file types. |

### Image Crop

For image files, an **Image Crop** tab is available. Use the interactive cropper to select a region of the image. The selected dimensions are displayed in pixels.

## Upload

Drag and drop files or use the file picker to upload. Multiple files can be uploaded at once. The maximum upload size is determined by your server's PHP configuration (both `upload_max_filesize` and `post_max_size`).

## Create Folder

Create a new subdirectory within the current folder to organise your files.

## Rebuild Database

Scans the filesystem and rebuilds the file manager database. Use this if files have been added via FTP or if the database is out of sync with the actual files on disk.

> [!TIP]
> When editing a file, the **Alt Text** field is used for the HTML alt attribute (important for accessibility and SEO), and the **Description** field is used for image tooltips on the storefront.
