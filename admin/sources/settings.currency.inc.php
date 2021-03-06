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


$GLOBALS['gui']->addBreadcrumb($lang['settings']['title_currency']);

###########################################

if (isset($_POST['autoupdate']) && Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {
    foreach ($GLOBALS['hooks']->load('admin.settings.currency.pre_process') as $hook) {
        include $hook;
    }

    ## European Central Bank
    if (($request = new Request('www.ecb.europa.eu', '/stats/eurofxref/eurofxref-daily.xml')) !== false) {
        $request->setMethod('get');
        $request->setSSL();
        $rates_xml = $request->send();

        // If this fails fall back to original file_get_contents, if that failes we have tried all we can
        if (empty($rates_xml)) {
            $rates_xml = file_get_contents('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
        }

        if (!empty($rates_xml)) {
            $xml  = new SimpleXMLElement($rates_xml);
            foreach ($xml->Cube->Cube->Cube as $currency) {
                $rate = $currency->attributes();
                $fx[(string)$rate['currency']] = (float)$rate['rate'];
            }
            $fx['EUR'] = 1;
            $updated = strtotime((string)$xml->Cube->Cube->attributes()->time);
            # Get the divisor
            $base  = (1/$fx[strtoupper($GLOBALS['config']->get('config', 'default_currency'))]);
            foreach ($fx as $code => $rate) {
                $value = ($base/(1/$rate));
                $GLOBALS['db']->update('CubeCart_currency', array('value' => $value, 'updated' => $updated), array('code' => $code), true);
            }
            $GLOBALS['main']->successMessage($lang['settings']['notify_currency_rates_update']);
        } else {
            $GLOBALS['main']->successMessage($lang['settings']['notify_currency_rates_update_fail']);
        }
    } else {
        $GLOBALS['main']->successMessage($lang['settings']['notify_currency_rates_update_fail']);
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
    if (is_array($_POST['currency'])) {
        $updated = false;
        foreach ($_POST['currency'] as $code => $array) {
            if (isset($array['value'])) {
                $array['value']   = round((float)$array['value'], 6);
                $array['updated'] = time();
            }
            if ($GLOBALS['db']->update('CubeCart_currency', $array, array('code' => $code), true)) {
                $updated = true;
            }
        }
        if ($updated) {
            $GLOBALS['main']->successMessage($lang['settings']['notify_currency_rates_update']);
        } else {
            $GLOBALS['main']->successMessage($lang['settings']['error_currency_rates_update']);
        }
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

if (($currencies = $GLOBALS['db']->select('CubeCart_currency', false, false, array('active' => 'DESC', 'code' => 'ASC'))) !== false) {
    foreach ($currencies as $currency) {
        $currency['updated'] = formatTime($currency['updated']);
        $smarty_data['currencies'][] = $currency;
    }
    $GLOBALS['smarty']->assign('CURRENCIES', $smarty_data['currencies']);
}
$page_content = $GLOBALS['smarty']->fetch('templates/settings.currency.php');
