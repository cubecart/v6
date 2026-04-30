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

// Lightweight email-open tracking endpoint. Hit by a 1x1 transparent pixel
// embedded in outgoing HTML emails. Avoids the full CubeCart bootstrap so
// it stays cheap when prefetchers/clients fire many opens at once.
//
// URL: /track/open.php?t={32-char-hex-token}
//
// Always returns a 1x1 transparent GIF and HTTP 200, even on missing/invalid
// token, so we never leak signal back to the client about which tokens are real.

// Send the pixel response no matter what happens later.
$gif = base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
header('Content-Type: image/gif');
header('Content-Length: '.strlen($gif));
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
echo $gif;

// Flush to the client and detach so DB work doesn't hold the worker against the
// recipient's mail client. The token format check below is deliberately strict.
if (function_exists('session_write_close')) {
    @session_write_close();
}
while (ob_get_level() > 0) {
    @ob_end_flush();
}
@flush();
if (function_exists('fastcgi_finish_request')) {
    @fastcgi_finish_request();
} elseif (function_exists('litespeed_finish_request')) {
    @litespeed_finish_request();
}

@ignore_user_abort(true);

$token = isset($_GET['t']) ? (string)$_GET['t'] : '';
if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
    return;
}

// Load the global config to get DB credentials. Don't pull in the full bootstrap.
$global_file = __DIR__.'/../includes/global.inc.php';
if (!file_exists($global_file)) {
    return;
}
require $global_file;
if (!isset($glob) || !isset($glob['installed']) || !$glob['installed']) {
    return;
}

$host    = isset($glob['dbhost'])     ? $glob['dbhost']     : 'localhost';
$user    = isset($glob['dbusername']) ? $glob['dbusername'] : '';
$pass    = isset($glob['dbpassword']) ? $glob['dbpassword'] : '';
$db      = isset($glob['dbdatabase']) ? $glob['dbdatabase'] : '';
$port    = !empty($glob['dbport'])    ? (int)$glob['dbport']  : (int)ini_get('mysqli.default_port');
$socket  = !empty($glob['dbsocket'])  ? $glob['dbsocket']     : ini_get('mysqli.default_socket');
$prefix  = isset($glob['dbprefix'])   ? $glob['dbprefix']     : '';

mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = @new mysqli($host, $user, $pass, $db, $port, $socket);
if ($mysqli->connect_errno) {
    return;
}
$mysqli->set_charset('utf8mb4');

$sql = sprintf(
    'UPDATE `%sCubeCart_email_log` SET `seen_count` = `seen_count` + 1, `seen_at` = COALESCE(`seen_at`, NOW()) WHERE `tracking_token` = ? LIMIT 1',
    str_replace('`', '', $prefix)
);
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param('s', $token);
    @$stmt->execute();
    $stmt->close();
}
$mysqli->close();
