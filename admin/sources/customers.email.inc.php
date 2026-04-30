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


$GLOBALS['gui']->addBreadcrumb($lang['email']['title_newsletters'], currentPage(array('action', 'newsletter_id')));

$seo  = SEO::getInstance();
$newsletter = Newsletter::getInstance();

if (isset($_POST['newsletter']) && !empty($_POST['newsletter'])) {
    $redirect = false;
    $proceed = true;

    if (empty($_POST['newsletter']['subject'])) {
        $proceed = false;
        $GLOBALS['main']->errorMessage($lang['email']['error_no_subject']);
    }
    if (empty($_POST['newsletter']['content_html'])) {
        $proceed = false;
        $GLOBALS['main']->errorMessage($lang['email']['error_no_message']);
    }
    if ($proceed) {
        $_POST['newsletter']['content_html'] = $GLOBALS['RAW']['POST']['newsletter']['content_html'];
        if ($newsletter->saveNewsletter($_POST['newsletter'])) {
            $redirect = true;
            $_POST['newsletter']['newsletter_id'] = (!empty($_POST['newsletter']['newsletter_id'])) ? $_POST['newsletter']['newsletter_id'] : $newsletter->_newsletter_id;
            $GLOBALS['main']->successMessage($lang['email']['notify_news_save']);
        } else {
            $GLOBALS['main']->errorMessage($lang['email']['error_news_save']);
        }
        if (isset($_POST['newsletter']['test_email']) && !empty($_POST['newsletter']['test_email'])) {
            if ($newsletter->sendTest($_POST['newsletter']['newsletter_id'], $_POST['newsletter']['test_email'])) {
                $GLOBALS['main']->successMessage($lang['email']['notify_news_test_sent']);
            }
        }
        if ($redirect) {
            httpredir('?_g=customers&node=email');
        }
    }
}

if (isset($_GET['action']) && strtolower($_GET['action']) == 'delete') {
    if (Admin::getInstance()->permissions('customers', CC_PERM_DELETE) && $newsletter->deleteNewsletter($_GET['newsletter_id'])) {
        $GLOBALS['main']->successMessage($lang['email']['notify_news_delete']);
    } else {
        $GLOBALS['main']->errorMessage($lang['email']['error_news_delete']);
    }
    httpredir(currentPage(array('newsletter_id', 'action')));
} elseif (isset($_GET['action']) && strtolower($_GET['action']) == 'send') {
    if (isset($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id'])) {
        if ($newsletter->queueNewsletter((int)$_GET['newsletter_id'])) {
            $GLOBALS['main']->successMessage($lang['email']['notify_news_queued']);
        } else {
            $GLOBALS['main']->errorMessage($lang['email']['error_news_queue']);
        }
    }
    httpredir('?_g=customers&node=email');
} elseif (isset($_GET['action']) && strtolower($_GET['action']) == 'pause') {
    if (isset($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id'])) {
        if ($newsletter->pauseNewsletter((int)$_GET['newsletter_id'])) {
            $GLOBALS['main']->successMessage($lang['email']['notify_news_paused']);
        } else {
            $GLOBALS['main']->errorMessage($lang['email']['error_news_pause']);
        }
    }
    httpredir('?_g=customers&node=email');
} elseif (isset($_GET['action']) && strtolower($_GET['action']) == 'resume') {
    if (isset($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id'])) {
        if ($newsletter->resumeNewsletter((int)$_GET['newsletter_id'])) {
            $GLOBALS['main']->successMessage($lang['email']['notify_news_resumed']);
        } else {
            $GLOBALS['main']->errorMessage($lang['email']['error_news_resume']);
        }
    }
    httpredir('?_g=customers&node=email');
} elseif (isset($_GET['action']) && strtolower($_GET['action']) == 'cancel') {
    if (isset($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id'])) {
        if ($newsletter->cancelNewsletter((int)$_GET['newsletter_id'])) {
            $GLOBALS['main']->successMessage($lang['email']['notify_news_cancelled']);
        } else {
            $GLOBALS['main']->errorMessage($lang['email']['error_news_cancel']);
        }
    }
    httpredir('?_g=customers&node=email');
} elseif (isset($_GET['action']) && in_array(strtolower($_GET['action']), array('add', 'edit'))) {
    Admin::getInstance()->permissions('customers', CC_PERM_EDIT, true);

    $GLOBALS['main']->addTabControl($lang['common']['general'], 'general');
    $GLOBALS['main']->addTabControl($lang['email']['title_content_html'], 'email_html');
    $GLOBALS['main']->addTabControl($lang['email']['title_send_test'], 'send_test');
    if (isset($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id'])) {
        if (($content = $GLOBALS['db']->select('CubeCart_newsletter', false, array('newsletter_id' => (int)$_GET['newsletter_id']))) !== false) {
            // Render editor window
            $GLOBALS['gui']->addBreadcrumb($content[0]['subject'], currentPage());
            $GLOBALS['smarty']->assign('NEWSLETTER', $content[0]);
        } else {
            httpredir(currentPage(array('newsletter_id')));
        }
    }
    // Get template list (form path only — used by the dropdown).
    if (($form_templates = $GLOBALS['db']->select('CubeCart_email_template', array('template_default', 'template_id', 'title'))) !== false) {
        $existing_templates = array();
        foreach ($form_templates as $template) {
            if (isset($content)) {
                $template['selected'] = ($template['template_id'] == $content[0]['template_id']) ? ' selected="selected"' : '';
            } else {
                $template['selected'] = '';
            }
            $existing_templates[] = $template;
        }
        $GLOBALS['smarty']->assign('EXISTING_TEMPLATES', $existing_templates);
    }

    $GLOBALS['smarty']->assign('DISPLAY_FORM', true);
} else {
    $GLOBALS['main']->addTabControl($lang['email']['title_newsletters'], 'newsletter-list');
    $GLOBALS['main']->addTabControl($lang['email']['title_news_create'], false, currentPage(null, array('action' => 'add')));
    // List newsletters, reverse chronology
    $has_active_send = false;
    if (($contents = $GLOBALS['db']->select('CubeCart_newsletter', false)) !== false) {
        foreach ($contents as $content) {
            $nid = (int)$content['newsletter_id'];
            $content['edit']   = currentPage(null, array('action' => 'edit',   'newsletter_id' => $nid));
            $content['send']   = currentPage(null, array('action' => 'send',   'newsletter_id' => $nid, 'token' => SESSION_TOKEN));
            $content['delete'] = currentPage(null, array('action' => 'delete', 'newsletter_id' => $nid, 'token' => SESSION_TOKEN));
            $content['pause']  = currentPage(null, array('action' => 'pause',  'newsletter_id' => $nid, 'token' => SESSION_TOKEN));
            $content['resume'] = currentPage(null, array('action' => 'resume', 'newsletter_id' => $nid, 'token' => SESSION_TOKEN));
            $content['cancel'] = currentPage(null, array('action' => 'cancel', 'newsletter_id' => $nid, 'token' => SESSION_TOKEN));
            // formatTime() expects a unix timestamp; the column is a TIMESTAMP string.
            $content['date_created_formatted'] = !empty($content['date_created']) ? formatTime(strtotime($content['date_created'])) : '';
            // Decorate row with available actions and a human-readable status. The action
            // buttons that show depend on which transitions are legal from the current state.
            $status = (int)$content['status'];
            // Trigger auto-refresh on the list view if anything in flight — queued + sending only.
            // Paused/cancelled/sent/draft don't change without admin action so polling is wasted.
            if (in_array($status, array(2, 3), true)) {
                $has_active_send = true;
            }
            $content['can_send']   = ($status === 0);
            $content['can_pause']  = ($status === 3);
            $content['can_resume'] = ($status === 5);
            $content['can_cancel'] = in_array($status, array(2, 3, 5), true);
            // Edit only when nothing is in flight — draft (0) or paused (5).
            // Editing mid-send would change content for future batches but not
            // already-delivered ones, leaving recipients with mismatched messages.
            $content['can_edit']   = in_array($status, array(0, 5), true);
            // Delete only from terminal states — draft (0), sent (1), cancelled (4).
            // Deleting queued/sending/paused orphans the cron task and loses cursor
            // state; admin should Cancel first to land in status 4.
            $content['can_delete'] = in_array($status, array(0, 1, 4), true);
            switch ($status) {
                case 1:
                    $content['status_text'] = $lang['email']['news_status_sent'];
                    break;
                case 2:
                    $content['status_text'] = sprintf($lang['email']['news_status_queued'], (int)$content['total_subscribers']);
                    break;
                case 3:
                    $content['status_text'] = sprintf($lang['email']['news_status_sending'], (int)$content['sent_count'], (int)$content['total_subscribers']);
                    break;
                case 4:
                    $content['status_text'] = sprintf($lang['email']['news_status_cancelled'], (int)$content['sent_count'], (int)$content['total_subscribers']);
                    break;
                case 5:
                    $content['status_text'] = sprintf($lang['email']['news_status_paused'], (int)$content['sent_count'], (int)$content['total_subscribers']);
                    break;
                default:
                    $content['status_text'] = $lang['email']['news_status_draft'];
            }
            $smarty_data['newsletters'][] = $content;
        }
        $GLOBALS['smarty']->assign('NEWSLETTERS', $smarty_data['newsletters']);
    }
    $GLOBALS['smarty']->assign('NEWSLETTER_AUTO_REFRESH', $has_active_send);

    // Throttle banner: only computed when something is actually in flight.
    // Reads the rolling-hour counter from the same send_log table the cron uses, so
    // the numbers shown to admin and the numbers the cron is gating on are identical.
    if ($has_active_send) {
        global $glob;
        $pfx = $GLOBALS['config']->get('config', 'dbprefix');
        $hourly_limit = isset($glob['newsletter_hourly_limit']) ? (int)$glob['newsletter_hourly_limit'] : 200;
        if ($hourly_limit <= 0) {
            $hourly_limit = 200;
        }
        $rows = $GLOBALS['db']->misc(sprintf(
            "SELECT COUNT(*) AS c, UNIX_TIMESTAMP(MIN(sent_at)) AS oldest FROM `%sCubeCart_newsletter_send_log` WHERE sent_at >= (NOW() - INTERVAL 1 HOUR)",
            $pfx
        ));
        $sent_last_hour = isset($rows[0]['c']) ? (int)$rows[0]['c'] : 0;
        $oldest_in_window = isset($rows[0]['oldest']) ? (int)$rows[0]['oldest'] : 0;

        // Next batch ETA: assumes the standard */5 crontab — the cron tick is the
        // binding constraint, not the task's `frequency`. Show clock time, not "≤X min",
        // so the admin can glance at their own clock.
        $next_tick_ts = (int)(ceil(time() / 300) * 300);

        $throttled = ($sent_last_hour >= $hourly_limit);
        $banner = array(
            'throttled'           => $throttled,
            'status_text'         => sprintf(
                $throttled ? $lang['email']['throttle_status_capped'] : $lang['email']['throttle_status'],
                $sent_last_hour, $hourly_limit
            ),
            'next_batch_text'     => sprintf($lang['email']['throttle_next_batch'], date('H:i', $next_tick_ts)),
            'window_resets_text'  => ($throttled && $oldest_in_window > 0)
                ? sprintf($lang['email']['throttle_window_resets'], date('H:i', $oldest_in_window + 3600))
                : '',
        );
        $GLOBALS['smarty']->assign('THROTTLE_BANNER', $banner);
    }

    $GLOBALS['smarty']->assign('DISPLAY_LIST', true);
}

// ----------------------------------------------------------------------
// Preview support (shared by the editor's Preview button and the list's
// magnifier icon). We expose:
//   * NEWSLETTER_TEMPLATES_JSON — { template_id: content_html, ... }
//   * PREVIEW_MACROS_JSON       — values for {$DATA.X} substitution
//   * NEWSLETTERS_PREVIEW_JSON  — { newsletter_id: {subject, content_html, template_id}, ... }
// ----------------------------------------------------------------------
if (($all_templates = $GLOBALS['db']->select('CubeCart_email_template', array('template_id', 'content_html'))) !== false) {
    $template_map = array();
    foreach ($all_templates as $t) {
        $template_map[(int)$t['template_id']] = (string)$t['content_html'];
    }
    $tmpl_json = json_encode($template_map, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($tmpl_json !== false) {
        $GLOBALS['smarty']->assign('NEWSLETTER_TEMPLATES_JSON', $tmpl_json);
    }
}

$preview_macros = array(
    'logoURL'        => $GLOBALS['gui']->getLogo(true, 'emails'),
    'store_name'     => $GLOBALS['config']->get('config', 'store_name'),
    'storeName'      => $GLOBALS['config']->get('config', 'store_name'),
    'storeURL'       => $GLOBALS['storeURL'],
    'unsubscribeURL' => $GLOBALS['storeURL'].'/index.php?_a=unsubscribe',
    'jsonLd'         => '',
);
$macros_json = json_encode($preview_macros, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
if ($macros_json !== false) {
    $GLOBALS['smarty']->assign('PREVIEW_MACROS_JSON', $macros_json);
}

if (!empty($smarty_data['newsletters'])) {
    $news_preview = array();
    foreach ($smarty_data['newsletters'] as $row) {
        $news_preview[(int)$row['newsletter_id']] = array(
            'subject'      => (string)$row['subject'],
            'content_html' => (string)$row['content_html'],
            'template_id'  => (int)$row['template_id'],
        );
    }
    $news_json = json_encode($news_preview, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($news_json !== false) {
        $GLOBALS['smarty']->assign('NEWSLETTERS_PREVIEW_JSON', $news_json);
    }
}

$page_content = $GLOBALS['smarty']->fetch('templates/customers.email.php');
