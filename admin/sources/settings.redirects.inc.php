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

if (Admin::getInstance()->permissions('settings', CC_PERM_EDIT)) {

    // Single-row ignore / un-ignore (legacy GET).
    if(isset($_GET['ignore']) && !empty($_GET['ignore'])) {
        $GLOBALS['db']->update('CubeCart_404_log', array('ignore' => 1, 'done' => 0, 'warn' => 0), array('id' => (int)$_GET['ignore']));
        httpredir('?_g=settings&node=redirects','missing_uris');
    }
    if(isset($_GET['remove_ignore']) && !empty($_GET['remove_ignore'])) {
        $GLOBALS['db']->update('CubeCart_404_log', array('ignore' => 0, 'done' => 0, 'warn' => 0), array('id' => (int)$_GET['remove_ignore']));
        httpredir('?_g=settings&node=redirects','ignored_uris');
    }

    // Clear all ignored entries.
    if (isset($_GET['clear_ignored'])) {
        $GLOBALS['db']->delete('CubeCart_404_log', array('ignore' => 1));
        $GLOBALS['main']->successMessage($lang['settings']['ignored_cleared']);
        httpredir('?_g=settings&node=redirects', 'ignored_uris');
    }

    // Bulk action on Missing URIs (with checkbox names="missing_id[]").
    if (isset($_POST['action']) && !empty($_POST['missing_id']) && is_array($_POST['missing_id'])) {
        $ids = array_values(array_filter(array_map('intval', $_POST['missing_id'])));
        if ($ids) {
            switch ($_POST['action']) {
                case 'ignore':
                    $GLOBALS['db']->update('CubeCart_404_log', array('ignore' => 1, 'done' => 0, 'warn' => 0), 'id IN ('.implode(',', $ids).')');
                    break;
                case 'delete_log':
                    $GLOBALS['db']->delete('CubeCart_404_log', 'id IN ('.implode(',', $ids).')');
                    break;
            }
        }
        httpredir('?_g=settings&node=redirects', 'missing_uris');
    }

    // Add a redirect (single row from the Add form).
    if(isset($_POST['path']) && !empty($_POST['path'])) {
        // Normalise the supplied path: strip the CubeCart install prefix so a
        // pasted "/v6/foo.html" is stored as "foo.html" — the form labels show
        // site-root paths but the DB column is install-relative.
        $_post_path = ltrim((string)$_POST['path'], '/');
        $_path_prefix = ltrim(CC_ROOT_REL, '/');
        if ($_path_prefix !== '' && strpos($_post_path, $_path_prefix) === 0) {
            $_post_path = substr($_post_path, strlen($_path_prefix));
        }
        $_POST['path'] = ltrim($_post_path, '/');

        // Check product, category, doc exists
        $exists = false;
        switch($_POST['type']) {
            case 'prod':
                $exists = $GLOBALS['db']->select('CubeCart_inventory', false, array('product_id' => (int)$_POST['item_id']));
            break;
            case 'cat':
                $exists = $GLOBALS['db']->select('CubeCart_category', false, array('cat_id' => (int)$_POST['item_id']));
            break;
            case 'doc':
                $exists = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_id' => (int)$_POST['item_id']));
            break;
            default: // Catch static sections
                $exists = true;
                $_POST['item_id'] = 0;
        }
        if($exists) {
            if($GLOBALS['seo']->setdbPath($_POST['type'], (int)$_POST['item_id'], $_POST['path'], true, false, $_POST['redirect'])) {
                $GLOBALS['main']->successMessage($lang['notification']['notify_success_add_redirect']);
                // Mark every matching 404 entry as resolved (was: only the first).
                $GLOBALS['db']->update('CubeCart_404_log', array('done' => 1, 'warn' => 0), array('uri' => $_POST['path']));
            } else {
                $existing = $GLOBALS['db']->select('CubeCart_seo_urls', false, array('path' => $_POST['path']));
                $a = '';
                switch($existing[0]['type']) {
                    case 'prod':
                        $a = '?_g=products&node=index&action=edit&product_id='.$existing[0]['item_id'].'#seo';
                        $type = 'Product';
                    break;
                    case 'cat':
                        $a = '?_g=categories&action=edit&cat_id='.$existing[0]['item_id'].'#seo';
                        $type = 'Category';
                    break;
                    case 'doc':
                        $a = '?_g=documents&action=edit&doc_id='.$existing[0]['item_id'].'#seo';
                        $type = 'Document';
                    break;
                }
                $item = '('.$type;
                if($existing[0]['item_id']>0) {
                    $item .= ': '.$existing[0]['item_id'];
                }
                $item .= ')';
                if(!empty($a)) {
                    $item .= ' <a href="'.$a.'">'.$lang['common']['view'].'</a>';
                }
                $GLOBALS['main']->errorMessage($lang['notification']['notify_fail_add_redirect'].' '.$item);
            }
        } else {
            $GLOBALS['main']->errorMessage($lang['notification']['notify_object_not_found']);
        }
        httpredir('?_g=settings&node=redirects');
    }
}

if (isset($_GET['delete']) && ctype_digit($_GET['delete']) && Admin::getInstance()->permissions('settings', CC_PERM_DELETE)) {
    if($GLOBALS['db']->delete('CubeCart_seo_urls', array('id' => $_GET['delete']))) {
        $GLOBALS['main']->successMessage($lang['notification']['notify_seo_url_deleted']);
    } else {
        $GLOBALS['main']->errorMessage($lang['notification']['notify_seo_url_not_deleted']);
    }
    $redirect = currentPage(array('delete'));
    if(isset($_GET['item_id']) && isset($_GET['type'])) {
        switch($_GET['type']) {
            case "prod":
                $redirect = '?_g=products&node=index&action=edit&product_id='.$_GET['item_id'];
            break;
            case "cat":
                $redirect = '?_g=categories&action=edit&cat_id='.$_GET['item_id'];
            break;
            case "doc":
                $redirect = '?_g=documents&action=edit&doc_id='.$_GET['item_id'];
            break;
        }
        httpredir($redirect, 'seo');
    } else {
        httpredir($redirect);
    }
}

$redirect_types = array(
    'static' => array(
        'certificates' => $lang['catalogue']['gift_certificates'],
        'contact' => $lang['documents']['document_contact'],
        'login' => $lang['account']['login'],
        'register' => $lang['account']['register'],
        'saleitems' => $lang['navigation']['saleitems'],
        'search' => $lang['common']['search']
    ),
    'dynamic' => array(
        'prod' => $lang['common']['product'],
        'cat'  => $lang['common']['category'],
        'doc'  => $lang['common']['document']
    )
);

foreach ($GLOBALS['hooks']->load('admin.settings.redirect.types') as $hook) {
    include $hook;
}

$GLOBALS['smarty']->assign('REDIRECT_TYPES', $redirect_types);

// Filter state for the redirects list.
$filter = array(
    'path'     => isset($_GET['filter_path'])     ? trim((string)$_GET['filter_path'])     : '',
    'redirect' => isset($_GET['filter_redirect']) ? (string)$_GET['filter_redirect']      : '',
    'type'     => isset($_GET['filter_type'])     ? (string)$_GET['filter_type']           : '',
);
$where = array();
$where[] = "`redirect` IN ('301', '302')";
if ($filter['path'] !== '') {
    $where[] = sprintf("`path` LIKE '%%%s%%'", $GLOBALS['db']->sqlSafe($filter['path']));
}
if (in_array($filter['redirect'], array('301', '302'), true)) {
    $where[] = sprintf("`redirect` = '%s'", $filter['redirect']);
}
$valid_types = array_merge(array_keys($redirect_types['dynamic']), array_keys($redirect_types['static']));
if (in_array($filter['type'], $valid_types, true)) {
    $where[] = sprintf("`type` = '%s'", $GLOBALS['db']->sqlSafe($filter['type']));
}
$where_sql = implode(' AND ', $where);

$page  = (isset($_GET['page'])) ? $_GET['page'] : 1;
$per_page = 100;
$redirect_dataset = array();
$total = 0;
if($redirects = $GLOBALS['db']->select('CubeCart_seo_urls', false, $where_sql, false, $per_page, $page)) {
    $total = $GLOBALS['db']->count('CubeCart_seo_urls', false, $where_sql);
    $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination($total, $per_page, $page));
    foreach($redirects as $redirect) {
        $redirect['destination'] = $GLOBALS['seo']->getdbPath($redirect['type'], $redirect['item_id']);
        // Stored paths are CubeCart-install-relative; show them site-root-relative.
        $redirect['display_path']        = CC_ROOT_REL.ltrim($redirect['path'], '/');
        $redirect['display_destination'] = CC_ROOT_REL.ltrim($redirect['destination'], '/');
        $redirect_dataset[] = $redirect;
    }
}
$GLOBALS['main']->addTabControl($lang['settings']['redirects'], 'redirects');
$GLOBALS['smarty']->assign('REDIRECTS', $redirect_dataset);
$GLOBALS['smarty']->assign('FILTER', $filter);

// Pre-fill values for the Add form when arriving via a "Redirect this URI" link.
$prefill = array(
    'path' => isset($_GET['from_uri']) ? (string)$_GET['from_uri'] : '',
);
$GLOBALS['smarty']->assign('PREFILL', $prefill);

$page  = (isset($_GET['404_page'])) ? $_GET['404_page'] : 1;
$per_page = 100;
$missing_dataset = array();
$total = 0;
if($missing = $GLOBALS['db']->select('CubeCart_404_log', false, array('ignore' => 0), array('updated' => 'DESC'), $per_page, $page)) {
    $total = $GLOBALS['db']->count('CubeCart_404_log', false, array('ignore' => 0));
    $GLOBALS['smarty']->assign('PAGINATION_404', $GLOBALS['db']->pagination($total, $per_page, $page, 5, '404_page', 'missing_uris'));
    foreach($missing as $m) {
        $m['updated']     = formatTime(strtotime($m['updated']));
        $m['display_uri'] = CC_ROOT_REL.ltrim($m['uri'], '/');
        $missing_dataset[] = $m;
    }
}
$GLOBALS['main']->addTabControl($lang['settings']['missing_uris'], 'missing_uris');
$GLOBALS['smarty']->assign('MISSING', $missing_dataset);

$page  = (isset($_GET['404_ignored'])) ? $_GET['404_ignored'] : 1;
$per_page = 10;
$ignored_dataset = array();
$total = 0;
if($ignored =  $GLOBALS['db']->select('CubeCart_404_log', false, array('ignore' => 1), array('created' => 'DESC'), $per_page, $page)) {
    $total = $GLOBALS['db']->count('CubeCart_404_log', false, array('ignore' => 1));
    $GLOBALS['smarty']->assign('PAGINATION_IGNORED', $GLOBALS['db']->pagination($total, $per_page, $page, 5, '404_ignored', 'missing_uris'));
    foreach($ignored as $m) {
        $m['display_uri']  = CC_ROOT_REL.ltrim($m['uri'], '/');
        $ignored_dataset[] = $m;
    }
}
$GLOBALS['smarty']->assign('IGNORED', $ignored_dataset);
$GLOBALS['main']->addTabControl($lang['settings']['ignored_uris'], 'ignored_uris');
$GLOBALS['gui']->addBreadcrumb($lang['navigation']['nav_redirects404s'], currentPage());

$page_content = $GLOBALS['smarty']->fetch('templates/settings.redirects.php');
