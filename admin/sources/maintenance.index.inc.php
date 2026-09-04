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
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('maintenance', CC_PERM_EDIT, true);


function imagesToFolders() {
    $image_path = 'images/source/';
    $image_path_dest = $image_path.'a-z/';
    mkdir($image_path_dest);
    if(file_exists($image_path_dest)) {
        foreach (glob($image_path.'*') as $filename) {
            if(is_file($filename)) {
                $base_name = basename($filename);
                $folder_name = strtoupper(substr($base_name,0,1));
                $folder_path = $image_path_dest.$folder_name;
                if(!file_exists($folder_path)) {
                    mkdir($folder_path);
                }
                echo $filename.' to '.$folder_path.'/'.$base_name.'<br>';
                rename($filename,$folder_path.'/'.$base_name);
            }
        }
        $files = $GLOBALS['db']->select('CubeCart_filemanager', false,array('filepath' => null));
        foreach($files as $file) {
            $folder = strtoupper(substr($file['filename'], 0, 1));
            $GLOBALS['db']->update('CubeCart_filemanager', array('filepath' => 'a-z/'.$folder.'/'), array('file_id' => $file['file_id'], 'filepath' => null));
        }
    }
}

function crc_integrity_check($files, $mode = 'upgrade')
{
    $errors = array();

    $log_path = CC_BACKUP_DIR.$mode.'_error_log';
    if (file_exists($log_path)) {
        unlink($log_path);
    }

    foreach ($files as $file => $value) {
        $abs_file = CC_ROOT_DIR.'/'.$file;
        if (!file_exists($abs_file)) {
            $errors[] = "$file - Missing but expected after extract";
        } elseif (is_file($abs_file)) {
            ## Open the source file
            if (($v_file = fopen($abs_file, "rb")) == 0) {
                $errors[] = "$file - Unable to read in order to validate integrity";
            }

            ## Read the file content
            $filesize = filesize($abs_file);
            $v_content = ((int)$filesize > 0) ? fread($v_file, $filesize) : '';
            fclose($v_file);

            if (crc32($v_content) !== $value) {
                $errors[] = "$file - Content after extract doesn't match source";
            }
        }
    }
    if (count($errors)>0) {
        $errors[] = '--';
        $errors[] = 'Errors were found which may indicate that the source archive has not been extracted successfully.';
        $errors[] = 'It is recommended that a manual '.$mode.' is performed.';
            
        $error_data = "### START ".strtoupper($mode)." LOG - (".date("d M Y - H:i:s").") ###\r\n";
        $error_data .= implode("\r\n", $errors);
        $error_data .=  "\r\n### END RESTORE LOG ###";

        $fp = fopen($log_path, 'w');
        fwrite($fp, $error_data);
        fclose($fp);
    } else {
        return false;
    }
}

$versions = $GLOBALS['db']->select('CubeCart_history');
$version_history = array();
if ($versions) {
    foreach ($versions as $version) {
        $release_notes_path = CC_ROOT_DIR.'/'.$GLOBALS['config']->get('config', 'adminFolder').'/sources/release_notes/'.$version['version'].'.inc.php';
        $version_history[$version['version']] = array(
            'time' => formatTime($version['time']),
            'version' => file_exists($release_notes_path) ? '<a href="?_g=release_notes&node='.$version['version'].'">'.$version['version'].'</a>' : $version['version']
        );
    }
}
krsort($version_history, SORT_NATURAL);
$GLOBALS['smarty']->assign('VERSIONS', $version_history);


if (isset($_GET['compress']) && !empty($_GET['compress'])) {
    chdir(CC_BACKUP_DIR);
    $file_path = './'.basename($_GET['compress']);
    $zip = new ZipArchive;
    
    if (file_exists($file_path) && $zip->open($file_path.'.zip', ZipArchive::CREATE)==true) {
        $zip->addFile($file_path);
        $zip->close();
        $GLOBALS['main']->successMessage(sprintf($lang['maintain']['file_compressed'], basename($file_path)));
        httpredir('?_g=maintenance&node=index', 'backup');
    } else {
        $GLOBALS['main']->errorMessage("Error reading file ".basename($file_path));
    }
}

if (isset($_GET['restore']) && !empty($_GET['restore'])) {

    // Prevent user stopping process
    ignore_user_abort(true);
    // Set max execution time to three minutes
    set_time_limit(180);
    $file_name = basename($_GET['restore']);
    $file_path = CC_BACKUP_DIR.$file_name;

    if (preg_match('/^database_full/', $file_name)) { // Restore database
        $delete_source = false;
        if (preg_match('/\.sql.zip$/', $file_name)) { // unzip first
            
            $zip = new ZipArchive;
            if ($zip->open($file_path) === true) {
                $file_path = rtrim($file_path, '.zip');
                // Only delete if it diesn't exist before
                $delete_source = file_exists($file_path) ? false : true;
                $zip->extractTo(CC_BACKUP_DIR);
                $zip->close();
            } else {
                $GLOBALS['main']->errorMessage("Error reading file ".$file_name);
                httpredir('?_g=maintenance&node=index', 'backup');
            }
        }
        
        $handle = fopen($file_path, "r");
        $import = false;
        $GLOBALS['debug']->status(false); // This prevents memory errors
        if ($handle) {
            $sql = '';
            while (($buffer = fgets($handle)) !== false) {
                $sql .= $buffer;
                if (substr(trim($buffer), -4) === '#EOQ') {
                    if ($GLOBALS['db']->parseSchema($sql)) {
                        $import = true;
                    }
                    $sql = '';
                }
            }
            fclose($handle);
        }
        
        if ($delete_source) {
            unlink($file_path);
        }

        if ($import) {
            $GLOBALS['main']->successMessage($lang['maintain']['db_restored']);
            $GLOBALS['cache']->clear();
            httpredir('?_g=maintenance&node=index', 'backup');
        }
    } elseif (preg_match('/^files/', $file_name)) { // restore archive
        
        $file_path = CC_BACKUP_DIR.$file_name;
        $zip = new ZipArchive;
        if ($zip->open($file_path) === true) {
            $crc_check_list = array();
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $crc_check_list[$stat['name']] = $stat['crc'];
            }

            $zip->extractTo(CC_ROOT_DIR);
            $zip->close();

            $errors = crc_integrity_check($crc_check_list, 'restore');
            
            if ($errors!==false) {
                $GLOBALS['main']->errorMessage($lang['maintain']['files_restore_fail']);
                httpredir('?_g=maintenance&node=index', 'backup');
            } else {
                $GLOBALS['main']->successMessage($lang['maintain']['files_restore_success']);
                httpredir('?_g=maintenance&node=index', 'backup');
            }
        } else {
            $GLOBALS['main']->errorMessage($lang['maintain']['files_restore_not_possible']);
        }
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['files_restore_not_possible']);
        httpredir('?_g=maintenance&node=index', 'backup');
    }
}

if (isset($_GET['upgrade']) && !empty($_GET['upgrade'])) {
    ignore_user_abort(true);
    set_time_limit(300);
    $contents = false;
    $upgrade_version = $_GET['upgrade'];
    ## Download from GitHub
    $download_url = '/cubecart/v6/legacy.zip/refs/tags/'.$upgrade_version;
    $request = new Request('codeload.github.com', $download_url, 443, false, true, 120);
    $request->setMethod('get');
    $request->setSSL();
    $request->skiplog(true);
    $request->customOption(CURLOPT_FOLLOWLOCATION, true);

    if (!$contents = $request->send()) {
        $contents = file_get_contents('https://codeload.github.com'.$download_url);
    }

    if (empty($contents)) {
        $GLOBALS['main']->errorMessage($lang['maintain']['files_upgrade_download_fail']);
        httpredir('?_g=maintenance&node=index', 'upgrade');
    } else {
        if (stristr($contents, 'DOCTYPE') || stristr($contents, 'Not Found')) {
            $GLOBALS['main']->errorMessage("Sorry. CubeCart ".$upgrade_version." was not found on GitHub. Please try again later.");
            httpredir('?_g=maintenance&node=index', 'upgrade');
        }

        $destination_path = CC_BACKUP_DIR.'CubeCart-'.$upgrade_version.'.zip';
        $fp = fopen($destination_path, 'w');
        fwrite($fp, $contents);
        fclose($fp);

        if (file_exists($destination_path)) {
            $zip = new ZipArchive;
            if ($zip->open($destination_path) === true) {
                $crc_check_list = array();

                ## Detect GitHub zip prefix directory (e.g. "cubecart-v6-6503165/")
                $first_entry = $zip->statIndex(0);
                $zip_prefix = (substr($first_entry['name'], -1) === '/') ? $first_entry['name'] : '';

                ## Extract files individually, stripping prefix
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);
                    $name = $stat['name'];

                    ## Strip prefix directory
                    if (!empty($zip_prefix) && strpos($name, $zip_prefix) === 0) {
                        $name = substr($name, strlen($zip_prefix));
                    }
                    if (empty($name)) continue;

                    ## Build CRC check list with admin folder rename
                    if (preg_match("#^admin/#", $name)) {
                        $custom_file_name = preg_replace("#^admin#", $glob['adminFolder'], $name);
                    } elseif ($name == 'admin.php') {
                        $custom_file_name = $glob['adminFile'];
                    } else {
                        $custom_file_name = $name;
                    }
                    $crc_check_list[$custom_file_name] = $stat['crc'];

                    ## Write file to target location
                    $target = CC_ROOT_DIR.'/'.$name;
                    if (substr($name, -1) === '/') {
                        if (!is_dir($target)) mkdir($target, 0755, true);
                    } else {
                        $dir = dirname($target);
                        if (!is_dir($dir)) mkdir($dir, 0755, true);
                        file_put_contents($target, $zip->getFromIndex($i));
                    }
                }
                $zip->close();

                $suffix = '-'.(string)time();
                rename(CC_ROOT_DIR.'/'.$glob['adminFolder'], CC_ROOT_DIR.'/'.$glob['adminFolder'].$suffix);
                rename(CC_ROOT_DIR.'/'.$glob['adminFile'], CC_ROOT_DIR.'/'.$glob['adminFile'].$suffix);
                rename(CC_ROOT_DIR.'/admin', CC_ROOT_DIR.'/'.$glob['adminFolder']);
                rename(CC_ROOT_DIR.'/admin.php', CC_ROOT_DIR.'/'.$glob['adminFile']);
                unlink(CC_ROOT_DIR.'/'.$glob['adminFile'].$suffix);
                recursiveDelete(CC_ROOT_DIR.'/'.$glob['adminFolder'].$suffix);

                $errors = crc_integrity_check($crc_check_list, 'upgrade');

                if ($errors!==false) {
                    $GLOBALS['main']->errorMessage($lang['maintain']['files_upgrade_fail']);
                    httpredir('?_g=maintenance&node=index', 'upgrade');
                } elseif ($_POST['force']) {
                    ## Try to delete setup folder
                    recursiveDelete(CC_ROOT_DIR.'/setup');
                    ## If that fails we try an obscure rename
                    if (file_exists(CC_ROOT_DIR.'/setup')) {
                        rename(CC_ROOT_DIR.'/setup', CC_ROOT_DIR.'/setup'.$suffix);
                    }
                    $GLOBALS['main']->successMessage($lang['maintain']['current_version_restored']);
                    httpredir('?_g=maintenance&node=index', 'upgrade');
                } else {
                    httpredir(CC_ROOT_REL.'setup/index.php?autoupdate=1');
                }
            } else {
                $GLOBALS['main']->errorMessage("Unable to read archive.");
                httpredir('?_g=maintenance&node=index', 'upgrade');
            }
        }
    }
}

if (isset($_GET['delete_all_backups'])) {
    $backup_files = cc_glob(CC_BACKUP_DIR.'*.{sql,zip}');
    $count = 0;
    if (is_array($backup_files)) {
        foreach ($backup_files as $bf) {
            if (is_file($bf)) {
                unlink($bf);
                $count++;
            }
        }
    }
    $GLOBALS['main']->successMessage(sprintf($lang['maintain']['backups_deleted_all'], $count));
    httpredir('?_g=maintenance&node=index', 'backup');
}

if (isset($_GET['delete'])) {
    $file = 'backup/'.basename($_GET['delete']);
    if (in_array($_GET['delete'], array('restore_error_log','upgrade_error_log'))) {
        unlink($file);
        switch ($_GET['delete']) {
            case 'upgrade_error_log':
                $anchor = 'upgrade';
            break;
            case 'restore_error_log':
                $anchor = 'backup';
            break;
        }
        httpredir('?_g=maintenance&node=index', $anchor);
    } elseif (file_exists($file) && preg_match('/^.*\.(sql|zip)$/i', $file)) {
        ## Generic error message for logs delete specific for backup
        $message = preg_match('/\_error_log$/', $file) ? $lang['filemanager']['notify_file_delete'] : sprintf($lang['maintain']['backup_deleted'], basename($file));
        $GLOBALS['main']->successMessage($message);
        unlink($file);
        httpredir('?_g=maintenance&node=index', 'backup');
    }
}
if (isset($_GET['download'])) {
    $file = 'backup/'.basename($_GET['download']);
    if (file_exists($file)) {
        deliverFile($file);
        httpredir('?_g=maintenance&node=index', 'backup');
    }
}

########## Rebuild ##########
$clear_post = false;
if (isset($_POST['clear_sessions'])) {
    if ($GLOBALS['db']->truncate('CubeCart_sessions')) {
        $GLOBALS['main']->successMessage($lang['maintain']['sessions_cleared']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['sessions_not_cleared']);
    }
    $clear_post = true;
}
if (isset($_POST['truncate_seo_custom'])) {
    if ($GLOBALS['db']->delete('CubeCart_seo_urls', array('custom' => 1))) {
        $GLOBALS['main']->successMessage($lang['maintain']['seo_urls_emptied']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['seo_urls_not_emptied']);
    }
    $clear_post = true;
}
if (isset($_POST['truncate_seo_auto'])) {
    if ($GLOBALS['db']->delete('CubeCart_seo_urls', array('custom' => 0))) {
        $GLOBALS['main']->successMessage($lang['maintain']['seo_urls_emptied']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['seo_urls_not_emptied']);
    }
    $clear_post = true;
}

if (isset($_POST['sitemap'])) {
    if ($GLOBALS['seo']->sitemap()) {
        $GLOBALS['main']->successMessage($lang['maintain']['notify_sitemap']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['notify_sitemap_fail']);
    }
    $clear_post = true;
}

if (isset($_REQUEST['emptyTransLogs']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    if ($GLOBALS['db']->truncate('CubeCart_transactions')) {
        $GLOBALS['main']->successMessage($lang['maintain']['notify_logs_transaction']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['error_logs_transaction']);
    }
    $clear_post = true;
    if(isset($_GET['redir']) && $_GET['redir']=='transactions') {
        httpredir('?_g=orders&node=transactions');
        exit;
    }
}

if (isset($_REQUEST['emptyEmailLogs']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    if ($GLOBALS['db']->truncate(array('CubeCart_email_log'))) {
        // Sweep on-disk attachments now that no rows reference any of them.
        Mailer::pruneOrphanedAttachments();
        $GLOBALS['main']->successMessage($lang['maintain']['notify_logs_email']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['error_logs_email']);
    }
    $clear_post = true;
    if(isset($_GET['redir']) && $_GET['redir']=='emaillog') {
        httpredir('?_g=statistics&node=emaillog');
        exit;
    }
}

if (isset($_REQUEST['emptyErrorLogs']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    // Scope by value: 'admin' / 'system' clears just that log; anything else clears both.
    $scope = (string)$_REQUEST['emptyErrorLogs'];
    if ($scope === 'admin') {
        $tables = array('CubeCart_admin_error_log');
        $redir_anchor = 'admin_error_log';
    } elseif ($scope === 'system') {
        $tables = array('CubeCart_system_error_log');
        $redir_anchor = 'system_error_log';
    } else {
        $tables = array('CubeCart_system_error_log', 'CubeCart_admin_error_log');
        $redir_anchor = 'system_error_log';
    }
    if ($GLOBALS['db']->truncate($tables)) {
        $GLOBALS['main']->successMessage($lang['maintain']['notify_logs_error']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['error_logs_error']);
    }
    $clear_post = true;
    if(isset($_GET['redir']) && $_GET['redir']=='viewlog') {
        httpredir('?_g=settings&node=errorlog', $redir_anchor);
        exit;
    }
}

if (isset($_REQUEST['emptyRequestLogs']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    if ($GLOBALS['db']->truncate('CubeCart_request_log')) {
        $GLOBALS['main']->successMessage($lang['maintain']['notify_logs_request']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['error_logs_request']);
    }
    $clear_post = true;
    if(isset($_GET['redir']) && $_GET['redir']=='viewlog') {
        httpredir('?_g=settings&node=requestlog');
        exit;
    }
}

if (isset($_REQUEST['clearSearch']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    if ($GLOBALS['db']->truncate('CubeCart_search')) {
        $GLOBALS['main']->successMessage($lang['maintain']['notify_search_clear']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['error_search_clear']);
    }
    $clear_post = true;
    if(isset($_GET['redir']) && $_GET['redir']=='searchlog') {
        httpredir('?_g=statistics');
        exit;
    }
}

if (isset($_POST['clearCache']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    $GLOBALS['cache']->clear();
    $GLOBALS['cache']->tidy();
    $GLOBALS['main']->successMessage($lang['maintain']['notify_cache_cleared']);
    $clear_post = true;
}

if (isset($_POST['clearImageCache']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    function cleanImageCache($path = null, $failed = array())
    {
        $path = (isset($path) && is_dir($path)) ? $path : CC_ROOT_DIR.'/images/cache/';
        $scan = glob($path.'*', GLOB_MARK);
        if (is_array($scan) && !empty($scan)) {
            foreach ($scan as $result) {
                if (is_dir($result)) {
                    cleanImageCache($result);
                    if(!rmdir($result)) {
                        $failed[] = str_replace(CC_ROOT_DIR.'/images/cache/','',$result);
                    }
                } else {
                    if(!unlink($result)) {
                        $failed[] = str_replace(CC_ROOT_DIR.'/images/cache/','',$result);
                    }
                }
            }
        }
        return count(glob(CC_ROOT_DIR.'/images/cache/'.'*', GLOB_MARK)) > 0 ? $failed : true;
    }
    ## recursively delete the contents of the images/cache folder
    $clearImageCache = cleanImagecache();
    if($clearImageCache===true) {
        $GLOBALS['main']->successMessage($lang['maintain']['notify_cache_image']);
    } else if(is_array($clearImageCache)) {
        foreach($clearImageCache as $file) {
            $GLOBALS['main']->errorMessage(sprintf($lang['maintain']['notify_failed_to_delete'], $file));
        }
    }
    $clear_post = true;
}
if (isset($_POST['prodViews'])) {
    if ($GLOBALS['db']->update('CubeCart_inventory', array('popularity' => 0), '', true)) {
        $GLOBALS['main']->successMessage($lang['maintain']['notify_reset_product']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['error_reset_product']);
    }
    $clear_post = true;
}

if (isset($_REQUEST['clearLogs'])) {
    if ($GLOBALS['db']->truncate(array('CubeCart_admin_log', 'CubeCart_access_log'))) {
        $GLOBALS['main']->successMessage($lang['maintain']['notify_logs_admin']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['error_logs_admin']);
    }
    $clear_post = true;
    if(isset($_GET['redir']) && $_GET['redir']=='viewlog') {
        httpredir('?_g=settings&node=errorlog');
        exit;
    }
    if(isset($_GET['redir']) && $_GET['redir']=='adminlogs') {
        httpredir('?_g=settings&node=logs');
        exit;
    }
}

if (isset($_REQUEST['clearApiLog'])) {
    if ($GLOBALS['db']->truncate('CubeCart_api_log')) {
        $GLOBALS['main']->successMessage('API request log cleared.');
    } else {
        $GLOBALS['main']->errorMessage('Failed to clear API request log.');
    }
    $clear_post = true;
    if (isset($_GET['redir']) && $_GET['redir'] == 'apikeys') {
        httpredir('?_g=settings&node=apikeys', 'apilog');
        exit;
    }
}

########## Database ##########
if (!empty($_POST['database'])) {
    if (is_array($_POST['tablename'])) {
        $valid_tables = array();
        foreach ((array)$GLOBALS['db']->misc("SHOW TABLES") as $row) {
            $valid_tables[] = reset($row);
        }
        $tableList = array();
        foreach ($_POST['tablename'] as $value) {
            // Only operate on tables that actually exist, and escape any backticks in
            // the identifier so it cannot break out of the quoting (GHSA-qcx6-cg43-ffmx).
            if (in_array($value, $valid_tables, true)) {
                $tableList[] = sprintf('`%s`', str_replace('`', '``', $value));
            }
        }
        if(!empty($tableList) && in_array($_POST['action'], array('REBUILD','CHECK','ANALYZE'))) {
        if ($_POST['action'] === 'REBUILD') {
            foreach ($tableList as $table) {
                $GLOBALS['db']->query(sprintf("ALTER TABLE %s ENGINE=InnoDB;", $table));
            }
        } else {
            $GLOBALS['db']->query(sprintf("%s TABLE %s;", $_POST['action'], implode(',', $tableList)));
        }
        $GLOBALS['main']->successMessage(sprintf($lang['maintain']['notify_db_action'], $_POST['action']));
        } else {
            die('Action not allowed.');
        }
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['db_none_selected']);
    }
}

########## Backup ##########
if (isset($_GET['files_backup'])) {

    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // Prevent user stopping process
    ignore_user_abort(true);
    // Allow up to 1 hour — large stores with many images can take several minutes.
    // Note: web-server level timeouts (nginx fastcgi_read_timeout, PHP-FPM
    // request_terminate_timeout) are not affected and may also need raising.
    set_time_limit(3600);

    chdir(CC_ROOT_DIR);
    $destination = CC_BACKUP_DIR.'files_'.CC_VERSION.'_'.date("dMy-His").'.zip';

    // Detect a PHP execution timeout (E_ERROR fatal) in the shutdown handler.
    // Web-server-level kills are not catchable, but the PHP-level timeout is.
    register_shutdown_function(function () use ($destination) {
        $error = error_get_last();
        if ($error && $error['type'] === E_ERROR && strpos($error['message'], 'Maximum execution time') !== false) {
            if (file_exists($destination)) {
                unlink($destination); // remove the incomplete zip
            }
        }
    });

    // For AJAX requests, release the session and send response immediately
    // so the user can navigate away while the backup continues.
    if ($is_ajax) {
        session_write_close();
        $response = json_encode(array('status' => 'started'));
        header('Content-Type: application/json');
        header('Content-Length: ' . strlen($response));
        header('Connection: close');
        while (ob_get_level()) ob_end_flush();
        echo $response;
        flush();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }

    $zip = new ZipArchive();

    $zip = new ZipArchive();

    if ($zip->open($destination, ZipArchive::CREATE)!==true) {
        if ($is_ajax) {
            $mailer = new Mailer();
            $mailer->sendEmail($GLOBALS['config']->get('config', 'email_address'), array(
                'subject'      => sprintf($lang['maintain']['backup_failed_email_subject'], ucwords($lang['maintain']['title_files_backup'])),
                'content_html' => '<p>'.sprintf($lang['maintain']['backup_failed_email_body'], strtolower($lang['maintain']['title_files_backup'])).'</p>',
            ));
        } else {
            $GLOBALS['main']->errorMessage("Error: Backup failed.");
        }
    } else {
        $cache_folder = basename(CC_CACHE_DIR);
        $backup_folder = basename(CC_BACKUP_DIR);
        $files_folder = basename(CC_FILES_DIR);

        $skip_folders = $backup_folder.'|'.$cache_folder.'|images/cache|includes/extra/sess_';
        if (isset($_POST['skip_images']) && $_POST['skip_images']=='1') {
            $zip->addEmptyDir('./images/source');
            $skip_folders .= '|images/source';
        }
        if (isset($_POST['skip_downloads']) && $_POST['skip_downloads']=='1') {
            $zip->addEmptyDir('./'.$files_folder);
            if (file_exists('./'.$files_folder.'/.htaccess')) {
                $zip->addFile('./'.$files_folder.'/.htaccess');
            }
            $skip_folders .= '|'.$files_folder;
        }

        $zip->addEmptyDir('./'.$backup_folder);
        if (file_exists('./'.$backup_folder.'/.htaccess')) {
            $zip->addFile('./'.$backup_folder.'/.htaccess');
        }

        $zip->addEmptyDir('./'.$cache_folder);
        if (file_exists('./'.$cache_folder.'/.htaccess')) {
            $zip->addFile('./'.$cache_folder.'/.htaccess');
        }
        $zip->addEmptyDir('./images/cache');

        // Use a lazy RecursiveDirectoryIterator instead of glob_recursive() to
        // avoid loading the entire file-tree into a PHP array before we start.
        // The RecursiveCallbackFilterIterator prunes skip-listed directories so
        // we never even descend into them.
        $skip_pattern = '#^('.$skip_folders.')#';
        $dir_iter = new RecursiveDirectoryIterator('.', RecursiveDirectoryIterator::SKIP_DOTS);
        $filter_iter = new RecursiveCallbackFilterIterator($dir_iter, function ($current) use ($skip_pattern) {
            if ($current->isDir()) {
                $path = ltrim(str_replace('\\', '/', $current->getPathname()), './');
                return !preg_match($skip_pattern, $path);
            }
            return true;
        });
        $iterator = new RecursiveIteratorIterator($filter_iter, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $fileInfo) {
            $path = ltrim(str_replace('\\', '/', $fileInfo->getPathname()), './');
            if ($path === 'images' || preg_match($skip_pattern, $path)) {
                continue;
            }
            if ($fileInfo->isDir()) {
                $zip->addEmptyDir('./'.$path);
            } else {
                $zip->addFile($fileInfo->getPathname(), './'.$path);
            }
        }
        $zip->close();
        if ($is_ajax) {
            $backup_file = basename($destination);
            $backup_size = formatBytes(filesize($destination), true);
            $backup_url  = $GLOBALS['storeURL'].'/'.$GLOBALS['config']->get('config', 'adminFile').'?_g=maintenance&node=index#backup';
            $type_label  = strtolower($lang['maintain']['title_files_backup']);
            $type_label_uc = ucwords($lang['maintain']['title_files_backup']);
            $mailer = new Mailer();
            $mailer->sendEmail($GLOBALS['config']->get('config', 'email_address'), array(
                'subject'      => sprintf($lang['maintain']['backup_complete_email_subject'], $type_label_uc),
                'content_html' => '<p>'.sprintf($lang['maintain']['backup_complete_email_body'], $type_label).'</p>'
                    .'<p>'.sprintf($lang['maintain']['backup_complete_email_filename'], $backup_file).'<br>'
                    .sprintf($lang['maintain']['backup_complete_email_size'], $backup_size).'</p>'
                    .'<p>'.sprintf($lang['maintain']['backup_complete_email_download'], '<a href="'.$backup_url.'">'.$backup_url.'</a>').'</p>',
            ));
        } else {
            $GLOBALS['main']->successMessage($lang['maintain']['files_backup_complete']);
        }
    }
    if ($is_ajax) {
        exit;
    }
    httpredir('?_g=maintenance&node=index', 'backup');
}

if (isset($_POST['backup'])) {

    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // Prevent user stopping process
    ignore_user_abort(true);
    // Allow up to 1 hour — large databases can take several minutes.
    // Note: web-server level timeouts (nginx fastcgi_read_timeout, PHP-FPM
    // request_terminate_timeout) are not affected and may also need raising.
    set_time_limit(3600);

    if (!$_POST['drop'] && !$_POST['structure'] && !$_POST['data']) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'message' => $lang['maintain']['error_db_backup_option']));
            exit;
        }
        $GLOBALS['main']->errorMessage($lang['maintain']['error_db_backup_option']);
    } else {
        if ($_POST['drop'] && !$_POST['structure']) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'error', 'message' => $lang['maintain']['error_db_backup_conflict']));
                exit;
            }
            $GLOBALS['main']->errorMessage($lang['maintain']['error_db_backup_conflict']);
        } else {
            $full = ($_POST['drop'] && $_POST['structure'] && $_POST['data']) ? '_full' : '';
            chdir(CC_BACKUP_DIR);
            $fileName 	= 'database'.$full.'_'.CC_VERSION.'_'.$glob['dbdatabase']."_".date("dMy-His").'.sql';
            if (file_exists($fileName)) { // Keep file pointer at the start
                unlink($fileName);
            }

            // Detect a PHP execution timeout (E_ERROR fatal) in the shutdown handler.
            register_shutdown_function(function () use ($fileName) {
                $error = error_get_last();
                if ($error && $error['type'] === E_ERROR && strpos($error['message'], 'Maximum execution time') !== false) {
                    if (file_exists($fileName)) {
                        unlink($fileName);
                    }
                    if (file_exists($fileName.'.zip')) {
                        unlink($fileName.'.zip');
                    }
                }
            });

            // For AJAX requests, release the session and send response immediately
            // so the user can navigate away while the backup continues.
            if ($is_ajax) {
                session_write_close();
                $response = json_encode(array('status' => 'started'));
                header('Content-Type: application/json');
                header('Content-Length: ' . strlen($response));
                header('Connection: close');
                while (ob_get_level()) ob_end_flush();
                echo $response;
                flush();
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }
            }

            $all_tables = (isset($_POST['db_3rdparty']) && $_POST['db_3rdparty'] == '1') ? true : false;
            $write = $GLOBALS['db']->doSQLBackup($_POST['drop'], $_POST['structure'], $_POST['data'], $fileName, $_POST['compress'], $all_tables);
            if ($is_ajax) {
                $mailer = new Mailer();
                $admin_email = $GLOBALS['config']->get('config', 'email_address');
                if ($write) {
                    $actual_file = $_POST['compress'] ? $fileName.'.zip' : $fileName;
                    $backup_size = file_exists($actual_file) ? formatBytes(filesize($actual_file), true) : '';
                    $backup_url  = $GLOBALS['storeURL'].'/'.$GLOBALS['config']->get('config', 'adminFile').'?_g=maintenance&node=index#backup';
                    $type_label  = strtolower($lang['maintain']['title_db_backup']);
                    $type_label_uc = ucwords($lang['maintain']['title_db_backup']);
                    $mailer->sendEmail($admin_email, array(
                        'subject'      => sprintf($lang['maintain']['backup_complete_email_subject'], $type_label_uc),
                        'content_html' => '<p>'.sprintf($lang['maintain']['backup_complete_email_body'], $type_label).'</p>'
                            .'<p>'.sprintf($lang['maintain']['backup_complete_email_filename'], basename($actual_file)).'<br>'
                            .sprintf($lang['maintain']['backup_complete_email_size'], $backup_size).'</p>'
                            .'<p>'.sprintf($lang['maintain']['backup_complete_email_download'], '<a href="'.$backup_url.'">'.$backup_url.'</a>').'</p>',
                    ));
                } else {
                    $mailer->sendEmail($admin_email, array(
                        'subject'      => sprintf($lang['maintain']['backup_failed_email_subject'], ucwords($lang['maintain']['title_db_backup'])),
                        'content_html' => '<p>'.sprintf($lang['maintain']['backup_failed_email_body'], strtolower($lang['maintain']['title_db_backup'])).'</p>',
                    ));
                }
            } else {
                if ($write) {
                    $GLOBALS['main']->successMessage($lang['maintain']['db_backup_complete']);
                } else {
                    $GLOBALS['main']->errorMessage($lang['maintain']['db_backup_failed']);
                }
            }
        }
        if ($is_ajax) {
            exit;
        }
        $clear_post = true;
    }
}

if ($clear_post) {
    httpredir(currentPage(array('clearLogs', 'emptyErrorLogs')));
    exit;
}

########## Tabs ##########
$GLOBALS['main']->addTabControl($lang['maintain']['tab_rebuild'], 'rebuild');
$GLOBALS['main']->addTabControl($lang['maintain']['tab_backup'], 'backup');
$GLOBALS['main']->addTabControl($lang['common']['upgrade'], 'upgrade');
$GLOBALS['main']->addTabControl($lang['maintain']['tab_db'], 'database');
$GLOBALS['main']->addTabControl($lang['maintain']['tab_elasticsearch'], 'elasticsearch');
if($GLOBALS['config']->get('config', 'elasticsearch')=='1') {
    $es = new ElasticsearchHandler;
    $GLOBALS['smarty']->assign('ES_STATS', $es->getStats());
}
$GLOBALS['main']->addTabControl($lang['maintain']['tab_query_sql'], 'general', '?_g=maintenance&node=sql');

##########

## Database
if (isset($database_result) && $database_result) {
    $GLOBALS['smarty']->assign('TABLES_AFTER', $database_result);
} elseif (($tables = $GLOBALS['db']->getRows()) !== false) {

    ## Build a reference schema by replaying structure.sql into throwaway scratch tables,
    ## then compare each live table against it via SHOW COLUMNS / SHOW INDEX. Letting MySQL
    ## parse the schema (instead of regex) keeps column types and single vs composite
    ## keys canonical, so the diff is correct by construction. Real (non-TEMPORARY) tables
    ## are used because FULLTEXT indexes cannot be created on TEMPORARY InnoDB tables; they
    ## live in a distinct _ccref_ namespace and are dropped before and after the check.
    $all_fix_sql  = array();
    $found_tables = array();
    $prefix       = $GLOBALS['config']->get('config', 'dbprefix');
    $ref_prefix   = '_ccref_';   // scratch namespace for reference tables (never collides with live CubeCart_ tables)

    $ref_cols   = array();  // canonical => array(col_lower => array('name','def'))
    $ref_idx    = array();  // canonical => array(index_name => array('type','sig','cols','parts'))
    $ref_temp   = array();  // canonical => temp table name
    $ref_create = array();  // canonical => SHOW CREATE TABLE sql (for missing-table fix)

    // group SHOW INDEX rows into name => {type, ordered cols, signature}
    $build_indexes = function($rows) {
        $by = array();
        if (is_array($rows)) {
            foreach ($rows as $r) {
                $n = $r['Key_name'];
                if (!isset($by[$n])) {
                    if ($n === 'PRIMARY')                    $t = 'PRIMARY';
                    elseif ($r['Index_type'] === 'FULLTEXT') $t = 'FULLTEXT';
                    elseif ($r['Non_unique'] == '0')         $t = 'UNIQUE';
                    else                                     $t = 'KEY';
                    $by[$n] = array('type' => $t, 'cols' => array(), 'parts' => array());
                }
                $seq = (int)$r['Seq_in_index'];
                $by[$n]['cols'][$seq]  = strtolower($r['Column_name']);
                $by[$n]['parts'][$seq] = '`'.$r['Column_name'].'`'.((!empty($r['Sub_part'])) ? '('.$r['Sub_part'].')' : '');
            }
        }
        foreach ($by as &$ix) {
            ksort($ix['cols']); ksort($ix['parts']);
            $ix['cols']  = array_values($ix['cols']);
            $ix['parts'] = array_values($ix['parts']);
            $ix['sig']   = $ix['type'].':'.implode(',', $ix['cols']);
        }
        return $by;
    };
    $type_label = function($t) {
        return ($t === 'UNIQUE') ? 'UNIQUE KEY' : (($t === 'FULLTEXT') ? 'FULLTEXT' : (($t === 'PRIMARY') ? 'PRIMARY' : 'KEY'));
    };
    $add_clause = function($name, $ix) {
        $cols = implode(',', $ix['parts']);
        if ($ix['type'] === 'PRIMARY')  return 'ADD PRIMARY KEY ('.$cols.')';
        if ($ix['type'] === 'UNIQUE')   return 'ADD UNIQUE KEY `'.$name.'` ('.$cols.')';
        if ($ix['type'] === 'FULLTEXT') return 'ADD FULLTEXT KEY `'.$name.'` ('.$cols.')';
        return 'ADD KEY `'.$name.'` ('.$cols.')';
    };
    $drop_clause = function($name) {
        return ($name === 'PRIMARY') ? 'DROP PRIMARY KEY' : 'DROP INDEX `'.$name.'`';
    };

    ## Build reference tables from structure.sql as TEMPORARY tables
    $structure_file = CC_ROOT_DIR.'/classes/db/schema/structure.sql';
    if (!file_exists($structure_file)) {
        $GLOBALS['main']->errorMessage('Unable to read classes/db/schema/structure.sql.');
    } else {
        $sql = file_get_contents($structure_file);
        $rp  = preg_quote($ref_prefix, '/');
        ## Clean up any scratch tables left behind by an interrupted previous run
        $like = str_replace('_', '\\_', $ref_prefix.'CubeCart_').'%';
        foreach ((array)$GLOBALS['db']->misc("SHOW TABLES LIKE '".$like."'") as $row) {
            $GLOBALS['db']->query('DROP TABLE IF EXISTS `'.reset($row).'`');
        }
        foreach (preg_split('/;\s*#EOQ/i', $sql) as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') continue;
            $stmt = str_replace(array('CubeCart_', '{%DEFAULT_EN-XX%}'), array($ref_prefix.'CubeCart_', 'en-GB'), $stmt);
            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?('.$rp.'CubeCart_\w+)`?/i', $stmt, $m)) {
                $temp = $m[1];
                $GLOBALS['db']->query('DROP TABLE IF EXISTS `'.$temp.'`');
                $GLOBALS['db']->query($stmt); // scratch reference table (real table: FULLTEXT-capable)
                $ref_temp[strtolower(str_replace($ref_prefix, '', $temp))] = $temp;
            } elseif (preg_match('/ALTER\s+TABLE\s+`?'.$rp.'CubeCart_\w+`?/i', $stmt) && preg_match('/\bADD\b/i', $stmt)) {
                $GLOBALS['db']->query($stmt); // replay index/column alters onto the reference table
            }
            // non-DDL statements (seed data etc.) are irrelevant to structure and skipped
        }
        foreach ($ref_temp as $canonical => $temp) {
            $cr = $GLOBALS['db']->misc('SHOW CREATE TABLE `'.$temp.'`');
            if (!is_array($cr) || empty($cr[0])) { unset($ref_temp[$canonical]); continue; }
            $create_sql = isset($cr[0]['Create Table']) ? $cr[0]['Create Table'] : end($cr[0]);
            $ref_create[$canonical] = $create_sql;
            $cols = array();
            foreach (preg_split('/\r?\n/', $create_sql) as $line) {
                if (preg_match('/^`([^`]+)`\s+(.+?),?$/', trim($line), $cm)) {
                    $cols[strtolower($cm[1])] = array('name' => $cm[1], 'def' => rtrim($cm[2], ','));
                }
            }
            $ref_cols[$canonical] = $cols;
            $ref_idx[$canonical]  = $build_indexes($GLOBALS['db']->misc('SHOW INDEX FROM `'.$temp.'`'));
        }
    }

    ## Compare each live table against the reference
    foreach ($tables as $table) {
        if (!is_array($table) || !isset($table['Name'])) continue;
        if (!preg_match('/^'.$prefix.'CubeCart_/i', $table['Name'])) continue;
        $canonical = strtolower(str_replace($prefix, '', $table['Name']));
        $found_tables[] = $canonical;
        $errors = array();

        if (isset($ref_cols[$canonical])) {
            // columns (existence check, matching prior behaviour)
            $live_cols = array();
            foreach ((array)$GLOBALS['db']->misc('SHOW COLUMNS FROM `'.$table['Name'].'`') as $c) {
                $live_cols[strtolower($c['Field'])] = true;
            }
            foreach ($ref_cols[$canonical] as $cl => $ci) {
                if (!isset($live_cols[$cl])) {
                    $errors[] = sprintf($lang['maintain']['missing_column'], $table['Name'], $ci['name']);
                    $all_fix_sql[] = 'ALTER TABLE `'.$table['Name'].'` ADD `'.$ci['name'].'` '.$ci['def'].'; #EOQ';
                }
            }

            // indexes
            $rfx = $ref_idx[$canonical];
            $lfx = $build_indexes($GLOBALS['db']->misc('SHOW INDEX FROM `'.$table['Name'].'`'));
            $consumed = array();
            foreach ($rfx as $n => $ix) {
                if (isset($lfx[$n]) && $lfx[$n]['sig'] === $ix['sig']) continue; // already correct
                if (isset($lfx[$n])) { // present but wrong columns/type -> recreate
                    $errors[] = sprintf($lang['maintain']['missing_index'], $table['Name'].'.'.implode(', ', $ix['cols']), $type_label($ix['type']));
                    $all_fix_sql[] = 'ALTER TABLE `'.$table['Name'].'` '.$drop_clause($n).', '.$add_clause($n, $ix).'; #EOQ';
                    continue;
                }
                // missing by name: maybe the same index exists under a different name
                $alt = null;
                foreach ($lfx as $ln => $lx) {
                    if (!isset($rfx[$ln]) && !in_array($ln, $consumed) && $lx['sig'] === $ix['sig']) { $alt = $ln; break; }
                }
                if ($alt !== null) {
                    $consumed[] = $alt;
                    $errors[] = sprintf($lang['maintain']['wrong_index_name'], $table['Name'], $alt, $n);
                    $all_fix_sql[] = 'ALTER TABLE `'.$table['Name'].'` '.$drop_clause($alt).', '.$add_clause($n, $ix).'; #EOQ';
                } else {
                    $errors[] = sprintf($lang['maintain']['missing_index'], $table['Name'].'.'.implode(', ', $ix['cols']), $type_label($ix['type']));
                    $all_fix_sql[] = 'ALTER TABLE `'.$table['Name'].'` '.$add_clause($n, $ix).'; #EOQ';
                }
            }
            // redundant live indexes: an EXACT duplicate (same signature) of an expected index
            // or of another live index. Leftmost-prefix indexes are intentionally NOT flagged.
            foreach ($lfx as $ln => $lx) {
                if ($ln === 'PRIMARY' || isset($rfx[$ln]) || in_array($ln, $consumed)) continue;
                $dup = false;
                foreach ($rfx as $rx) { if ($rx['sig'] === $lx['sig']) { $dup = true; break; } }
                if (!$dup) { foreach ($lfx as $ln2 => $lx2) { if ($ln2 !== $ln && $lx2['sig'] === $lx['sig']) { $dup = true; break; } } }
                if ($dup) {
                    $loc = $table['Name'].'.'.(isset($lx['cols'][0]) ? $lx['cols'][0] : $ln);
                    $errors[] = sprintf($lang['maintain']['duplicate_index'], $loc, $type_label($lx['type']));
                    $all_fix_sql[] = 'ALTER TABLE `'.$table['Name'].'` DROP INDEX `'.$ln.'`; #EOQ';
                }
            }
        }

        $table['Data_free']    = ($table['Data_free'] > 0) ? formatBytes($table['Data_free'], true) : '-';
        $table_size            = $table['Data_length'] + $table['Index_length'];
        $data_length           = formatBytes($table_size);
        $table['Data_length']  = ($table_size > 0) ? $data_length['size'].' '.$data_length['suffix'] : '-';
        $table['Name_Display'] = $GLOBALS['config']->get('config', 'dbdatabase').'.'.$table['Name'];
        $table['errors']       = count($errors) > 0 ? implode('<br>', $errors) : false;
        $smarty_data['tables'][] = $table;
    }

    ## Missing tables
    $missing_tables = array();
    foreach ($ref_create as $canonical => $create_sql) {
        if (in_array($canonical, $found_tables)) continue;
        $live_name = $prefix.str_replace('cubecart_', 'CubeCart_', $canonical);
        $missing_tables[] = sprintf($lang['maintain']['missing_table'], $live_name);
        $fix = preg_replace('/^CREATE\s+(?:TEMPORARY\s+)?TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?[^`\s(]+`?/i', 'CREATE TABLE IF NOT EXISTS `'.$live_name.'`', $create_sql, 1);
        $fix = preg_replace('/\s+AUTO_INCREMENT=\d+/i', '', $fix);
        $all_fix_sql[] = $fix.'; #EOQ';
    }
    if (!empty($missing_tables)) {
        $GLOBALS['smarty']->assign('MISSING_TABLES', $missing_tables);
    }

    ## Drop the scratch reference tables
    foreach ($ref_temp as $temp) { $GLOBALS['db']->query('DROP TABLE IF EXISTS `'.$temp.'`'); }

    $GLOBALS['smarty']->assign('TABLES', isset($smarty_data['tables']) ? $smarty_data['tables'] : array());
    if (!empty($all_fix_sql)) {
        $GLOBALS['smarty']->assign('INDEX_FIX_SQL', implode("\n", $all_fix_sql));
    }
}


## Existing Backups
$files = cc_glob('{backup/*.sql,backup/*.zip}');
$existing_backups = array();
if (count($files)>0) {
    foreach ($files as $file) {
        $sorted_files[filemtime($file)] = $file;
    }
    unset($files);

    krsort($sorted_files); // Sort to time order

    foreach ($sorted_files as $file) {
        $filename = basename($file);
        $type = preg_match('/^database/', $filename) ? 'database' : 'files';
        $restore = preg_match('/^database_full|files/', $filename) ? '?_g=maintenance&node=index&restore='.$filename.'#backup' : false;
        $compress = (preg_match('/.zip$/', $filename) || file_exists($file.'.zip')) ? false : '?_g=maintenance&node=index&compress='.$filename.'#backup';
        $existing_backups[] = array('filename' => $filename,
            'delete_link' => '?_g=maintenance&node=index&delete='.$filename.'#backup',
            'download_link' => '?_g=maintenance&node=index&download='.$filename.'#backup',
            'restore_link' => $restore,
            'compress' =>  $compress,
            'type' => $type,
            'warning' => ($type=='database') ? $lang['maintain']['restore_db_confirm'] : $lang['maintain']['restore_files_confirm'],
            'size' => formatBytes(filesize($file), true)
        );
    }
}
$GLOBALS['smarty']->assign('EXISTING_BACKUPS', $existing_backups);

## Upgrade
## Check current version via GitHub releases
if ($request = new Request('api.github.com', '/repos/cubecart/v6/releases/latest')) {
    $request->skiplog(true);
    $request->setMethod('get');
    $request->cache(true);
    $request->setSSL();
    $request->customHeaders('Accept: application/vnd.github.v3+json');

    if (($response = $request->send()) !== false) {
        $release = json_decode($response, true);
        $latest_version = isset($release['tag_name']) ? trim($release['tag_name']) : CC_VERSION;
        if (version_compare($latest_version, CC_VERSION, '>')) {
            $GLOBALS['smarty']->assign('OUT_OF_DATE', sprintf($lang['dashboard']['error_version_update'], $latest_version, CC_VERSION));
            $GLOBALS['smarty']->assign('LATEST_VERSION', $latest_version);
            $GLOBALS['smarty']->assign('UPGRADE_NOW', $lang['maintain']['upgrade_now']);
            $GLOBALS['smarty']->assign('FORCE', '0');
        } else {
            $GLOBALS['smarty']->assign('LATEST_VERSION', CC_VERSION);
            $GLOBALS['smarty']->assign('UPGRADE_NOW', $lang['maintain']['force_upgrade']);
            $GLOBALS['smarty']->assign('FORCE', '1');
        }
    } else {
        $GLOBALS['smarty']->assign('LATEST_VERSION', $lang['common']['unknown']);
        $GLOBALS['smarty']->assign('UPGRADE_NOW', $lang['maintain']['force_upgrade']);
        $GLOBALS['smarty']->assign('FORCE', '1');
        $GLOBALS['main']->successMessage($lang['maintain']['latest_version_unknown']);
    }
}

if (file_exists(CC_BACKUP_DIR.'restore_error_log')) {
    $contents = file_get_contents(CC_BACKUP_DIR.'restore_error_log');
    if (!empty($contents)) {
        $GLOBALS['smarty']->assign('RESTORE_ERROR_LOG', $contents);
    }
}

if (file_exists(CC_BACKUP_DIR.'upgrade_error_log')) {
    $contents = file_get_contents(CC_BACKUP_DIR.'upgrade_error_log');
    if (!empty($contents)) {
        $GLOBALS['smarty']->assign('UPGRADE_ERROR_LOG', $contents);
    }
}

$page_content = $GLOBALS['smarty']->fetch('templates/maintenance.index.php');
