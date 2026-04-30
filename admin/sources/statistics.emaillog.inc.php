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
Admin::getInstance()->permissions('statistics', CC_PERM_READ, true);

if (isset($_GET['reset']) && !empty($_GET['reset'])) {
    $GLOBALS['session']->delete('email_filter');
    httpredir('?_g=statistics&node=emaillog');
}

if (isset($_GET['resend']) && $_GET['resend']>0 && isset($_GET['token']) && $_GET['token'] === SESSION_TOKEN) {
    $email_data = $GLOBALS['db']->select('CubeCart_email_log', false, array('id' => (int)$_GET['resend']));

    if ($email_data) {
        // Resend bypasses Mailer::sendEmail() because the stored content is already
        // template-wrapped and contains literal `{`/`}` from CSS/JSON-LD that Smarty's
        // string-fetch would choke on. Manual mailer path, but with a freshly generated
        // open-tracking token so the new log row tracks independently of the original.
        $mailer = new Mailer();
        $mailer->Subject = $email_data[0]['subject'];

        $body_html = $email_data[0]['content_html'];
        $new_token = null;
        if (!empty($body_html) && $GLOBALS['config']->get('config', 'email_track_opens')) {
            $new_token = bin2hex(random_bytes(16));
            $new_pixel_url = rtrim($GLOBALS['storeURL'], '/').'/track/open.php?t='.$new_token;
            $new_pixel_tag = '<img src="'.htmlspecialchars($new_pixel_url, ENT_QUOTES).'" width="1" height="1" alt="" border="0" style="display:none;max-height:0;visibility:hidden;overflow:hidden;mso-hide:all;">';
            // Replace any existing tracking pixel so the resend doesn't ping both rows
            $stripped = preg_replace('#<img[^>]+/track/open\.php\?t=[^"\']*["\'][^>]*>#i', '', $body_html);
            if ($stripped !== null) {
                $body_html = $stripped;
            }
            if (stripos($body_html, '</body>') !== false) {
                $body_html = str_ireplace('</body>', $new_pixel_tag.'</body>', $body_html);
            } else {
                $body_html .= $new_pixel_tag;
            }
        }

        if (empty($body_html)) {
            $mailer->IsHTML(false);
            $mailer->Body = $email_data[0]['content_text'];
        } else {
            $mailer->Body = $body_html;
            $mailer->AltBody = $email_data[0]['content_text'];
        }

        $recipients = explode(',', $email_data[0]['to']);
        foreach ($recipients as $recipient) {
            if ($parts = User::getEmailAddressParts($recipient)) {
                $mailer->AddAddress($parts['email']);
            }
        }
        if ($from = User::getEmailAddressParts($email_data[0]['from'])) {
            $mailer->Sender = $from['email'];
        }

        $send_result = $mailer->Send();

        // Build the new log row from scratch (don't carry tracking state from the original).
        $new_row = array(
            'subject'          => $email_data[0]['subject'],
            'content_html'     => $body_html,
            'content_text'     => $email_data[0]['content_text'],
            'to'               => $email_data[0]['to'],
            'from'             => $email_data[0]['from'],
            'result'           => $send_result ? 1 : 0,
            'email_method'     => $email_data[0]['email_method'],
            'email_content_id' => $email_data[0]['email_content_id'],
            'attachment'       => $email_data[0]['attachment'],
            'fail_reason'      => !empty($mailer->ErrorInfo) ? htmlspecialchars($mailer->ErrorInfo, ENT_QUOTES) : '',
            'tracking_token'   => $new_token,
        );
        $GLOBALS['db']->insert('CubeCart_email_log', $new_row);

        if ($send_result) {
            $GLOBALS['main']->successMessage(sprintf($lang['statistics']['email_resent'], htmlspecialchars($email_data[0]['subject']), htmlspecialchars($email_data[0]['to'])));
        } else {
            $GLOBALS['main']->errorMessage($lang['statistics']['email_not_resent']);
        }
        httpredir(currentPage(array('resend', 'token')));
    }
}

if (isset($_POST['email_filter'])) {
    $raw_filter = trim((string)$_POST['email_filter']);
    if ($raw_filter === '') {
        $GLOBALS['session']->delete('email_filter');
    } elseif (preg_match('/^[a-z0-9._@+-]+$/i', $raw_filter)) {
        // Anchored regex: the WHOLE input must be a plausible email-fragment.
        // Stops a previously-undetected injection vector where unanchored validation
        // accepted any string with at least one alphanum/punct character — the value
        // was then interpolated directly into the LIKE clause below.
        $GLOBALS['session']->set('email_filter', $raw_filter);
    }
}

$GLOBALS['main']->addTabControl($lang['settings']['title_email_log'], 'email_log');
$GLOBALS['gui']->addBreadcrumb($lang['settings']['title_email_log'], currentPage());

if ($GLOBALS['session']->has('email_filter') && $email_filter = $GLOBALS['session']->get('email_filter')) {
    $GLOBALS['smarty']->assign('EMAIL_FILTER', $email_filter);
    if (filter_var($email_filter, FILTER_VALIDATE_EMAIL)) {
        $where = array('to' => $email_filter);
    } else {
        // Belt-and-braces: the value is already constrained by the anchored regex on
        // assign, but escape again so any future code path that stores looser data
        // still can't inject through this LIKE clause.
        $where = "`to` LIKE '%".$GLOBALS['db']->sqlSafe($email_filter)."%'";
    }
} else {
    $where = false;
}

$per_page = 25;
$page = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;
$email_logs = $GLOBALS['db']->select('CubeCart_email_log', false, $where, array('date' => 'DESC'), $per_page, $page, false);
$email_log = array();
$count = $GLOBALS['db']->getFoundRows();
// Friendly labels for the email_method column. Hardcoded universal acronyms — no
// lang strings — so display is correct under any installed language. Unknown values
// (e.g. plugin-injected methods like 'sendgrid_api') fall through to the raw value.
$method_labels = array(
    'mail'     => 'PHP mail()',
    'phpmail'  => 'PHP mail()',
    'smtp'     => 'SMTP',
    'smtp_ssl' => 'SMTP (SSL)',
    'smtp_tls' => 'SMTP (TLS)',
);

if ($email_logs!==false) {
    foreach ($email_logs as $row) {
        $row['to_email'] = array();
        $row['email_method_label'] = isset($method_labels[$row['email_method']])
            ? $method_labels[$row['email_method']]
            : $row['email_method'];
        $row['to'] = explode(',', $row['to']);
        foreach($row['to'] as $value) {
            if($to = User::getEmailAddressParts($value)) {
                $row['to_email'][] = array(
                    'email' => $to['email'],
                    'name' => $to['name']
                );
            }
        }
        $attachments = json_decode($row['attachment'] ?? '', true);
        if(is_array($attachments) && !empty($attachments)) {
            $row['attachment'] = array();
            foreach ($attachments as $file) {
                if (file_exists(CC_FILES_DIR.'attachments/'.$file)) {
                    $row['attachment'][] = array(
                        'name' => $file,
                        'download_file' => base64_encode('files/attachments/'.$file)
                    );
                }
            }
        } else {
            $row['attachment'] = array();
        }

        if($from = User::getEmailAddressParts($row['from'])) {
            $row['from_name'] = $from['name'];
            $row['from_email'] = $from['email'];
            $email_log[] = $row;
        }
    }
}

$GLOBALS['smarty']->assign('EMAIL_LOG', $email_log);
$GLOBALS['smarty']->assign('PAGINATION_EMAIL_LOG', $GLOBALS['db']->pagination($count, $per_page, $page, 5, 'page', 'email_log'));

$page_content = $GLOBALS['smarty']->fetch('templates/statistics.emaillog.php');
