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

        $subdir = isset($_POST['filepath']) ? trim($_POST['filepath'], '/') . '/' : '';
        $destDir = CC_ROOT_DIR . '/images/source/' . $subdir;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $dest = $destDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            ApiResponse::error('Failed to save uploaded file', 'INTERNAL_ERROR', 500);
        }

        $fileId = $this->_db->insert('CubeCart_filemanager', array(
            'type'        => $this->_getFileType($filename),
            'filepath'    => $subdir,
            'filename'    => $filename,
            'filesize'    => filesize($dest),
            'mime_type'   => $file['type'],
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

        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $data['filename']);
        $subdir   = isset($data['filepath']) ? trim($data['filepath'], '/') . '/' : '';
        $destDir  = CC_ROOT_DIR . '/images/source/' . $subdir;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $dest = $destDir . $filename;
        if (file_put_contents($dest, $content) === false) {
            ApiResponse::error('Failed to write file', 'INTERNAL_ERROR', 500);
        }

        $mimeType = isset($data['mime_type']) ? $data['mime_type'] : 'application/octet-stream';

        $fileId = $this->_db->insert('CubeCart_filemanager', array(
            'type'        => $this->_getFileType($filename),
            'filepath'    => $subdir,
            'filename'    => $filename,
            'filesize'    => strlen($content),
            'mime_type'   => $mimeType,
            'md5hash'     => md5($content),
            'description' => isset($data['description']) ? $data['description'] : '',
        ));

        $result = $this->_db->select('CubeCart_filemanager', false, array('file_id' => $fileId));
        ApiResponse::created($this->_formatFile($result[0]));
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
