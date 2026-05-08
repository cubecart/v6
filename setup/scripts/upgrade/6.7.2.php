<?php
/**
 * GHSA-7pvc-gxc4-chmc — pin standard_url and cookie_domain to includes/global.inc.php
 * so they cannot be overridden by a forged Host: header at request time.
 */

$existing = array();
$rows = $db->select('CubeCart_config', array('config_key', 'config_value'),
    array('name' => 'config'));
if (is_array($rows)) {
    foreach ($rows as $row) {
        if ($row['config_key'] === 'standard_url' || $row['config_key'] === 'cookie_domain') {
            $existing[$row['config_key']] = $row['config_value'];
        }
    }
}

$standard_url = isset($existing['standard_url']) ? trim((string)$existing['standard_url']) : '';
if ($standard_url === '' || !filter_var($standard_url, FILTER_VALIDATE_URL)) {
    $standard_url = CC_STORE_URL;
}
$standard_url = preg_replace('#^http://#i', 'https://', $standard_url);
$standard_url = rtrim($standard_url, '/');

$cookie_domain = isset($existing['cookie_domain']) ? trim((string)$existing['cookie_domain']) : '';
$store_host    = parse_url($standard_url, PHP_URL_HOST);
if ($cookie_domain !== '') {
    $bare = ltrim($cookie_domain, '.');
    if (!$store_host || strpos($store_host, $bare) === false) {
        $cookie_domain = ($store_host && strpos($store_host, '.') !== false)
            ? '.'.preg_replace('#^www\.#i', '', $store_host)
            : '';
    }
}

$contents = file_get_contents($global_file);
if ($contents !== false) {
    $needs_write = false;
    if (strpos($contents, "glob['standard_url']") === false) {
        $contents = preg_replace(
            '/(<\?php\s*(?:\/\/[^\n]*\n)?)/',
            "$1\$glob['standard_url']  = '".addslashes($standard_url)."';\n",
            $contents,
            1
        );
        $needs_write = true;
    }
    if (strpos($contents, "glob['cookie_domain']") === false) {
        $contents = preg_replace(
            '/(<\?php\s*(?:\/\/[^\n]*\n)?)/',
            "$1\$glob['cookie_domain'] = '".addslashes($cookie_domain)."';\n",
            $contents,
            1
        );
        $needs_write = true;
    }
    if ($needs_write) {
        file_put_contents($global_file, $contents);
        clearstatcache(true, $global_file);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($global_file, true);
        }
    }
}

$db->delete('CubeCart_config', array('name' => 'config', 'config_key' => 'standard_url'));
$db->delete('CubeCart_config', array('name' => 'config', 'config_key' => 'cookie_domain'));

if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
    $GLOBALS['cache']->clear();
}
