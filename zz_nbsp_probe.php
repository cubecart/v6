<?php
require dirname(__FILE__).DIRECTORY_SEPARATOR.'ini.inc.php';
define('CC_IN_ADMIN', false);
define('ADMIN_CP', false);
require CC_INCLUDES_DIR.'functions.inc.php';
require CC_INCLUDES_DIR.'bootstrap.data.inc.php';
$GLOBALS['config']->merge('config', '', $config_default);
require CC_INCLUDES_DIR.'bootstrap.view.inc.php';
header('Content-Type: text/plain; charset=utf-8');
define('HTML_MINIFY_URL_ENABLED', false);
include(CC_INCLUDES_DIR.'smarty/filters/HTMLMinify.smarty.php');

$r = $GLOBALS['db']->select('CubeCart_newsletter', false, array('newsletter_id' => 3));
$GLOBALS['smarty']->assign('NEWSLETTER', $r[0]);
$GLOBALS['smarty']->assign('CTRL_VIEW', true);
$min = fn_minify_html($GLOBALS['smarty']->fetch('templates/content.newsletter.php'));
$GLOBALS['smarty']->assign('PAGE_CONTENT', $min);
$input = $GLOBALS['smarty']->fetch('templates/main.php');

function probe($label, $s) {
    $p = strpos($s, 'cron job');
    echo str_pad($label, 22), ($p === false ? 'not found' : bin2hex(substr($s, $p, 16))), "\n";
}
probe('page pre-minify', $input);

$in = n(trim($input));
probe('after n()/trim', $in);
$output = $prev = "";
$hit = 0;
foreach (fn_minify([MINIFY_COMMENT_HTML, MINIFY_HTML_KEEP, MINIFY_HTML, MINIFY_HTML_ENT], $in) as $part) {
    if (strpos($part, 'cron job') !== false && !$hit) { $hit = 1; echo "token containing text: ", bin2hex($part), "\n"; }
    if ($part === "\n") continue;
    if ($part !== ' ' && trim($part) === "" || strpos($part, '<!--') === 0) {
        if (substr($part, -12) === '<![endif]-->') { $output .= $part; }
        continue;
    }
    if ($part[0] === '<' && substr($part, -1) === '>') {
        $output .= fn_minify_html_union($part, 1);
    } else if ($part[0] === '&' && substr($part, -1) === ';' && !in_array(strtolower($part), array('&lt;','&gt;','&amp;','&quot;','&#039;','&#39;','&apos;'), true)) {
        $output .= html_entity_decode($part);
    } else {
        $output .= preg_replace('#\s+#', ' ', $part);
    }
}
probe('after token loop', $output);
$o2 = str_replace(' </', '</', $output);
probe('after str_replace', $o2);
$o3 = str_ireplace([' ', ' ', "\r\n", "\r\n"], [' ', ' ', "\n", "\n"], trim($o2));
probe('after str_ireplace', $o3);
