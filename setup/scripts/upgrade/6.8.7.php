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

$email_templates = $db->select('CubeCart_email_template', array('template_id', 'content_html'));
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
