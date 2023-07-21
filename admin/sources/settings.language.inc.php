<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('settings', CC_PERM_READ, true);


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
    $base_strings = $GLOBALS['language']->loadLanguageXML('lang.core', $_GET['language'], CC_LANGUAGE_DIR, true, false);

    # Save strings to Database
    $clear = false;
    if (isset($_POST['delete']) && is_array($_POST['delete'])) {
        foreach ($_POST['delete'] as $name => $value) {
            $record = array(
                'language' => $_GET['language'],
                'type'  => $_GET['type'],
                'name'  => $name,
            );
            $GLOBALS['db']->delete('CubeCart_lang_strings', $record);
        }
    }
    
    if (isset($_POST['string']) && is_array($_POST['string'])) {
        foreach ($GLOBALS['RAW']['POST']['string'] as $type => $data) {
            foreach ($data as $name => $value) {
                $record = array(
                    'language' => $_GET['language'],
                    'type'  => $type,
                    'name'  => $name
                );
                $basic = htmlspecialchars($base_strings[$type][$name], ENT_COMPAT, 'UTF-8', false);
                $existing = $GLOBALS['db']->select('CubeCart_lang_strings',false, $record);
                
                if ($existing && $basic == $value) {
                    $GLOBALS['db']->delete('CubeCart_lang_strings', $record);
                    $clear = true;
                } elseif($existing && $basic !== $value) {
                    $value = htmlspecialchars_decode($value, ENT_COMPAT);
                    $GLOBALS['db']->update('CubeCart_lang_strings', array('value' => $value), $record);
                    $clear = true;
                } else if(!$existing && $basic !== $value) {
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
    if (isset($_REQUEST['type']) && $_REQUEST['type'][0] == '.') {
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
    $GLOBALS['smarty']->assign('SHOW_SEARCH', true);
    if (isset($_REQUEST['type']) && !empty($_REQUEST['type'])) { // The group name or module has been chosen. Load and retrieve the appropriate definitions, strings, and customizations for this language.
        $GLOBALS['smarty']->assign('SHOW_SEARCH', false);
        if (file_exists($_REQUEST['type']) && stripos($_REQUEST['type'], "modules")!==false) {
            $breadcrumb  = $GLOBALS['language']->getFriendlyModulePath($_REQUEST['type']);
            $basename   = basename($_REQUEST['type']);
            $module_name  = $GLOBALS['language']->getFriendlyModulePath($_REQUEST['type'], true);
            $GLOBALS['language']->loadDefinitions($module_name, str_replace($basename, '', $_REQUEST['type']), $basename, false, false);

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

        $GLOBALS['gui']->addBreadcrumb($breadcrumb, currentPage(array('key')));

        $GLOBALS['smarty']->assign('STRING_TYPE', ucfirst($breadcrumb));
        ## Load all strings for this section
        if(isset($_GET['key'])) {
            $GLOBALS['smarty']->assign('SECTIONS', false);
            $GLOBALS['smarty']->assign('BACK', currentPage(array('key','type')));
        }
        if (!empty($definitions)) {
            foreach ($definitions as $name => $data) {
                if(isset($_GET['key']) && $_GET['key']!==$name) continue;
                $default = (isset($strings[$name])) ? $strings[$name] : $data['value'];
                $defined = (isset($strings[$name]) || isset($custom[$name])) ? true : false;
                $value = (isset($custom[$name])) ? $custom[$name] : $default;
                $countPlaceholders = countPlaceholders($value);
                $assign = array(
                    'name'  => $name,
                    'type'  => $type,
                    'default' => htmlspecialchars($default, ENT_COMPAT, 'UTF-8', false),
                    'value'  => htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false),
                    'defined' => (int)$defined,
                    'multiline' => detectEol($value),
                    'placeholders' => (!empty($countPlaceholders) ? "There must be $countPlaceholders placeholder".(($countPlaceholders > 1) ? "s. Unless using n\$ position specifiers, their existing order in the string must stay that way." : ".") : null),
                    'disabled' => ($default!==$value) ? false : true
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
                    'multiline' => detectEol($value)
                );
                $smarty_data['strings'][] = $assign;
            }
        }
        $GLOBALS['smarty']->assign('STRINGS', $smarty_data['strings']);
    } elseif (isset($_POST['lang_groups_search_phrase']) && !empty($_POST['lang_groups_search_phrase'])) { // We have a language to search through.
        $language_strings_to_search = $GLOBALS['language']->getLanguageStrings();
        $search_hits = array();
        unset($language_strings_to_search['_language_strings_def']); // Do not want this group - it has array of arrays instead of array of strings.
        foreach ($language_strings_to_search as $keySearchGroup => $arrSearchPhrases) {
            $search_hits[$keySearchGroup] = array_filter($arrSearchPhrases, function($v) { return stripos($v, $GLOBALS['RAW']['POST']['lang_groups_search_phrase']) !== false; }); // Filter for simple matches.
            if (empty($search_hits[$keySearchGroup])) unset($search_hits[$keySearchGroup]); // No matches? Do not keep this array element.
        }
        if($db_strings = $GLOBALS['db']->select('CubeCart_lang_strings', false, array('language' => $_GET['language'], 'value' => '~'.$GLOBALS['RAW']['POST']['lang_groups_search_phrase']))) {
            foreach($db_strings as $s) {
                $search_hits[$s['type']][$s['name']] = $s['value'];
            }
            
        }
        if(!empty($search_hits)) {
            $mark = $phrase_group_name = $phrase_group_title = array();
            foreach($search_hits as $g => $a) {
                foreach($a as $k => $v) {
                    $mark[$g][$k] = preg_replace("/(".$GLOBALS['RAW']['POST']['lang_groups_search_phrase'].")/i", "<mark>$1</mark>", $v);
                    $group_name_split = explode("-",$lang['translate']['phrase_group_'.$g]);
                    $phrase_group_name[$g] = trim($group_name_split[0]);
                    $phrase_group_title[$g] = trim($group_name_split[1]);
                }
            }
            $search_hits = $mark;
            unset($mark);
        }
        $GLOBALS['smarty']->assign("SEARCH_PHRASE_GROUPS", $phrase_group_name);
        $GLOBALS['smarty']->assign("SEARCH_PHRASE_TITLES", $phrase_group_title);
        $GLOBALS['smarty']->assign("SEARCH_PHRASE", $GLOBALS['RAW']['POST']['lang_groups_search_phrase']);
        $GLOBALS['smarty']->assign("SEARCH_LANG", $_GET['language']);
        $GLOBALS['smarty']->assign("SEARCH_HITS", isset($search_hits) ? $search_hits : array());
    }
    $plural = isset($_GET['key']) ? '' : 's';
    $GLOBALS['main']->addTabControl($lang['translate']['tab_edit_phrase'.$plural], 'general');
    $GLOBALS['smarty']->assign("EDIT_TITLE", $lang['translate']['tab_edit_phrase'.$plural]);
    if (!preg_match('/^(modules)/', $_REQUEST['type'] ?? '')) {
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
        if(is_array($_POST['domain'])) {
            foreach($_POST['domain'] as $language => $domain) {
                if(empty($domain)) {
                    $GLOBALS['db']->delete('CubeCart_domains', array('language' => $language));
                    continue;
                }
                if($GLOBALS['db']->select('CubeCart_domains', false, array('language' => $language))) {
                    $GLOBALS['db']->update('CubeCart_domains', array('domain' => $domain), array('language' => $language));
                } else {
                    $GLOBALS['db']->insert('CubeCart_domains', array('domain' => $domain, 'language' => $language));
                }
            }
        }

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
    $url_parts = parse_url(CC_STORE_URL);
    $domain = ltrim($url_parts['host'],'www.');
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
            $subdomain = ($GLOBALS['config']->get('config', 'default_language') == $info['code']) ? 'www' : substr($info['code'],0,2);
            $info['placeholder'] = $subdomain.'.'.$domain;
            $smarty_data['languages'][] = $info;
        }
        $GLOBALS['main']->addTabControl($lang['translate']['title_language_create'], 'lang_create');
        $GLOBALS['main']->addTabControl($lang['translate']['title_language_import'], 'lang_import');
        $GLOBALS['smarty']->assign('LANGUAGES', $smarty_data['languages']);
    }
    $GLOBALS['smarty']->assign('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
}

$page_content = $GLOBALS['smarty']->fetch('templates/settings.language.php');
