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

// The store shows a time moved by its own setting (formatTime()). A time typed in is read the
// same way round, so that what is asked for and what the list then says agree.
$news_offset = function () {
    $offset = (string)$GLOBALS['config']->get('config', 'time_offset');
    $value  = (int)substr($offset, 1);
    return (substr($offset, 0, 1) === '-') ? -$value : ((substr($offset, 0, 1) === '+') ? $value : 0);
};
$news_time_in = function ($text) use ($news_offset) {
    $text = is_scalar($text) ? trim((string)$text) : '';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?$/', $text)) {
        return 0;
    }
    $time = strtotime(str_replace('T', ' ', $text));
    return ($time === false) ? 0 : $time - $news_offset();
};
$news_time_out = function ($time) use ($news_offset) {
    return ((int)$time > 0) ? date('Y-m-d\TH:i', (int)$time + $news_offset()) : '';
};
// A line of plain text as it was typed: these are escaped where they are shown
$news_line = function ($field, $length) {
    $raw = isset($GLOBALS['RAW']['POST']['newsletter'][$field]) && is_scalar($GLOBALS['RAW']['POST']['newsletter'][$field]) ? (string)$GLOBALS['RAW']['POST']['newsletter'][$field] : '';
    $raw = trim(preg_replace('/\s+/', ' ', strip_tags($raw)));
    return function_exists('mb_substr') ? mb_substr($raw, 0, $length, 'UTF-8') : substr($raw, 0, $length);
};

// Saving, sending, pausing and the rest change the newsletter, which looking at the list does not
$news_action = isset($_GET['action']) && is_scalar($_GET['action']) ? strtolower((string)$_GET['action']) : '';
if (!empty($_POST['newsletter']) || in_array($news_action, array('send', 'pause', 'resume', 'cancel', 'unschedule'), true)) {
    Admin::getInstance()->permissions('customers', CC_PERM_EDIT, true);
}

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
        // A newsletter goes through Smarty for each subscriber. The editor refuses one that is not
        // valid before the form is sent; this is for a save that did not come through it (#4742)
        if (($smarty_problem = EmailEditor::smartyProblem($_POST['newsletter']['content_html'])) !== '') {
            $GLOBALS['main']->errorMessage(sprintf($lang['email']['editor_smarty_newsletter'], htmlspecialchars($smarty_problem, ENT_QUOTES, 'UTF-8')));
        }
        // The preview text and the name to greet by are plain lines. The subject and the preview text
        // go through Smarty too, as the content does, so a macro works in them and a stray brace does not.
        $_POST['newsletter']['preheader']     = $news_line('preheader', 255);
        $_POST['newsletter']['name_fallback'] = $news_line('name_fallback', 64);
        foreach (array(html_entity_decode((string)$_POST['newsletter']['subject'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $_POST['newsletter']['preheader']) as $line) {
            if ($line !== '' && ($smarty_problem = EmailEditor::smartyProblem($line)) !== '') {
                $GLOBALS['main']->errorMessage(sprintf($lang['email']['editor_smarty_newsletter'], htmlspecialchars($smarty_problem, ENT_QUOTES, 'UTF-8')));
            }
        }
        // When to send it: typed as the store's clock reads, kept as a moment in time. One that
        // has already passed is no time at all, and the newsletter goes when Send is pressed.
        $send_at = isset($_POST['newsletter']['send_at']) ? $news_time_in($_POST['newsletter']['send_at']) : 0;
        $_POST['newsletter']['send_at'] = ($send_at > time()) ? $send_at : 0;
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
            $queued = $GLOBALS['db']->select('CubeCart_newsletter', array('send_at'), array('newsletter_id' => (int)$_GET['newsletter_id']), false, 1, false, false);
            if ($queued && (int)$queued[0]['send_at'] > time()) {
                $GLOBALS['main']->successMessage(sprintf($lang['email']['notify_news_scheduled'], formatTime((int)$queued[0]['send_at'], false, true)));
            } else {
                $GLOBALS['main']->successMessage($lang['email']['notify_news_queued']);
            }
        } else {
            $GLOBALS['main']->errorMessage($lang['email']['error_news_queue']);
        }
    }
    httpredir('?_g=customers&node=email');
} elseif ($news_action == 'unschedule') {
    if (isset($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id'])) {
        if ($newsletter->unscheduleNewsletter((int)$_GET['newsletter_id'])) {
            $GLOBALS['main']->successMessage($lang['email']['notify_news_unscheduled']);
        } else {
            $GLOBALS['main']->errorMessage($lang['email']['error_news_unschedule']);
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
    } elseif (isset($_GET['copy']) && is_numeric($_GET['copy']) && ($content = $GLOBALS['db']->select('CubeCart_newsletter', array('subject', 'preheader', 'sender_name', 'sender_email', 'dbl_opt', 'template_id', 'content_html', 'name_fallback'), array('newsletter_id' => (int)$_GET['copy']))) !== false) {
        // A copy of another newsletter: everything it said, as a new draft that has not been saved yet
        $content[0]['subject']       = sprintf($lang['email']['news_copy_of'], $content[0]['subject']);
        $content[0]['newsletter_id'] = '';
        $content[0]['send_at']       = 0;
        $GLOBALS['gui']->addBreadcrumb($content[0]['subject'], currentPage());
        $GLOBALS['smarty']->assign('NEWSLETTER', $content[0]);
    } else {
        unset($content); // a copy that could not be found leaves this behind
        // A new newsletter greets by "there" where it does not know a name, until told otherwise
        $GLOBALS['smarty']->assign('NEWSLETTER', array('newsletter_id' => '', 'subject' => '', 'preheader' => '', 'sender_name' => '', 'sender_email' => '', 'content_html' => '', 'name_fallback' => $lang['email']['news_name_fallback_default'], 'send_at' => 0));
    }
    $GLOBALS['smarty']->assign('NEWSLETTER_SEND_AT', (isset($content) && !empty($content[0]['send_at']) && (int)$content[0]['send_at'] > time()) ? $news_time_out((int)$content[0]['send_at']) : '');
    $GLOBALS['smarty']->assign('NEWSLETTER_SEND_FROM', $news_time_out(time() + 600));
    // Get template list (form path only — used by the dropdown).
    if (($form_templates = $GLOBALS['db']->select('CubeCart_email_template', array('template_default', 'newsletter_default', 'template_id', 'title'))) !== false) {
        $existing_templates = array();
        foreach ($form_templates as $template) {
            if (isset($content)) {
                $template['selected'] = ($template['template_id'] == $content[0]['template_id']) ? ' selected="selected"' : '';
            } else {
                // A new newsletter starts in the template chosen for newsletters (Email Templates)
                $template['selected'] = ((int)$template['newsletter_default'] === 1) ? ' selected="selected"' : '';
            }
            $existing_templates[] = $template;
        }
        $GLOBALS['smarty']->assign('EXISTING_TEMPLATES', $existing_templates);
    }

    // What the wording of a newsletter can use (Mailer::assignStoreValues), for the editor's Macros menu
    $newsletter_macros = array();
    foreach (array('store_name' => '{$DATA.store_name}', 'store_url' => '{$DATA.storeURL}') as $macro_name => $macro_tag) {
        $newsletter_macros[] = array(
            'tag'         => $macro_tag,
            'label'       => EmailEditor::macroName($lang['email']['label_'.$macro_name]),
            'description' => $lang['email']['macro_'.$macro_name],
            'sample'      => ($macro_name === 'store_name') ? (string)$GLOBALS['config']->get('config', 'store_name') : (string)$GLOBALS['storeURL'],
        );
    }
    // And the one value that differs from reader to reader (Newsletter::_greet())
    array_unshift($newsletter_macros, array(
        'tag'         => '{$DATA.first_name}',
        'label'       => EmailEditor::macroName($lang['email']['label_subscriber_first_name']),
        'description' => $lang['email']['macro_subscriber_first_name'],
        'sample'      => html_entity_decode((string)Admin::getInstance()->get('name'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
    ));
    $GLOBALS['smarty']->assign('NEWSLETTER_MACROS', json_encode($newsletter_macros, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));

    // The template each choice in the list sends the newsletter in, for the editor to draw round
    // the wording (#4738). "None" is sent in the store's default template, so that is what it shows.
    $newsletter_frames = array();
    $frame_ids = array(0);
    if (is_array($form_templates)) {
        foreach ($form_templates as $template) {
            $frame_ids[] = (int)$template['template_id'];
        }
    }
    foreach ($frame_ids as $frame_id) {
        $frame = EmailEditor::frame('newsletter', ($frame_id > 0) ? $frame_id : false);
        if (!empty($frame['html'])) {
            $newsletter_frames[(string)$frame_id] = $frame;
        }
    }
    $frames_json = json_encode((object)$newsletter_frames, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    $GLOBALS['smarty']->assign('NEWSLETTER_FRAMES', ($frames_json !== false) ? $frames_json : '{}');
    $GLOBALS['smarty']->assign('NEWSLETTER_FRAMED', !empty($newsletter_frames));
    $GLOBALS['smarty']->assign('DISPLAY_FORM', true);
} else {
    $GLOBALS['main']->addTabControl($lang['email']['title_newsletters'], 'newsletter-list');
    $GLOBALS['main']->addTabControl($lang['email']['title_news_create'], false, currentPage(null, array('action' => 'add')));
    // Build a template_id => title map once so the list rows can show the
    // human-readable template name without an N+1 lookup.
    $template_titles = array();
    if (($list_templates = $GLOBALS['db']->select('CubeCart_email_template', array('template_id', 'title'))) !== false) {
        foreach ($list_templates as $t) {
            $template_titles[(int)$t['template_id']] = (string)$t['title'];
        }
    }
    // List newsletters, reverse chronology
    $has_active_send = false;
    $track_opens     = (bool)$GLOBALS['config']->get('config', 'email_track_opens');
    if (($contents = $GLOBALS['db']->select('CubeCart_newsletter', false)) !== false) {
        foreach ($contents as $content) {
            $nid = (int)$content['newsletter_id'];
            $content['copy']       = currentPage(null, array('action' => 'add', 'copy' => $nid));
            $content['unschedule'] = currentPage(null, array('action' => 'unschedule', 'newsletter_id' => $nid, 'token' => SESSION_TOKEN));
            $content['template_title'] = isset($template_titles[(int)$content['template_id']]) ? $template_titles[(int)$content['template_id']] : '';
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
            // A newsletter waiting for its time is not in flight: nothing about it changes until then.
            $send_at   = isset($content['send_at']) ? (int)$content['send_at'] : 0;
            $scheduled = ($status === 2 && $send_at > time() && (int)$content['sent_count'] === 0);
            if (in_array($status, array(2, 3), true) && !$scheduled) {
                $has_active_send = true;
            }
            $content['can_send']   = ($status === 0);
            $content['can_pause']  = ($status === 3);
            $content['can_resume'] = ($status === 5);
            $content['can_cancel'] = in_array($status, array(2, 3, 5), true) && !$scheduled;
            $content['can_unschedule'] = $scheduled;
            // A draft with a time ahead of it is scheduled by the same button that would send it
            $content['send_label']   = ($status === 0 && $send_at > time()) ? $lang['email']['news_schedule'] : $lang['common']['send'];
            $content['send_confirm'] = ($status === 0 && $send_at > time()) ? sprintf($lang['email']['confirm_schedule'], formatTime($send_at, false, true)) : $lang['email']['confirm_send'];
            // What came of it, once anything has gone out
            $content['results'] = '';
            if ((int)$content['sent_count'] > 0) {
                $sent_to = (int)$content['sent_count'];
                $results = array(sprintf($lang['email']['news_results_sent'], number_format($sent_to)));
                // No count at all means it went out before these were kept, which is not the same as none
                if (isset($content['opened_count']) && ($track_opens || (int)$content['opened_count'] > 0)) {
                    $results[] = sprintf($lang['email']['news_results_opened'], number_format((int)$content['opened_count']), round((int)$content['opened_count'] / $sent_to * 100));
                }
                if (isset($content['unsubscribed_count'])) {
                    $results[] = sprintf($lang['email']['news_results_unsubscribed'], number_format((int)$content['unsubscribed_count']));
                }
                $content['results'] = implode(' &middot; ', $results);
            }
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
                    $content['status_text'] = $scheduled ? sprintf($lang['email']['news_status_scheduled'], formatTime($send_at, false, true)) : sprintf($lang['email']['news_status_queued'], (int)$content['total_subscribers']);
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
    $GLOBALS['smarty']->assign('NEWSLETTER_CAN_EDIT', (bool)Admin::getInstance()->permissions('customers', CC_PERM_EDIT));

    // Throttle banner: only computed when something is actually in flight.
    // Reads from the same email_log table the cron uses, so the numbers shown to admin
    // and the numbers the cron is gating on are identical. Counts ALL outgoing email
    // (newsletter + transactional) — host SMTP caps don't discriminate.
    if ($has_active_send) {
        global $glob;
        $pfx = $GLOBALS['config']->get('config', 'dbprefix');
        $hourly_limit = isset($glob['newsletter_hourly_limit']) ? (int)$glob['newsletter_hourly_limit'] : 200;
        if ($hourly_limit <= 0) {
            $hourly_limit = 200;
        }
        $rows = $GLOBALS['db']->misc(sprintf(
            "SELECT COUNT(*) AS c, UNIX_TIMESTAMP(MIN(`date`)) AS oldest FROM `%sCubeCart_email_log` WHERE `date` >= (NOW() - INTERVAL 1 HOUR)",
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
if (($all_templates = $GLOBALS['db']->select('CubeCart_email_template', array('template_id', 'content_html', 'template_default'))) !== false) {
    $template_map = array();
    foreach ($all_templates as $t) {
        $template_map[(int)$t['template_id']] = (string)$t['content_html'];
        if ((int)$t['template_default'] === 1) {
            // A newsletter with no template of its own is sent in the default one
            $template_map[0] = (string)$t['content_html'];
        }
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
    'first_name'     => (string)Admin::getInstance()->get('name'), // whoever is looking stands in for the reader
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
