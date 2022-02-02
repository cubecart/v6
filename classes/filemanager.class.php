<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

class FileManager
{
    private $_directories;
    private $_mode;

    private $_manage_cache;
    private $_manage_dir;
    private $_manage_root;
    private $_recently_uploaded = array();
    private $_sub_dir;
    private $_sendfile = false;
    private $_max_upload_image_size = 350000;
    private $_md5_filesize_limit = 10485760;

    public $form_fields = false;

    const FM_FILETYPE_IMG 	= 1;
    const FM_FILETYPE_DL 	= 2;

    const FM_DL_ERROR_EXPIRED 	= 1;
    const FM_DL_ERROR_MAXDL 	= 2;
    const FM_DL_ERROR_NOFILE 	= 3;
    const FM_DL_ERROR_NOPRODUCT = 4;
    const FM_DL_ERROR_NORECORD 	= 5;
    const FM_DL_ERROR_PAYMENT 	= 6;

    ##############################################

    public function __construct($mode = false, $sub_dir = false)
    {
        switch ($mode) {
        case self::FM_FILETYPE_DL:
            $this->_manage_root = CC_ROOT_DIR.'/files';
            break;
        case self::FM_FILETYPE_IMG:
        default:
            $mode = 1;
            $this->_manage_root = CC_ROOT_DIR.'/images/source';
            $this->_manage_cache = CC_ROOT_DIR.'/images/cache';
        }
    
        $this->_setUploadLimit();
        $this->_mode  = (int)$mode;
        $this->_manage_dir = str_replace(CC_ROOT_DIR.'/', '', $this->_manage_root);
        $this->_sub_dir  = ($sub_dir) ? $this->formatPath($sub_dir) : null;

        //Auto-handler: Create Directory
        if (isset($_POST['fm']['create-dir']) && $_POST['fm']['create-dir']!=='') {
            if ($create = $this->createDirectory($_POST['fm']['create-dir'])) {
                $GLOBALS['gui']->setNotify($GLOBALS['language']->filemanager['success_create_folder']);
            } else {
                $GLOBALS['gui']->setError($GLOBALS['language']->filemanager['error_create_folder']);
            }
        }
        // Auto-handler: image details & cropping
        if (isset($_POST['file_id']) && is_numeric($_POST['file_id'])) {
            if (($file = $GLOBALS['db']->select('CubeCart_filemanager', false, array('file_id' => (int)$_POST['file_id']))) !== false) {
                if (isset($_POST['details'])) {
                    if (!$this->filenameIsIllegal($_POST['details']['filename'])) {

                        // Update details
                        $new_location = $current_location = $this->_manage_root.'/'.urldecode($this->_sub_dir);
                        $new_filename = $current_filename = $file[0]['filename'];
                        $new_subdir  = $this->_sub_dir;

                        if ($file[0]['filename'] != $_POST['details']['filename']) {
                            $new_filename = $this->formatName($_POST['details']['filename']);
                        }
                        if (isset($_POST['details']['move']) && !empty($_POST['details']['move'])) {
                            $move_to = $this->_manage_root.'/'.$this->formatPath($_POST['details']['move']);
                            if (is_dir($move_to)) {
                                $new_location = $move_to;
                                $new_subdir  = $this->formatPath(str_replace($this->_manage_root, '', $new_location), false);
                            }
                        }
                        // Does it need moving?
                        if ($new_location != $current_location || $new_filename != $current_filename) {
                            if (file_exists($current_location.$current_filename) && rename($current_location.$current_filename, $new_location.$new_filename)) {
                                $this->_sub_dir  = $new_subdir;
                                $current_location = $new_location;
                                $current_filename = $new_filename;
                                // Database record
                                $record['filename'] = $new_filename;
                                $record['filepath'] = $this->formatPath($this->_sub_dir);
                                $record['filepath'] = ($this->_sub_dir == null) ? 'NULL' : $this->formatPath($this->_sub_dir);
                            } else {
                                $GLOBALS['gui']->setError($GLOBALS['language']->filemanager['error_file_moved']);
                            }
                        }
                        $record['description'] = strip_tags($_POST['details']['description']);
                        $record['title'] = $_POST['details']['title'];
                        $record['stream'] = $_POST['details']['stream'];

                        $update = false;
                        foreach ($record as $k => $v) {
                            if (!isset($file[0][$k]) || $file[0][$k] != $v) {
                                $update = true;
                            }
                        }
                        if ($update) {
                            if (!$GLOBALS['db']->update('CubeCart_filemanager', $record, array('file_id' => (int)$_POST['file_id']))) {
                                $GLOBALS['gui']->setError($GLOBALS['language']->filemanager['error_file_update']);
                            }
                        }
                    } else {
                        $GLOBALS['gui']->setError($GLOBALS['language']->filemanager['error_file_update']);
                    }
                }
                if (isset($_POST['resize']) && !empty($_POST['resize']['w']) && !empty($_POST['resize']['h'])) {
                    $resize = $_POST['resize'];
                    if (file_exists($this->_manage_root.'/'.$this->_sub_dir.$current_filename)) {
                        // Use Hi-res image
                        $source = $this->_manage_root.'/'.$this->_sub_dir.$current_filename;
                        $size = getimagesize($source);
                        $gd  = new GD(dirname($source), false, 100);
                        $gd->gdLoadFile($source);
                        # TO DO: ROTATION
                        $gd->gdCrop((int)$resize['x'], (int)$resize['y'], (int)$resize['w'], (int)$resize['h']);
                        if ($gd->gdSave(basename($source))) {
                            // Delete previously generated images
                            preg_match('#(\w+)(\.\w+)$#', $current_filename, $match);
                            if (($files = glob($current_location.$match[1].'*', GLOB_NOSORT)) !== false) {
                                foreach ($files as $file) {
                                    if ($file != $source) {
                                        unlink($file);
                                    }
                                }
                            }
                            $this->deleteCachedImages($source);
                            $GLOBALS['gui']->setNotify($GLOBALS['language']->filemanager['notify_image_update']);
                        } else {
                            $GLOBALS['gui']->setError($GLOBALS['language']->filemanager['error_image_update']);
                        }
                    }
                }
                httpredir(currentPage(null, array('subdir' => $this->formatPath($this->_sub_dir, false))));
            }
        }
        // Create a directory list
        $this->findDirectories($this->_manage_root);
    }

    //=====[ Public ]=======================================

    /**
     * Setup admin screen
     *
     * @param bool $select_button
     * @return bool
     */
    public function admin($select_button = false)
    {
        $this->listFiles(false, $select_button);
        if (isset($_GET['CKEditorFuncNum'])) {
            $GLOBALS['smarty']->assign('CK_FUNC_NUM', (int)$_GET['CKEditorFuncNum']);
        }

        $GLOBALS['smarty']->assign('mode_list', true);

        return $GLOBALS['smarty']->fetch('templates/filemanager.index.php');
    }

    /**
     * Assign products to an image
     *
     * @param array $image_ids
     * @param int $product_id
     * @return bool
     */
    public function assignProductImages($image_ids, $product_id)
    {
        $old_images = array();
        $img_add = array();
        $removed_images = array();
        // md5 compare of before / after so we know if changes have been made or not
        if (($before = $GLOBALS['db']->select('CubeCart_image_index', array('product_id', 'file_id', 'main_img'), array('product_id' => (int)$product_id))) !== false) {
            $hash_before = md5(serialize($before));
            foreach ($before as $old_img) {
                $old_images[] = $old_img['file_id'];
                if ($old_img['main_img'] == 1) {
                    $old_default = $old_img['file_id'];
                }
            }
        }

        foreach ($image_ids as $image_id => $status) {
            if ($status == 0) {
                $removed_images[] = $image_id;
                continue;
            }

            if ($status == 2) {
                $default = $image_id;
            }

            $img_add[] = $image_id;
        }

        foreach ($old_images as $image_id) {
            if (!in_array($image_id, $removed_images) && !in_array($image_id, $img_add)) {
                $img_add[] = $image_id;
                if (isset($old_default) && $image_id == $old_default && !isset($default)) {
                    $default = $old_default;
                }
            }
        }

        // If no default image was chosen pick last one and let staff member know
        if (!$default && sizeof($img_add) > 0) {
            $default = (int)$img_add[0];
            // Display warning message if more than one image was chosen
            if (sizeof($img_add) > 1) {
                $GLOBALS['main']->errorMessage($lang['catalogue']['error_image_defaulted']);
            }
        }

        $GLOBALS['db']->delete('CubeCart_image_index', array('product_id' => (int)$product_id));

        if (isset($img_add) && is_array($img_add)) {
            foreach ($img_add as $image_id) {
                if (($image = $GLOBALS['db']->select('CubeCart_filemanager', false, array('file_id' => (int)$image_id))) !== false) {
                    $record = array(
                        'file_id'  => (int)$image_id,
                        'product_id' => (int)$product_id,
                        'main_img'  => ($default == (int)$image_id) ? '1' : '0'
                    );
                    $GLOBALS['db']->insert('CubeCart_image_index', $record);
                }
            }
        }

        // md5 compare of before / after so we know if changes have been made or not
        if (($after = $GLOBALS['db']->select('CubeCart_image_index', array('product_id', 'file_id', 'main_img'), array('product_id' => (int)$product_id))) !== false) {
            $hash_after = md5(serialize($after));
        }
        if (isset($hash_before, $hash_after) && $hash_before !== $hash_after) {
            return true;
        }
        return false;
    }

    /**
     * Build image DB
     *
     * @param bool $purge
     * @param bool $tidy
     * @param string $dir
     * @return bool
     */
    public function buildDatabase($purge = false, $tidy = false, $dir = '')
    {
        $dir = (!empty($dir)) ? $dir : $this->_manage_root.'/'.$this->_sub_dir;
        findFiles($file_array, $dir);
        if (($existing = $GLOBALS['db']->select('CubeCart_filemanager', array('filename', 'filepath'), false, array('filename' => 'ASC'))) !== false) {
            foreach ($existing as $file) {
                $exists[] = $file['filepath'].$file['filename'];
            }
        }
        if ($file_array) {
            foreach ($file_array as $key => $file) {
                if (!is_dir($file)) {
                    // Skip file if it is not an image and we're in image mode
                    if ($this->_mode == 1 && !preg_match('/\.(jpeg|jpg|png|gif|webp)$/i', $file)) {
                        continue;
                    }

                    // Skip existing entries, and sources/thumbs
                    if (isset($exists) && in_array(str_replace(array($this->_manage_root.'/', 'source/'), '', $file), $exists)) {
                        continue;
                    }

                    $newfilename = $this->makeFilename($file);
                    $oldfilename = basename($file);
                    if ($newfilename !== $oldfilename) {
                        // rename file so we match up
                        $new_path = str_replace($oldfilename, $newfilename, $file);
                        if (!rename($file, $new_path)) {
                            trigger_error("Failed to rename file from '$oldfilename' to '$newfilename'.", E_USER_WARNING);
                        } else {
                            $file = $new_path;
                        }
                    }

                    $filepath_record = $this->formatPath(str_replace($this->_manage_root, '', dirname($file)));
                    $filepath_record = empty($filepath_record) ? 'NULL' : $filepath_record;
                    $filepath_record = str_replace(chr(92), "/", $filepath_record);

                    $filesize = filesize($file);

                    $record = array(
                        'type'  => (int)$this->_mode,
                        'filepath' => $filepath_record,
                        'filename' => $newfilename,
                        'filesize' => $filesize,
                        'mimetype' => $this->getMimeType($file),
                        'md5hash' => $this->md5file($file, $filesize),
                    );

                    // Hash comparison check
                    $checkhash = $GLOBALS['db']->select('CubeCart_filemanager', array('file_id'), array('type' => $this->_mode, 'md5hash' => $record['md5hash'], 'filepath' => $record['filepath'], 'filename' => $record['filename']), false, 1);
                    if (!$checkhash) {
                        $GLOBALS['db']->insert('CubeCart_filemanager', $record);
                        $updated = true;
                    } else {
                        if ($tidy) {
                            unlink($file);
                        }
                    }
                }
            }
        }
        // Remove orphaned records
        if (($existing = $GLOBALS['db']->select('CubeCart_filemanager', false, array('type' => $this->_mode))) !== false) {
            foreach ($existing as $file) {
                if ($file['file_id']>0 && !file_exists($this->_manage_root.'/'.$file['filepath'].$file['filename'])) {
                    $GLOBALS['db']->delete('CubeCart_filemanager', array('file_id' => (int)$file['file_id']));
                    $updated = true;
                }
            }
        }

        if (isset($updated) && $updated === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Images assigned to a category
     *
     * @param string $cat_id
     * @return array
     */
    public function catImages($cat_id)
    {
        if (!empty($cat_id) && $cat_id>0) {
            $images = $GLOBALS['db']->select('CubeCart_category', array('cat_image'), array('cat_id' => (int)$cat_id));
            if ($images!==false) {
                $assigned_images = array();
                foreach ($images as $image) {
                    $assigned_images[$image['cat_image']] = '1';
                }
                return $assigned_images;
            }
        } elseif ($GLOBALS['session']->has('recently_uploaded')) {
            $assigned_images = $GLOBALS['session']->get('recently_uploaded');
            end($assigned_images); // Set last image as selected
            $key = key($assigned_images);
            $GLOBALS['session']->delete('recently_uploaded');
            $this->form_fields = true;
            return array($key => '1');
        }
        return array();
    }
    
    /**
     * Get unique assigned image info
     *
     * @param string $id (of image)
     * @return array
     */
    public function uniqueImage($id)
    {       
        if ($GLOBALS['session']->has('recently_uploaded')) {
            $assigned_images = $GLOBALS['session']->get('recently_uploaded');
            end($assigned_images); // Set last image as selected
            $key = key($assigned_images);
            $GLOBALS['session']->delete('recently_uploaded');
            $this->form_fields = true;
            return array($key => '1');
        } else {
            return array($id => 1);
        }
    }

    /**
     * Create folder
     *
     * @param string $new_dir
     * @return bool
     */
    private function createDirectory($new_dir = '')
    {
        if ($new_dir !== '') {
            $create = $this->formatName($new_dir);
            $path = $this->_manage_root.'/'.$this->_sub_dir.$create;
            if (!file_exists($path)) {
                $result = (bool)mkdir($path);
                if (!is_writable($path)) {
                    chmod($path, chmod_writable());
                }
                return $result;
            }
        }
        return false;
    }

    /**
     * Delete file
     *
     * @param string $target
     * @param string $del_folder
     * @return bool
     */
    public function delete($target = null, $del_folder = false)
    {
        if (!is_null($target)) {
            if (is_numeric($target)) {
                $status = $this->deleteFile($target);
            } else {
                $status = $this->deleteRecursive($target);
            }
            return $status;
        }
        return false;
    }

    /**
     * Delete cached images
     *
     * @param string $source
     * @return count
     */
    public function deleteCachedImages($source) {
        $cache_path = str_replace('/images/source/', '/images/cache/', $source);
        $ext = pathinfo($cache_path, PATHINFO_EXTENSION);
        $strlen = strlen($ext)*-1;
        $cache_path = substr($cache_path, 0, $strlen);
        $cache_path = $cache_path.'*.'.$ext;
        $i=0;
        if (($caches = glob($cache_path, GLOB_BRACE)) !== false) {
            foreach ($caches as $cached) {
                if(unlink($cached)) {
                    $i++;
                }
            }
        }
        return $i;
    }
    

    /**
     * Delete file
     *
     * @param int $file_id
     * @return bool
     */
    public function deleteFile($file_id = null)
    {
        if (!is_null($file_id) && is_numeric($file_id)) {
            if (($result = $GLOBALS['db']->select('CubeCart_filemanager', false, array('file_id' => (int)$file_id))) !== false) {
                if ($this->_mode == self::FM_FILETYPE_IMG && preg_match('#^image#', $result[0]['mimetype'])) {
                    // Clean the image cache
                    if (preg_match('#(.*)(\.\w+)$#iu', $result[0]['filename'], $match)) {
                        $filename = sprintf('%s.*%s', $match[1], $match[2]);
                        if (($caches = glob($this->_manage_cache.'/'.$this->_sub_dir.$filename, GLOB_BRACE)) !== false) {
                            foreach ($caches as $cached) {
                                unlink($cached);
                            }
                        }
                    }
                }
                $file = $this->_manage_root.'/'.$this->_sub_dir.$result[0]['filename'];
                if (file_exists($file) && unlink($file) || !file_exists($file)) {
                    if ($GLOBALS['db']->delete('CubeCart_filemanager', array('file_id' => (int)$file_id))) {
                        // Remove associated product indexes
                        $GLOBALS['db']->delete('CubeCart_image_index', array('file_id' => (int)$file_id));
                        // Remove associated category images
                        $GLOBALS['db']->update('CubeCart_category', array('cat_image' => 0), array('cat_image' => (int)$file_id));
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Recursive delete
     *
     * @param string $directory
     * @return bool
     */
    private function deleteRecursive($directory = null)
    {
        $directory = urldecode($directory);

        $valid_base_path = realpath($this->_manage_root);
        $path = $this->_manage_root.'/'.$directory;
        $realpath = realpath($path);
        if ($realpath === false || strpos($realpath, $valid_base_path) !== 0) {
            // Abort on potential directory traversal
            return false;
        }

        $scan = glob($path.'/'.'*');
        if (is_array($scan)) {
            foreach ($scan as $entry) {
                $this->_sub_dir = str_replace(array($this->_manage_root.'/', basename($entry)), '', $entry);
                if (is_dir($entry)) {
                    $this->deleteRecursive(str_replace($this->_manage_root.'/', '', $entry));
                } else {
                    if (!in_array(basename(dirname($entry)), array('source', 'thumbs', '_vti_cnf'))) {
                        $files = $GLOBALS['db']->select('CubeCart_filemanager', array('file_id'), array('filename' => basename($entry), 'filepath' => $this->_sub_dir));
                        if ($files) {
                            foreach ($files as $file) {
                                $this->deleteFile($file['file_id']);
                            }
                        }
                    }
                }
            }
            return (bool)rmdir($this->_manage_root.'/'.$directory);
        }
        return false;
    }

    /**
     * Deliver download file
     *
     * @param string $access_key
     * @param string $error
     * @return bool
     */
    public function deliverDownload($access_key = false, &$error = null, $stream = false)
    {
        if ($this->_mode == self::FM_FILETYPE_DL && $access_key) {
            if (($downloads = $GLOBALS['db']->select('CubeCart_downloads', false, array('accesskey' => $access_key), false, false, false, false)) !== false) {
                $download = $downloads[0];
                if (($summary = $GLOBALS['db']->select('CubeCart_order_summary', false, array('cart_order_id' => $download['cart_order_id']))) !== false) {
                    // Order/Download Validation
                    // Download has expired
                    if ($download['expire']>0 && $download['expire'] < time()) {
                        $error = self::FM_DL_ERROR_EXPIRED;
                    }
                    // Order hasn't been paid for
                    if (!in_array((int)$summary[0]['status'], array(2, 3))) {
                        $error = self::FM_DL_ERROR_PAYMENT;
                    }
                    // Maximum download limit has been reached
                    if ($GLOBALS['config']->get('config', 'download_count') > 0 && (int)$download['downloads'] >= $GLOBALS['config']->get('config', 'download_count')) {
                        $error = self::FM_DL_ERROR_MAXDL;
                    }
                    if (!empty($error)) {
                        return false;
                    }
                    $data = $this->getFileInfo($download['product_id']);
                    foreach ($GLOBALS['hooks']->load('class.filemanager.deliver.download.pre') as $hook) {
                        include $hook;
                    }
                    if($stream) {
                        return $data;
                    } else if ($data !== false) {
                        // Deliver file contents
                        if (isset($data['file']) && ($data['is_url'] || file_exists($data['file']))) {
                            if ($data['is_url']) {
                                $GLOBALS['db']->update('CubeCart_downloads', array('downloads' => $download['downloads']+1), array('digital_id' => $download['digital_id']));
                                httpredir($data['file']);
                                return true;
                            } else if($data['stream']=='1') {
                                
                                $GLOBALS['db']->update('CubeCart_downloads', array('downloads' => $download['downloads']+1), array('digital_id' => $download['digital_id']));
                                
                                $fp = @fopen($data['file'], 'rb');

                                $size = filesize($data['file']);
                                $length = $size;
                                $start = 0;
                                $end = $size - 1;

                                header('Content-type: '.$data['mimetype']);
                                header("Accept-Ranges: bytes");
                                if (isset($_SERVER['HTTP_RANGE'])) {
                                    $c_start = $start;
                                    $c_end = $end;

                                    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                                    if (strpos($range, ',') !== false) {
                                        header('HTTP/1.1 416 Requested Range Not Satisfiable');
                                        header("Content-Range: bytes $start-$end/$size");
                                        exit;
                                    }
                                    if ($range == '-') {
                                        $c_start = $size - substr($range, 1);
                                    } else {
                                        $range = explode('-', $range);
                                        $c_start = $range[0];
                                        $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
                                    }
                                    $c_end = ($c_end > $end) ? $end : $c_end;
                                    if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                                        header('HTTP/1.1 416 Requested Range Not Satisfiable');
                                        header("Content-Range: bytes $start-$end/$size");
                                        exit;
                                    }
                                    $start = $c_start;
                                    $end = $c_end;
                                    $length = $end - $start + 1;
                                    fseek($fp, $start);
                                    header('HTTP/1.1 206 Partial Content');
                                }
                                header("Content-Range: bytes $start-$end/$size");
                                header("Content-Length: " . $length);

                                $buffer = 1024 * 8;
                                while (!feof($fp) && ($p = ftell($fp)) <= $end) {

                                    if ($p + $buffer > $end) {
                                        $buffer = $end - $p + 1;
                                    }
                                    set_time_limit(0);
                                    echo fread($fp, $buffer);
                                    flush();
                                }

                                fclose($fp);
                                exit();
            
                            } else {
                                ob_end_clean();
                                if (!is_file($data['file']) or connection_status()!=0) {
                                    return false;
                                }

                                header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
                                header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
                                $mimeParts = $this->mimeParts($data['mimetype']);
                                if($mimeParts['type']=='application' && $mimeParts['subtype']=='pdf') {
                                    header('Content-Disposition: inline; filename="'.basename($data['file']).'"');
                                    header("Content-Type: ".$mimeParts['type']."/".$mimeParts['subtype']."");
                                } else {
                                    header('Content-Disposition: attachment; filename="'.basename($data['file']).'"');
                                    header("Content-Type: application/octet-stream");
                                }
                                header("Content-Transfer-Encoding: binary");
                                ## IE 7 Fix
                                header('Vary: User-Agent');

                                if (($openfile = fopen($data['file'], 'rb')) !== false) {
                                    while (!feof($openfile)) {
                                        set_time_limit(120);
                                        echo fread($openfile, 8192);
                                        flush();
                                    }
                                    fclose($openfile);
                                }
                                if (!connection_status() && !connection_aborted()) {
                                    $GLOBALS['db']->update('CubeCart_downloads', array('downloads' => $download['downloads']+1), array('digital_id' => $download['digital_id']));
                                    return true;
                                }
                            }
                        }
                        ## File doesn't exist
                        $error = self::FM_DL_ERROR_NOFILE;
                        return false;
                    }
                    ## Product record doesn't exist
                    $error = self::FM_DL_ERROR_NOPRODUCT;
                    return false;
                }
            }
            // Download record doesn't exist
            $error = self::FM_DL_ERROR_NORECORD;
        }
        return false;
    }

    /**
     * Edit file
     *
     * @param int $file_id
     * @return bool
     */
    public function editor($file_id = null)
    {
        if (!is_null($file_id)) {
            if (!empty($this->_sub_dir)) {
                // Breadcrumbs
                if (($elements = explode('/', $this->_sub_dir)) !== false) {
                    foreach ($elements as $sub_dir) {
                        $path[] = $sub_dir;
                        $GLOBALS['gui']->addBreadcrumb($sub_dir, currentPage(array('fm-edit'), array('subdir' => $this->formatPath(implode('/', $path), false))));
                    }
                }
            }
            if (($file = $GLOBALS['db']->select('CubeCart_filemanager', false, array('file_id' => $file_id))) !== false) {
                $source = $this->_manage_dir.'/'.$this->_sub_dir;
                $sub_dir = (substr($this->_sub_dir, 0, 1) == '/') ? $this->_sub_dir : '/'.$this->_sub_dir;
                if (file_exists($source.$file[0]['filename'])) {
                    $GLOBALS['gui']->addBreadcrumb($file[0]['filename'], currentPage());
                    $GLOBALS['main']->addTabControl('Details', 'fm-details');
                    if ($this->_directories) {
                        $list[] = '/';
                        foreach ($this->_directories as $root => $folders) {
                            if ($this->_mode == self::FM_FILETYPE_IMG && in_array(basename($root), array('thumbs', 'source'))) {
                                continue;
                            }
                            foreach ($folders as $folder) {
                                if ($this->_mode == self::FM_FILETYPE_IMG && in_array(basename($folder), array('thumbs', 'source'))) {
                                    continue;
                                }
                                $list[] = '/'.str_replace($this->_manage_dir, '', $root).$folder.'/';
                            }
                        }
                        natsort($list);
                        foreach ($list as $dir) {
                            $vars['dirs'][] = array(
                                'path'  => $dir,
                                'selected' => ($sub_dir == $dir) ? ' selected="selected"' : '',
                            );
                        }
                        $GLOBALS['smarty']->assign('DIRS', $vars['dirs']);
                    }
                    $file[0]['filepath'] = $source;
                    $file[0]['random']  = mt_rand();
                    $GLOBALS['smarty']->assign('FILE', $file[0]);

                    if ($file[0]['type'] == self::FM_FILETYPE_IMG) {
                        $GLOBALS['main']->addTabControl($GLOBALS['language']->filemanager['tab_crop'], 'fm-cropper');
                        $GLOBALS['smarty']->assign('SHOW_CROP', true);
                    } else {
                        $GLOBALS['smarty']->assign('STREAMABLE', $this->_streamable($file[0]['mimetype']));
                    }
                    $GLOBALS['smarty']->assign('mode_form', true);
                    return $GLOBALS['smarty']->fetch('templates/filemanager.index.php');
                } else {
                    // File doesn't exist - Delete record, and all associations legacy names and id
                    $GLOBALS['db']->update('CubeCart_category', array('cat_image' => ''), array('cat_image' => $file[0]['file_id']));
                    $GLOBALS['db']->update('CubeCart_category', array('cat_image' => ''), array('cat_image' => $file[0]['filename']));

                    if ($file[0]['file_id']>0) {
                        $GLOBALS['db']->delete('CubeCart_image_index', array('file_id' => $file[0]['file_id']));
                        $GLOBALS['db']->delete('CubeCart_filemanager', array('file_id' => $file[0]['file_id']));
                    }
                    // Set error message
                    $GLOBALS['gui']->setError($GLOBALS['language']->filemanager['error_image_missing']);
                }
            }
            // Redirect back to file list
            httpredir(currentPage(array('fm-edit')));
        }
    }

    /**
     * Check filename is allowed (true on illegal!)
     *
     * @param string $type
     * @return bool
     */

    public function filenameIsIllegal($file_name)
    {
        if (preg_match('/(\.sh\.inc\.ini|\.htaccess|\.php|\.phtml|\.php[3-6])$/i', $file_name)) {
            return true;
        } elseif (preg_match('/\.php\./i', $file_name)) {
            return true;
        }
        return false;
    }

    /**
     * Find directories
     *
     * @param string $search_dir
     * @param int $i
     *
     * @return string/false
     */
    public function findDirectories($search_dir = '', $i = 0)
    {
        $search_dir = ($search_dir==='') ? $this->_manage_dir : $search_dir;
        if (file_exists($search_dir)) {
            $list = glob($search_dir.'/'.'*', GLOB_ONLYDIR);
            if (is_array($list) && count($list)>0) {
                foreach ($list as $dir) {
                    if ($this->_mode == self::FM_FILETYPE_IMG && in_array(basename($dir), array('thumbs', 'source', '_vti_cnf'))) {
                        continue;
                    }
                    $this->_directories[$this->makeFilepath($dir)][] = basename($dir);
                    if (is_dir($dir)) {
                        $this->findDirectories($dir, $i++);
                    }
                }
                return $this->_directories;
            }
        }
        return false;
    }

    /**
     * Format file name
     *
     * @param string $name
     * @return string
     */
    private function formatName($name)
    {
        return preg_replace('#[^\p{L}\p{N}\w\.\-\_]#iu', '_', $name);
    }

    /**
     * Format path string
     *
     * @param string $path
     * @param bool $slash
     * @return string
     */
    public function formatPath($path, $slash = true)
    {
        $path = preg_replace('#[\\\/]{2,}#', '/', urldecode($path));
        if ($path == '.' || $path == '..') {
            return null;
        }
        $path = str_replace('..', '', $path);
        // Remove preceeding slash
        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1);
        }
        // Append a trailing slash, if there isn't one
        if ($slash && substr($path, -1) != '/') {
            $path .= '/';
        }

        return ($path != '/') ? $path : null;
    }

    /**
     * Get all directories
     *
     * @return array
     */
    public function getDirectories()
    {
        return $this->_directories;
    }

    /**
     * Get file icon
     *
     * @param string $mimetype
     * @return string
     */
    private function getFileIcon($mimetype = false)
    {
        $mimeParts = $this->mimeParts($mimetype);
        if ($mimeParts['type']=='image') {
            return 'image';
        } else {
            if($mimeParts['type']=='video') {
                $icon = 'file-video-o';
            } else if ($mimeParts['type']=='audio') {
                $icon = 'file-audio-o';
            } else {
                switch ($mimetype) {
                    case 'application/x-bzip':
                    case 'application/x-bzip2':
                    case 'application/gzip':
                    case 'application/vnd.rar':
                    case 'application/x-7z-compressed':
                    case 'application/x-gzip':
                    case 'application/x-gtar':
                    case 'application/x-tar':
                    case 'application/x-zip':
                    case 'application/x-zip-compressed':
                    case 'application/zip':
                        $icon = 'file-archive-o';
                    break;
                    case 'application/pdf':
                        $icon = 'file-pdf-o';
                    break;
                    case 'application/msword':
                    case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                    case 'application/vnd.openxmlformats-officedocument.wordprocessingml.template':
                        $icon = 'file-word-o';
                        break;
                    case 'application/vnd.ms-excel':
                    case 'application/msexcel':
                    case 'application/x-msexcel':
                    case 'application/x-ms-excel':
                    case 'application/x-excel':
                    case 'application/x-dos_ms_excel':
                    case 'application/xls':
                    case 'application/x-xls':
                    case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                        $icon = 'file-excel-o';
                        break;
                    default:
                        $icon = 'file-o';
                }
            }
            return $icon;
        }
    }

    /**
     * Get file info
     *
     * @param string $id
     * @return array/false
     */
    public function getFileInfo($product_id)
    {
        $product = $GLOBALS['db']->select('CubeCart_inventory', array('digital', 'digital_path'), array('product_id' => $product_id), false, 1);

        if (empty($product[0]['digital_path'])) {
            if (($files = $GLOBALS['db']->select('CubeCart_filemanager', false, array('file_id' => $product[0]['digital']))) !== false) {
                $data = $files[0];
                $data['is_url'] = false;
                $data['file'] = $this->_manage_root.'/'.$data['filepath'].'/'.$data['filename'];
                return $data;
            }
        } else {
            if (filter_var($product[0]['digital_path'], FILTER_VALIDATE_URL)) {
                $data = array(
                    'mimetype' => 'application/octet-stream',
                    'filename' => basename($product[0]['digital_path']),
                    'filesize' => null,
                    'md5hash' => '',
                    'is_url' => true,
                    'file'  => $product[0]['digital_path'],
                    'url'  => parse_url($product[0]['digital_path'])
                );
                return $data;
            } elseif (file_exists($product[0]['digital_path'])) {
                $filesize = filesize($product[0]['digital_path']);
                $data = array(
                        'mimetype' => 'application/octet-stream',
                        'filename' => basename($product[0]['digital_path']),
                        'filepath' => dirname($product[0]['digital_path']),
                        'filesize' => $filesize,
                        'md5hash' => $this->md5file($product[0]['digital_path'], $filesize),
                        'is_url' => false
                    );
                $data['file'] = $product[0]['digital_path'];
            }
            return $data;
        }
        return false;
    }

    /**
     * Get file mime type
     *
     * @return string
     */
    public function getMimeType($file)
    {
        $finfo = (extension_loaded('fileinfo')) ? new finfo(FILEINFO_MIME_TYPE) : false;
        if ($finfo && $finfo instanceof finfo) {
            $mime = $finfo->file($file);
        } elseif (function_exists('mime_content_type')) {
            $mime = mime_content_type($file);
        } else {
            $data = getimagesize($file);
            $mime = $data['mime'];
        }
        return (empty($mime)) ? 'application/octet-stream' : $mime;
    }

    /**
     * Get current mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * List file type
     *
     * @param string $type
     * @param bool $select_button
     * @return array/false
     */
    public function listFiles($type = false, $select_button = false)
    {
        // Display Breadcrumbs
        if (!empty($this->_sub_dir)) {
            $elements = explode('/', $this->_sub_dir);
            if ($elements) {
                foreach ($elements as $sub_dir) {
                    $path[] = $sub_dir;
                    $GLOBALS['gui']->addBreadcrumb($sub_dir, currentPage(null, array('subdir' => $this->formatPath(implode('/', $path), false))));
                }
            }
        }
        $type_desc = ($this->_mode == self::FM_FILETYPE_IMG) ? $GLOBALS['language']->filemanager['file_type_image'] : $GLOBALS['language']->filemanager['file_type_dl'];
        $GLOBALS['smarty']->assign('FILMANAGER_TITLE', $type_desc." Filemanager");
        $GLOBALS['smarty']->assign('FILMANAGER_MODE', (string)$this->_mode);

        // Create a backlink to the parent directory, if is exists
        if ($this->_directories && isset($this->_directories[$this->formatPath($this->_sub_dir)])) {
            // List subdirectories
            foreach ($this->_directories[$this->formatPath($this->_sub_dir)] as $dir) {
                if ($this->_mode == self::FM_FILETYPE_IMG && in_array($this->makeFilename($dir), array('thumbs', 'source'))) {
                    continue;
                }
                $name = $this->makeFilename($dir);
                $path = $this->formatPath($this->_sub_dir.$dir, false);
                $folder = array(
                    'name'  => $name,
                    'link'  => currentPage(null, array('subdir' => $path)),
                    'delete' => (substr($name, 0, 1) !== '.') ? currentPage(null, array('delete' => $path, 'token' => SESSION_TOKEN)) : null,
                    'value' => (substr($name, 0, 1) !== '.') ? $path : null,
                );
                $list_folders[] = $folder;
            }
            
            $GLOBALS['smarty']->assign('FOLDERS', $list_folders);
        }

        if (isset($_GET['subdir'])) {
            if (stristr($_GET['subdir'], '/')) {
                $parts = explode('/', $_GET['subdir']);
                unset($parts[count($parts)-1]);
                $subdir = implode('/', $parts);
                $parent_link = currentPage(null, array('subdir' => $subdir));
            } else {
                $parent_link = currentPage(array('subdir'));
            }
            $GLOBALS['smarty']->assign('FOLDER_PARENT', $parent_link);
        }

        $filepath_where  = empty($this->_sub_dir) ? 'IS NULL' : '= \''.str_replace('\\', '/', $this->_sub_dir).'\'';
        $where = '`disabled` = 0 AND `type` = '.(int)$this->_mode.' AND `filepath` '.$filepath_where;
        $GLOBALS['smarty']->assign('FM_SIZE', isset($_COOKIE['fm_size']) ? 'fm-item-'.$_COOKIE['fm_size'] : 'fm-item-medium');
        if (($files = $GLOBALS['db']->select('CubeCart_filemanager', false, $where, array('filename' => 'ASC'))) !== false) {
            $catalogue = $GLOBALS['catalogue']->getInstance();
            $GLOBALS['smarty']->assign('ROOT_REL', $GLOBALS['rootRel']);
            foreach ($files as $key => $file) {
                $file['icon']   = $this->getFileIcon($file['mimetype']);
                $file['class']   = (preg_match('#^image#', $file['mimetype'])) ? 'colorbox' : '';
                $file['edit']   = currentPage(null, array('fm-edit' => $file['file_id']));
                $file['delete']   = currentPage(null, array('delete' => $file['file_id'], 'token' => SESSION_TOKEN));
                $file['value']   = $file['file_id'];
                $file['random']   = mt_rand();
                $file['description'] = (!empty($file['description'])) ? $file['description'] : $file['filename'];
                $file['master_filepath']= str_replace(chr(92), "/", $this->_manage_dir.'/'.$file['filepath'].$file['filename']);
                $file['filepath']   = ($this->_mode == self::FM_FILETYPE_IMG) ? $catalogue->imagePath($file['file_id'], 'medium') : $this->_manage_dir.'/'.$file['filepath'].$file['filename'];
                $file['select_button'] = (bool)$select_button;
                $file['filesize'] = formatBytes($file['filesize'], true);
                $file['file_name_hash'] = 'file_'.md5($file['filename']);

                if ($select_button) {
                    $file['master_filepath'] = $GLOBALS['rootRel'].$file['master_filepath'];
                } // Fix the image path added to the FCK editor area

                $list_files[$key] = $file;
            }
            if(isset($_GET['file_id'])) {
                $GLOBALS['smarty']->assign('HILIGHTED_FILE', $_GET['file_id']);
            }
            $GLOBALS['smarty']->assign('FILES', $list_files);
            return $list_files;
        }
        return false;
    }

    /**
     * Make file name
     *
     * @param string $file
     * @return string
     */
    private function makeFilename($file)
    {
        // Standardize the filename
        return $this->formatName(basename($file));
    }

    /**
     * Make file path
     *
     * @param string $file
     * @return string
     */
    private function makeFilepath($file)
    {
        $path =  str_replace($this->_manage_root, '', dirname($file));
        return $this->formatPath($path);
    }

    private function md5file($file, $size, $force = false) {
        if($force || $size <= $this->_md5_filesize_limit) {
            return md5_file($file);
        }
        return '';
    }

    /**
     * File assigned to a product
     *
     * @param string $product_id
     * @return int/false
     */
    public function productFile($product_id)
    {
        if (empty($product_id) || !is_numeric($product_id)) {
            return false;
        }
        $file = $GLOBALS['db']->select('CubeCart_inventory', array('digital'), array('product_id' => (int)$product_id));
        if ($file!==false) {
            return $file[0]['digital'];
        }
        return false;
    }

    /**
     * Images assigned to a product
     *
     * @param string $product_id
     * @return array
     */
    public function productImages($product_id)
    {
        if (!empty($product_id) && $product_id>0) {
            $images = $GLOBALS['db']->select('CubeCart_image_index', array('file_id', 'main_img'), array('product_id' => (int)$product_id));
            if ($images!==false) {
                $assigned_images = array();
                foreach ($images as $image) {
                    $assigned_images[$image['file_id']] = ($image['main_img']== '1') ? '2': '1';
                }
                return $assigned_images;
            }
        } elseif ($GLOBALS['session']->has('recently_uploaded')) {
            $assigned_images = $GLOBALS['session']->get('recently_uploaded');
            end($assigned_images); // Set last image as main_img
            $key = key($assigned_images);
            $assigned_images[$key] = '2';
            $GLOBALS['session']->delete('recently_uploaded');
            $this->form_fields = true;
            return $assigned_images;
        }
        return array();
    }

    /**
     * Upgrade file
     *
     * @param array/string $start
     * @param string $dir
     * @return bool
     */
    public function upgrade($start = null, $dir = null)
    {
        if (is_array($start)) {
            foreach ($start as $seek) {
                $this->upgrade($seek, $dir);
            }
        } else {
            $scan_root = CC_ROOT_DIR.'/images/uploads/'.$start;
            if (substr($scan_root, -1, 1) != '/') {
                $scan_root .= '/';
            }

            $scan_dir = $scan_root;
            if (!is_null($dir)) {
                $scan_dir .= (substr($dir, 0, 1) == '/') ? substr($dir, 1) : $dir;
            }

            if (file_exists($scan_dir) && is_dir($scan_dir)) {
                if (($files = glob($scan_dir.'*', GLOB_MARK)) !== false) {
                    foreach ($files as $file) {
                        $target = str_replace($scan_root, '', $file);
                        if (is_dir($file)) {
                            if (in_array($target, array('source', 'thumbs', '_vti_cnf'))) {
                                continue;
                            }
                            $this->upgrade($start, $target);
                            rmdir($file);
                        } else {
                            // Copy to new sources
                            $to = $this->_manage_root.'/'.$target;
                            if (!file_exists(dirname($to))) {
                                mkdir(dirname($to), chmod_writable(), true);
                            }
                            rename($file, $to);
                        }
                        continue;
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Upload file
     *
     * @param string $type
     * @param bool $thumbnail
     *
     * @return int/false
     */
    public function upload($type = false, $thumbnail = false)
    {
        if (!is_writable($this->_manage_root)) {
            return false;
        }

        if (!empty($_FILES)) {
            $finfo = (extension_loaded('fileinfo')) ? new finfo(FILEINFO_SYMLINK | FILEINFO_MIME) : false;
            foreach ($_FILES as $file) {
                if ($this->filenameIsIllegal($file['name'])) {
                    continue;
                }

                $gd = new GD($this->_manage_root.'/'.$this->_sub_dir);
                if (!empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
                    if ($this->_mode == self::FM_FILETYPE_IMG && $file['size'] > $this->_max_upload_image_size) {
                        $GLOBALS['gui']->setError(sprintf($GLOBALS['lang']['filemanager']['error_file_upload_size'], $file['name'], formatBytes($this->_max_upload_image_size, true, 0)));
                        return false;
                    }

                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $this->_uploadError($file['error']);
                        continue;
                    }

                    $target = $target_old = $this->_manage_root.'/'.$this->_sub_dir.$file['name'];
                    $newfilename = $this->makeFilename($file['name']);
                    $oldfilename = $file['name'];

                    if ($newfilename !== $oldfilename) {
                        $target = str_replace($oldfilename, $newfilename, $target);
                    }

                    $filepath_record = $this->formatPath(str_replace($this->_manage_root, '', dirname($target)));
                    $filepath_record = empty($filepath_record) ? 'NULL' : $filepath_record;
                    $filepath_record = str_replace(chr(92), "/", $filepath_record);

                    $record = array(
                        'type'  => (int)$this->_mode,
                        'filepath' => $filepath_record,
                        'filename' => $newfilename,
                        'filesize' => $file['size'],
                        'mimetype' => $file['type'] ? $file['type'] : $this->getMimeType($file['tmp_name']),
                        'md5hash' => $this->md5file($file['tmp_name'], $file['size'], true),
                    );

                    $existing = $GLOBALS['db']->select('CubeCart_filemanager', 'file_id', array('filepath' => $filepath_record, 'filename' => $newfilename));
                    if ($existing!==false && (int)$existing[0]['file_id']>0) {
                        $GLOBALS['db']->update('CubeCart_filemanager', $record, array('file_id' => $existing[0]['file_id']));
                        $fid = $existing[0]['file_id'];
                    } else {
                        $fid = $GLOBALS['db']->insert('CubeCart_filemanager', $record);
                    }
                    
                    $file_id[] = $fid;
                    $this->_recently_uploaded[$fid] = '1';
                    
                    if (isset($_GET['product_id']) && $_GET['product_id']>0) {
                        $this->_assignProduct((int)$_GET['product_id'], (int)$fid);
                    }
                    if ($this->_mode == self::FM_FILETYPE_IMG && isset($_GET['cat_id']) && $_GET['cat_id']>0) {
                        $this->_assignCategory((int)$_GET['cat_id'], (int)$fid);
                    }
                    if ($this->_mode == self::FM_FILETYPE_IMG && isset($_GET['gc']) && $_GET['gc']==1) {
                        $GLOBALS['config']->set('gift_certs', 'image', (int)$fid);
                    }
                    move_uploaded_file($file['tmp_name'], $target);
                    foreach ($GLOBALS['hooks']->load('class.filemanager.upload') as $hook) include $hook;
                    chmod($target, chmod_writable());
                }
            }
            if (isset($_GET['product_id']) || isset($_GET['cat_id'])) {
                $GLOBALS['session']->set('recently_uploaded', $this->_recently_uploaded);
            }

            return (isset($file_id)) ? $file_id : true;
        }
        return false;
    }

    /**
     * Assign FileManager file_id to category
     *
     * @param int $cat_id
     * @param int $file_id
     *
     */
    private function _assignCategory($cat_id, $file_id)
    {
        $GLOBALS['db']->update('CubeCart_category', array('cat_image' => $file_id), array('cat_id' => $cat_id));
    }

    /**
     * Assign FileManager file_id to product
     *
     * @param int $product_id
     * @param int $file_id
     *
     */
    private function _assignProduct($product_id, $file_id)
    {
        if ($this->_mode == self::FM_FILETYPE_IMG) {
            if ($GLOBALS['db']->select('CubeCart_image_index', false, array('main_img' => 1, 'product_id' => $product_id))!==false) {
                $main_image = '0';
            } else {
                $GLOBALS['db']->update('CubeCart_image_index', array('main_img' => 0), array('product_id' => $product_id));
                $main_image = '1';
            }

            $record = array(
                'file_id'  => $file_id,
                'product_id' => $product_id,
                'main_img'  => $main_image
            );
            $GLOBALS['db']->insert('CubeCart_image_index', $record);
        } else {
            $GLOBALS['db']->update('CubeCart_inventory', array('digital' => $file_id), array('product_id' => $product_id));
        }
    }

    private function _setUploadLimit()
    {
        $size_str = ini_get('upload_max_filesize');
        switch (substr($size_str, -1))
        {
            case 'M':
            case 'm':
                $this->_max_upload_image_size = (int)$size_str * 1048576;
            break;
            case 'K':
            case 'k':
                $this->_max_upload_image_size = (int)$size_str * 1024;
            break;
            case 'G':
            case 'g':
                $this->_max_upload_image_size = (int)$size_str * 1073741824;
            break;
            default: //2M PHP default
                $this->_max_upload_image_size = 2 * 1048576;
        }
    }

    private function _streamable($mimetype) {
        $mime_parts = $this->mimeParts($mimetype);
        return in_array($mime_parts['type'], array('video', 'audio'));
    }

    function mimeParts($mimetype) {
        $mime_parts = explode('/', $mimetype);
        return array(
            'type' => $mime_parts[0],
            'subtype' => $mime_parts[1]
        );
    }

    /**
     * Upload error messages
     *
     * @param int $error_no
     *
     * @return false
     */
    private function _uploadError($error_no)
    {
        switch ($error_no) {
        case UPLOAD_ERR_INI_SIZE:
            $message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            break;
        case UPLOAD_ERR_FORM_SIZE:
            $message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
            break;
        case UPLOAD_ERR_PARTIAL:
            $message = 'The uploaded file was only partially uploaded';
            break;
        case UPLOAD_ERR_NO_FILE:
            $message = 'No file was uploaded';
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            $message = 'Missing a temporary folder';
            break;
        case UPLOAD_ERR_CANT_WRITE:
            $message = 'Failed to write file to disk';
            break;
        case UPLOAD_ERR_EXTENSION:
            $message = 'File upload stopped by extension';
            break;
        default:
            $message = 'Unknown upload error';
        }
        trigger_error($message, E_USER_WARNING);
        return false;
    }
}
