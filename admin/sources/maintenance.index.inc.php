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
            $v_content = fread($v_file, filesize($file));
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
        $version_history[$version['version']] = $version;
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
    // Make sure line endings can be detected
    ini_set("auto_detect_line_endings", true);
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

if (isset($_POST['clearSQLCache']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    $GLOBALS['cache']->clear('sql');
    $GLOBALS['main']->successMessage($lang['maintain']['notify_cache_cleared']);
    $clear_post = true;
}

if (isset($_POST['clearLangCache']) && Admin::getInstance()->permissions('maintenance', CC_PERM_DELETE)) {
    $GLOBALS['cache']->clear('lang');
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

    // Prevent user stopping process
    ignore_user_abort(true);
    // Set max execution time to three minutes
    set_time_limit(180);
    
    chdir(CC_ROOT_DIR);
    $destination = CC_BACKUP_DIR.'files_'.CC_VERSION.'_'.date("dMy-His").'.zip';

    $zip = new ZipArchive();

    if ($zip->open($destination, ZipArchive::CREATE)!==true) {
        $GLOBALS['main']->errorMessage("Error: Backup failed.");
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

        $files = glob_recursive('*');


        $zip->addEmptyDir('./'.$backup_folder);
        if (file_exists('./'.$backup_folder.'/.htaccess')) {
            $zip->addFile('./'.$backup_folder.'/.htaccess');
        }

        $zip->addEmptyDir('./'.$cache_folder);
        if (file_exists('./'.$cache_folder.'/.htaccess')) {
            $zip->addFile('./'.$cache_folder.'/.htaccess');
        }
        $zip->addEmptyDir('./images/cache');

        foreach ($files as $file) {
            $file_match = preg_replace('#^./#', '', $file);
            if ($file == 'images' || preg_match('#^('.$skip_folders.')#', $file_match)) {
                continue;
            }
            if (is_dir($file)) {
                $zip->addEmptyDir($file);
            } else {
                $zip->addFile($file);
            }
        }
        $zip->close();
        $GLOBALS['main']->successMessage($lang['maintain']['files_backup_complete']);
    }
    httpredir('?_g=maintenance&node=index', 'backup');
}

if (isset($_POST['backup'])) {

    // Prevent user stopping process
    ignore_user_abort(true);
    // Set max execution time to three minutes
    set_time_limit(180);

    if (!$_POST['drop'] && !$_POST['structure'] && !$_POST['data']) {
        $GLOBALS['main']->errorMessage($lang['maintain']['error_db_backup_option']);
    } else {
        if ($_POST['drop'] && !$_POST['structure']) {
            $GLOBALS['main']->errorMessage($lang['maintain']['error_db_backup_conflict']);
        } else {
            $full = ($_POST['drop'] && $_POST['structure'] && $_POST['data']) ? '_full' : '';
            chdir(CC_BACKUP_DIR);
            $fileName 	= 'database'.$full.'_'.CC_VERSION.'_'.$glob['dbdatabase']."_".date("dMy-His").'.sql';
            if (file_exists($fileName)) { // Keep file pointer at the start
                unlink($fileName);
            }
            $all_tables = (isset($_POST['db_3rdparty']) && $_POST['db_3rdparty'] == '1') ? true : false;
            $write = $GLOBALS['db']->doSQLBackup($_POST['drop'], $_POST['structure'], $_POST['data'], $fileName, $_POST['compress'], $all_tables);
            if ($write) {
                $GLOBALS['main']->successMessage($lang['maintain']['db_backup_complete']);
            } else {
                $GLOBALS['main']->errorMessage($lang['maintain']['db_backup_failed']);
            }
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
if($GLOBALS['config']->get('config', 'elasticsearch')=='1') {
    $GLOBALS['main']->addTabControl($lang['maintain']['tab_elasticsearch'], 'elasticsearch');
    $es = new ElasticsearchHandler;
    $GLOBALS['smarty']->assign('ES_STATS', $es->getStats());
}
$GLOBALS['main']->addTabControl($lang['maintain']['tab_query_sql'], 'general', '?_g=maintenance&node=sql');

##########

## Database
if (isset($database_result) && $database_result) {
    $GLOBALS['smarty']->assign('TABLES_AFTER', $database_result);
} elseif (($tables = $GLOBALS['db']->getRows()) !== false) {
    $index_map = array(
        'cubecart_access_log' => array(
            'log_id' => 'PRIMARY',
            'time' => 'KEY',
            'type' => 'KEY'
        ),
        'cubecart_addressbook' => array(
            'address_id' => 'PRIMARY',
            'customer_id' => 'KEY',
            'billing' => 'KEY',
            'hash' => 'KEY',
            'default' => 'KEY'
        ),
        'cubecart_admin_log' => array(
            'log_id' => 'PRIMARY',
            'admin_id' => 'KEY',
            'time' => 'KEY'
        ),
        'cubecart_admin_error_log' => array(
            'log_id' => 'PRIMARY',
            'admin_id' => 'KEY'
        ),
        'cubecart_admin_users' => array(
            'admin_id' => 'PRIMARY'
        ),
        'cubecart_alt_shipping' => array(
            'id' => 'PRIMARY'
        ),
        'cubecart_alt_shipping_prices' => array(
            'id' => 'PRIMARY'
        ),
        'cubecart_blocker' => array(
            'block_id' => 'PRIMARY',
            'location' => 'KEY',
            'last_attempt' => 'KEY'
        ),
        'cubecart_category' => array(
            'cat_id' => 'PRIMARY',
            'cat_parent_id' => 'KEY'
        ),
        'cubecart_category_index' => array(
            'id' => 'PRIMARY',
            'cat_id' => 'KEY',
            'product_id' => 'KEY'
        ),
        'cubecart_category_language' => array(
            'translation_id' => 'PRIMARY',
            'cat_id' => 'KEY'
        ),
        'cubecart_code_snippet' => array(
            'snippet_id' => 'PRIMARY',
            'unique_id' => 'UNIQUE KEY',
            'hook_trigger' => 'KEY',
            'enabled' => 'KEY'
        ),
        'cubecart_config' => array(
            'name' => 'UNIQUE KEY'
        ),
        'cubecart_coupons' => array(
            'coupon_id' => 'PRIMARY',
            'code' => 'UNIQUE KEY'
        ),
        'cubecart_currency' => array(
            'currency_id' => 'PRIMARY',
            'code' => 'UNIQUE KEY'
        ),
        'cubecart_customer' => array(
            'customer_id' => 'PRIMARY',
            'email' => 'UNIQUE KEY',
            'first_name' => 'FULLTEXT',
            'last_name' => 'FULLTEXT',
            'email' => 'FULLTEXT'
        ),
        'cubecart_customer_group' => array(
            'group_id' => 'PRIMARY',
            'group_name' => 'KEY'
        ),
        'cubecart_customer_membership' => array(
            'membership_id' => 'PRIMARY',
            'group_id' => 'KEY',
            'customer_id' => 'KEY'
        ),
        'cubecart_documents' => array(
            'doc_id' => 'PRIMARY',
            'doc_parent_id' => 'KEY',
            'doc_status' => 'KEY',
            'doc_home' => 'KEY',
            'doc_privacy' => 'KEY'
        ),
        'cubecart_cookie_consent' => array(
            'id' => 'PRIMARY',
            'session_id' => 'KEY',
            'customer_id' => 'KEY',
            'ip_address' => 'KEY'
        ),
        'cubecart_downloads' => array(
            'digital_id' => 'PRIMARY'
        ),
        'cubecart_email_content' => array(
            'content_id' => 'PRIMARY',
            'content_type' => 'KEY',
            'language' => 'KEY'
        ),
        'cubecart_email_template' => array(
            'template_id' => 'PRIMARY'
        ),
        'cubecart_extension_info' => array(
            'file_id' => 'PRIMARY'
        ),
        'cubecart_filemanager' => array(
            'file_id' => 'PRIMARY',
            'filepath' => 'KEY',
            'filename' => 'KEY'
        ),
        'cubecart_geo_country' => array(
            'iso' => 'PRIMARY',
            'id' => 'KEY',
            'eu' => 'KEY'
        ),
        'cubecart_geo_zone' => array(
            'id' => 'PRIMARY',
            'status' => 'KEY'
        ),
        'cubecart_history' => array(
            'id' => 'PRIMARY'
        ),
        'cubecart_hooks' => array(
            'hook_id' => 'PRIMARY',
            'enabled' => 'KEY'
        ),
        'cubecart_image_index' => array(
            'id' => 'PRIMARY',
            'file_id' => 'KEY',
            'product_id' => 'KEY'
        ),
        'cubecart_inventory' => array(
            'product_id' => 'PRIMARY',
            'status' => 'KEY',
            'live_from' => 'KEY',
            'popularity' => 'KEY',
            'product_code' => 'FULLTEXT',
            'description' => 'FULLTEXT',
            'name' => 'FULLTEXT',
            'featured' => 'KEY'
        ),
        'cubecart_inventory_language' => array(
            'translation_id' => 'PRIMARY',
            'name' => 'FULLTEXT',
            'description' => 'FULLTEXT'
        ),
        'cubecart_lang_strings' => array(
            'string_id' => 'PRIMARY',
            'language' => 'KEY',
            'type' => 'KEY',
            'name' => 'KEY'
        ),
        'cubecart_logo' => array(
            'logo_id' => 'PRIMARY'
        ),
        'cubecart_manufacturers' => array(
            'id' => 'PRIMARY'
        ),
        'cubecart_modules' => array(
            'module_id' => 'PRIMARY',
            'folder' => 'KEY',
            'status' => 'KEY',
            'module' => 'KEY'
        ),
        'cubecart_newsletter' => array(
            'newsletter_id' => 'PRIMARY'
        ),
        'cubecart_newsletter_subscriber' => array(
            'subscriber_id' => 'PRIMARY',
            'customer_id' => 'KEY',
            'status' => 'KEY',
            'email' => 'KEY',
            'dbl_opt' => 'KEY'
        ),
        'cubecart_newsletter_subscriber_log' => array(
            'id' => 'PRIMARY',
            'email' => 'KEY'
        ),
        'cubecart_options_set' => array(
            'set_id' => 'PRIMARY'
        ),
        'cubecart_options_set_member' => array(
            'set_member_id' => 'PRIMARY',
            'set_id' => 'KEY'
        ),
        'cubecart_options_set_product' => array(
            'set_product_id' => 'PRIMARY',
            'set_id' => 'KEY',
            'product_id' => 'KEY'
        ),
        'cubecart_option_assign' => array(
            'assign_id' => 'PRIMARY',
            'set_member_id' => 'KEY',
            'product' => 'KEY',
            'set_enabled' => 'KEY'
        ),
        'cubecart_option_group' => array(
            'option_id' => 'PRIMARY',
            'option_name' => 'UNIQUE KEY'
        ),
        'cubecart_option_matrix' => array(
            'matrix_id' => 'PRIMARY',
            'product_id' => 'KEY',
            'options_identifier' => 'KEY',
            'status' => 'KEY',
            'timestamp' => 'KEY'
        ),
        'cubecart_option_value' => array(
            'value_id' => 'PRIMARY',
            'option_id' => 'KEY'
        ),
        'cubecart_order_history' => array(
            'history_id' => 'PRIMARY',
            'cart_order_id' => 'KEY'
        ),
        'cubecart_order_inventory' => array(
            'id' => 'PRIMARY',
            'product_id' => 'KEY',
            'cart_order_id' => 'KEY',
            'options_identifier' => 'KEY',
            'quantity' => 'KEY'
        ),
        'cubecart_order_notes' => array(
            'note_id' => 'PRIMARY',
            'admin_id' => 'KEY',
            'cart_order_id' => 'KEY',
            'time' => 'KEY',
            'content' => 'FULLTEXT'
        ),
        'cubecart_order_summary' => array(
            'id' => 'PRIMARY',
            'cart_order_id' => 'UNIQUE KEY',
            'customer_id' => 'KEY',
            'status' => 'KEY',
            'email' => 'KEY',
            'order_date' => 'KEY',
            'custom_oid' => 'UNIQUE KEY'
        ),
        'cubecart_order_tax' => array(
            'id' => 'PRIMARY',
            'cart_order_id' => 'KEY'
        ),
        'cubecart_permissions' => array(
            'permission_id' => 'PRIMARY',
            'admin_id' => 'KEY',
            'section_id' => 'KEY'
        ),
        'cubecart_pricing_group' => array(
            'price_id' => 'PRIMARY',
            'group_id' => 'KEY',
            'product_id' => 'KEY',
            'tax_type' => 'KEY'
        ),
        'cubecart_pricing_quantity' => array(
            'discount_id' => 'PRIMARY',
            'product_id' => 'KEY',
            'group_id' => 'KEY',
            'quantity' => 'KEY'
        ),
        'cubecart_reviews' => array(
            'id' => 'PRIMARY',
            'product_id' => 'KEY',
            'vote_up' => 'KEY',
            'vote_down' => 'KEY',
            'approved' => 'KEY',
            'name' => 'FULLTEXT',
            'email' => 'FULLTEXT',
            'title' => 'FULLTEXT',
            'review' => 'FULLTEXT'
        ),
        'cubecart_saved_cart' => array(
            'customer_id' => 'PRIMARY'
        ),
        'cubecart_search' => array(
            'id' => 'PRIMARY'
        ),
        'cubecart_sessions' => array(
            'session_id' => 'PRIMARY',
            'customer_id' => 'KEY',
            'session_last' => 'KEY',
            'acp' => 'KEY'
        ),
        'cubecart_shipping_rates' => array(
            'id' => 'PRIMARY',
            'zone_id' => 'KEY',
            'method_name' => 'KEY',
            'min_weight' => 'KEY',
            'max_weight' => 'KEY',
            'min_value' => 'KEY'
        ),
        'cubecart_shipping_zones' => array(
            'id' => 'PRIMARY',
            'zone_name' => 'KEY'
        ),
        'cubecart_system_error_log' => array(
            'log_id' => 'PRIMARY',
            'time' => 'KEY',
            'read' => 'KEY'
        ),
        'cubecart_tax_class' => array(
            'id' => 'PRIMARY'
        ),
        'cubecart_tax_details' => array(
            'id' => 'PRIMARY',
            'name' => 'UNIQUE KEY'
        ),
        'cubecart_tax_rates' => array(
            'id' => 'PRIMARY',
            'type_id' => 'UNIQUE KEY',
            'details_id' => 'UNIQUE KEY',
            'country_id' => 'UNIQUE KEY',
            'county_id' => 'UNIQUE KEY',
            'active' => 'KEY'
        ),
        'cubecart_transactions' => array(
            'id' => 'PRIMARY',
            'order_id' => 'KEY',
            'customer_id' => 'KEY',
            'time' => 'KEY',
        ),
        'cubecart_request_log' => array(
            'request_id' => 'PRIMARY'
        ),
        'cubecart_seo_urls' => array(
            'path' => 'PRIMARY',
            'id' => 'KEY',
            'type' => 'KEY',
            'item_id' => 'KEY',
            'custom' => 'KEY',
            'redirect' => 'KEY'
        ),
        'cubecart_email_log' => array(
            'id' => 'PRIMARY',
            'to' => 'KEY'
        ),
        'cubecart_invoice_template' => array(
            'id' => 'PRIMARY',
            'hash' => 'KEY'
        )
    );

    $actual_map = array();

    foreach ($tables as $table) {
        if (!preg_match('/^'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_/i', $table['Name'])) {
            continue;
        }

        // Get index and map them
        $indexes = $GLOBALS['db']->misc("SHOW INDEX FROM `".$table['Name']."`");
        $index_errors = array();

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
            if (isset($actual_map[$index['Table']][$index['Column_name']]) && $actual_map[$index['Table']][$index['Column_name']]==$key_type) {
                $duplicate = sprintf($lang['maintain']['duplicate_index'], $table_name.'.'.$index['Column_name'], $key_type);
            }
            $actual_map[$index['Table']][$index['Column_name']] = $key_type;
        }
        
        if (isset($index_map[strtolower($index['Table'])])) {
            foreach ($index_map[strtolower($index['Table'])] as $column => $key) {
                $table_name = $GLOBALS['config']->get('config', 'dbprefix').str_replace('cubecart', 'CubeCart', $index['Table']);
                if (!isset($actual_map[$index['Table']][$column])) {
                    $index_errors[] = sprintf($lang['maintain']['missing_index'], $table_name.'.'.$column, $key);
                } elseif (isset($actual_map[$index['Table']][$column]) && $actual_map[$index['Table']][$column]!==$key) {
                    $index_errors[] = sprintf($lang['maintain']['wrong_index'], $table_name.'.'.$column, $actual_map[$index['Table']][$column], $key);
                }
            }
        }

        if ($duplicate !== false) {
            $index_errors[] = $duplicate;
        }

        $table['Data_free'] = ($table['Data_free'] > 0) ? formatBytes($table['Data_free'], true) : '-';
        $table_size   = $table['Data_length']+$table['Index_length'];
        $data_length  = formatBytes($table_size);
        $table['Data_length'] = ($table_size>0) ? $data_length['size'].' '.$data_length['suffix'] : '-';
        $table['Name_Display'] = $GLOBALS['config']->get('config', 'dbdatabase').'.'.$table['Name'];
        $table['errors'] = count($index_errors)>0 ? implode('<br>', $index_errors) : false;
        $smarty_data['tables'][] = $table;
    }
    $GLOBALS['smarty']->assign('TABLES', $smarty_data['tables']);
}


## Existing Backups
$files = glob('{backup/*.sql,backup/*.zip}', GLOB_BRACE);

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
