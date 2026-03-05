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


$GLOBALS['gui']->addBreadcrumb($lang['settings']['title_currency']);

###########################################

if (isset($_GET['autoupdate']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
    foreach ($GLOBALS['hooks']->load('admin.settings.currency.pre_process') as $hook) {
        include $hook;
    }
    $cron = new Cron();
    $result = $cron->updateExchangeRates('', false);
    if(!empty($result)) {
        $GLOBALS['main']->successMessage($lang['settings']['notify_currency_rates_update']);
    } else {
        $GLOBALS['main']->errorMessage($lang['settings']['notify_currency_rates_update_fail']);
    }
    httpredir('?_g=settings&node=currency', 'exchange');
}

if (isset($_POST['add'])) {
    if (!empty($_POST['add']['name']) && !empty($_POST['add']['code'])) {
        $_POST['add']['updated'] = time();
        if (Admin::getInstance()->permissions('settings', CC_PERM_EDIT) && $GLOBALS['db']->insert('CubeCart_currency', $_POST['add'])) {
            $GLOBALS['main']->successMessage($lang['settings']['notify_currency_add']);
        } else {
            $GLOBALS['main']->errorMessage($lang['settings']['error_currency_add']);
        }
        httpredir('?_g=settings&node=currency', 'exchange');
    }
}

if (isset($_POST['update_manual']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
    $updated = false;
    $current_default = isset($_POST['default_currency']) ? (string)$_POST['default_currency'] : $GLOBALS['config']->get('config', 'default_currency');
    if (is_array($_POST['currency'])) {
        if (isset($_POST['currency'][$current_default])) {
            $_POST['currency'][$current_default]['active'] = '1';
        }
        foreach ($_POST['currency'] as $code => $array) {
            if (isset($array['value'])) {
                $array['value'] = round((float)$array['value'], 6);
            }
            $GLOBALS['db']->update('CubeCart_currency', $array, array('code' => $code));
            if ($GLOBALS['db']->affected() > 0) {
                $updated = true;
            }
        }
    }
    if (isset($_POST['default_currency']) && !empty($_POST['default_currency'])) {
        $new_default = (string)$_POST['default_currency'];
        if ($new_default !== $GLOBALS['config']->get('config', 'default_currency')) {
            $GLOBALS['db']->update('CubeCart_currency', array('value' => 1), array('code' => $new_default));
            $GLOBALS['config']->set('config', 'default_currency', $new_default);
            $GLOBALS['main']->successMessage($lang['settings']['notify_currency_default_changed_manual']);
            $updated = true;
        }
    }
    if ($updated) {
        $GLOBALS['main']->successMessage($lang['settings']['notify_currency_update']);
    } else {
        $GLOBALS['main']->errorMessage($lang['settings']['error_settings_update']);
    }
    httpredir('?_g=settings&node=currency', 'exchange');
}

if (isset($_GET['delete'])) {
    if (Admin::getInstance()->permissions('settings', CC_PERM_DELETE) && $GLOBALS['db']->delete('CubeCart_currency', array('code' => $_GET['delete']))) {
        $GLOBALS['main']->successMessage(sprintf($lang['settings']['error_currency_delete'], $_GET['delete']));
    } else {
        $GLOBALS['main']->errorMessage($lang['settings']['error_currency_delete']);
    }
    httpredir('?_g=settings&node=currency', 'exchange');
}

###########################################

foreach ($GLOBALS['hooks']->load('admin.settings.currency.post_process') as $hook) {
    include $hook;
}

$GLOBALS['main']->addTabControl($lang['settings']['tab_currency_rate'], 'exchange');
$GLOBALS['main']->addTabControl($lang['settings']['tab_currency_add'], 'addrate');

$GLOBALS['smarty']->assign('DEFAULT_CURRENCY', $GLOBALS['config']->get('config', 'default_currency'));
if (($currencies = $GLOBALS['db']->select('CubeCart_currency', false, false, array('active' => 'DESC', 'name' => 'ASC'))) !== false) {
    foreach ($currencies as $currency) {
        $currency['updated'] = formatTime($currency['updated']);
        $smarty_data['currencies'][] = $currency;
    }
    $GLOBALS['smarty']->assign('CURRENCIES', $smarty_data['currencies']);
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.currency.php');
