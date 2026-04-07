<?php
/**
 * CubeCart Smarty Security Policy
 *
 * Restricts dangerous Smarty tags ({math}, {eval}, {php}) that can
 * execute arbitrary PHP code while allowing all functionality used
 * by CubeCart templates.
 *
 * @package CubeCart
 */
class CubeCart_Smarty_Security extends Smarty_Security
{
    /**
     * Allowed PHP functions in templates.
     * If empty, all functions are allowed — we explicitly list those used
     * by CubeCart templates plus common safe functions.
     *
     * @var array
     */
    public $php_functions = array(
        // Smarty defaults
        'isset', 'empty', 'count', 'sizeof', 'in_array', 'is_array', 'time',
        // Used in CubeCart templates
        'sprintf', 'number_format', 'strlen', 'http_build_query',
        'str_replace', 'ucwords', 'floatval', 'ucfirst', 'strip_tags',
        'addslashes', 'substr', 'intval', 'array_key_exists', 'strtolower',
        'strtoupper', 'trim', 'nl2br', 'urlencode', 'urldecode',
        'htmlspecialchars', 'htmlentities', 'html_entity_decode',
        'json_encode', 'json_decode', 'implode', 'explode',
        'round', 'ceil', 'floor', 'abs', 'max', 'min',
        'date', 'strtotime', 'array_merge', 'is_numeric',
        'preg_replace', 'str_pad', 'chunk_split',
        'base64_encode', 'is_bool', 'is_null', 'strpos', 'formatTime',
    );

    /**
     * Allowed PHP modifiers in templates.
     *
     * @var array
     */
    public $php_modifiers = array(
        'escape', 'count', 'sizeof', 'nl2br',
        'ucwords', 'strip_tags', 'html_entity_decode',
        'json_encode', 'sprintf', 'number_format',
        'strlen', 'strtolower', 'strtoupper', 'trim',
        'urlencode', 'intval', 'floatval', 'round',
        'str_replace', 'addslashes', 'substr',
    );

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
     * CubeCart templates don't use static class calls,
     * but plugins might, so we leave this open.
     *
     * @var array
     */
    public $static_classes = array();

    /**
     * Allow all streams (default).
     *
     * @var array
     */
    public $streams = array('file');

    /**
     * Secure directories for {include} file access.
     * js/ is needed for legacy v5 skins that include js/common.html.
     *
     * @var array
     */
    public $secure_dir = array();

    public function __construct($smarty)
    {
        parent::__construct($smarty);
        $this->secure_dir = array(CC_ROOT_DIR . '/js/');
    }
}
