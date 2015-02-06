<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

class FileManager {
	private $_directories;
	private $_mode;

	private $_manage_cache;
	private $_manage_dir;
	private $_manage_root;
	private $_sub_dir;

	private $_sendfile = false;

	private $_max_upload_image_size = 358400;

	const FM_FILETYPE_IMG = 1;
	const FM_FILETYPE_DL = 2;

	##############################################

	public function __construct($mode = false, $sub_dir = false) {
		// Define some constants
		if (!defined('FM_DL_ERROR_EXPIRED')) define('FM_DL_ERROR_EXPIRED', 1);
		if (!defined('FM_DL_ERROR_MAXDL'))  define('FM_DL_ERROR_MAXDL', 2);
		if (!defined('FM_DL_ERROR_NOFILE'))  define('FM_DL_ERROR_NOFILE', 3);
		if (!defined('FM_DL_ERROR_NOPRODUCT')) define('FM_DL_ERROR_NOPRODUCT', 4);
		if (!defined('FM_DL_ERROR_NORECORD')) define('FM_DL_ERROR_NORECORD', 5);
		if (!defined('FM_DL_ERROR_PAYMENT')) define('FM_DL_ERROR_PAYMENT', 6);

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
		$this->_mode  = (int)$mode;
		$this->_manage_dir = str_replace(CC_ROOT_DIR.'/', '', $this->_manage_root);
		$this->_sub_dir  = ($sub_dir) ? $this->formatPath($sub_dir) : null;

		//Auto-handler: Create Directory
		if (isset($_POST['fm']['create-dir']) && !empty($_POST['fm']['create-dir'])) {
			$create = $this->createDirectory($_POST['fm']['create-dir']);
		}
		// Auto-handler: image details & cropping
		if (isset($_POST['file_id']) && is_numeric($_POST['file_id'])) {
			if (($file = $GLOBALS['db']->select('CubeCart_filemanager', false, array('file_id' => (int)$_POST['file_id']))) !== false) {
				if (isset($_POST['details'])) {

					if (!$this->filename_is_illegal($_POST['details']['filename'])) {

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

	/**
	 * Setup admin screen
	 *
	 * @param bool $select_button
	 * @return bool
	 */
	public function admin($select_button = false) {
		$this->listFiles(false, $select_button);
		if (isset($_GET['CKEditorFuncNum'])) {
			$GLOBALS['smarty']->assign('CK_FUNC_NUM', (int)$_GET['CKEditorFuncNum']);
		}

		$GLOBALS['smarty']->assign('mode_list', true);

		return $GLOBALS['smarty']->fetch('templates/filemanager.index.php');
	}

	/**
	 * Build image DB
	 *
	 * @param bool $purge
	 * @param bool $tidy
	 * @param string $dir
	 * @return bool
	 */
	public function buildDatabase($purge = false, $tidy = false, $dir = '') {
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
					// Skip existing entries, and sources/thumbs
					if (isset($exists) && in_array(str_replace(array($this->_manage_root.'/', 'source/'), '', $file), $exists)) continue;

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

					$record = array(
						'type'  => (int)$this->_mode,
						'filepath' => $filepath_record,
						'filename' => $newfilename,
						'filesize' => filesize($file),
						'mimetype' => $this->getMimeType($file),
						'md5hash' => md5_file($file),
					);

					// Hash comparison check
					$checkhash = $GLOBALS['db']->select('CubeCart_filemanager', array('file_id'), array('type' => $this->_mode, 'md5hash' => $record['md5hash'], 'filepath' => $record['filepath']), false, 1);
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
		}
	}

	/**
	 * Create folder
	 *
	 * @param string $new_dir
	 * @return bool
	 */
	private function createDirectory($new_dir = false) {
		if (!empty($new_dir)) {
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
	public function delete($target = null, $del_folder = false) {
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
	 * Delete file
	 *
	 * @param int $file_id
	 * @return bool
	 */
	private function deleteFile($file_id = null) {
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
	private function deleteRecursive($directory = null) {
		$directory = urldecode($directory);
		$scan = glob($this->_manage_root.'/'.$directory.'/'.'*');
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
	public function deliverDownload($access_key = false, &$error = null) {
		if ($this->_mode == self::FM_FILETYPE_DL && $access_key) {
			if (($downloads = $GLOBALS['db']->select('CubeCart_downloads', false, array('accesskey' => $access_key))) !== false) {
				$download = $downloads[0];
				if (($summary = $GLOBALS['db']->select('CubeCart_order_summary', false, array('cart_order_id' => $download['cart_order_id']))) !== false) {
					// Order/Download Validation
					// Download has expired
					if ($download['expire']>0 && $download['expire'] < time())  $error = self::FM_DL_ERROR_EXPIRED;
					// Order hasn't been paid for
					if (!in_array((int)$summary[0]['status'], array(2, 3)))     $error = self::FM_DL_ERROR_PAYMENT;
					// Maximum download limit has been reached
					if ($GLOBALS['config']->get('config', 'download_count') > 0 && (int)$download['downloads'] >= $GLOBALS['config']->get('config', 'download_count')) $error = self::FM_DL_ERROR_MAXDL;
					if (!empty($error)) return false;
					if ($data = $this->getFileInfo($download['product_id']) !== false) {


						// Deliver file contents
						if (isset($data['file']) && ($data['is_url'] || file_exists($data['file']))) {
							if ($is_url) {
								$GLOBALS['db']->update('CubeCart_downloads', array('downloads' => $download['downloads']+1), array('digital_id' => $download['digital_id']));
								httpredir($file);
								return true;
							} else {
								ob_end_clean();
								if (!is_file($file) or connection_status()!=0) return false;

								header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
								header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
								header('Content-Disposition: attachment; filename="'.basename($file).'"');
								header("Content-Type: application/octet-stream");
								header("Content-Transfer-Encoding: binary");
								## IE 7 Fix
								header('Vary: User-Agent');

								if (($openfile = fopen($file, 'rb')) !== false) {
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
	public function editor($file_id = null) {
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
							if ($this->_mode == self::FM_FILETYPE_IMG && in_array(basename($root), array('thumbs', 'source'))) continue;
							foreach ($folders as $folder) {
								if ($this->_mode == self::FM_FILETYPE_IMG && in_array(basename($folder), array('thumbs', 'source'))) continue;
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

	public function filename_is_illegal($file_name) {
		if (preg_match('/(\.sh\.inc\.ini|\.htaccess|\.php|\.phtml|\.php[3-6])$/i', $file_name)) {
			return true;
		} else if (preg_match('/\.php\./i', $file_name)) {
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
	public function findDirectories($search_dir = false, $i = 0) {
		$search_dir = (!$search_dir) ? $this->_manage_dir : $search_dir;
		if ($search_dir && file_exists($search_dir)) {
			$list = glob($search_dir.'/'.'*', GLOB_ONLYDIR);
			if ($list) {
				foreach ($list as $dir) {
					if ($this->_mode == self::FM_FILETYPE_IMG && in_array(basename($dir), array('thumbs', 'source', '_vti_cnf'))) continue;
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
	private function formatName($name) {
		return preg_replace('#[^\w\.\-\_]#i', '_', $name);
	}

	/**
	 * Format path string
	 *
	 * @param string $path
	 * @param bool $slash
	 * @return string
	 */
	public function formatPath($path, $slash = true) {
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
	 * Get file icon
	 *
	 * @param string $mimetype
	 * @return string
	 */
	private function getFileIcon($mimetype = false) {
		if (preg_match('#^image#i', $mimetype)) {
			return 'image';
		} else {
			switch ($mimetype) {
			case 'application/x-gzip':
			case 'application/x-gtar':
			case 'application/x-tar':
			case 'application/x-zip':
			case 'application/zip':
				$icon = 'page_archive';
				break;
			case 'video/mpeg':
			case 'video/quicktime':
			case 'video/x-msvideo':
				$icon = 'video';
				break;
			case 'application/msword':
				$icon = 'page_word';
				break;
			case 'application/vnd.ms-excel':
				$icon = 'page_excel';
				break;
			default:
				$icon = 'page_generic';
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
	public function getFileInfo($product_id) {
		$product = $GLOBALS['db']->select('CubeCart_inventory', array('digital', 'digital_path'), array('product_id' => $product_id), false, 1);  $GLOBALS['db']->select('CubeCart_filemanager', false, array('file_id' => $product[0]['digital']));

		if (empty($product[0]['digital_path'])) {
			if (($files = $GLOBALS['db']->select('CubeCart_filemanager', false, array('file_id' => $product[0]['digital']))) !== false) {
				$data = $files[0];
				$data['is_url'] = false;
				$data['file'] = $this->_manage_root.'/'.$data['filepath'].'/'.$data['filename'];
				return $data;
			}
		} else {
			if (filter_var($product[0]['digital_path'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
				$data = array(
					'mimetype' => 'application/octet-stream',
					'filename' => basename($product[0]['digital_path']),
					'filesize' => null,
					'md5hash' => md5($product[0]['digital_path']),
					'is_url' => true,
					'file'  => $product[0]['digital_path'],
					'url'  => parse_url($product[0]['digital_path'])
				);
				return $data;
			} else if (file_exists($product[0]['digital_path'])) {
					$data = array(
						'mimetype' => 'application/octet-stream',
						'filename' => basename($product[0]['digital_path']),
						'filepath' => dirname($product[0]['digital_path']),
						'filesize' => filesize($product[0]['digital_path']),
						'md5hash' => md5_file($product[0]['digital_path']),
						'is_url' => true
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
	public function getMimeType($file) {
		$finfo = (extension_loaded('fileinfo')) ? new finfo(FILEINFO_MIME_TYPE) : false;
		if ($finfo && $finfo instanceof finfo) {
			$mime = $finfo->file($file);
		} else if (function_exists('mime_content_type')) {
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
	public function getMode() {
		return $this->_mode;
	}

	/**
	 * List file type
	 *
	 * @param string $type
	 * @param bool $select_button
	 * @return array/false
	 */
	public function listFiles($type = false, $select_button = false) {
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
				if ($this->_mode == self::FM_FILETYPE_IMG && in_array($this->makeFilename($dir), array('thumbs', 'source'))) continue;
				$name = $this->makeFilename($dir);
				$folder = array(
					'name'  => $name,
					'link'  => currentPage(null, array('subdir' => $this->formatPath($this->_sub_dir.$dir, false))),
					'delete' => (substr($name, 0, 1) !== '.') ? currentPage(null, array('delete' => $this->formatPath($this->_sub_dir.$dir, false))) : null,
				);
				$list_folders[] = $folder;
			}
			$GLOBALS['smarty']->assign('FOLDERS', $list_folders);
		}

		$filepath_where  = empty($this->_sub_dir) ? 'IS NULL' : '= \''.str_replace('\\', '/', $this->_sub_dir).'\'';
		$where = '`disabled` = 0 AND `type` = '.(int)$this->_mode.' AND `filepath` '.$filepath_where;

		if (($files = $GLOBALS['db']->select('CubeCart_filemanager', false, $where, array('filename' => 'ASC'))) !== false) {
			$catalogue = new Catalogue();
			$GLOBALS['smarty']->assign('ROOT_REL', $GLOBALS['rootRel']);
			foreach ($files as $key => $file) {
				$file['icon']   = $this->getFileIcon($file['mimetype']);
				$file['class']   = (preg_match('#^image#', $file['mimetype'])) ? 'class="colorbox"' : '';
				$file['edit']   = currentPage(null, array('fm-edit' => $file['file_id']));
				$file['delete']   = currentPage(null, array('delete' => $file['file_id']));
				$file['random']   = mt_rand();
				$file['description'] = (!empty($file['description'])) ? $file['description'] : $file['filename'];
				$file['master_filepath']= str_replace(chr(92), "/", $this->_manage_dir.'/'.$file['filepath'].$file['filename']);
				$file['filepath']   = ($this->_mode == self::FM_FILETYPE_IMG) ? $catalogue->imagePath($file['file_id'], 'medium') : $this->_manage_dir.'/'.$file['filepath'].$file['filename'];
				$file['select_button'] = (bool)$select_button;

				if ($select_button) $file['master_filepath'] = $GLOBALS['rootRel'].$file['master_filepath']; // Fix the image path added to the FCK editor area

				$list_files[$key] = $file;
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
	private function makeFilename($file) {
		// Standardize the filename
		return $this->formatName(basename($file));
	}

	/**
	 * Make file path
	 *
	 * @param string $file
	 * @return string
	 */
	private function makeFilepath($file) {
		$path =  str_replace($this->_manage_root, '', dirname($file));
		return $this->formatPath($path);
	}

	/**
	 * Upgrade file
	 *
	 * @param array/string $start
	 * @param string $dir
	 * @return bool
	 */
	public function upgrade($start = null, $dir = null) {
		if (is_array($start)) {
			foreach ($start as $seek) {
				$this->upgrade($seek, $dir);
			}
		} else {
			$scan_root = CC_ROOT_DIR.'/images/uploads/'.$start;
			if (substr($scan_root, -1, 1) != '/') $scan_root .= '/';

			$scan_dir = $scan_root;
			if (!is_null($dir)) {
				$scan_dir .= (substr($dir, 0, 1) == '/') ? substr($dir, 1) : $dir;
			}

			if (file_exists($scan_dir) && is_dir($scan_dir)) {
				if (($files = glob($scan_dir.'*', GLOB_MARK)) !== false) {
					foreach ($files as $file) {
						$target = str_replace($scan_root, '', $file);
						if (is_dir($file)) {
							if (in_array($target, array('source', 'thumbs', '_vti_cnf'))) continue;
							$this->upgrade($start, $target);
							rmdir($file);
						} else {
							// Copy to new sources
							$to = $this->_manage_root.'/'.$target;
							if (!file_exists(dirname($to))) mkdir(dirname($to), chmod_writable(), true);
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
	public function upload($type = false, $thumbnail = false) {
		if (!empty($_FILES)) {
			$finfo = (extension_loaded('fileinfo')) ? new finfo(FILEINFO_SYMLINK | FILEINFO_MIME) : false;
			foreach ($_FILES as $file) {

				if ($this->filename_is_illegal($file['name'])) continue;

				if (is_array($file['tmp_name'])) {
					foreach ($file['tmp_name'] as $offset => $tmp_name) {
						$gd = new GD($this->_manage_root.'/'.$this->_sub_dir);
						if (!empty($tmp_name) && is_uploaded_file($tmp_name)) {


							if ($this->_mode == self::FM_FILETYPE_IMG && $file['size'][$offset] > $this->_max_upload_image_size) {
								$GLOBALS['gui']->setError(sprintf($GLOBALS['lang']['filemanager']['error_file_upload_size'], $file['name'][$offset], formatBytes($this->_max_upload_image_size, true, 0)));
								continue;
							}

							if ($file['error'][$offset] !== UPLOAD_ERR_OK) {
								$this->uploadError($file['error'][$offset]);
								continue;
							}

							$target = $target_old = $this->_manage_root.'/'.$this->_sub_dir.$file['name'][$offset];
							$newfilename = $this->makeFilename($file['name'][$offset]);
							$oldfilename = $file['name'][$offset];

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
								'filesize' => $file['size'][$offset],
								'mimetype' => $file['type'][$offset] ? $file['type'][$offset] : $this->getMimeType($tmp_name),
								'md5hash' => md5_file($tmp_name),
							);

							if ($GLOBALS['db']->insert('CubeCart_filemanager', $record)) {
								$file_id[] = $GLOBALS['db']->insertid();
								move_uploaded_file($tmp_name, $target);
								chmod($target, chmod_writable());
							}

						}
					}
				} else {

					$gd = new GD($this->_manage_root.'/'.$this->_sub_dir);
					if (!empty($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {

						if ($this->_mode == self::FM_FILETYPE_IMG && $file['size'] > $this->_max_upload_image_size) {
							$GLOBALS['gui']->setError(sprintf($GLOBALS['lang']['filemanager']['error_file_upload_size'], $file['name'], formatBytes($this->_max_upload_image_size, true, 0)));
							return false;
						}

						if ($file['error'] !== UPLOAD_ERR_OK) {
							$this->uploadError($file['error']);
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
							'md5hash' => md5_file($file['tmp_name']),
						);

						if ($GLOBALS['db']->insert('CubeCart_filemanager', $record)) {
							$file_id[] = $GLOBALS['db']->insertid();
							move_uploaded_file($file['tmp_name'], $target);
							chmod($target, chmod_writable());
						}


					}
				}
			}
			return (isset($file_id)) ? $file_id : true;
		}
		return false;
	}

	/**
	 * Upload error messages
	 *
	 * @param int $error_no
	 *
	 * @return false
	 */
	private function uploadError($error_no) {
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