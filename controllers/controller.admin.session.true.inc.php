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

if (isset($_GET['clear_cache']) && $_GET['clear_cache'] == 'true') {
    $GLOBALS['cache']->clear();
    $GLOBALS['session']->delete('CLEAR_CACHE');
    $GLOBALS['main']->successMessage($GLOBALS['language']->maintain['notify_cache_cleared'], false);
    httpredir(currentPage(array('clear_cache')));
}

// Load admin user details
if (!isset($_GET['_g']) || !in_array(strtolower($_GET['_g']), array('login', 'logout', 'password', 'recovery'))) {
    $GLOBALS['main']->setTemplate();
}

if (isset($_GET['_g']) && in_array($_GET['_g'], array('login', 'password', 'recovery'))) {
    httpredir('?');
}
// Backard compatibility for links to v5 modules
if (isset($_GET['_g']) && $_GET['_g']=='modules') {
    $_GET['_g'] = 'plugins';
    unset($_GET['type']);
}

if (isset($_GET['_g']) && !empty($_GET['_g']) && $_GET['_g'] != 'plugins') {
    $GLOBALS['gui']->addBreadcrumb(ucwords($_GET['_g']));
}

if (!empty($_GET['_g'])) {
    $module_type = (isset($_GET['type']) && preg_match("/[a-z]/i", $_GET['type'])) ? $_GET['type'] : '';

    $node = (!empty($_GET['node'])) ? strtolower($_GET['node']) : 'index';
    $node = preg_replace('/[^a-z0-9._-]/', '', $node);
    $_g = preg_replace('/[^a-z0-9_-]/', '', $_GET['_g']);
    
    if (!isset($_GET['delete']) && strtolower($_g) == 'plugins' && !empty($module_type)) {
        $module_type = preg_match("/[a-z]/i", $_GET['type']) ? $_GET['type'] : '';
        $GLOBALS['gui']->addBreadcrumb($GLOBALS['language']->navigation['nav_plugins'], '?_g=plugins');
        // Display Modules
        $GLOBALS['main']->wikiNamespace('Modules');
        
        if (!empty($_GET['module'])) {
            // Load Module
            $GLOBALS['main']->wikiPage($_GET['module']);
            // Load additional data from XML
            $config_xml = CC_ROOT_DIR.'/modules/'.$module_type.'/'.$_GET['module'].'/config.xml';
            
            if (file_exists($config_xml)) {
                try {
                    $xml   = new SimpleXMLElement(file_get_contents($config_xml));
                    $module_info = array(
                        'name' => (string)$xml->info->name,
                    );
                    
                    $module = array(
                        'type' => strtolower($module_type),
                        'module'=> ($module_type == 'installer') ? '' : $_GET['module'],
                    );
                    $GLOBALS['gui']->addBreadcrumb((isset($_GET['variant']) ? $_GET['variant'] : $module_info['name']), $_GET);

                    $module_admin = CC_ROOT_DIR.'/modules/'.$module['type'].'/'.$module['module'].'/admin/'.$node.'.inc.php';
                    if (file_exists($module_admin)) {
                        define('MODULE_FORM_ACTION', (defined('VAL_SELF')) ? constant('VAL_SELF') : currentPage());
                        include $module_admin;
                    } else {
                        trigger_error(sprintf("File '%s' doesn't exist", $module_admin), E_USER_WARNING);
                    }
                } catch (Exception $e) {
                    trigger_error($e, E_USER_WARNING);
                }
            } else {
                $GLOBALS['main']->errorMessage("Extension has missing or corrupt config.xml file.");
                trigger_error("Extension config.xml file doesn't exist. (".$config_xml.")", E_USER_WARNING);
            }
        }
    } elseif (strtolower($_g) == 'plugin' && isset($_GET['name'])) {
        // Include plugins
        $GLOBALS['main']->wikiNamespace('Plugins');
        foreach ($GLOBALS['hooks']->load('admin.'.strtolower($_GET['name'])) as $hook) {
            include $hook;
        }
    } elseif (strtolower($_g) == 'plugin' && (!isset($_GET['name']) || empty($_GET['name']))) {
        httpredir('?_g=plugins');
        exit;
    } elseif ($_g == '401') {
        $GLOBALS['gui']->setError($GLOBALS['language']->navigation['error_401']);
    } else {
        if (strtolower($_g) == 'xml') {
            $suppress_output = true;
            // Process an XMLHTTPRequest
            $json = AJAX::load();
            @ob_end_clean();
            die($json);
        } else {
            // Everything else
            $include = $GLOBALS['main']->importNode($_g, $node);
            if (file_exists($include)) {
                require $include;
            } else {
                $page_content = str_replace(CC_ROOT_DIR, '', $include)." - not found.";
                trigger_error(sprintf('Unable to load content for %s:%s', $_GET['_g'], $node), E_USER_WARNING);
            }
        }
    }
} else {
    include CC_ROOT_DIR.'/'.$GLOBALS['config']->get('config', 'adminFolder').'/'.'sources/dashboard.index.inc.php';
}
$GLOBALS['main']->showHelp();
$GLOBALS['main']->showPerformance();

include CC_ROOT_DIR.'/'.$glob['adminFolder'].'/sources/element.navigation.inc.php';
if (is_array($nav_sections)) {
    foreach ($nav_sections as $key => $name) {
        if (isset($nav_items[$key]) && is_array($nav_items[$key])) {
            $GLOBALS['main']->addNavItem($name, $nav_items[$key]);
        }
    }
}
// Create the page tabs
$GLOBALS['main']->showTabs();
// Navigation
$GLOBALS['main']->showNavigation();
// Notify if cache needs to be cleared
$GLOBALS['smarty']->assign('CLEAR_CACHE', $GLOBALS['session']->has('CLEAR_CACHE'));

// Render main page content
if (!empty($page_content)) {
    $GLOBALS['smarty']->assign('DISPLAY_CONTENT', $page_content);
}

$body_js = array();
foreach ($GLOBALS['hooks']->load('admin.body_js') as $hook) {
    include $hook;
}
$GLOBALS['smarty']->assign('BODY_JS', $body_js);

$head_js = array();
foreach ($GLOBALS['hooks']->load('admin.head_js') as $hook) {
    include $hook;
}
$GLOBALS['smarty']->assign('HEAD_JS', $head_js);

$head_css = array();
foreach ($GLOBALS['hooks']->load('admin.head_css') as $hook) {
    include $hook;
}
$GLOBALS['smarty']->assign('HEAD_CSS', $head_css);

// CKEditor is kept for extensions that use class="fck", and is only loaded on a page
// that needs it (#4742). A page says so by having a textarea of that class or by naming
// CKEDITOR in its own script; a hook above can also set $load_ckeditor = true.
$GLOBALS['smarty']->assign('LOAD_CKEDITOR', !empty($load_ckeditor)
    || (!empty($page_content) && preg_match('/\bfck\b|ckeditor/i', (string)$page_content) === 1));

// A whole document or a template is edited as code, on <textarea class="cc-code"> (admin.js),
// with Ace where the page has one
$GLOBALS['smarty']->assign('LOAD_CODE_EDITOR', !empty($load_code_editor)
    || (!empty($page_content) && preg_match('/\bcc-code\b/', (string)$page_content) === 1));

// Core's own editor is TinyMCE, on <textarea class="cc-editor"> (admin.editor.js). The same
// rule: it is loaded where the page has one, or where a hook above set $load_editor = true.
if (!empty($load_editor) || (!empty($page_content) && preg_match('/\bcc-editor\b/', (string)$page_content) === 1)) {
    // TinyMCE's language packs are named by language, and by country only where they differ
    $editor_language = '';
    $admin_language  = (string)$GLOBALS['language']->current();
    $short           = strtolower(substr($admin_language, 0, 2));
    $short           = ($short === 'dk') ? 'da' : $short; // the Danish pack is dk-DK here
    if ($short !== 'en' && preg_match('/^[a-z]{2}$/', $short)) {
        $packs = glob(CC_ROOT_DIR.'/includes/tinymce/langs/'.$short.'*.js');
        $names = is_array($packs) ? array_map(function ($pack) {
            return basename($pack, '.js');
        }, $packs) : array();
        foreach (array(str_replace('_', '-', $admin_language), $short) as $wanted) {
            if (in_array($wanted, $names, true)) {
                $editor_language = $wanted;
                break;
            }
        }
        if ($editor_language === '' && !empty($names)) {
            $editor_language = $names[0];
        }
    }
    $editor_lang    = $GLOBALS['language']->email;
    $editor_strings = array(
        'loading' => $GLOBALS['language']->common['loading'].'...',
        'failed'  => $editor_lang['preview_failed'],
    );
    foreach (array('editor_locked', 'editor_locked_note', 'editor_locked_markup', 'editor_locked_markup_note', 'editor_logic_note',
        'editor_edit_code', 'editor_move_up', 'editor_move_down', 'editor_remove', 'editor_new_paragraph', 'editor_locked_code_help',
        'editor_apply', 'editor_code_view', 'editor_parked', 'editor_content_code_only', 'editor_source_help',
        'editor_source_help_smarty', 'editor_smarty_refused', 'editor_placeholders') as $editor_string) {
        $editor_strings[$editor_string] = isset($editor_lang[$editor_string]) ? $editor_lang[$editor_string] : '';
    }
    $GLOBALS['smarty']->assign('EDITOR_DATA', json_encode(array(
        'tinymce'  => 'includes/tinymce',
        'endpoint' => $glob['adminFile'].'?_g=xml&function=editorUnits',
        'storeUrl' => rtrim((string)$GLOBALS['storeURL'], '/').'/',
        'language' => $editor_language,
        'strings'  => $editor_strings,
    ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE));
    unset($editor_language, $admin_language, $short, $packs, $names, $wanted, $editor_lang, $editor_strings, $editor_string);
}
