<?php
/**
 * Compile every Atrium template through a real Smarty instance configured the
 * way CubeCart configures it — same Smarty build, same security policy.
 *
 * This is the only reliable brace check. Reading templates by eye does not
 * catch a stray "{" followed by a non-space: Smarty parses that as a tag and
 * throws at RENDER time, which on a storefront means a white page for every
 * customer. Compiling here surfaces it at build time instead. It also catches
 * anything CubeCart_Smarty_Security forbids.
 *
 * Deliberately does NOT boot CubeCart. ini.inc.php exits under CLI, and a
 * linter that needs a database is a linter nobody runs. Only the two classes
 * and the few constants they touch are loaded, so this is side-effect free and
 * safe to run against a live tree.
 *
 * Usage: php build/lint-compile.php     (exit 1 on any failure, so it can gate a commit)
 */

$skinDir = dirname(__DIR__) . '/';                 // skins/atrium/
$root    = dirname(dirname(rtrim($skinDir, '/'))); // CubeCart root
$skin    = basename(dirname(__DIR__));             // 'atrium'
$tplDir  = $skinDir . 'templates';

define('CC_INI_SET', true);
if (!defined('CC_ROOT_DIR'))     define('CC_ROOT_DIR', $root);
if (!defined('CC_DS'))           define('CC_DS', DIRECTORY_SEPARATOR);
if (!defined('CC_INCLUDES_DIR')) define('CC_INCLUDES_DIR', $root . '/includes/');

require_once $root . '/includes/smarty/Smarty.class.php';
require_once $root . '/classes/cubecart_smarty_security.class.php';

$smarty = new Smarty();
$smarty->muteUndefinedOrNullWarnings();
$smarty->error_reporting = E_ALL & ~E_NOTICE & ~E_WARNING;

// Throwaway compile dir: never disturb the live compiled-template cache, or a
// lint run would silently change what the storefront serves.
$tmp = sys_get_temp_dir() . '/atrium-lint-' . getmypid();
@mkdir($tmp, 0777, true);
$smarty->compile_dir = $tmp;
$smarty->config_dir  = $tmp;
$smarty->cache_dir   = $tmp;

$smarty->debugging = false;
$smarty->enableSecurity(new CubeCart_Smarty_Security($smarty));
$smarty->setTemplateDir($skinDir);
$smarty->setCompileCheck(true);
$smarty->force_compile = true;

// Include module override templates, which keep the MODULE's file name (.tpl)
// — e.g. templates/modules/gateway/Card_Capture/form.tpl. They compile through
// the same Smarty and can break the payment page just as easily.
$files = array_merge(
    glob($tplDir . '/*.php'),
    glob($tplDir . '/modules/*/*/*.tpl')
);
printf("linting %d templates in skins/%s/templates\n\n", count($files), $skin);

$fail = 0;
$ok   = 0;

foreach ($files as $file) {
    // Relative to the skin dir, not just the basename — the override lives at
    // templates/modules/gateway/<Module>/form.tpl.
    $rel = ltrim(str_replace($skinDir, '', $file), '/');
    try {
        $smarty->createTemplate($rel)->compileTemplateSource();
        printf("  ok    %s\n", $rel);
        $ok++;
    } catch (\Throwable $e) {
        printf("  FAIL  %s\n          %s\n", $rel, $e->getMessage());
        $fail++;
    }
}

foreach (glob($tmp . '/*') as $f) { if (is_file($f)) { unlink($f); } }
@rmdir($tmp);

printf("\n%d compiled, %d failed\n", $ok, $fail);
exit($fail > 0 ? 1 : 0);
