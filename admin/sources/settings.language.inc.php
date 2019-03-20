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
Admin::getInstance()->permissions('settings', CC_PERM_READ, true);

global $lang;

if (isset($_GET['delete']) && Admin::getInstance()->permissions('settings', CC_PERM_DELETE)) {
    ## Purge database
    if($_GET['delete']==$GLOBALS['config']->get('config', 'default_language')) {
        $GLOBALS['main']->errorMessage(sprintf($lang['translate']['error_lang_status_fixed'],$_GET['delete']));
    } else if ($GLOBALS['language']->deleteLanguage($_GET['delete'])) {
        $GLOBALS['main']->successMessage($lang['translate']['notify_language_delete']);
    } else {
        $GLOBALS['main']->errorMessage($lang['translate']['error_language_delete']);
    }
    httpredir(currentPage(array('delete')));
}

if (isset($_GET['download']) && Admin::getInstance()->permissions('settings', CC_PERM_READ)) {
    deliverFile(CC_ROOT_DIR.'/language/'.$_GET['download'].'.xml');
    exit;
}

if (isset($_POST['save']) && (isset($_POST['string']) || isset($_POST['delete'])) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
    ## Load all existing language strings
    $GLOBALS['language']->loadDefinitions($_GET['language']);
    $base_strings = $GLOBALS['language']->loadLanguageXML($_GET['language']);

    # Save strings to Database
    $clear = false;
    if (is_array($_POST['delete'])) {
        foreach ($_POST['delete'] as $name => $value) {
            $record = array(
                'language' => $_GET['language'],
                'type'  => $_GET['type'],
                'name'  => $name,
            );
            $GLOBALS['db']->delete('CubeCart_lang_strings', $record);
        }
    }
    
    if (is_array($_POST['string'])) {
        foreach ($GLOBALS['RAW']['POST']['string'] as $type => $data) {
            foreach ($data as $name => $value) {
                $record = array(
                    'language' => $_GET['language'],
                    'type'  => $type,
                    'name'  => $name,
                );
                $basic = htmlspecialchars($base_strings[$type][$name], ENT_COMPAT, 'UTF-8', false);
                if ($basic != $value) {
                    $GLOBALS['db']->delete('CubeCart_lang_strings', $record);
                    $record['value'] = htmlspecialchars_decode($value, ENT_COMPAT);
                    $GLOBALS['db']->insert('CubeCart_lang_strings', $record);
                    $clear = true;
                }
            }
        }
    }
    if ($clear) {
        $GLOBALS['cache']->clear('lang');
    }
    $GLOBALS['main']->successMessage($lang['translate']['notify_strings_update']);
    httpredir(currentPage());
}

if (isset($_POST['export']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
    $replace = (isset($_POST['export_opt']['replace'])) ? (bool)$_POST['export_opt']['replace'] : false;
    if ($GLOBALS['language']->saveLanguageXML($_GET['export'], false, $replace)) {
        ## Success!
        $GLOBALS['main']->successMessage(sprintf($lang['email']['notify_export_language'], $GLOBALS['language']->exported_lang_file));
    } else {
        ## Fail :(
        $GLOBALS['main']->errorMessage($lang['email']['error_export']);
    }
    httpredir(currentPage(array('export'), array('language' => $_GET['export'])));
}

if (isset($_POST['type'])) {
    httpredir(currentPage(null, array('type' => $_POST['type'])));
}

$GLOBALS['gui']->addBreadcrumb($lang['translate']['title_languages']);

if (isset($_GET['export'])) {
    ## display the export options

    $GLOBALS['language']->loadLanguageXML($_GET['export']);
    $lang_info = $GLOBALS['language']->getLanguageInfo($_GET['export']);

    $GLOBALS['gui']->addBreadcrumb($lang_info['title'], currentPage(array('export'), array('language' => $_GET['export'])));
    $GLOBALS['gui']->addBreadcrumb($lang['translate']['merge_db_file'], currentPage());

    $GLOBALS['main']->addTabControl($lang['translate']['merge_db_file'], 'merge');
    if (function_exists('gzencode')) {
        $GLOBALS['smarty']->assign('COMPRESSION', true);
    }
    $GLOBALS['smarty']->assign('REPLACE_OPTION', $_GET['export']);
    $GLOBALS['smarty']->assign('DISPLAY_EXPORT', true);
} elseif (isset($_GET['language'])) {

    //Security against ../ or ./
    if (isset($_REQUEST['type']) && $_REQUEST['type']{0} == '.') {
        die();
    }

    $GLOBALS['smarty']->assign('DISPLAY_EDITOR', true);

    $GLOBALS['language']->loadDefinitions($_GET['language']);

    $GLOBALS['language']->loadLanguageXML($_GET['language'], $_GET['language'], CC_LANGUAGE_DIR, true, false);
    $lang_info = $GLOBALS['language']->getLanguageInfo($_GET['language']);
    $GLOBALS['gui']->addBreadcrumb($lang_info['title'], currentPage(array('type'), array('language' => $_GET['language'])));

    if (($groups = $GLOBALS['language']->getGroups()) !== false) {
        foreach ($groups as $group => $data) {
            $smarty_data['sections'][] = array(
                'name'   => $group,
                'description' => $lang['translate']['phrase_group_'.$group],
                'selected'  => (isset($_REQUEST['type']) && $group == $_REQUEST['type']) ? 'selected="selected"' : '',
            );
        }
        $GLOBALS['smarty']->assign('SECTIONS', $smarty_data['sections']);
        ## Assign module paths eeep!
        foreach (glob('modules/*/*/language/module.definitions.xml') as $path) {
            $GLOBALS['language']->cloneModuleLanguage($path, $_GET['language']);
            $modules[] = array(
                'path' => $path,
                'name' => str_replace('_', ' ', $GLOBALS['language']->getFriendlyModulePath($path)),
                'selected' => (isset($_REQUEST['type']) && $path == $_REQUEST['type']) ? 'selected="selected"' : '',
            );
        }
        $GLOBALS['smarty']->assign('MODULES', $modules);
    }

    if (isset($_REQUEST['type'])) {
        if (file_exists($_REQUEST['type']) && stripos($_REQUEST['type'], "modules")!==false) {
            $breadcrumb  = $GLOBALS['language']->getFriendlyModulePath($_REQUEST['type']);
            $basename   = basename($_REQUEST['type']);
            $module_name  = $GLOBALS['language']->getFriendlyModulePath($_REQUEST['type'], true);
            $GLOBALS['language']->loadDefinitions($module_name, str_replace($basename, '', $_REQUEST['type']), $basename);

            $definitions = $GLOBALS['language']->getDefinitions($module_name);
            $type  = $module_name;
            $strings = $GLOBALS['language']->getStrings($module_name);
            $custom  = $GLOBALS['language']->getCustom($module_name, $_GET['language']);
        } else {
            $breadcrumb = $_REQUEST['type'];
            $definitions = $GLOBALS['language']->getDefinitions($_REQUEST['type']);
            $type  = $_REQUEST['type'];
            $strings = $GLOBALS['language']->getStrings($type);
            $custom  = $GLOBALS['language']->getCustom($type, $_GET['language']);
        }

        $GLOBALS['gui']->addBreadcrumb($breadcrumb, currentPage());

        $GLOBALS['smarty']->assign('STRING_TYPE', ucfirst($breadcrumb));
        ## Load all strings for this section
        if (($definitions = $GLOBALS['language']->getDefinitions($_REQUEST['type'])) !== false) {
            if (!empty($definitions)) {
                foreach ($definitions as $name => $data) {
                    $default = (isset($strings[$name])) ? $strings[$name] : $data['value'];
                    $defined = (isset($strings[$name]) || isset($custom[$name])) ? true : false;
                    $value = (isset($custom[$name])) ? $custom[$name] : $default;
                    $assign = array(
                        'name'  => $name,
                        'type'  => $type,
                        'default' => htmlspecialchars($default, ENT_COMPAT, 'UTF-8', false),
                        'value'  => htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false),
                        'defined' => (int)$defined,
                        'multiline' => strstr($value, PHP_EOL) ? true : false,
                        'disabled' => ($default!==$value) ? false : true,
                    );
                    $smarty_data['strings'][] = $assign;
                }
            } else {
                // add-on language files
                foreach ($strings as $name => $data) {
                    $default = (isset($strings[$name])) ? $strings[$name] : $data['value'];
                    $defined = (isset($strings[$name]) || isset($custom[$name])) ? true : false;
                    $value = (isset($custom[$name])) ? $custom[$name] : $default;
                    $assign = array(
                        'name'  	=> $name,
                        'type'  	=> $type,
                        'default' 	=> htmlspecialchars($default, ENT_COMPAT, 'UTF-8', false),
                        'value'  	=> htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false),
                        'defined' 	=> (int)$defined,
                        'multiline' => strstr($value, PHP_EOL) ? true : false
                    );
                    $smarty_data['strings'][] = $assign;
                }
            }
            if (!empty($custom)) {
                ## For custom strings that aren't listed in the definitions file
                foreach ($custom as $name => $value) {
                    continue;
                }
            }
            $GLOBALS['smarty']->assign('STRINGS', $smarty_data['strings']);
        }
    }
    $GLOBALS['main']->addTabControl($lang['translate']['tab_string_edit'], 'general');
    if (!preg_match('/^(modules)/', $_GET['type'])) {
        $GLOBALS['main']->addTabControl($lang['translate']['merge_db_file'], false, currentPage(array('language'), array('export' => $_GET['language'])));
    }
} else {
    if (!empty($_FILES['import']['tmp_name']['file'])) {
        if ($GLOBALS['language']->importLanguage($_FILES['import'], $_POST['import']['overwrite'])) {
            $GLOBALS['main']->successMessage($lang['translate']['notify_language_import_success']);
        } else {
            $GLOBALS['main']->errorMessage($lang['translate']['error_language_import_failed']);
        }
    } elseif (isset($_POST['create']) && !empty($_POST['create']['code'])) {
        if ($GLOBALS['language']->create($_POST['create'])) {
            $GLOBALS['main']->successMessage($lang['translate']['notify_language_create']);
            ## Set status to disabled to begin with
            $GLOBALS['config']->set('languages', $_POST['create']['code'], "0");
            httpredir(currentPage(null, array('language' => $_POST['create']['code'])));
        } else {
            $GLOBALS['main']->errorMessage($lang['translate']['error_language_create']);
        }
    } elseif (isset($_POST['status']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
        // We don't allow the default language to be disabled
        $default_lang = $GLOBALS['config']->get('config', 'default_language');
        $before = $GLOBALS['config']->set('languages', false, $_POST['status']);
        if($_POST['status'][$default_lang]=='0') {
            $_POST['status'][$default_lang] = '1';
            $GLOBALS['main']->errorMessage(sprintf($lang['translate']['error_lang_status_fixed'],$default_lang));
        }
        $GLOBALS['config']->set('languages', false, $_POST['status']);
        $after = $GLOBALS['config']->get('config', 'default_language');
        if (md5($before) !== md5($after)) {
            $GLOBALS['main']->successMessage($lang['translate']['notify_language_status']);
        } else {
            $GLOBALS['main']->errorMessage($lang['translate']['error_language_status']);
        }
        httpredir(currentPage());
    }
    $enabled = $GLOBALS['config']->get('languages');

    $GLOBALS['main']->addTabControl($lang['translate']['title_languages'], 'lang_list');
    
    ## List available language files
    if (($languageList = $GLOBALS['language']->listLanguages()) !== false) {
        foreach ($languageList as $code => $info) {
            $info['status'] = (isset($enabled[$code])) ? (int)$enabled[$code] : 1;
            if (file_exists('language/flags/'.$info['code'].'.png')) {
                $info['flag'] = 'language/flags/'.$info['code'].'.png';
            } else {
                $info['flag'] = 'language/flags/unknown.png';
            }
            $info['edit'] = currentPage(null, array('language' => $info['code']));
            $info['delete'] = currentPage(null, array('delete' => $info['code'], 'token' => SESSION_TOKEN));
            $info['download'] = currentPage(null, array('download' => $info['code']));
            $smarty_data['languages'][] = $info;
        }
        $GLOBALS['main']->addTabControl($lang['translate']['title_language_create'], 'lang_create');
        $GLOBALS['main']->addTabControl($lang['translate']['title_language_import'], 'lang_import');
        $GLOBALS['smarty']->assign('LANGUAGES', $smarty_data['languages']);
    }
}

$page_content = $GLOBALS['smarty']->fetch('templates/settings.language.php');
