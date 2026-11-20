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
// A merge moves one account's records and then deletes it, so it needs both rights
Admin::getInstance()->permissions('customers', CC_PERM_EDIT, true);
Admin::getInstance()->permissions('customers', CC_PERM_DELETE, true);

$keep_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
$keep    = CustomerMerge::customer($keep_id);
if (!$keep) {
    $GLOBALS['main']->errorMessage($lang['customer']['error_customer_found']);
    httpredir('?_g=customers');
}
$merge_url = '?_g=customers&node=merge&customer_id='.$keep_id;
$edit_url  = '?_g=customers&action=edit&customer_id='.$keep_id;

// Customer details are stored encoded by the forms that save them. Anything that
// arrived another way (an import, an extension) is encoded here, and nothing twice.
$merge_safe = function ($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8', false);
};
$merge_name = function ($customer) use ($merge_safe) {
    return $merge_safe(trim($customer['first_name'].' '.$customer['last_name']));
};
$merge_plain = function ($text) {
    return html_entity_decode((string)$text, ENT_QUOTES, 'UTF-8');
};
$merge_problem = function ($problem) use ($lang) {
    $messages = array(
        'missing' => $lang['customer']['error_customer_found'],
        'same'    => $lang['customer']['merge_error_same'],
        'guest'   => $lang['customer']['merge_error_guest'],
    );
    return isset($messages[$problem]) ? $messages[$problem] : '';
};

if (isset($_POST['merge']) && is_array($_POST['merge'])) {
    if (!empty($_POST['merge']['confirm'])) {
        // The review step was accepted
        $from_id = isset($_POST['merge']['from']) ? (int)$_POST['merge']['from'] : 0;
        $from    = CustomerMerge::customer($from_id);
        $problem = CustomerMerge::problem($keep, $from);
        if ($problem !== '') {
            $GLOBALS['main']->errorMessage($merge_problem($problem));
            httpredir($merge_url);
        }
        $note = sprintf(
            $lang['customer']['merge_note'],
            $from_id,
            $merge_plain($merge_name($from)),
            $merge_plain($from['email']),
            formatTime(time(), false, true),
            $merge_plain(Admin::getInstance()->get('name'))
        );
        if (CustomerMerge::merge($keep_id, $from_id, $note) !== false) {
            $GLOBALS['main']->successMessage(sprintf($lang['customer']['merge_done'], $merge_name($from), $merge_safe($from['email'])));
            httpredir($edit_url);
        }
        $GLOBALS['main']->errorMessage($lang['customer']['merge_failed']);
        httpredir($merge_url.'&from='.$from_id);
    }
    // An account was chosen: a search result wins over a ticked suggestion
    $from_id = !empty($_POST['merge']['search']) ? (int)$_POST['merge']['search'] : (isset($_POST['merge']['pick']) ? (int)$_POST['merge']['pick'] : 0);
    if ($from_id < 1) {
        $GLOBALS['main']->errorMessage($lang['customer']['merge_error_choose']);
        httpredir($merge_url);
    }
    httpredir($merge_url.'&from='.$from_id);
}

$merge_describe = function ($customer) use ($lang, $merge_name, $merge_safe) {
    $customer_id = (int)$customer['customer_id'];
    return array(
        'customer_id' => $customer_id,
        'name'        => $merge_name($customer),
        'email'       => $merge_safe($customer['email']),
        'type'        => (int)$customer['type'],
        'type_name'   => ((int)$customer['type'] === CustomerMerge::GUEST) ? $lang['customer']['title_key_unregistered'] : $lang['customer']['title_key_registered'],
        'registered'  => formatTime($customer['registered']),
        'orders'      => isset($customer['orders']) ? (int)$customer['orders'] : (int)$GLOBALS['db']->count('CubeCart_order_summary', false, array('customer_id' => $customer_id)),
        'edit'        => '?_g=customers&action=edit&customer_id='.$customer_id,
    );
};

$GLOBALS['gui']->addBreadcrumb($merge_name($keep), $edit_url);
$GLOBALS['gui']->addBreadcrumb($lang['customer']['merge_title'], $merge_url);
$GLOBALS['main']->addTabControl($lang['customer']['merge_title'], 'general');

$GLOBALS['smarty']->assign('MERGE_KEEP', $merge_describe($keep));
$GLOBALS['smarty']->assign('MERGE_URL', $merge_url);
$GLOBALS['smarty']->assign('MERGE_BACK', $edit_url);

if (isset($_GET['from'])) {
    // Review: what merging this account in would do
    $plan = CustomerMerge::plan($keep_id, (int)$_GET['from']);
    if ($plan['problem'] === 'missing' || $plan['problem'] === 'same') {
        $GLOBALS['main']->errorMessage($merge_problem($plan['problem']));
        httpredir($merge_url);
    }
    $from = $plan['from'];
    $rows = array();
    if ($plan['orders'] > 0) {
        $rows[] = array('label' => $lang['customer']['merge_orders'], 'value' => number_format($plan['orders']));
    }
    if ($plan['addresses'] > 0 || $plan['addresses_duplicate'] > 0) {
        $value = number_format($plan['addresses']);
        if ($plan['addresses_duplicate'] > 0) {
            $value .= ' ('.sprintf($lang['customer']['merge_addresses_duplicate'], $plan['addresses_duplicate']).')';
        }
        $rows[] = array('label' => $lang['customer']['merge_addresses'], 'value' => $value);
    }
    foreach (array('downloads', 'reviews', 'withdrawals', 'coupons') as $item) {
        if ($plan[$item] > 0) {
            $rows[] = array('label' => $lang['customer']['merge_'.$item], 'value' => number_format($plan[$item]));
        }
    }
    if (!empty($plan['groups'])) {
        $rows[] = array('label' => $lang['customer']['merge_groups'], 'value' => implode(', ', array_map($merge_safe, $plan['groups'])));
    }
    if ($plan['credit'] > 0) {
        // Shown as the plain amount the customer page holds, not converted for display
        $rows[] = array('label' => $lang['customer']['merge_credit'], 'value' => number_format($plan['credit'], 2, '.', ''));
    }
    if ($plan['basket'] !== '') {
        $rows[] = array('label' => $lang['customer']['merge_basket'], 'value' => $lang['customer']['merge_basket_'.$plan['basket']]);
    }
    if ($plan['newsletter']) {
        $rows[] = array('label' => $lang['navigation']['nav_newsletters'], 'value' => sprintf($lang['customer']['merge_newsletter_removed'], $merge_safe($from['email'])));
    }
    if ($plan['notes']) {
        $rows[] = array('label' => $lang['common']['notes'], 'value' => $lang['customer']['merge_notes_added']);
    }
    $GLOBALS['smarty']->assign('MERGE_FROM', $merge_describe($from));
    $GLOBALS['smarty']->assign('MERGE_ROWS', $rows);
    $GLOBALS['smarty']->assign('MERGE_PROBLEM', $merge_problem($plan['problem']));
    $GLOBALS['smarty']->assign('MERGE_WARNING', sprintf($lang['customer']['merge_warning'], $merge_name($from), $merge_safe($from['email'])));
    // The other way round: keep the account that was going to be merged in
    $GLOBALS['smarty']->assign('MERGE_SWAP', array(
        'url'   => '?_g=customers&node=merge&customer_id='.(int)$from['customer_id'].'&from='.$keep_id,
        'label' => sprintf($lang['customer']['merge_swap'], $merge_safe($from['email'])), // duplicates tend to share a name
    ));
} else {
    // Choose: the accounts that look like the same person, or any other by search
    $reasons = array(
        'name'    => $lang['customer']['merge_match_name'],
        'address' => $lang['customer']['merge_match_address'],
        'phone'   => $lang['customer']['merge_match_phone'],
    );
    $candidates = array();
    foreach (CustomerMerge::candidates($keep_id) as $candidate) {
        $row = $merge_describe($candidate);
        $row['reasons'] = implode(', ', array_intersect_key($reasons, array_flip($candidate['reasons'])));
        $candidates[] = $row;
    }
    $GLOBALS['smarty']->assign('MERGE_CANDIDATES', $candidates);
}

$page_content = $GLOBALS['smarty']->fetch('templates/customers.merge.php');
