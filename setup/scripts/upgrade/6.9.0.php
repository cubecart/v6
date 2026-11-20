<?php
// #4732 - The Zendesk support widget has gone; upgrades overlay files, so remove its class.
@unlink(CC_ROOT_DIR.'/classes/supportwidget.class.php');

/**
 * #4738 - From 6.7.0 the email template editor rearranged the document on every save.
 * The <head> was left empty, its <meta> and <style> went to the top of the <body>, and
 * the title, base URL, structured data and the whole layout ended up after </html>.
 * Mail clients mostly coped, but the plain text part of every email then began with
 * the template's title. The editor is fixed; this puts templates saved that way back.
 *
 * Only a template in exactly that shape is touched, and only by moving text: the last
 * check refuses any result that is not the same characters in a different order.
 *
 * @param string $html
 * @return string|false The repaired document, or false when there is nothing to repair
 */
if (!function_exists('cc_repair_email_template_head')) {
    function cc_repair_email_template_head($html)
    {
        $html = (string)$html;
        // An empty head, a body, and then more after the document has ended
        if (!preg_match('#^(.*?<head\b[^>]*>)\s*</head>\s*(<body\b[^>]*>)(.*?)</body>\s*</html>(.*)$#is', $html, $doc)) {
            return false;
        }
        list(, $start, $body_tag, $in_body, $after) = $doc;
        if (trim($after) === '') {
            return false;
        }
        $head_element = '<meta\b[^>]*>|<link\b[^>]*>|<base\b[^>]*>|<style\b[^>]*>.*?</style>|<title\b[^>]*>.*?</title>';
        // All that was left in the body is the start of the head
        if (!preg_match('#^(?:\s*(?:'.$head_element.'))*\s*$#is', $in_body)) {
            return false;
        }
        // The rest of the head comes first after </html>, and the real body follows it
        if (!preg_match('#^((?:\s*(?:'.$head_element.'|\{\$DATA\.jsonLd\}))*)(.*)$#is', $after, $tail)) {
            return false;
        }
        $eol  = (strpos($html, "\r\n") !== false) ? "\r\n" : "\n";
        $head = trim(trim($in_body).$eol.trim($tail[1]));
        $body = trim($tail[2]);
        if ($body === '' || preg_match('#</body>|</html>|<head\b|<body\b#i', $body)) {
            return false;
        }
        $fixed = rtrim($start).$eol.($head === '' ? '' : $head.$eol).'</head>'.$eol.$body_tag.$eol.$body.$eol.'</body>'.$eol.'</html>';
        $letters = function ($string) {
            return count_chars(preg_replace('/\s+/', '', $string), 1);
        };
        return ($letters($fixed) === $letters($html)) ? $fixed : false;
    }
}

// The two repairs below read text and write it back, so the connection has to carry every
// character. The installer leaves it on the server's default, and where that is latin1
// anything outside it (Greek, Cyrillic, Japanese, an emoji) is read as a question mark and
// would be stored as one. So they run on utf8mb4, and do not run at all without it.
$email_names_before = $db->misc('SELECT @@character_set_client AS `client`, @@character_set_results AS `results`, @@character_set_connection AS `connection`', false);
$db->misc('SET NAMES utf8mb4', false);
$email_names_now = $db->misc('SELECT @@character_set_client AS `client`, @@character_set_results AS `results`', false);
$email_repairs   = (is_array($email_names_now) && $email_names_now[0]['client'] === 'utf8mb4' && $email_names_now[0]['results'] === 'utf8mb4');

$email_templates = $email_repairs ? $db->select('CubeCart_email_template', array('template_id', 'content_html')) : false;
if (is_array($email_templates)) {
    foreach ($email_templates as $email_template) {
        $repaired = cc_repair_email_template_head($email_template['content_html']);
        if ($repaired !== false) {
            // update() strips backslashes from every value it is given, so shield any the template has
            $db->update('CubeCart_email_template', array('content_html' => addslashes($repaired)), array('template_id' => (int)$email_template['template_id']));
        }
    }
    if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
        $GLOBALS['cache']->clear();
    }
}
unset($email_templates, $email_template, $repaired);

/**
 * #4738 - In 6.7.x the email content editor saved each email as a whole HTML document:
 * <html><head><title></title></head><body> around the wording, which then sat inside
 * the template's own <body>. #4238 stopped the editor doing it, but emails saved that
 * way are still stored that way. This takes the wrapper off.
 *
 * Only that exact wrapper is removed: an empty title, a bare <body>, nothing after.
 *
 * @param string $html
 * @return string|false The email without the wrapper, or false when it has none
 */
if (!function_exists('cc_unwrap_email_content')) {
    function cc_unwrap_email_content($html)
    {
        if (!preg_match('#^\s*<html>\s*<head>\s*<title>\s*</title>\s*</head>\s*<body>(.*)</body>\s*</html>\s*$#is', (string)$html, $document)) {
            return false;
        }
        $content = trim($document[1]);
        if ($content === '' || preg_match('#</?(?:html|head|body)\b#i', $content)) {
            return false;
        }
        return $content;
    }
}

$email_contents = $email_repairs ? $db->select('CubeCart_email_content', array('content_id', 'content_html'), "`content_html` LIKE '%<html>%'") : false;
if (is_array($email_contents)) {
    foreach ($email_contents as $email_content) {
        $unwrapped = cc_unwrap_email_content($email_content['content_html']);
        if ($unwrapped !== false) {
            // update() strips backslashes from every value it is given, so shield any the email has
            $db->update('CubeCart_email_content', array('content_html' => addslashes($unwrapped)), array('content_id' => (int)$email_content['content_id']), true, array('content_html'));
        }
    }
    if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
        $GLOBALS['cache']->clear();
    }
}
unset($email_contents, $email_content, $unwrapped);

// The connection goes back to how the rest of the upgrade found it
if (is_array($email_names_before) && isset($email_names_before[0]['client'], $email_names_before[0]['results'], $email_names_before[0]['connection'])) {
    $email_names = array_map(function ($name) {
        return preg_replace('/[^a-z0-9_]/i', '', (string)$name);
    }, $email_names_before[0]);
    if ($email_names['client'] !== '' && $email_names['results'] !== '' && $email_names['connection'] !== '') {
        $db->misc("SET character_set_client = '".$email_names['client']."', character_set_results = '".$email_names['results']."', character_set_connection = '".$email_names['connection']."'", false);
    }
}
unset($email_names_before, $email_names_now, $email_names, $email_repairs);
