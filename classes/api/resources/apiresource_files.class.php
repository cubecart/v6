<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * File Manager API resource controller
 */
class ApiResource_Files extends ApiResource
{
    protected $_resourceName = 'files';

    /**
     * GET /files
     */
    public function listing()
    {
        $this->_requireRead();
        $pagination = $this->_getPagination();

        $where = array();
        if (isset($_GET['type'])) {
            $where['type'] = (int)$_GET['type'];
        }
        if (isset($_GET['filepath'])) {
            $where['filepath'] = $_GET['filepath'];
        }

        $search = $this->_buildSearch(array('filename', 'description'));
        $where = array_merge($where, $search);

        $sort = $this->_getSort(array('file_id', 'filename', 'filepath', 'type'), 'file_id');

        $total = $this->_db->count('CubeCart_filemanager', 'file_id', $where);
        $files = $this->_db->select('CubeCart_filemanager', false, $where, $sort, $pagination['per_page'], $pagination['page']);

        $data = array();
        if ($files) {
            foreach ($files as $file) {
                $data[] = $this->_formatFile($file);
            }
        }

        ApiResponse::paginated($data, $pagination['page'], $pagination['per_page'], (int)$total);
    }

    /**
     * GET /files/{id}
     * Special: GET /files/directories
     */
    public function get($id)
    {
        $this->_requireRead();

        if ($id === 'directories') {
            $this->_listDirectories();
            return;
        }

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid file ID', 'BAD_REQUEST', 400);
        }

        $result = $this->_db->select('CubeCart_filemanager', false, array('file_id' => (int)$id));
        if (!$result) {
            ApiResponse::error('File not found', 'NOT_FOUND', 404);
        }

        $file = $this->_formatFile($result[0]);

        // Add full URL
        $file['url'] = CC_STORE_URL . '/images/source/' . $file['filepath'] . $file['filename'];

        ApiResponse::success($file);
    }

    /**
     * POST /files - upload a file
     * Accepts multipart/form-data OR JSON with base64 content
     */
    public function create()
    {
        $this->_requireWrite();

        // Handle multipart upload
        if (!empty($_FILES['file'])) {
            $this->_handleFileUpload();
            return;
        }

        // Handle base64 JSON upload
        $data = $this->_getRequestBody();
        if (isset($data['content']) && isset($data['filename'])) {
            $this->_handleBase64Upload($data);
            return;
        }

        ApiResponse::error('No file provided. Use multipart/form-data with "file" field or JSON with "content" (base64) and "filename"', 'BAD_REQUEST', 400);
    }

    /**
     * DELETE /files/{id}
     */
    public function delete($id)
    {
        $this->_requireDelete();

        if (!is_numeric($id)) {
            ApiResponse::error('Invalid file ID', 'BAD_REQUEST', 400);
        }

        $result = $this->_db->select('CubeCart_filemanager', false, array('file_id' => (int)$id));
        if (!$result) {
            ApiResponse::error('File not found', 'NOT_FOUND', 404);
        }

        $file = $result[0];

        // Delete physical file
        $filePath = CC_ROOT_DIR . '/images/source/' . $file['filepath'] . $file['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Remove image index entries
        $this->_db->delete('CubeCart_image_index', array('file_id' => (int)$id));

        // Remove DB record
        $this->_db->delete('CubeCart_filemanager', array('file_id' => (int)$id));

        ApiResponse::noContent();
    }

    // ======= Private helpers =======

    private function _handleFileUpload()
    {
        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = array(
                UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit',
                UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit',
                UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            );
            $msg = isset($errors[$file['error']]) ? $errors[$file['error']] : 'Unknown upload error';
            ApiResponse::error($msg, 'UPLOAD_ERROR', 400);
        }

        $filename = $this->_sanitiseFilename($file['name']);
        if ($filename === false) {
            ApiResponse::error('Disallowed file type', 'BAD_REQUEST', 400);
        }

        $subdir = $this->_sanitiseSubdir(isset($_POST['filepath']) ? $_POST['filepath'] : '');
        if ($subdir === false) {
            ApiResponse::error('Invalid filepath', 'BAD_REQUEST', 400);
        }

        $destDir = $this->_resolveDestDir($subdir);
        if ($destDir === false) {
            ApiResponse::error('Invalid filepath', 'BAD_REQUEST', 400);
        }
        $dest = $destDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            ApiResponse::error('Failed to save uploaded file', 'INTERNAL_ERROR', 500);
        }

        $fileId = $this->_db->insert('CubeCart_filemanager', array(
            'type'        => $this->_getFileType($filename),
            'filepath'    => $subdir,
            'filename'    => $filename,
            'filesize'    => filesize($dest),
            'mimetype'   => $file['type'],
            'md5hash'     => md5_file($dest),
            'description' => isset($_POST['description']) ? $_POST['description'] : '',
        ));

        $result = $this->_db->select('CubeCart_filemanager', false, array('file_id' => $fileId));
        ApiResponse::created($this->_formatFile($result[0]));
    }

    private function _handleBase64Upload($data)
    {
        $content  = base64_decode($data['content']);
        if ($content === false) {
            ApiResponse::error('Invalid base64 content', 'BAD_REQUEST', 400);
        }

        $filename = $this->_sanitiseFilename($data['filename']);
        if ($filename === false) {
            ApiResponse::error('Disallowed file type', 'BAD_REQUEST', 400);
        }

        $subdir = $this->_sanitiseSubdir(isset($data['filepath']) ? $data['filepath'] : '');
        if ($subdir === false) {
            ApiResponse::error('Invalid filepath', 'BAD_REQUEST', 400);
        }

        $destDir = $this->_resolveDestDir($subdir);
        if ($destDir === false) {
            ApiResponse::error('Invalid filepath', 'BAD_REQUEST', 400);
        }
        $dest = $destDir . $filename;

        if (file_put_contents($dest, $content) === false) {
            ApiResponse::error('Failed to write file', 'INTERNAL_ERROR', 500);
        }

        $mimeType = isset($data['mimetype']) ? $data['mimetype'] : 'application/octet-stream';

        $fileId = $this->_db->insert('CubeCart_filemanager', array(
            'type'        => $this->_getFileType($filename),
            'filepath'    => $subdir,
            'filename'    => $filename,
            'filesize'    => strlen($content),
            'mimetype'   => $mimeType,
            'md5hash'     => md5($content),
            'description' => isset($data['description']) ? $data['description'] : '',
        ));

        $result = $this->_db->select('CubeCart_filemanager', false, array('file_id' => $fileId));
        ApiResponse::created($this->_formatFile($result[0]));
    }

    /**
     * Sanitise an uploaded filename. Returns the cleaned name, or false if
     * the file type is disallowed (executable extensions) — see
     * FileManager::filenameIsIllegal() for the canonical denylist.
     */
    private function _sanitiseFilename($raw)
    {
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', (string)$raw);
        if ($name === '' || $name === null) {
            return false;
        }
        // Block executable / handler extensions and the .php.something pattern.
        if (preg_match('/(\.sh\.inc\.ini|\.htaccess|\.php|\.phar|\.phtml|\.php[3-6]|\.shtml|\.svg|\.cgi|\.pl|\.py|\.rb)$/i', $name)) {
            return false;
        }
        if (preg_match('/\.php\./i', $name)) {
            return false;
        }
        return $name;
    }

    /**
     * Sanitise the requested sub-directory under images/source/. Strips
     * embedded "..", absolute paths, drive letters and control chars, and
     * restricts segments to a safe charset. Returns the trailing-slashed
     * relative path, "" for root, or false for any traversal attempt.
     */
    private function _sanitiseSubdir($raw)
    {
        if ($raw === '' || $raw === null) {
            return '';
        }
        $path = str_replace('\\', '/', (string)$raw);
        if (preg_match('#(^|/)\.\.(/|$)#', $path)) {
            return false;
        }
        if ($path[0] === '/' || strpos($path, ':') !== false) {
            return false;
        }
        if (preg_match('/[\x00-\x1f]/', $path)) {
            return false;
        }
        $path = trim($path, '/');
        if ($path === '') {
            return '';
        }
        if (!preg_match('#^[a-zA-Z0-9._/-]+$#', $path)) {
            return false;
        }
        return $path . '/';
    }

    /**
     * Create (if needed) and resolve the destination directory under
     * images/source/. Returns the absolute filesystem path with trailing
     * slash, or false if the resolved path escapes the allowed root.
     */
    private function _resolveDestDir($subdir)
    {
        $base = CC_ROOT_DIR . '/images/source/';
        $destDir = $base . $subdir;
        if (!is_dir($destDir)) {
            if (!@mkdir($destDir, 0755, true) && !is_dir($destDir)) {
                return false;
            }
        }
        $realBase = realpath($base);
        $realDest = realpath($destDir);
        if ($realBase === false || $realDest === false) {
            return false;
        }
        // Ensure $realDest is $realBase or a descendant of it.
        if (strpos($realDest . DIRECTORY_SEPARATOR, $realBase . DIRECTORY_SEPARATOR) !== 0) {
            return false;
        }
        return rtrim($realDest, DIRECTORY_SEPARATOR) . '/';
    }

    private function _listDirectories()
    {
        $sourceDir = CC_ROOT_DIR . '/images/source/';
        $dirs = array();
        $this->_scanDirectories($sourceDir, '', $dirs);
        ApiResponse::success($dirs);
    }

    private function _scanDirectories($baseDir, $relative, &$dirs)
    {
        if (!is_dir($baseDir . $relative)) {
            return;
        }
        $handle = opendir($baseDir . $relative);
        if (!$handle) {
            return;
        }
        while (($entry = readdir($handle)) !== false) {
            if ($entry === '.' || $entry === '..') continue;
            $path = $relative . $entry . '/';
            if (is_dir($baseDir . $path)) {
                $dirs[] = $path;
                $this->_scanDirectories($baseDir, $path, $dirs);
            }
        }
        closedir($handle);
    }

    private function _formatFile($file)
    {
        $intFields = array('file_id', 'type', 'filesize');
        foreach ($intFields as $f) {
            if (isset($file[$f])) {
                $file[$f] = (int)$file[$f];
            }
        }
        return $file;
    }

    private function _getFileType($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $imageExts = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico');
        if (in_array($ext, $imageExts)) {
            return 1; // Image
        }
        return 2; // Other
    }
}
