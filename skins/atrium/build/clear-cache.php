<?php
/**
 * Clear CubeCart's caches after editing config.xml or a template.
 *
 * Needed because several things are cached in ways that mask your edits:
 *   - info.skins.list        parsed config.xml for EVERY skin (gui.class.php:702)
 *   - skin.<name>.custom     the <custom> block                (gui.class.php:1946)
 *   - html.<skin>.menu.*     rendered navigation
 *   - Smarty setCompileCheck(false) when debug is off + cache on, so edited
 *     templates are not recompiled at all until this runs.
 *
 * Usage: php build/clear-cache.php
 */
define('CC_INI_SET', true);
$root = dirname(dirname(dirname(__DIR__)));   // skins/atrium/build -> CubeCart root
require $root . '/includes/global.inc.php';

if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
    $GLOBALS['cache']->clear();
    echo "cache cleared\n";
} else {
    echo "cache object unavailable; clearing skin compile dir only\n";
}

$dir = $root . '/cache/skin';
if (is_dir($dir)) {
    $n = 0;
    foreach (glob($dir . '/*') as $f) { if (is_file($f)) { unlink($f); $n++; } }
    echo "removed {$n} compiled templates from cache/skin\n";
}
