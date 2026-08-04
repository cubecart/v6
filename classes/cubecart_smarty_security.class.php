<?php
/**
 * CubeCart Smarty Security Policy
 *
 * Blocks dangerous PHP functions/modifiers and Smarty tags while
 * allowing all safe ones — no whitelist to maintain.
 *
 * @package CubeCart
 */
class CubeCart_Smarty_Security extends Smarty_Security
{
    /**
     * PHP functions that must NEVER be callable from templates.
     * Covers code execution, file system writes, process control,
     * and information disclosure.
     *
     * @var array
     */
    private $dangerous_php = array(
        // Code execution
        'exec', 'system', 'passthru', 'shell_exec', 'popen', 'proc_open',
        'pcntl_exec', 'eval', 'assert', 'create_function', 'call_user_func',
        'call_user_func_array', 'preg_replace_callback', 'usort', 'uasort',
        'uksort', 'array_map', 'array_filter', 'array_walk',
        'forward_static_call', 'forward_static_call_array', 'iterator_apply',
        // Callback-taking siblings of the above. array_walk_recursive is the
        // bypass reported in GHSA-5mr8-hgcv-3pcj; the rest are the same sink
        // reached by a different name.
        'array_walk_recursive', 'array_reduce',
        'preg_replace_callback_array', 'mb_ereg_replace_callback',
        'array_udiff', 'array_udiff_assoc', 'array_udiff_uassoc',
        'array_uintersect', 'array_uintersect_assoc', 'array_uintersect_uassoc',
        'array_diff_ukey', 'array_diff_uassoc',
        'array_intersect_ukey', 'array_intersect_uassoc',
        // Handler registration — deferred callbacks, and the two register_*
        // functions instantiate an attacker-named class.
        'set_error_handler', 'set_exception_handler', 'spl_autoload_register',
        'session_set_save_handler', 'header_register_callback',
        'libxml_set_external_entity_loader',
        'stream_wrapper_register', 'stream_filter_register',
        // File system writes / destructive
        'file_put_contents', 'fwrite', 'fputs', 'fopen', 'tmpfile',
        'mkdir', 'rmdir', 'rename', 'unlink', 'copy', 'move_uploaded_file',
        'chmod', 'chown', 'chgrp', 'symlink', 'link', 'tempnam',
        'touch', 'ftruncate', 'umask',
        // error_log writes to a file when message_type=3 — this is the
        // RCE vector reported in GHSA-wpjx-g695-qc5j.
        'error_log',
        'fputcsv', 'gzwrite', 'gzputs', 'bzwrite',
        'imagepng', 'imagejpeg', 'imagegif', 'imagewebp', 'imagebmp',
        // File reads (templates should use Smarty includes, not raw reads)
        'file_get_contents', 'file', 'readfile', 'fgets', 'fread',
        'highlight_file', 'show_source', 'parse_ini_file',
        // Compressed/binary file readers — readgzfile is the file-disclosure
        // vector reported in GHSA-wpjx-g695-qc5j.
        'readgzfile', 'gzfile', 'gzopen', 'gzread', 'gzgets', 'gzgetss',
        'gzpassthru', 'gzdecode', 'gzinflate',
        'bzopen', 'bzread', 'bzdecompress',
        'fpassthru', 'readlink', 'linkinfo', 'realpath',
        'glob', 'scandir', 'opendir', 'readdir', 'closedir', 'rewinddir',
        'pathinfo', 'fileowner', 'filegroup', 'fileperms',
        // XML/HTML loaders that accept stream wrappers (file://, php://).
        'simplexml_load_file', 'simplexml_import_dom',
        // Include / require
        'include', 'include_once', 'require', 'require_once',
        // Network
        'curl_init', 'curl_exec', 'fsockopen', 'pfsockopen',
        // curl_init/curl_exec alone leave the multi-handle API as a way round.
        'curl_setopt', 'curl_setopt_array',
        'curl_multi_init', 'curl_multi_exec', 'curl_multi_add_handle',
        'stream_socket_client', 'stream_socket_server',
        'mail', 'header', 'setcookie',
        'mb_send_mail', // same as mail()
        'ftp_connect', 'ftp_get', 'ftp_put', 'ftp_raw',
        'gethostbyname', 'gethostbyaddr', 'gethostbynamel', 'gethostname',
        'dns_get_record', 'dns_check_record', 'checkdnsrr', 'getmxrr',
        // Process control
        'pclose', 'pcntl_fork', 'pcntl_signal', 'pcntl_wait', 'pcntl_waitpid',
        'posix_kill', 'posix_setuid', 'posix_setgid', 'posix_setsid',
        'proc_close', 'proc_terminate', 'proc_nice', 'proc_get_status',
        // Apache — virtual() executes a subrequest
        'virtual', 'apache_setenv', 'apache_note', 'apache_child_terminate',
        // Info disclosure
        'phpinfo', 'php_uname', 'getenv', 'putenv',
        'get_defined_vars', 'get_defined_functions', 'get_defined_constants',
        'getmypid', 'getmyuid',
        // Dangerous misc
        'extract', 'parse_str', 'compact',
        'serialize', 'unserialize',
        'ob_start', 'ob_end_clean', 'ob_get_contents',
        'ini_set', 'ini_alter', 'set_time_limit', 'set_include_path',
        'dl', 'register_shutdown_function', 'register_tick_function',
        'define',
    );

    /**
     * Allow all PHP functions except dangerous ones (populated in constructor).
     * @var array
     */
    public $php_functions = array();

    /**
     * Allow all PHP modifiers except dangerous ones (populated in constructor).
     * @var array
     */
    public $php_modifiers = array();

    /**
     * Disabled Smarty tags — these can execute arbitrary PHP code.
     *
     * @var array
     */
    public $disabled_tags = array('math', 'eval', 'php', 'fetch');

    /**
     * Allow constants (used by CubeCart templates).
     *
     * @var boolean
     */
    public $allow_constants = true;

    /**
     * Allow super globals (used by CubeCart templates).
     *
     * @var boolean
     */
    public $allow_super_globals = true;

    /**
     * Trusted static classes — empty means all allowed.
     *
     * @var array
     */
    public $static_classes = array();

    /**
     * Allow file streams.
     *
     * @var array
     */
    public $streams = array('file');

    /**
     * Secure directories for {include} file access.
     *
     * @var array
     */
    public $secure_dir = array();

    /**
     * Commonly-used PHP functions pre-registered as Smarty modifiers so the
     * compiler matches them in case 1 (registered modifier) before reaching
     * case 5 (PHP function) which emits E_USER_DEPRECATED. Also ensures the
     * compiled template's $_smarty_tpl->registered_plugins[...] lookup finds
     * the callable at cache-execution time (registration happens every request).
     *
     * @var array
     */
    private $safe_modifiers = array(
        'json_encode', 'json_decode',
        'html_entity_decode', 'htmlentities', 'htmlspecialchars', 'htmlspecialchars_decode',
        'strtolower', 'strtoupper', 'ucfirst', 'ucwords',
        'sprintf', 'printf',
        'print_r', 'var_export',
        'intval', 'floatval', 'boolval', 'strval',
        'stripslashes', 'addslashes',
        'urlencode', 'urldecode', 'rawurlencode', 'rawurldecode',
        'base64_encode', 'base64_decode',
        'md5', 'sha1', 'crc32', 'hash',
        'strtotime', 'mktime', 'time', 'microtime',
        'implode', 'explode',
        'ltrim', 'rtrim', 'trim',
        'str_replace', 'preg_replace', 'preg_match', 'str_ireplace',
        'str_pad',
        'substr', 'strpos', 'strrpos', 'stripos', 'strstr', 'stristr', 'strrev',
        'strcmp', 'strcasecmp',
        'floor', 'ceil', 'abs', 'min', 'max', 'pow', 'sqrt',
        'array_reverse', 'array_keys', 'array_values', 'array_merge',
        'array_slice', 'array_sum', 'array_unique', 'in_array',
        'count', 'sizeof',
        'nl2br', 'wordwrap',
        'filter_var', 'is_numeric', 'is_array', 'is_string', 'is_null',
        'number_format', 'http_build_query', 'strip_tags', 'strlen',
        // CubeCart helpers used as {fn(...)} in admin/storefront templates
        'formatTime', 'formatBytes',
    );

    /**
     * Static classes accessible from templates as `ClassName::CONST` /
     * `ClassName::method()`. Smarty 4+ deprecates unregistered static
     * class access at compile time.
     *
     * @var array
     */
    private $trusted_static_classes = array(
        'Catalogue'   => 'Catalogue',
        'FileManager' => 'FileManager',
    );

    public function __construct($smarty)
    {
        parent::__construct($smarty);
        $this->secure_dir = array(CC_ROOT_DIR . '/js/', CC_ROOT_DIR . '/modules/');

        // Normalise once: the trusted-function checks compare lowercased names.
        $this->dangerous_php = array_map('strtolower', $this->dangerous_php);

        // Per-install exceptions. A third-party extension may legitimately need
        // something on the list, and there is no way to enumerate in advance
        // what every extension's templates call — so allow it to be un-banned
        // rather than force a fork of this file. Set in includes/global.inc.php;
        // see global.inc.php-dist for the documented example.
        //
        // $never_allow can't be overridden. Nothing legitimate in a template
        // needs to run a shell or eval a string, so those stay banned no matter
        // what ends up in the config — which also means a compromised or
        // careless extension can't disarm the policy meant to contain it.
        if (isset($GLOBALS['glob']['smarty_allowed_php']) && is_array($GLOBALS['glob']['smarty_allowed_php'])) {
            $never_allow = array(
                'exec', 'system', 'passthru', 'shell_exec', 'proc_open', 'popen',
                'pcntl_exec', 'eval', 'assert', 'create_function',
            );
            foreach ($GLOBALS['glob']['smarty_allowed_php'] as $allow) {
                $allow = strtolower(trim((string)$allow));
                if ($allow === '' || in_array($allow, $never_allow, true)) {
                    continue;
                }
                $key = array_search($allow, $this->dangerous_php, true);
                if ($key !== false) {
                    unset($this->dangerous_php[$key]);
                }
            }
            $this->dangerous_php = array_values($this->dangerous_php);
        }

        $dangerous = $this->dangerous_php;

        // Pre-register safe PHP functions as modifiers. Registering at
        // construction (every request) means the cache file's runtime
        // lookup into $_smarty_tpl->registered_plugins['modifier'][$name]
        // always finds the callable.
        foreach ($this->safe_modifiers as $fn) {
            if (in_array($fn, $dangerous)) {
                continue;
            }
            if (isset($smarty->registered_plugins[Smarty::PLUGIN_MODIFIER][$fn])) {
                continue;
            }
            if (!is_callable($fn)) {
                continue;
            }
            $smarty->registered_plugins[Smarty::PLUGIN_MODIFIER][$fn] = array($fn, true, array());
        }

        // Register trusted static classes so {ClassName::CONST} compiles
        // without a deprecation warning.
        foreach ($this->trusted_static_classes as $alias => $class) {
            if (!isset($smarty->registered_classes[$alias])) {
                $smarty->registered_classes[$alias] = $class;
            }
        }

        // Fallback for any PHP function not in the pre-registered list.
        // Sets $callback by reference so Smarty compiles a direct function
        // call ({$name}(...)) rather than a registered_plugins lookup.
        $smarty->registerDefaultPluginHandler(function ($name, $type, $template, &$callback, &$script, &$cacheable) use ($dangerous) {
            if ($type === Smarty::PLUGIN_MODIFIER && is_callable($name) && !in_array($name, $dangerous)) {
                $callback = $name;
                return true;
            }
            return false;
        });
    }

    /**
     * Check if PHP function is trusted (blacklist approach).
     */
    public function isTrustedPhpFunction($function_name, $compiler)
    {
        // strtolower: PHP function names are case-insensitive, so a
        // case-sensitive in_array() let {EXEC(...)} or {Array_Walk(...)} past
        // the entire list regardless of what was on it.
        if (in_array(strtolower((string)$function_name), $this->dangerous_php, true)) {
            $compiler->trigger_template_error("PHP function '{$function_name}' not allowed by security setting");
            return false;
        }
        return true;
    }

    /**
     * Check if PHP modifier is trusted (blacklist approach).
     */
    public function isTrustedPhpModifier($modifier_name, $compiler)
    {
        if (in_array(strtolower((string)$modifier_name), $this->dangerous_php, true)) {
            $compiler->trigger_template_error("modifier '{$modifier_name}' not allowed by security setting");
            return false;
        }
        return true;
    }

    /**
     * Add a trusted directory path for Smarty template access.
     *
     * @param string $path Absolute path to trust
     */
    public function addTrustedDir($path)
    {
        $real = realpath($path);
        if ($real !== false) {
            $this->secure_dir[] = $real;
        }
    }
}
