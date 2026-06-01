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
Admin::getInstance()->permissions('customers', CC_PERM_READ, true);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

## Decision actions (accept / reject / mark refunded / resend ack)
if ($id > 0 && !empty($_POST['decision_action'])) {
    Admin::getInstance()->permissions('customers', CC_PERM_EDIT, true);
    $row = $GLOBALS['db']->select('CubeCart_withdrawal_requests', false, array('id' => $id), false, 1);
    if (!$row) {
        $GLOBALS['main']->errorMessage('Withdrawal request not found.');
        httpredir('?_g=customers&node=withdrawals');
    }
    $row = $row[0];
    $action = (string)$_POST['decision_action'];
    $note = trim((string)($_POST['decision_note'] ?? ''));
    $now = time();
    $admin_id = (int)Admin::getInstance()->get('admin_id');
    $new_status = null;
    if ($action === 'accept') {
        $new_status = 'accepted';
    } elseif ($action === 'reject') {
        $new_status = 'rejected';
    } elseif ($action === 'refunded') {
        $new_status = 'refunded';
    } elseif ($action === 'resend_ack') {
        // Resend the original acknowledgment to the consumer
        $reference = 'W-'.str_pad((string)$row['id'], 5, '0', STR_PAD_LEFT);
        $mailer = new Mailer();
        if (($content = $mailer->loadContent('cart.withdrawal_acknowledgment', $row['lang'])) !== false) {
            $GLOBALS['smarty']->assign('DATA', array(
                'name'              => $row['name'],
                'email'             => $row['email'],
                'reference'         => $reference,
                'submitted_at'      => formatTime((int)$row['submitted_at']),
                'cart_order_id'     => $row['cart_order_id'],
                'reported_delivery' => $row['reported_delivery'],
                'statement'         => $row['statement'],
                'reason'            => $row['reason'],
                'store_name'        => $GLOBALS['config']->get('config', 'store_name'),
            ));
            if ($mailer->sendEmail($row['email'], $content)) {
                $GLOBALS['db']->update('CubeCart_withdrawal_requests', array('acknowledged_at' => $now), array('id' => $id));
                $GLOBALS['main']->successMessage('Acknowledgment resent.');
            } else {
                $GLOBALS['main']->errorMessage('Failed to resend acknowledgment.');
            }
        }
        httpredir('?_g=customers&node=withdrawals&id='.$id);
    }

    if ($new_status !== null) {
        $update = array(
            'status'        => $new_status,
            'decision_at'   => $now,
            'decision_by'   => $admin_id,
            'decision_note' => $note,
        );
        if ($new_status === 'refunded') {
            $update['refunded_at'] = $now;
        }
        $GLOBALS['db']->update('CubeCart_withdrawal_requests', $update, array('id' => $id));

        // Notify consumer of the decision on a durable medium
        $reference = 'W-'.str_pad((string)$row['id'], 5, '0', STR_PAD_LEFT);
        $mailer = new Mailer();
        if (($content = $mailer->loadContent('cart.withdrawal_decision', $row['lang'])) !== false) {
            $GLOBALS['smarty']->assign('DATA', array(
                'name'          => $row['name'],
                'reference'     => $reference,
                'cart_order_id' => $row['cart_order_id'],
                'status'        => $new_status,
                'decision_at'   => formatTime($now),
                'note'          => $note,
                'store_name'    => $GLOBALS['config']->get('config', 'store_name'),
            ));
            $mailer->sendEmailAsync($row['email'], $content);
        }
        $GLOBALS['main']->successMessage('Decision recorded — consumer notified.');
        httpredir('?_g=customers&node=withdrawals&id='.$id);
    }
}

if ($id > 0) {
    ## Detail view
    $row = $GLOBALS['db']->select('CubeCart_withdrawal_requests', false, array('id' => $id), false, 1);
    if (!$row) {
        $GLOBALS['main']->errorMessage('Withdrawal request not found.');
        httpredir('?_g=customers&node=withdrawals');
    }
    $row = $row[0];
    $row['reference'] = 'W-'.str_pad((string)$row['id'], 5, '0', STR_PAD_LEFT);
    $row['status_label'] = $GLOBALS['language']->withdrawal['status_'.$row['status']] ?? $row['status'];
    $row['submitted_at_human']    = $row['submitted_at']    ? formatTime((int)$row['submitted_at']) : '';
    $row['acknowledged_at_human'] = $row['acknowledged_at'] ? formatTime((int)$row['acknowledged_at']) : '';
    $row['decision_at_human']     = $row['decision_at']     ? formatTime((int)$row['decision_at']) : '';
    $row['refunded_at_human']     = $row['refunded_at']     ? formatTime((int)$row['refunded_at']) : '';
    $row['decided_by_human'] = '';
    if ((int)$row['decision_by'] > 0) {
        $admin = $GLOBALS['db']->select('CubeCart_admin_users', array('username', 'name'), array('admin_id' => (int)$row['decision_by']), false, 1);
        if ($admin) {
            $row['decided_by_human'] = !empty($admin[0]['name']) ? $admin[0]['name'] : $admin[0]['username'];
        }
    }
    // Order + status history if the request references a known order
    $order_summary = false;
    $order_history = array();
    if (!empty($row['cart_order_id'])) {
        $os = $GLOBALS['db']->select('CubeCart_order_summary', array('cart_order_id', 'order_date', 'status', 'total', 'ship_tracking', 'first_name', 'last_name', 'email'), array('cart_order_id' => $row['cart_order_id']), false, 1);
        if ($os) {
            $order_summary = $os[0];
            $order_summary['order_date_human'] = formatTime((int)$order_summary['order_date']);
            $order_summary['status_text'] = $GLOBALS['language']->order_state['name_'.(int)$order_summary['status']];
        }
        $oh = $GLOBALS['db']->select('CubeCart_order_history', array('status', 'updated', 'initiator'), array('cart_order_id' => $row['cart_order_id']), array('history_id' => 'ASC'));
        if ($oh) {
            foreach ($oh as $h) {
                $order_history[] = array(
                    'status_text' => $GLOBALS['language']->order_state['name_'.(int)$h['status']],
                    'updated_human' => formatTime((int)$h['updated']),
                    'initiator' => $h['initiator'],
                );
            }
        }
    }
    $GLOBALS['smarty']->assign('ROW', $row);
    $GLOBALS['smarty']->assign('ORDER_SUMMARY', $order_summary);
    $GLOBALS['smarty']->assign('ORDER_HISTORY', $order_history);
    $page_content = $GLOBALS['smarty']->fetch('templates/customers.withdrawals.detail.php');
} else {
    ## List view
    $filter_status = isset($_GET['filter_status']) ? (string)$_GET['filter_status'] : '';
    $where = false;
    if (in_array($filter_status, array('new','accepted','rejected','refunded'), true)) {
        $where = array('status' => $filter_status);
    }
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $per_page = 25;
    $rows = $GLOBALS['db']->select(
        'CubeCart_withdrawal_requests',
        array('id', 'cart_order_id', 'name', 'email', 'submitted_at', 'status'),
        $where,
        array('submitted_at' => 'DESC'),
        $per_page,
        $page,
        false
    );
    $total = $GLOBALS['db']->getFoundRows();
    $list = array();
    if ($rows) {
        foreach ($rows as $r) {
            $list[] = array(
                'id'                  => (int)$r['id'],
                'reference'           => 'W-'.str_pad((string)$r['id'], 5, '0', STR_PAD_LEFT),
                'cart_order_id'       => $r['cart_order_id'],
                'name'                => $r['name'],
                'email'                => $r['email'],
                'submitted_at_human'  => formatTime((int)$r['submitted_at']),
                'status'              => $r['status'],
                'status_label'        => $GLOBALS['language']->withdrawal['status_'.$r['status']] ?? $r['status'],
            );
        }
    }
    $GLOBALS['smarty']->assign('ROWS', $list);
    $GLOBALS['smarty']->assign('FILTER_STATUS', $filter_status);
    $GLOBALS['smarty']->assign('PAGINATION', $GLOBALS['db']->pagination($total, $per_page, $page));
    $page_content = $GLOBALS['smarty']->fetch('templates/customers.withdrawals.index.php');
}

// Count "new" requests so the tab carries a badge when there's work waiting.
$new_count = (int)$GLOBALS['db']->count('CubeCart_withdrawal_requests', 'id', array('status' => 'new'));
$GLOBALS['main']->addTabControl($lang['withdrawal']['admin_section_title'], 'general', null, null, $new_count ?: null);
