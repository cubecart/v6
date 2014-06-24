<?php
if (!defined('CC_INI_SET')) die('Access Denied');
Admin::getInstance()->permissions('filemanager', CC_PERM_READ, true);

global $lang;

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
		$GLOBALS['main']->setACPNotify($lang['filemanager']['notify_list_update']);
	} else {
		# $GLOBALS['main']->setACPWarning($lang['filemanager']['error_list_update']);
	}
	httpredir(currentPage(array('rebuild')));
}

if (Admin::getInstance()->permissions('filemanager', CC_PERM_EDIT) && !empty($_FILES)) {
	if ($fm->upload()) {
		$GLOBALS['main']->setACPNotify($lang['filemanager']['notify_file_upload']);
	} else {
		$GLOBALS['main']->setACPWarning($lang['filemanager']['error_file_upload']);
	}
	httpredir(currentPage());
}

if (Admin::getInstance()->permissions('filemanager', CC_PERM_DELETE) && isset($_GET['delete'])) {
	if ($fm->delete($_GET['delete'])) {
		$GLOBALS['main']->setACPNotify($lang['filemanager']['notify_file_delete']);
	} else {
		$GLOBALS['main']->setACPWarning($lang['filemanager']['error_file_delete']);
	}
	httpredir(currentPage(array('delete')));
}

if (isset($_GET['fm-edit']) && is_numeric($_GET['fm-edit'])) {
	$page_content = $fm->editor($_GET['fm-edit']);
} else {
	$GLOBALS['main']->addTabControl($lang['filemanager']['tab_files'], 'filemanager');
	$GLOBALS['main']->addTabControl($lang['filemanager']['file_upload'], 'upload');
	$GLOBALS['main']->addTabControl($lang['filemanager']['folder_create'], 'folder');
	$GLOBALS['main']->addTabControl($lang['filemanager']['tab_rebuild'], false, currentPage(null, array('rebuild' => 'true')));
	$page_content = $fm->admin($select_button);
}
