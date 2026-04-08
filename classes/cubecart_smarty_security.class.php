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
        // File system writes / destructive
        'file_put_contents', 'fwrite', 'fputs', 'fopen', 'tmpfile',
        'mkdir', 'rmdir', 'rename', 'unlink', 'copy', 'move_uploaded_file',
        'chmod', 'chown', 'chgrp', 'symlink', 'link', 'tempnam',
        // File reads (templates should use Smarty includes, not raw reads)
        'file_get_contents', 'file', 'readfile', 'fgets', 'fread',
        'highlight_file', 'show_source', 'parse_ini_file',
        // Include / require
        'include', 'include_once', 'require', 'require_once',
        // Network
        'curl_init', 'curl_exec', 'fsockopen', 'pfsockopen',
        'stream_socket_client', 'stream_socket_server',
        'mail', 'header', 'setcookie',
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

    public function __construct($smarty)
    {
        parent::__construct($smarty);
        $this->secure_dir = array(CC_ROOT_DIR . '/js/', CC_ROOT_DIR . '/modules/');
    }

    /**
     * Check if PHP function is trusted (blacklist approach).
     */
    public function isTrustedPhpFunction($function_name, $compiler)
    {
        if (in_array($function_name, $this->dangerous_php)) {
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
        if (in_array($modifier_name, $this->dangerous_php)) {
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
