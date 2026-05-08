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
                $basic = htmlspecialchars(isset($base_strings[$type][$name]) ? (string)$base_strings[$type][$name] : '', ENT_COMPAT, 'UTF-8', false);
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

if (isset($_GET['install']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT) && isset($_GET['token']) && $_GET['token'] == SESSION_TOKEN) {
    $install_code = $_GET['install'];
    $installed = false;

    // Validate language code format
    if (preg_match('#^[a-z]{2}\-[A-Z]{2}$#', $install_code)) {
        // Fetch API to get download URL
        $api_url_path = '/api/languages';
        $request = new Request('extensions.cubecart.com', $api_url_path, 443, false, true, 10);
        $request->setMethod('get');
        $request->setSSL();
        $request->skiplog(true);
        if (!$json = $request->send()) {
            $json = file_get_contents('https://extensions.cubecart.com'.$api_url_path);
        }

        $download_url = false;
        if ($json) {
            $api_data = json_decode($json, true);
            if ($api_data && !empty($api_data['languages'])) {
                foreach ($api_data['languages'] as $api_lang) {
                    if ($api_lang['code'] === $install_code) {
                        $download_url = $api_lang['download'];
                        break;
                    }
                }
            }
        }

        if ($download_url) {
            // Download the ZIP file
            $url_parts = parse_url($download_url);
            $dl_request = new Request($url_parts['host'], $url_parts['path'], 443, false, true, 30);
            $dl_request->setMethod('get');
            $dl_request->setSSL();
            $dl_request->skiplog(true);
            $zip_data = $dl_request->send();

            if (!$zip_data) {
                $zip_data = file_get_contents($download_url);
            }

            if ($zip_data) {
                $tmp_path = CC_BACKUP_DIR.$install_code.'.zip';
                file_put_contents($tmp_path, $zip_data);

                $zip = new ZipArchive();
                if ($zip->open($tmp_path) === true) {
                    $extract_ok = true;

                    // Check all files are writable if they exist
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $entry = $zip->getNameIndex($i);
                        // Determine destination based on file type
                        if (preg_match('/\.png$/i', $entry)) {
                            $dest = CC_LANGUAGE_DIR.'flags/'.basename($entry);
                        } else {
                            $dest = CC_LANGUAGE_DIR.basename($entry);
                        }
                        if (file_exists($dest) && !is_writable($dest)) {
                            $GLOBALS['main']->errorMessage(sprintf($lang['translate']['error_language_install_write'], $dest));
                            $extract_ok = false;
                        }
                    }

                    if ($extract_ok) {
                        // Extract files to appropriate locations
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $entry = $zip->getNameIndex($i);
                            $file_data = $zip->getFromIndex($i);
                            if ($file_data === false) continue;

                            if (preg_match('/\.png$/i', $entry)) {
                                file_put_contents(CC_LANGUAGE_DIR.'flags/'.basename($entry), $file_data);
                            } else {
                                file_put_contents(CC_LANGUAGE_DIR.basename($entry), $file_data);
                            }
                        }

                        // Import email content if available
                        $email_file = 'email_'.$install_code.'.xml';
                        if (file_exists(CC_LANGUAGE_DIR.$email_file)) {
                            $GLOBALS['language']->importEmail($email_file);
                        }

                        // Preserve existing enabled/disabled status on upgrade; only
                        // default to disabled when the language wasn't already installed.
                        $existing_languages_cfg = $GLOBALS['config']->get('languages');
                        if (!is_array($existing_languages_cfg) || !isset($existing_languages_cfg[$install_code])) {
                            $GLOBALS['config']->set('languages', $install_code, '0');
                        }
                        $GLOBALS['cache']->clear('lang');
                        $installed = true;
                    }
                    $zip->close();
                }
                // Clean up temp file
                if (file_exists($tmp_path)) {
                    unlink($tmp_path);
                }
            }
        }
    }

    if ($installed) {
        $GLOBALS['main']->successMessage(sprintf($lang['translate']['notify_language_installed'], $install_code));
    } else {
        $GLOBALS['main']->errorMessage($lang['translate']['error_language_install']);
    }
    httpredir(currentPage(array('install', 'token')));
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
    if (!empty($_REQUEST['type']) && $_REQUEST['type'][0] == '.') {
        die();
    }

    $GLOBALS['smarty']->assign('DISPLAY_EDITOR', true);

    $GLOBALS['language']->loadDefinitions($_GET['language']);

    $foreign_strings = $GLOBALS['language']->loadLanguageXML($_GET['language'], $_GET['language'], CC_LANGUAGE_DIR, false, false);
    if (!is_array($foreign_strings)) $foreign_strings = array();
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
        $modules = array();
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
            $GLOBALS['language']->loadDefinitions($module_name, str_replace($basename, '', $_REQUEST['type']), $basename, false, true);

            $definitions = $GLOBALS['language']->getDefinitions($module_name);
            $type  = $module_name;
            $strings = $GLOBALS['language']->getStrings($module_name);
            $custom  = $GLOBALS['language']->getCustom($module_name, $_GET['language']);
        } else {
            $breadcrumb = $type = $_REQUEST['type'];
            $definitions = $GLOBALS['language']->getDefinitions($type);
            $strings = isset($foreign_strings[$type]) ? $foreign_strings[$type] : array();
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
                    'placeholders' => (!empty($countPlaceholders) ? "There must be $countPlaceholders placeholder".(($countPlaceholders > 1) ? "s. Unless using n\$ position specifiers, their existing order in the string must stay that way." : ".") : null)
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
        $language_strings_to_search = !empty($foreign_strings) ? $foreign_strings : $GLOBALS['language']->getLanguageStrings();
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
    if (isset($_POST['default_language']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
        if(is_array($_POST['domain'])) {
            foreach($_POST['domain'] as $lang_code => $domain) {
                if(empty($domain)) {
                    $GLOBALS['db']->delete('CubeCart_domains', array('language' => $lang_code));
                    continue;
                }
                if($GLOBALS['db']->select('CubeCart_domains', false, array('language' => $lang_code))) {
                    $GLOBALS['db']->update('CubeCart_domains', array('domain' => $domain), array('language' => $lang_code));
                } else {
                    $GLOBALS['db']->insert('CubeCart_domains', array('domain' => $domain, 'language' => $lang_code));
                }
            }
        }

        // Build full status array: checkboxes only submit checked values
        $checked = isset($_POST['status']) && is_array($_POST['status']) ? $_POST['status'] : array();
        $all_languages = $GLOBALS['language']->listLanguages();
        $status_array = array();
        if ($all_languages) {
            foreach ($all_languages as $code => $info) {
                $status_array[$code] = isset($checked[$code]) ? '1' : '0';
            }
        }

        // Handle default language change
        $nowLang = $_POST['default_language'];
        // Ensure default language is enabled
        $status_array[$nowLang] = '1';
        $GLOBALS['config']->set('config', 'default_language', $nowLang);

        $GLOBALS['config']->set('languages', false, $status_array);

        // For each language being enabled, ensure all email content types are seeded
        foreach ($status_array as $code => $status) {
            if ($status == '1') {
                $xml_file = CC_LANGUAGE_DIR.'email_'.$code.'.xml';
                if (!file_exists($xml_file)) continue;
                $xml_data = file_get_contents($xml_file);
                if (empty($xml_data)) continue;
                try {
                    $xml_emails = new SimpleXMLElement($xml_data);
                } catch (Exception $e) {
                    continue;
                }
                $xml_types = array();
                foreach ($xml_emails->email as $email) {
                    if ($email->content) {
                        $xml_types[] = (string)$email->attributes()->name;
                    }
                }
                if (empty($xml_types)) continue;
                $existing = $GLOBALS['db']->select('CubeCart_email_content', array('content_type'), array('language' => $code));
                $db_types = $existing ? array_column($existing, 'content_type') : array();
                if (!empty(array_diff($xml_types, $db_types))) {
                    $GLOBALS['language']->importEmail('email_'.$code.'.xml');
                    $GLOBALS['main']->successMessage(sprintf($lang['translate']['notify_email_content_imported'], $code));
                }
            }
        }

        $GLOBALS['main']->successMessage($lang['translate']['notify_language_status']);
        httpredir(currentPage());
    }
    $enabled = $GLOBALS['config']->get('languages');

    $GLOBALS['main']->addTabControl($lang['translate']['title_installed_languages'], 'lang_list');

    // Fetch available languages from API first so we can cross-reference versions
    $api_versions = array();
    $api_url_path = '/api/languages';
    $request = new Request('extensions.cubecart.com', $api_url_path, 443, false, true, 10);
    $request->setMethod('get');
    $request->setSSL();
    $request->skiplog(true);
    $request->cache(true);
    if (!$json = $request->send()) {
        $json = file_get_contents('https://extensions.cubecart.com'.$api_url_path);
    }
    $api_data = $json ? json_decode($json, true) : null;
    if ($api_data && !empty($api_data['languages'])) {
        foreach ($api_data['languages'] as $api_lang) {
            $api_versions[$api_lang['code']] = $api_lang['version'];
        }
    }

    ## List available language files
    $url_parts = parse_url(CC_STORE_URL);
    $domain = ltrim($url_parts['host'],'www.');
    $current_default = $GLOBALS['config']->get('config', 'default_language');
    if (($languageList = $GLOBALS['language']->listLanguages()) !== false) {
        foreach ($languageList as $code => $info) {
            $info['status'] = (isset($enabled[$code])) ? (int)$enabled[$code] : 1;
            $info['is_default'] = ($code == $current_default);
            if (file_exists('language/flags/'.$info['code'].'.png')) {
                $info['flag'] = 'language/flags/'.$info['code'].'.png';
            } else {
                $info['flag'] = 'language/flags/unknown.png';
            }
            $info['edit'] = currentPage(null, array('language' => $info['code']));
            $info['delete'] = currentPage(null, array('delete' => $info['code'], 'token' => SESSION_TOKEN));
            $subdomain = ($code == $current_default) ? 'www' : substr($info['code'],0,2);
            $info['placeholder'] = $subdomain.'.'.$domain;
            // Version info + upgrade flag
            $info['api_version'] = $api_versions[$code] ?? null;
            $info['upgrade_available'] = (!empty($info['api_version']) && !empty($info['version']) && version_compare($info['api_version'], $info['version'], '>'));
            if ($info['upgrade_available']) {
                $info['upgrade_url'] = currentPage(null, array('install' => $code, 'token' => SESSION_TOKEN)).'#lang_list';
            }
            $smarty_data['languages'][] = $info;
        }
        $GLOBALS['smarty']->assign('LANGUAGES', $smarty_data['languages']);
    }

    // Build "Available Languages" tab (those not installed) from the same API payload
    if ($api_data && !empty($api_data['languages'])) {
        $installed_codes = is_array($languageList) ? array_keys($languageList) : array();
        $available = array();
        foreach ($api_data['languages'] as $api_lang) {
            if (!in_array($api_lang['code'], $installed_codes)) {
                $api_lang['install_url'] = currentPage(null, array('install' => $api_lang['code'], 'token' => SESSION_TOKEN)).'#lang_available';
                $available[] = $api_lang;
            }
        }
        if (!empty($available)) {
            $GLOBALS['smarty']->assign('AVAILABLE_LANGUAGES', $available);
            $GLOBALS['main']->addTabControl($lang['translate']['title_available_languages'], 'lang_available');
        }
    }

    $GLOBALS['smarty']->assign('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);
}

$page_content = $GLOBALS['smarty']->fetch('templates/settings.language.php');
