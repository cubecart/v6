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
Admin::getInstance()->permissions('filemanager', CC_PERM_READ, true);

global $lang;

if(isset($_GET['download_file']) && !empty($_GET['download_file'])) {
    $file = CC_ROOT_DIR.'/'.base64_decode($_GET['download_file']);
    if(file_exists($file)) { // It really should exist
        deliverFile($file);
    }
}

if (isset($_POST['cancel'])) {
    httpredir(currentPage(array('fm-edit')));
}
if (isset($_POST['filter-update']) && isset($_POST['filter']['subdir'])) {
    httpredir(currentPage(null, array('subdir' => urlencode($_POST['filter']['subdir']))));
}

$select_button = false;
if (isset($_GET['mode'])) {
    switch (strtolower($_GET['mode'])) {
    case 'digital':
        $mode = FileManager::FM_FILETYPE_DL;
        break;
    case 'fck':
        $GLOBALS['main']->hideNavigation(true);
        $select_button = true;
        // no break
    default:
        $mode = FileManager::FM_FILETYPE_IMG;
    }
} else {
    $mode = FileManager::FM_FILETYPE_IMG;
}


$subdir = (isset($_GET['subdir'])) ? urldecode($_GET['subdir']) : '';

$fm  = new FileManager($mode, $subdir);

if (isset($_GET['rebuild']) && Admin::getInstance()->permissions('filemanager', CC_PERM_EDIT)) {
    if ($fm->buildDatabase()) {
        $GLOBALS['main']->successMessage($lang['filemanager']['notify_list_update']);
    } else {
        $GLOBALS['main']->errorMessage($lang['filemanager']['error_list_update']);
    }
    httpredir(currentPage(array('rebuild')));
}

if (!empty($_FILES) && Admin::getInstance()->permissions('filemanager', CC_PERM_EDIT)) {
    if ($fm->upload()) {
        if (count($_FILES)>1) {
            $GLOBALS['main']->successMessage($lang['filemanager']['notify_files_upload']);
        } elseif ($_FILES['file0']['size']>0) {
            $GLOBALS['main']->successMessage($lang['filemanager']['notify_file_upload']);
        }
    } else {
        $GLOBALS['main']->errorMessage($lang['filemanager']['error_file_upload']);
    }
    httpredir(currentPage());
}

if (isset($_GET['delete']) && Admin::getInstance()->permissions('filemanager', CC_PERM_DELETE)) {
    if ($fm->delete($_GET['delete'])) {
        $GLOBALS['main']->successMessage($lang['filemanager']['notify_file_delete']);
    } else {
        $GLOBALS['main']->errorMessage($lang['filemanager']['error_file_delete']);
    }
    httpredir(currentPage(array('delete')));
}

$post_max_size = ini_get('post_max_size');
$upload_max_filesize = ini_get('upload_max_filesize');

if ($post_max_size !== $upload_max_filesize) {
    $GLOBALS['smarty']->assign('UPLOAD_LIMIT_DESC', sprintf($lang['filemanager']['max_upload_diff'], $post_max_size, $upload_max_filesize));
} else {
    $GLOBALS['smarty']->assign('UPLOAD_LIMIT_DESC', sprintf($lang['filemanager']['max_upload_same'], $upload_max_filesize));
}

if (isset($_GET['fm-edit']) && is_numeric($_GET['fm-edit'])) {
    $page_content = $fm->editor($_GET['fm-edit']);
} else {
    $GLOBALS['main']->addTabControl($lang['filemanager']['tab_files'], false, currentPage(array('subdir')));
    $GLOBALS['main']->addTabControl($lang['filemanager']['file_upload'], 'upload');
    $GLOBALS['main']->addTabControl($lang['filemanager']['folder_create'], 'folder');
    $GLOBALS['main']->addTabControl($lang['filemanager']['tab_rebuild'], false, currentPage(null, array('rebuild' => 'true')));
    $page_content = $fm->admin($select_button);
}
