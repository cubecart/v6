<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2025. All rights reserved.
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
        if (!file_exists($file)) {
            $errors[] = "$file - Missing but expected after extract";
        } elseif (is_file($file)) {
            ## Open the source file
            if (($v_file = fopen($file, "rb")) == 0) {
                $errors[] = "$file - Unable to read in order to validate integrity";
            }

            ## Read the file content
            $filesize = filesize($file);
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
    $contents = false;
    ## Download the version we want
    $request = new Request('www.cubecart.com', '/download/'.$_GET['upgrade'].'.zip', 80, false, true, 10);#
    $request->setMethod('get');
    $request->setSSL();
    $request->setUserAgent('CubeCart');
    $request->skiplog(true);

    if (!$contents = $request->send()) {
        $contents = file_get_contents('https://www.cubecart.com/download/'.$_GET['upgrade'].'.zip');
    }

    if (empty($contents)) {
        $GLOBALS['main']->errorMessage($lang['maintain']['files_upgrade_download_fail']);
        httpredir('?_g=maintenance&node=index', 'upgrade');
    } else {
        if (stristr($contents, 'DOCTYPE')) {
            $GLOBALS['main']->errorMessage("Sorry. CubeCart-".$_GET['upgrade'].".zip was not found. Please try again later.");
            httpredir('?_g=maintenance&node=index', 'upgrade');
        }

        $destination_path = CC_BACKUP_DIR.'CubeCart-'.$_GET['upgrade'].'.zip';
        $fp = fopen($destination_path, 'w');
        fwrite($fp, $contents);
        fclose($fp);

        if (file_exists($destination_path)) {
            $zip = new ZipArchive;
            if ($zip->open($destination_path) === true) {
                $crc_check_list = array();

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $stat = $zip->statIndex($i);

                    if (preg_match("#^admin/#", $stat['name'])) {
                        $custom_file_name = preg_replace("#^admin#", $glob['adminFolder'], $stat['name']);
                    } elseif ($stat['name']=='admin.php') {
                        $custom_file_name = $glob['adminFile'];
                    } else {
                        $custom_file_name = $stat['name'];
                    }
                    $crc_check_list[$custom_file_name] = $stat['crc'];
                }

                $zip->extractTo(CC_ROOT_DIR);
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
if (isset($_POST['clearCookieConsent'])) {
    if ($GLOBALS['db']->truncate('CubeCart_cookie_consent')) {
        $GLOBALS['db']->truncate('CubeCart_cookie_consent_text');
        $GLOBALS['main']->successMessage($lang['maintain']['cookie_consent_cleared']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['cookie_consent_not_cleared']);
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

if (isset($_POST['emptyTransLogs']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    if ($GLOBALS['db']->truncate('CubeCart_transactions')) {
        $GLOBALS['main']->successMessage($lang['maintain']['notify_logs_transaction']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['error_logs_transaction']);
    }
    $clear_post = true;
}

if (isset($_REQUEST['emptyEmailLogs']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    if ($GLOBALS['db']->truncate(array('CubeCart_email_log'))) {
        $GLOBALS['main']->successMessage($lang['maintain']['notify_logs_email']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['error_logs_email']);
    }
    $clear_post = true;
}

if (isset($_REQUEST['emptyErrorLogs']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    if ($GLOBALS['db']->truncate(array('CubeCart_system_error_log', 'CubeCart_admin_error_log'))) {
        $GLOBALS['main']->successMessage($lang['maintain']['notify_logs_error']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['error_logs_error']);
    }
    $clear_post = true;
    if(isset($_GET['redir']) && $_GET['redir']=='viewlog') {
        httpredir('?_g=settings&node=errorlog','system_error_log');
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

if (isset($_POST['clearSearch']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    if ($GLOBALS['db']->truncate('CubeCart_search')) {
        $GLOBALS['main']->successMessage($lang['maintain']['notify_search_clear']);
    } else {
        $GLOBALS['main']->errorMessage($lang['maintain']['error_search_clear']);
    }
    $clear_post = true;
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
}

########## Database ##########
if (!empty($_POST['database'])) {
    if (is_array($_POST['tablename'])) {
        foreach ($_POST['tablename'] as $value) {
            $tableList[] = sprintf('`%s`', $value);
        }
        if(in_array($_POST['action'], array('OPTIMIZE','REPAIR','CHECK','ANALYZE'))) {
        $database_result = $GLOBALS['db']->query(sprintf("%s TABLE %s;", $_POST['action'], implode(',', $tableList)));
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

    ## Parse structure.sql to build index map and column map (single source of truth)
    $index_map = array();
    $full_indexes = array();
    $column_map = array();
    $structure_file = CC_ROOT_DIR.'/classes/db/schema/structure.sql';
    if (file_exists($structure_file)) {
        $sql = file_get_contents($structure_file);
        $statements = preg_split('/;\s*#EOQ/i', $sql);
        ## Track all index types per column (a column can be in multiple indexes)
        foreach ($statements as $stmt) {
            ## Parse CREATE TABLE statements
            if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $stmt, $tbl_match)) {
                $table_name = strtolower($tbl_match[1]);
                if (!isset($index_map[$table_name])) {
                    $index_map[$table_name] = array();
                }
                ## Match index definitions including bare UNIQUE(...) and FULLTEXT(...) without KEY
                if (preg_match_all('/^\s*(PRIMARY\s+KEY|UNIQUE\s+KEY|FULLTEXT\s+KEY|UNIQUE|FULLTEXT|KEY)\s*(?:`?(\w+)`?\s*)?\(((?:[^()]+|\([^)]*\))+)\)/im', $stmt, $idx_matches, PREG_SET_ORDER)) {
                    foreach ($idx_matches as $idx) {
                        $raw_type = strtoupper(trim($idx[1]));
                        if (strpos($raw_type, 'PRIMARY') !== false) {
                            $key_type = 'PRIMARY';
                        } elseif (strpos($raw_type, 'UNIQUE') !== false) {
                            $key_type = 'UNIQUE KEY';
                        } elseif (strpos($raw_type, 'FULLTEXT') !== false) {
                            $key_type = 'FULLTEXT';
                        } else {
                            $key_type = 'KEY';
                        }
                        ## Extract column names, strip prefix lengths e.g. (150) and backticks
                        $columns = explode(',', $idx[3]);
                        foreach ($columns as $col) {
                            $col = trim(str_replace('`', '', $col));
                            $col = trim(preg_replace('/\(\d+\)/', '', $col));
                            if (!empty($col)) {
                                if (!isset($index_map[$table_name][$col])) {
                                    $index_map[$table_name][$col] = array();
                                }
                                if (!in_array($key_type, $index_map[$table_name][$col])) {
                                    $index_map[$table_name][$col][] = $key_type;
                                }
                            }
                        }
                        ## Store full index definition for fix SQL generation
                        $idx_key_name = ($key_type === 'PRIMARY') ? 'PRIMARY' : (!empty($idx[2]) ? $idx[2] : null);
                        if ($idx_key_name !== null) {
                            $full_indexes[$table_name][$idx_key_name] = array('type' => $key_type, 'columns' => $idx[3]);
                        }
                    }
                }
                ## Parse column definitions
                $column_map[$table_name] = array();
                $stmt_lines = preg_split('/\r?\n/', $stmt);
                foreach ($stmt_lines as $line) {
                    $line = trim($line);
                    if (preg_match('/^`(\w+)`\s+(.+)$/', $line, $col_match)) {
                        $col_name = $col_match[1];
                        $col_def = rtrim(rtrim($col_match[2]), ',');
                        $column_map[$table_name][$col_name] = $col_def;
                    }
                }
            }
            ## Parse ALTER TABLE ... ADD INDEX/KEY/PRIMARY KEY/UNIQUE statements
            if (preg_match('/ALTER\s+TABLE\s+`?(\w+)`?\s+ADD\s+(PRIMARY\s+KEY|UNIQUE\s+KEY|UNIQUE|FULLTEXT|INDEX|KEY)\s*(?:`?(\w+)`?\s*)?\(((?:[^()]+|\([^)]*\))+)\)/i', $stmt, $alt_match)) {
                $table_name = strtolower($alt_match[1]);
                if (!isset($index_map[$table_name])) {
                    $index_map[$table_name] = array();
                }
                $raw_type = strtoupper(trim($alt_match[2]));
                if (strpos($raw_type, 'PRIMARY') !== false) {
                    $key_type = 'PRIMARY';
                } elseif (strpos($raw_type, 'UNIQUE') !== false) {
                    $key_type = 'UNIQUE KEY';
                } elseif (strpos($raw_type, 'FULLTEXT') !== false) {
                    $key_type = 'FULLTEXT';
                } else {
                    $key_type = 'KEY';
                }
                $columns = explode(',', $alt_match[4]);
                foreach ($columns as $col) {
                    $col = trim(str_replace('`', '', $col));
                    $col = trim(preg_replace('/\(\d+\)/', '', $col));
                    if (!empty($col)) {
                        if (!isset($index_map[$table_name][$col])) {
                            $index_map[$table_name][$col] = array();
                        }
                        if (!in_array($key_type, $index_map[$table_name][$col])) {
                            $index_map[$table_name][$col][] = $key_type;
                        }
                    }
                }
                ## Store full index definition for fix SQL generation
                $alt_key_name = ($key_type === 'PRIMARY') ? 'PRIMARY' : (!empty($alt_match[3]) ? $alt_match[3] : null);
                if ($alt_key_name !== null) {
                    $full_indexes[$table_name][$alt_key_name] = array('type' => $key_type, 'columns' => $alt_match[4]);
                }
            }
        }
    } else {
        $GLOBALS['main']->errorMessage('Unable to read classes/db/schema/structure.sql.');
    }

    $actual_map = array();
    $all_fix_sql = array();
    $found_tables = array();

    foreach ($tables as $table) {
        if (!preg_match('/^'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_/i', $table['Name'])) {
            continue;
        }
        $found_tables[] = strtolower(str_replace($GLOBALS['config']->get('config', 'dbprefix'), '', $table['Name']));

        // Get index and map them
        $indexes = $GLOBALS['db']->misc("SHOW INDEX FROM `".$table['Name']."`");
        $index_errors = array();
        $actual_full_indexes = array();

        foreach ($indexes as $index) {
            if ($index['Key_name']=='PRIMARY') {
                $key_type = 'PRIMARY';
            } elseif ($index['Index_type'] == 'FULLTEXT') {
                $key_type = 'FULLTEXT';
            } elseif ($index['Non_unique'] == '0') {
                $key_type = 'UNIQUE KEY';
            } else {
                $key_type = 'KEY';
            }
            $table_name = $GLOBALS['config']->get('config', 'dbprefix').str_replace('cubecart', 'CubeCart', $index['Table']);
            $duplicate = false;
            if (isset($actual_map[$index['Table']][$index['Column_name']]) && in_array($key_type, $actual_map[$index['Table']][$index['Column_name']])) {
                $duplicate = sprintf($lang['maintain']['duplicate_index'], $table_name.'.'.$index['Column_name'], $key_type);
            }
            if (!isset($actual_map[$index['Table']][$index['Column_name']])) {
                $actual_map[$index['Table']][$index['Column_name']] = array();
            }
            if (!in_array($key_type, $actual_map[$index['Table']][$index['Column_name']])) {
                $actual_map[$index['Table']][$index['Column_name']][] = $key_type;
            }
            // Build full index definitions grouped by key name
            if (!isset($actual_full_indexes[$index['Key_name']])) {
                $actual_full_indexes[$index['Key_name']] = array('type' => $key_type, 'columns' => array());
            }
            $actual_full_indexes[$index['Key_name']]['columns'][(int)$index['Seq_in_index']] = '`'.$index['Column_name'].'`';
        }

        if (!empty($index_map) && isset($index_map[strtolower($index['Table'])])) {
            foreach ($index_map[strtolower($index['Table'])] as $column => $expected_types) {
                $table_name = $GLOBALS['config']->get('config', 'dbprefix').str_replace('cubecart', 'CubeCart', $index['Table']);
                if (!isset($actual_map[$index['Table']][$column])) {
                    $index_errors[] = sprintf($lang['maintain']['missing_index'], $table_name.'.'.$column, implode(', ', $expected_types));
                } else {
                    $missing_types = array_diff($expected_types, $actual_map[$index['Table']][$column]);
                    foreach ($missing_types as $missing_type) {
                        $index_errors[] = sprintf($lang['maintain']['missing_index'], $table_name.'.'.$column, $missing_type);
                    }
                }
            }
        }

        if ($duplicate !== false) {
            $index_errors[] = $duplicate;
        }

        // Check for missing columns
        $table_key = strtolower(str_replace($GLOBALS['config']->get('config', 'dbprefix'), '', $table['Name']));
        if (isset($column_map[$table_key])) {
            $actual_columns = $GLOBALS['db']->misc("SHOW COLUMNS FROM `".$table['Name']."`");
            $actual_col_names = array();
            if ($actual_columns) {
                foreach ($actual_columns as $col) {
                    $actual_col_names[] = strtolower($col['Field']);
                }
            }
            foreach ($column_map[$table_key] as $col_name => $col_def) {
                if (!in_array(strtolower($col_name), $actual_col_names)) {
                    $index_errors[] = sprintf($lang['maintain']['missing_column'], $table['Name'], $col_name);
                    $all_fix_sql[] = 'ALTER TABLE `'.$table['Name'].'` ADD `'.$col_name.'` '.$col_def.'; #EOQ';
                }
            }
        }

        // Generate fix SQL by comparing full index definitions
        $table_lower = strtolower($index['Table']);
        if (!empty($full_indexes[$table_lower])) {
            $renamed_indexes = array();
            foreach ($full_indexes[$table_lower] as $key_name => $expected_idx) {
                $fix_table = '`'.$table['Name'].'`';
                if (!isset($actual_full_indexes[$key_name])) {
                    // Index name missing - check if same type+columns exist under a different name
                    $expected_cols_norm = array_map(function($c) {
                        return strtolower(trim(preg_replace('/\(\d+\)/', '', str_replace('`', '', trim($c)))));
                    }, explode(',', $expected_idx['columns']));
                    $old_name = null;
                    foreach ($actual_full_indexes as $act_name => $act_idx) {
                        if ($act_idx['type'] === $expected_idx['type']) {
                            ksort($act_idx['columns']);
                            $act_cols_norm = array_map(function($c) {
                                return strtolower(trim(str_replace('`', '', $c)));
                            }, array_values($act_idx['columns']));
                            if ($expected_cols_norm === $act_cols_norm) {
                                $old_name = $act_name;
                                break;
                            }
                        }
                    }
                    if ($key_name === 'PRIMARY') {
                        $all_fix_sql[] = 'ALTER TABLE '.$fix_table.' ADD PRIMARY KEY ('.$expected_idx['columns']; #EOQ');
                    } elseif ($old_name !== null) {
                        // Same index exists under wrong name - drop old, add with correct name
                        $renamed_indexes[] = $old_name;
                        $table_name = $GLOBALS['config']->get('config', 'dbprefix').str_replace('cubecart', 'CubeCart', $index['Table']);
                        $index_errors[] = sprintf($lang['maintain']['wrong_index_name'], $table_name, $old_name, $key_name);
                        $type_kw = ($expected_idx['type'] === 'FULLTEXT') ? 'FULLTEXT KEY' : $expected_idx['type'];
                        $all_fix_sql[] = 'ALTER TABLE '.$fix_table.' DROP INDEX `'.$old_name.'`, ADD '.$type_kw.' `'.$key_name.'` ('.$expected_idx['columns'].'); #EOQ';
                    } else {
                        $type_kw = ($expected_idx['type'] === 'FULLTEXT') ? 'FULLTEXT KEY' : $expected_idx['type'];
                        $all_fix_sql[] = 'ALTER TABLE '.$fix_table.' ADD '.$type_kw.' `'.$key_name.'` ('.$expected_idx['columns'].'); #EOQ';
                    }
                } elseif ($actual_full_indexes[$key_name]['type'] !== $expected_idx['type']) {
                    // Index exists but wrong type
                    if ($key_name === 'PRIMARY') {
                        $all_fix_sql[] = 'ALTER TABLE '.$fix_table.' DROP PRIMARY KEY, ADD PRIMARY KEY ('.$expected_idx['columns'].'); #EOQ';
                    } else {
                        $type_kw = ($expected_idx['type'] === 'FULLTEXT') ? 'FULLTEXT KEY' : $expected_idx['type'];
                        $all_fix_sql[] = 'ALTER TABLE '.$fix_table.' DROP INDEX `'.$key_name.'`, ADD '.$type_kw.' `'.$key_name.'` ('.$expected_idx['columns'].'); #EOQ';
                    }
                } else {
                    // Index exists with correct type - check columns match
                    $expected_cols = array_map(function($c) {
                        return strtolower(trim(preg_replace('/\(\d+\)/', '', str_replace('`', '', trim($c)))));
                    }, explode(',', $expected_idx['columns']));
                    ksort($actual_full_indexes[$key_name]['columns']);
                    $actual_cols = array_map(function($c) {
                        return strtolower(trim(str_replace('`', '', $c)));
                    }, array_values($actual_full_indexes[$key_name]['columns']));
                    if ($expected_cols !== $actual_cols) {
                        // Index has wrong columns - drop and recreate
                        if ($key_name === 'PRIMARY') {
                            $all_fix_sql[] = 'ALTER TABLE '.$fix_table.' DROP PRIMARY KEY, ADD PRIMARY KEY ('.$expected_idx['columns'].'); #EOQ';
                        } else {
                            $type_kw = ($expected_idx['type'] === 'FULLTEXT') ? 'FULLTEXT KEY' : $expected_idx['type'];
                            $all_fix_sql[] = 'ALTER TABLE '.$fix_table.' DROP INDEX `'.$key_name.'`, ADD '.$type_kw.' `'.$key_name.'` ('.$expected_idx['columns'].'); #EOQ';
                        }
                    }
                }
            }
            // Check for duplicate actual indexes that should be dropped
            foreach ($actual_full_indexes as $act_name => $act_idx) {
                if ($act_name === 'PRIMARY' || isset($full_indexes[$table_lower][$act_name]) || in_array($act_name, $renamed_indexes)) {
                    continue; // Skip expected indexes, PRIMARY, and already-renamed indexes
                }
                ksort($act_idx['columns']);
                $act_cols_norm = array_map(function($c) {
                    return strtolower(trim(str_replace('`', '', $c)));
                }, array_values($act_idx['columns']));
                foreach ($full_indexes[$table_lower] as $exp_name => $exp_idx) {
                    if ($act_idx['type'] !== $exp_idx['type']) {
                        continue;
                    }
                    $exp_cols_norm = array_map(function($c) {
                        return strtolower(trim(preg_replace('/\(\d+\)/', '', str_replace('`', '', trim($c)))));
                    }, explode(',', $exp_idx['columns']));
                    if ($act_cols_norm === $exp_cols_norm) {
                        $all_fix_sql[] = 'ALTER TABLE '.$fix_table.' DROP INDEX `'.$act_name.'`; #EOQ';
                        break;
                    }
                }
            }
        }

        $table['Data_free'] = ($table['Data_free'] > 0) ? formatBytes($table['Data_free'], true) : '-';
        $table_size   = $table['Data_length']+$table['Index_length'];
        $data_length  = formatBytes($table_size);
        $table['Data_length'] = ($table_size>0) ? $data_length['size'].' '.$data_length['suffix'] : '-';
        $table['Name_Display'] = $GLOBALS['config']->get('config', 'dbdatabase').'.'.$table['Name'];
        $table['errors'] = count($index_errors)>0 ? implode('<br>', $index_errors) : false;
        $smarty_data['tables'][] = $table;
    }
    // Check for missing tables
    $missing_tables = array();
    $prefix = $GLOBALS['config']->get('config', 'dbprefix');
    foreach ($column_map as $expected_table => $cols) {
        if (!in_array($expected_table, $found_tables)) {
            $display_name = $prefix.str_replace('cubecart_', 'CubeCart_', $expected_table);
            $missing_tables[] = sprintf($lang['maintain']['missing_table'], $display_name);
            // Generate CREATE TABLE fix from structure.sql
            foreach ($statements as $fix_stmt) {
                if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $fix_stmt, $fix_match)) {
                    if (strtolower($fix_match[1]) === $expected_table) {
                        $create_sql = trim($fix_stmt);
                        if ($prefix) {
                            $create_sql = str_replace('`'.$fix_match[1].'`', '`'.$display_name.'`', $create_sql);
                        }
                        $all_fix_sql[] = $create_sql.'; #EOQ';
                        break;
                    }
                }
            }
        }
    }
    if (!empty($missing_tables)) {
        $GLOBALS['smarty']->assign('MISSING_TABLES', $missing_tables);
    }

    $GLOBALS['smarty']->assign('TABLES', $smarty_data['tables']);
    if (!empty($all_fix_sql)) {
        $GLOBALS['smarty']->assign('INDEX_FIX_SQL', implode("\n", $all_fix_sql));
    }
}


## Existing Backups
$files = glob('{backup/*.sql,backup/*.zip}', GLOB_BRACE);
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
## Check current version
if ($request = new Request('www.cubecart.com', '/version-check/'.CC_VERSION)) {
    $request->skiplog(true);
    $request->setMethod('get');
    $request->cache(true);
    $request->setSSL();
    $request->setUserAgent('CubeCart');
    $request->setData(array('version' => CC_VERSION));

    if (($response = $request->send()) !== false) {
        $response_array = json_decode($response, true);
        if (version_compare(trim($response_array['version']), CC_VERSION, '>')) {
            $GLOBALS['smarty']->assign('OUT_OF_DATE', sprintf($lang['dashboard']['error_version_update'], $response_array['version'], CC_VERSION));
            $GLOBALS['smarty']->assign('LATEST_VERSION', $response_array['version']);
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
