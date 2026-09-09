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
 *
 * Settings for one skin, declared by that skin in the <settings> block of its
 * config.xml and stored in the config table under "skin_<folder>".
 *
 * Reached as ?_g=plugins&node=skin_settings&skin=<folder>. That URL needs no
 * routing change: controller.admin.session.true.inc.php only diverts _g=plugins
 * to a module when `type` is present, so without it the request falls through to
 * ACP::importNode() and lands here.
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}
Admin::getInstance()->permissions('maintenance', CC_PERM_READ, true);

global $lang;

$skin = isset($_GET['skin']) ? basename($_GET['skin']) : '';
// basename() alone still admits "..", and this value builds a filesystem path.
if ($skin === '' || !preg_match('/^[a-z0-9_-]+$/i', $skin) || !file_exists(CC_ROOT_DIR.'/skins/'.$skin.'/config.xml')) {
    $GLOBALS['main']->errorMessage(sprintf($lang['module']['not_exist'], 'skins/'.$skin));
    httpredir('?_g=plugins');
}

$schema = $GLOBALS['gui']->getSkinSettingsSchema($skin);
if (empty($schema)) {
    // No cog is rendered for these, so this is a hand-typed or stale URL.
    httpredir('?_g=plugins');
}

## Save
if (isset($_POST['skin_settings']) && Admin::getInstance()->permissions('maintenance', CC_PERM_EDIT)) {
    $posted = (array)$_POST['skin_settings'];
    $save   = array();

    foreach ($schema as $name => $definition) {
        switch ($definition['type']) {
            case 'bool':
                // An unchecked box posts nothing at all, which is the only way
                // "off" is expressed — so absence must mean 0, not "unchanged".
                $save[$name] = !empty($posted[$name]) ? '1' : '0';
                break;
            case 'select':
                // Never trust the posted value to be one of ours.
                $allowed = array();
                foreach ($definition['options'] as $option) {
                    $allowed[] = $option['value'];
                }
                $value = isset($posted[$name]) ? (string)$posted[$name] : '';
                $save[$name] = in_array($value, $allowed, true) ? $value : (string)$definition['default'];
                break;
            case 'color':
                // Empty is legitimate and means "no override". Anything else
                // must be #rrggbb: this value is interpolated into a <style>
                // block on the storefront.
                $value = isset($posted[$name]) ? trim((string)$posted[$name]) : '';
                if ($value !== '' && !preg_match('/^#[0-9a-f]{6}$/i', $value)) {
                    $value = (string)$definition['default'];
                }
                $save[$name] = strtolower($value);
                break;
            default:
                $save[$name] = isset($posted[$name]) ? (string)$posted[$name] : '';
        }
    }

    $GLOBALS['config']->set('skin_'.$skin, '', $save, true);
    // _setSkin() serves SKIN_SETTINGS from here, so the storefront would keep
    // showing the old values until this key goes.
    $GLOBALS['cache']->delete('skin.'.$skin.'.settings');

    $GLOBALS['main']->successMessage($lang['settings']['notify_settings_update']);
    httpredir('?_g=plugins&node=skin_settings&skin='.$skin);
}

## Display
$xml = $GLOBALS['gui']->getSkinConfig('', $skin);
$display_name = ($xml && (string)$xml->info->display !== '') ? (string)$xml->info->display : $skin;

$values = $GLOBALS['gui']->getSkinSettings($skin);
$fields = array();
foreach ($schema as $name => $definition) {
    $definition['value'] = isset($values[$name]) ? $values[$name] : $definition['default'];
    if ($definition['type'] === 'select') {
        foreach ($definition['options'] as $index => $option) {
            $definition['options'][$index]['selected'] = ((string)$option['value'] === (string)$definition['value']);
        }
    }
    $fields[] = $definition;
}

$GLOBALS['gui']->addBreadcrumb($lang['navigation']['nav_plugins'], '?_g=plugins');
$GLOBALS['gui']->addBreadcrumb($display_name, '?_g=plugins&node=skin_settings&skin='.$skin, true);
$GLOBALS['main']->addTabControl($lang['module']['config_settings'], 'skin_settings');

$GLOBALS['smarty']->assign('SKIN_NAME', $display_name);
$GLOBALS['smarty']->assign('SKIN_FOLDER_EDIT', $skin);
$GLOBALS['smarty']->assign('SKIN_SETTING_FIELDS', $fields);

$page_content = $GLOBALS['smarty']->fetch('templates/plugins.skin_settings.php');
