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

class FileManager
{
    private $_directories;
    private $_mode;

    private $_manage_cache;
    private $_manage_dir;
    private $_manage_root;
    private $_recently_uploaded = array();
    private $_sub_dir;
    private $_max_upload_image_size = 350000;
    private $_md5_filesize_limit = 268435456; // 256MB

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
                            $old = pathinfo($file[0]['filename']);
                            $new = pathinfo($_POST['details']['filename']);
                            // We can't allow extension change
                            $new_filename = $_POST['details']['filename'];
                            if(!isset($new['extension']) || $new['extension']!==$old['extension']) {
                                $new_filename = $new['basename'].'.'.$old['extension'];
                            }
                            $new_filename = $this->formatName($new_filename);
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
                        $record['description'] = strip_tags($_POST['details']['description'] ?? "");
                        $record['title'] = strip_tags($_POST['details']['title'] ?? "");
                        $record['stream'] = $_POST['details']['stream'] ?? "0"; // must be string "0" or "1"
                        $record['alt'] = strip_tags($_POST['details']['alt'] ?? "");

                        $update = false;
                        foreach ($record as $k => $v) {
                            if (!isset($file[0][$k]) || $file[0][$k] != $v) {
                                $update = true;
                            }
                        }
                        if ($update) {
                            if ($GLOBALS['db']->update('CubeCart_filemanager', $record, array('file_id' => (int)$_POST['file_id']))) {
                                $GLOBALS['gui']->setNotify($GLOBALS['language']->filemanager['notice_file_updated']);
                            } else {
                                $GLOBALS['gui']->setError($GLOBALS['language']->filemanager['error_file_update']);
                            }
                        } elseif (empty($_POST['resize']['w']) || empty($_POST['resize']['h'])) {
                            // Only flag "no changes" when there's no concurrent crop submission
                            // (cropping doesn't touch the details fields, so $update is false but the crop itself is a real change).
                            $GLOBALS['gui']->setError($GLOBALS['language']->filemanager['error_file_not_changed']);
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
                        $gd  = new GD(dirname($source), false, 80);
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
        $GLOBALS['smarty']->assign('SELECT_BUTTON', $select_button);
        if (isset($_GET['CKEditorFuncNum'])) {
            $GLOBALS['smarty']->assign('CK_FUNC_NUM', (int)$_GET['CKEditorFuncNum']);
        }

        // Folder list for the bulk-move dropdown. Excludes system folders the
        // merchant should never move uploaded files into (image cache subdirs
        // for image mode; the entire order-attachment tree for digital).
        // $this->_directories is already populated by the constructor.
        $dirs = array();
        if ($this->_directories) {
            $img_excluded_names    = array('thumbs', 'source'); // exclude these by basename anywhere
            $dl_excluded_prefixes  = array('/attachments/');    // exclude this path and any descendant
            $list = array('/');
            foreach ($this->_directories as $root => $folders) {
                if ($this->_mode == self::FM_FILETYPE_IMG && in_array(basename($root), $img_excluded_names)) continue;
                foreach ($folders as $folder) {
                    if ($this->_mode == self::FM_FILETYPE_IMG && in_array(basename($folder), $img_excluded_names)) continue;
                    $path = '/'.str_replace($this->_manage_dir, '', $root).$folder.'/';
                    if ($this->_mode == self::FM_FILETYPE_DL) {
                        $excluded = false;
                        foreach ($dl_excluded_prefixes as $prefix) {
                            if (strpos($path, $prefix) === 0) { $excluded = true; break; }
                        }
                        if ($excluded) continue;
                    }
                    $list[] = $path;
                }
            }
            natsort($list);
            foreach ($list as $dir) {
                $dirs[] = array('path' => $dir);
            }
            $GLOBALS['smarty']->assign('DIRS', $dirs);
        }

        $GLOBALS['smarty']->assign('mode_list', true);

        return $GLOBALS['smarty']->fetch('templates/filemanager.index.php');
    }

    /**
     * Move a single file to a different folder under the current manage root.
     * Returns true on success.
     *
     * @param int $file_id
     * @param string $target_subdir e.g. "/" or "/albums/"
     * @return bool
     */
    public function moveFile($file_id, $target_subdir)
    {
        $file = $GLOBALS['db']->select('CubeCart_filemanager', false, array('file_id' => (int)$file_id, 'type' => (int)$this->_mode), false, 1);
        if (!$file) return false;
        $row = $file[0];
        $current_path = $this->_manage_root.'/'.$row['filepath'];
        $target_path  = $this->_manage_root.'/'.$this->formatPath($target_subdir);
        if (!is_dir($target_path)) return false;
        if (rtrim($current_path, '/') === rtrim($target_path, '/')) return true; // already there

        $src = $current_path.$row['filename'];
        $dst = $target_path.$row['filename'];
        if (!file_exists($src) || file_exists($dst)) return false;
        if (!rename($src, $dst)) return false;

        $new_subdir = $this->formatPath(str_replace($this->_manage_root, '', $target_path), false);
        $record = array('filepath' => empty($new_subdir) ? 'NULL' : $this->formatPath($new_subdir));
        return (bool)$GLOBALS['db']->update('CubeCart_filemanager', $record, array('file_id' => (int)$file_id));
    }

    /**
     * Replace the binary contents of an existing file_id with a freshly-uploaded
     * file. Filename and folder stay the same so existing product/category
     * references via file_id keep working. Returns true on success.
     *
     * @param int $file_id
     * @param array $upload entry from $_FILES (e.g. $_FILES['replacement'])
     * @return bool
     */
    public function replaceFile($file_id, array $upload)
    {
        if (empty($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) return false;
        if ($upload['error'] !== UPLOAD_ERR_OK) return false;
        $row = $GLOBALS['db']->select('CubeCart_filemanager', false, array('file_id' => (int)$file_id, 'type' => (int)$this->_mode), false, 1);
        if (!$row) return false;
        $file = $row[0];
        // Disallow extension change so links to /files/foo.pdf or images keep working.
        $old_ext = strtolower(pathinfo($file['filename'], PATHINFO_EXTENSION));
        $new_ext = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
        if ($old_ext !== $new_ext) return false;
        $abs = $this->_manage_root.'/'.$file['filepath'].$file['filename'];
        if (!@move_uploaded_file($upload['tmp_name'], $abs)) return false;
        @chmod($abs, chmod_writable());
        clearstatcache(true, $abs);
        $update = array(
            'filesize' => filesize($abs),
            'mimetype' => $this->getMimeType($abs),
            'md5hash'  => $this->md5file($abs, filesize($abs), true),
        );
        // Bust cached image renders if this is an image.
        if ($this->_mode == self::FM_FILETYPE_IMG) {
            $this->deleteCachedImages($abs);
        }
        return (bool)$GLOBALS['db']->update('CubeCart_filemanager', $update, array('file_id' => (int)$file_id));
    }

    /**
     * Persist a product's image gallery from the picker.
     *
     * Accepts the new ordered POST shape:
     *   $image_ids[file_id] = position   // 1 = main, 2+ = secondary order
     * with `position = 0` meaning "remove". The legacy 0|1|2 status form is
     * still understood (2 = main) for any remaining call sites.
     */
    public function assignProductImages($image_ids, $product_id)
    {
        if (!is_array($image_ids)) {
            return false;
        }
        $product_id = (int)$product_id;

        // Snapshot current rows for change detection.
        $hash_before = '';
        if (($before = $GLOBALS['db']->select('CubeCart_image_index', array('product_id', 'file_id', 'main_img', 'position'), array('product_id' => $product_id))) !== false) {
            $hash_before = md5(serialize($before));
        }

        // Decide whether the input uses the new ordered form (positions 1..N) or
        // the legacy 0|1|2 status form (2 = main, 1 = include, 0 = remove).
        $is_ordered = false;
        foreach ($image_ids as $v) {
            if ((int)$v > 2) { $is_ordered = true; break; }
        }

        $ordered = array(); // file_id => position (>=1)
        $main    = 0;
        if ($is_ordered) {
            foreach ($image_ids as $fid => $pos) {
                $fid = (int)$fid;
                $pos = (int)$pos;
                if ($fid <= 0 || $pos <= 0) continue;
                $ordered[$fid] = $pos;
                if ($pos === 1) $main = $fid;
            }
        } else {
            // Legacy mapping: status 2 -> main (position 1); status 1 -> kept (positions 2..N)
            // in input order; status 0 -> dropped.
            $idx = 2;
            foreach ($image_ids as $fid => $status) {
                $fid = (int)$fid;
                $status = (int)$status;
                if ($fid <= 0 || $status <= 0) continue;
                if ($status === 2) {
                    $ordered[$fid] = 1;
                    $main = $fid;
                } else {
                    $ordered[$fid] = $idx++;
                }
            }
            // No main chosen -> promote the lowest-position kept image.
            if (!$main && $ordered) {
                $first = min($ordered);
                foreach ($ordered as $fid => $pos) {
                    if ($pos === $first) { $ordered[$fid] = 1; $main = $fid; break; }
                }
                if (count($ordered) > 1) {
                    $GLOBALS['main']->errorMessage($GLOBALS['language']->catalogue['error_image_defaulted']);
                }
            }
        }

        // Compact positions to 1..N preserving the chosen main at 1.
        if ($ordered) {
            asort($ordered, SORT_NUMERIC);
            $rank = 1;
            $compact = array();
            // Main first (if still in the kept set).
            if ($main && isset($ordered[$main])) {
                $compact[$main] = $rank++;
            }
            foreach ($ordered as $fid => $pos) {
                if (!isset($compact[$fid])) {
                    $compact[$fid] = $rank++;
                }
            }
            $ordered = $compact;
            if (!$main) {
                // Pick the first as main if none specified.
                $main = key($ordered);
            }
        }

        $GLOBALS['db']->delete('CubeCart_image_index', array('product_id' => $product_id));
        if ($ordered) {
            // Verify file_ids exist in one round-trip.
            $valid = array();
            $ids   = implode(',', array_map('intval', array_keys($ordered)));
            $pfx   = $GLOBALS['config']->get('config', 'dbprefix');
            if (($rows = $GLOBALS['db']->misc("SELECT `file_id` FROM `{$pfx}CubeCart_filemanager` WHERE `file_id` IN ($ids)", false)) !== false) {
                foreach ($rows as $r) {
                    $valid[(int)$r['file_id']] = true;
                }
            }
            foreach ($ordered as $fid => $pos) {
                if (!isset($valid[$fid])) continue;
                $GLOBALS['db']->insert('CubeCart_image_index', array(
                    'file_id'    => $fid,
                    'product_id' => $product_id,
                    'main_img'   => ($fid === $main) ? '1' : '0',
                    'position'   => $pos,
                ));
            }
        }

        $hash_after = '';
        if (($after = $GLOBALS['db']->select('CubeCart_image_index', array('product_id', 'file_id', 'main_img', 'position'), array('product_id' => $product_id))) !== false) {
            $hash_after = md5(serialize($after));
        }
        return $hash_before !== $hash_after;
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
        // Hash-keyed membership lookup so the per-file "already indexed" check
        // is O(1) instead of in_array()'s O(N). On a 10k-file rebuild this
        // collapses ~100M comparisons into ~10k.
        $exists_lookup = array();
        if (($existing = $GLOBALS['db']->select('CubeCart_filemanager', array('filename', 'filepath'), false, array('filename' => 'ASC'))) !== false) {
            foreach ($existing as $file) {
                $exists_lookup[$file['filepath'].$file['filename']] = true;
            }
        }
        if ($file_array) {
            foreach ($file_array as $key => $file) {
                if (!is_dir($file)) {
                    // Stale order-print temp files (print.<32hex>.php). Delete
                    // from disk and skip indexing so they never enter the DB.
                    if (preg_match('/^print\.[a-f0-9]{32}\.php$/i', basename($file))) {
                        @unlink($file);
                        continue;
                    }
                    // Skip already-indexed files BEFORE the expensive image
                    // validation below — opening every JPG/PNG/GIF/WebP just to
                    // re-confirm something we already know is in the DB was the
                    // dominant cost on rebuilds.
                    $rel_path = str_replace(array($this->_manage_root.'/', 'source/'), '', $file);
                    if (isset($exists_lookup[$rel_path])) {
                        continue;
                    }
                    // Skip file if it is not an image and we're in image mode
                    if ($this->_mode == 1) {
                        // Check mime matches extension
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        $mime = $this->getMimeType($file);
        
                        if(in_array($ext, array('jpg','jpeg'))) {
                            if($mime!=='image/jpeg') {
                                trigger_error($file.' has a mime type of '.$mime.'.');
                                continue;
                            } else {
                                try {
                                    $img = imagecreatefromjpeg($file);
                                    if(!$img) {
                                        trigger_error($file.' is not a valid jpg file.');
                                        continue;
                                    }
                                    imagedestroy($img);
                                } catch (Exception $e) {
                                    trigger_error($e->getMessage());
                                    continue;
                                }
                            }
                        }
                        if($ext == 'gif') {
                            if($mime!=='image/gif') {
                                trigger_error($file.' has a mime type of '.$mime.'.');
                                continue;
                            } else {
                                try {
                                    $img = imagecreatefromgif($file);
                                    if(!$img) {
                                        trigger_error($file.' is not a valid gif file.');
                                        continue;
                                    }
                                    imagedestroy($img);
                                } catch (Exception $e) {
                                    trigger_error($e->getMessage());
                                    continue;
                                }
                            }
                        }
                        if($ext == 'png') {
                            if($mime!=='image/png') {
                                trigger_error($file.' has a mime type of '.$mime.'.');
                                continue;
                            } else {
                                try {
                                    $img = imagecreatefrompng($file);
                                    if(!$img) {
                                        trigger_error($file.' is not a valid png file.');
                                        continue;
                                    }
                                    imagedestroy($img);
                                } catch (Exception $e) {
                                    trigger_error($e->getMessage());
                                    continue;
                                }
                            }
                        }
                        if($ext == 'webp') {
                            if($mime!=='image/webp') {
                                trigger_error($file.' has a mime type of '.$mime.'.');
                                continue;
                            } else {
                                try {
                                    $img = imagecreatefromwebp($file);
                                    if(!$img) {
                                        trigger_error($file.' is not a valid webp file.');
                                        continue;
                                    }
                                    imagedestroy($img);
                                } catch (Exception $e) {
                                    trigger_error($e->getMessage());
                                    continue;
                                }
                            }
                        }
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
                    $checkhash = $GLOBALS['db']->select('CubeCart_filemanager', array('file_id'), array('type' => $this->_mode, 'md5hash' => $record['md5hash']), false, 1);
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
        if (empty($ext)) {
            return 0;
        }
        $strlen = strlen($ext)*-1;
        $cache_path = substr($cache_path, 0, $strlen);
        $cache_path = $cache_path.'*.'.$ext;
        $i=0;
        if (($caches = glob($cache_path)) !== false) {
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
                        if (($caches = glob($this->_manage_cache.'/'.$this->_sub_dir.$filename)) !== false) {
                            foreach ($caches as $cached) {
                                unlink($cached);
                            }
                        }
                    }
                }
                $file = $this->_manage_root.'/'.$this->_sub_dir.$result[0]['filename'];
                if ((file_exists($file) && unlink($file)) || !file_exists($file)) {
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
                                    if ($range[0] == '-') {
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
                $sub_dir = (($this->_sub_dir.' ')[0] == '/') ? $this->_sub_dir : '/'.$this->_sub_dir;
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
                    if ($file[0]['type'] == self::FM_FILETYPE_IMG) {
                        $size = getimagesize(CC_ROOT_DIR.'/'.$file[0]['filepath'].$file[0]['filename']);
                        $file[0]['width'] = $size[0];
                        $file[0]['height'] = $size[1];
                    }
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
        if (preg_match('/(\.sh\.inc\.ini|\.htaccess|\.php|\.phar|\.phtml|\.php[3-6]|\.shtml|\.svg|\.cgi|\.pl|\.py|\.rb)$/i', $file_name)) {
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
                    $this->_directories[$this->makeFilepath($dir) ?? ''][] = basename($dir);
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
        return preg_replace('#[^\p{L}\p{N}\w\.\-\_\@]#iu', '_', $name);
    }

    /**
     * Format path string
     *
     * @param string $path
     * @param bool $slash
     * @return string/null
     */
    public function formatPath($path, $slash = true)
    {
        if(is_null($path)) return $path;

        $path = preg_replace('#[\\\/]{2,}#', '/', (string)urldecode($path));
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
                return $data;
            }
            return false;
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
        $sub_dir_path = $this->formatPath($this->_sub_dir) ?? '';
        if ($this->_directories && isset($this->_directories[$sub_dir_path])) {
            // List subdirectories
            foreach ($this->_directories[$sub_dir_path] as $dir) {
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
            
            if (isset($list_folders)) {
                $GLOBALS['smarty']->assign('FOLDERS', $list_folders);
            }
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

            // Full breadcrumb: root + each ancestor segment of the current subdir.
            $crumb_parts = array_filter(explode('/', trim((string)$_GET['subdir'], '/')));
            $crumbs = array(array('name' => '/', 'link' => currentPage(array('subdir'))));
            $accum = array();
            foreach ($crumb_parts as $segment) {
                $accum[] = $segment;
                $crumbs[] = array(
                    'name' => $segment,
                    'link' => currentPage(null, array('subdir' => implode('/', $accum))),
                );
            }
            $GLOBALS['smarty']->assign('FOLDER_BREADCRUMB', $crumbs);
        }

        $filepath_where  = empty($this->_sub_dir) ? 'IS NULL' : '= \''.$GLOBALS['db']->sqlSafe(str_replace('\\', '/', $this->_sub_dir)).'\'';
        $where = '`disabled` = 0 AND `type` = '.(int)$this->_mode.' AND `filepath` '.$filepath_where;


        $fm_size_cookie = isset($_COOKIE['cc_fm_size']) ? (string)$_COOKIE['cc_fm_size'] : 'medium';
        if (!in_array($fm_size_cookie, array('list', 'small', 'medium', 'large'), true)) $fm_size_cookie = 'medium';
        $GLOBALS['smarty']->assign('FM_SIZE', 'fm-item-'.$fm_size_cookie);
        
        $sort = array('filename' => 'ASC');
        if(isset($_POST['fm-sort']) && !empty($_POST['fm-sort'])) {
            $sort_param = $_POST['fm-sort'];
        } elseif($GLOBALS['session']->has('fm-sort')) {
            $sort_param = $GLOBALS['session']->get('fm-sort');
        }
        if(isset($sort_param) && !empty($sort_param)) {
            $sort_params = explode('-', $sort_param);             
            if(in_array($sort_params[0], array('filename', 'filesize', 'date_added')) && in_array($sort_params[1], array('asc', 'desc'))) {
                $GLOBALS['session']->set('fm-sort', $sort_param);
                $GLOBALS['smarty']->assign('FM_SORT', $sort_param);
                $sort = array($sort_params[0] => strtoupper($sort_params[1]));
            }
        }
        
        // Unused-images filter (image mode only). Best-effort: cross-references
        // FK columns and greps HTML content fields. Cannot detect references
        // from skin templates, CSS, JS or external URLs — disclaimer shown.
        $unused_filter = ($this->_mode == self::FM_FILETYPE_IMG && isset($_GET['fm-unused']));
        $referenced_ids = array();
        $html_haystack = '';
        if ($unused_filter) {
            $GLOBALS['smarty']->assign('FM_UNUSED_FILTER', true);
            // FK lookups
            if (($r = $GLOBALS['db']->select('CubeCart_inventory_images', 'file_id')) !== false) {
                foreach ($r as $row) $referenced_ids[(int)$row['file_id']] = true;
            }
            if (($r = $GLOBALS['db']->select('CubeCart_category', 'cat_image', '`cat_image` > 0')) !== false) {
                foreach ($r as $row) $referenced_ids[(int)$row['cat_image']] = true;
            }
            if (($r = $GLOBALS['db']->select('CubeCart_manufacturers', 'image', '`image` IS NOT NULL AND `image` > 0')) !== false) {
                foreach ($r as $row) $referenced_ids[(int)$row['image']] = true;
            }
            // Gift-cert image stored as a config value
            $gc_img = (int)$GLOBALS['config']->get('gift_certs', 'image');
            if ($gc_img > 0) $referenced_ids[$gc_img] = true;
            // HTML content sweep — concat into one big string for fast strpos lookup later.
            foreach (array(
                array('CubeCart_inventory', 'description'),
                array('CubeCart_inventory', 'short_description'),
                array('CubeCart_category', 'cat_desc'),
                array('CubeCart_email_content', 'content_html'),
                array('CubeCart_email_template', 'content_html'),
                array('CubeCart_documents', 'doc_content'),
            ) as $src) {
                if (($r = $GLOBALS['db']->select($src[0], $src[1])) !== false) {
                    foreach ($r as $row) $html_haystack .= ' '.(string)$row[$src[1]];
                }
            }
        }

        if (($files = $GLOBALS['db']->select('CubeCart_filemanager', false, $where, $sort)) !== false) {
            $catalogue = $GLOBALS['catalogue']->getInstance();
            $GLOBALS['smarty']->assign('ROOT_REL', $GLOBALS['rootRel']);
            $stats_total_bytes = 0;
            $stats_count = 0;
            foreach ($files as $key => $file) {
                // Skip images that look used when the unused filter is active.
                if ($unused_filter) {
                    if (isset($referenced_ids[(int)$file['file_id']])) continue;
                    if ($file['filename'] !== '' && strpos($html_haystack, $file['filename']) !== false) continue;
                }
                $stats_total_bytes += (int)$file['filesize'];
                $stats_count++;
                $file['icon']   = $this->getFileIcon($file['mimetype']);
                $file['class']   = (preg_match('#^image#', $file['mimetype'])) ? 'colorbox' : '';
                $file['edit']   = currentPage(null, array('fm-edit' => $file['file_id']));
                $file['delete']   = currentPage(null, array('delete' => $file['file_id'], 'token' => SESSION_TOKEN));
                $file['value']   = $file['file_id'];
                $file['random']   = mt_rand();
                $file['description'] = (!empty($file['description'])) ? $file['description'] : $file['filename'];
                $file['master_filepath']= str_replace(chr(92), "/", $this->_manage_dir.'/'.$file['filepath'].$file['filename']);
                // Image dimensions (fast: getimagesize hits disk per file but the
                // grid usually shows < 100 thumbs at a time).
                $file['dimensions'] = '';
                if ($this->_mode == self::FM_FILETYPE_IMG && preg_match('#^image#', $file['mimetype'])) {
                    $abs = $this->_manage_root.'/'.$file['filepath'].$file['filename'];
                    if (file_exists($abs)) {
                        $info = @getimagesize($abs);
                        if (is_array($info)) $file['dimensions'] = $info[0].'x'.$info[1];
                    }
                }
                $file['date_added_formatted'] = !empty($file['date_added']) ? formatTime(strtotime($file['date_added'])) : '';
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
            $GLOBALS['smarty']->assign('FM_STATS_COUNT', $stats_count);
            $GLOBALS['smarty']->assign('FM_STATS_SIZE', formatBytes($stats_total_bytes, true));
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
        return null;
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

                $gd = new GD($this->_manage_root.'/'.$this->_sub_dir, false, 80);
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
                        'mimetype' => $this->getMimeType($file['tmp_name']),
                        'md5hash' => $this->md5file($file['tmp_name'], $file['size'], true),
                    );

                    $existing = $GLOBALS['db']->select('CubeCart_filemanager', 'file_id', array('filepath' => $filepath_record, 'filename' => $newfilename, 'type' => (int)$this->_mode));
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
                    // Strip EXIF/metadata from JPEG uploads (privacy: removes GPS,
                    // device, timestamps; bonus: smaller filesize). Lossless via
                    // imagejpeg() at the highest quality. JPEG only because EXIF
                    // is the metadata container of concern; PNG/WebP have minor
                    // ancillary chunks that we leave alone.
                    if ($this->_mode == self::FM_FILETYPE_IMG && $record['mimetype'] === 'image/jpeg') {
                        $img = @imagecreatefromjpeg($target);
                        if ($img !== false) {
                            @imagejpeg($img, $target, 95);
                            @imagedestroy($img);
                            clearstatcache(true, $target);
                            $GLOBALS['db']->update('CubeCart_filemanager', array('filesize' => filesize($target)), array('file_id' => (int)$fid));
                        }
                    }
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

            // Append at the end of the existing drag-order so new uploads sit
            // last in the picker's assigned strip.
            $pfx  = $GLOBALS['config']->get('config', 'dbprefix');
            $next = 1;
            if (($mx = $GLOBALS['db']->misc('SELECT MAX(`position`) AS `mx` FROM `'.$pfx.'CubeCart_image_index` WHERE `product_id` = '.(int)$product_id, false)) !== false && isset($mx[0]['mx'])) {
                $next = (int)$mx[0]['mx'] + 1;
            }

            $record = array(
                'file_id'    => $file_id,
                'product_id' => $product_id,
                'main_img'   => $main_image,
                'position'   => $next,
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

    private function mimeParts($mimetype) {
        $mime_parts = explode('/', $mimetype, 2);
        return array(
            'type' => $mime_parts[0],
            'subtype' => isset($mime_parts[1]) ? $mime_parts[1] : ''
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
