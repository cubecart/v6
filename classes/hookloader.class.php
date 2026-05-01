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
 *
 * @tutorial
 * To call hooks, use the following:
 *   foreach ($hooks->load('HOOK_TRIGGER') as $hook) include $hook;
 * Note that all hooks will be included into the current scope
 */

/**
 * Hook controller
 */
class HookLoader
{

    /**
     * Enable/disable
     *
     * @var bool
     */
    private $_enabled  = true;
    /**
     * Hook path
     *
     * @var path
     */
    private $_hook_dir  = false;
    /**
     * Code snippet path
     *
     * @var path
     */
    private $_snippet_dir  = false;
    /**
     * Code snippet file prefix
     *
     * @var path
     */
    private $_snippet_prefix  = 'snippet_';
    /**
     * Hook list
     *
     * @var array
     */
    private $_hook_list  = array();
    /**
     * Code snippet list
     *
     * @var array
     */
    private $_snippet_list  = array();
    /**
     * Array of the currently loaded language files for hooks
     * to help stop repeated lang loads
     *
     * @var array
     */
    private $_loaded_lang = array();
    /**
     * Plugin list
     *
     * @var array
     */
    private $_plugin_list = array();

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    /**
     * Cache key for the pre-compiled hook + snippet map.
     */
    const CACHE_KEY = 'hookloader.compiled';

    final protected function __construct()
    {
        // Define the plugins directory
        $this->_hook_dir = CC_ROOT_DIR.'/modules/plugins';
        // Define the code snippets directory
        $this->_snippet_dir = CC_ROOT_DIR.'/includes/extra';

        // Try to read the pre-compiled map (built once, reused until a plugin
        // or snippet changes). Avoids two DB selects + per-call regex/file_exists
        // work on every request.
        $compiled = (isset($GLOBALS['cache']) && is_object($GLOBALS['cache']))
            ? $GLOBALS['cache']->read(self::CACHE_KEY)
            : false;
        if (!is_array($compiled) || !isset($compiled['hooks'], $compiled['snippets'], $compiled['plugins'])) {
            $compiled = $this->_compile();
            if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
                $GLOBALS['cache']->write($compiled, self::CACHE_KEY);
            }
        }
        $this->_hook_list = $compiled['hooks'];
        $this->_snippet_list = $compiled['snippets'];
        $this->_plugin_list = $compiled['plugins'];

        // Add each plugin's directory to the PHP include_path. Must run every
        // request because include_path affects the PHP runtime, not the cache.
        if (!empty($this->_plugin_list)) {
            $extra = '';
            foreach ($this->_plugin_list as $plugin) {
                $extra .= CC_PS.$this->_hook_dir.'/'.$plugin;
            }
            ini_set('include_path', ini_get('include_path').$extra);
        }
    }

    /**
     * Invalidate the compiled hook/snippet map. Called whenever the underlying
     * CubeCart_hooks or CubeCart_code_snippet tables are modified (plugin
     * install/uninstall, hook enable/disable, snippet add/edit/delete).
     */
    public function clearCache()
    {
        if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
            $GLOBALS['cache']->delete(self::CACHE_KEY);
        }
    }

    /**
     * Build the compiled map from the source-of-truth tables. Resolves and
     * sanitises plugin names + filepaths once, checks file existence once,
     * indexes everything by trigger so load() is O(matched hooks).
     *
     * @return array {hooks, snippets, plugins}
     */
    private function _compile()
    {
        $compiled = array('hooks' => array(), 'snippets' => array(), 'plugins' => array());
        $plugin_set = array();

        if (($hooks = $GLOBALS['db']->select('CubeCart_hooks', false, array('enabled' => '1'), array('priority' => 'ASC'))) !== false) {
            foreach ($hooks as $hook) {
                $this->_security_check($hook['filepath']);
                // Sanitise plugin name; don't use _plugin_name() here as it
                // mutates include_path/_plugin_list — the constructor handles
                // those uniformly from the cached _plugin_list.
                $hook['plugin'] = preg_replace('#[^a-z0-9]#iU', '_', $hook['plugin']);
                $filepath = !empty($hook['filepath']) ? $hook['filepath'] : 'hooks/'.$hook['trigger'].'.php';
                $fullpath = $this->_hook_dir.'/'.$hook['plugin'].'/'.$filepath;
                if (!file_exists($fullpath)) {
                    continue;
                }
                $compiled['hooks'][$hook['trigger']][] = array(
                    'plugin'   => $hook['plugin'],
                    'fullpath' => $fullpath,
                    'priority' => (int)$hook['priority'],
                );
                $plugin_set[$hook['plugin']] = true;
            }
        }

        if (($snippets = $GLOBALS['db']->select('CubeCart_code_snippet', false, array('enabled' => '1'), array('priority' => 'ASC'))) !== false) {
            foreach ($snippets as $snippet) {
                $file_name = $this->_snippet_dir.'/'.$this->_snippet_prefix.md5($snippet['unique_id']).'.php';
                if (!file_exists($file_name)) {
                    @file_put_contents($file_name, base64_decode($snippet['php_code']));
                }
                if (!file_exists($file_name)) {
                    continue;
                }
                $compiled['snippets'][$snippet['hook_trigger']][] = array(
                    'fullpath' => $file_name,
                    'priority' => (int)$snippet['priority'],
                );
            }
        }

        // Pre-sort each trigger bucket by priority so load() doesn't have to.
        foreach ($compiled['hooks'] as &$bucket) {
            usort($bucket, function($a, $b) { return $a['priority'] - $b['priority']; });
        }
        unset($bucket);
        foreach ($compiled['snippets'] as &$bucket) {
            usort($bucket, function($a, $b) { return $a['priority'] - $b['priority']; });
        }
        unset($bucket);

        $compiled['plugins'] = array_keys($plugin_set);
        return $compiled;
    }

    /**
     * Setup the instance (singleton)
     *
     * @return HookLoader
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    //=====[ Public ]=======================================

    /**
     * Delete code snippet include file
     *
     * @param int/string $unique_id
     * @return bool
     */
    public function delete_snippet_file($unique_id = '')
    {
        if (is_numeric($unique_id)) {
            $unique_id = $this->_get_unique_id($unique_id);
        }

        if (!empty($unique_id)) {
            $full_path = $this->_snippet_dir.'/'.$this->_snippet_prefix.md5($unique_id).'.php';
            if (file_exists($full_path)) {
                return unlink($full_path);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Enable/disable hooks
     *
     * @param bool $enable
     */
    public function enable($enable = true)
    {
        $this->_enabled = (bool)$enable;
    }

    /**
     * Import code snippets
     *
     * @param array file
     * @return bool
     */
    public function import_code_snippets($file)
    {
        if (file_exists($file['tmp_name'])) {
            if ($file['size']>0) {
                if (in_array($file['type'], array('application/x-zip', 'application/zip'))) {
                    $zip = new ZipArchive;
                    if ($zip->open($file['tmp_name']) === true) {
                        $contents = $zip->getFromIndex(0);
                    } else {
                        trigger_error("Error: Failed to read zip file.", E_USER_NOTICE);
                    }
                } else {
                    $contents = file_get_contents($file['tmp_name']);
                }
                if (empty($contents)) {
                    trigger_error("Error: No content found for code snippet.", E_USER_NOTICE);
                } else {
                    try {
                        $xml   = new simpleXMLElement($contents);
                        foreach ($xml->snippets->snippet as $snippet) {
                            $record = array(
                                'author'  => $xml->info->author,
                                'enabled'  => $snippet->enabled,
                                'description' => $snippet->description,
                                'hook_trigger' => $snippet->hook_trigger,
                                'php_code'  => base64_encode($snippet->php_code),
                                'version'  => $snippet->version,
                                'priority'  => $snippet->priority,
                            );

                            if ($GLOBALS['db']->select('CubeCart_code_snippet', array('snippet_id'), array('unique_id' => $snippet->unique_id))) {
                                $GLOBALS['db']->update('CubeCart_code_snippet', $record, array('unique_id' => $snippet->unique_id));
                            } else {
                                $record['unique_id'] = $snippet->unique_id;
                                $GLOBALS['db']->insert('CubeCart_code_snippet', $record);
                            }
                            $this->delete_snippet_file($snippet->unique_id);
                        }
                        $this->clearCache();
                        return true;
                    } catch (Exception $e) {
                        trigger_error($e->getMessage(), E_USER_WARNING);
                    }
                }
            } else {
                trigger_error("Error: Code snippet import failed as the file is ".$file['size']." bytes in size.", E_USER_NOTICE);
            }
        } else {
            trigger_error("Error: Code snippet import for '".$file['tmp_name']."' doesn't exist.", E_USER_NOTICE);
        }
        return false;
    }

    /**
     * Install new plugin
     *
     * @param string $plugin
     * @return bool
     */
    public function install($plugin)
    {
        if (!empty($plugin)) {
            $this->_plugin_name($plugin);
            $file = $this->_hook_dir.'/'.$plugin.'/'.'config.xml';
            if (file_exists($file)) {
                // Read each XML file, check contents, and update/add to database
                try {
                    $xml   = new simpleXMLElement(file_get_contents($file));
                    $allowed_hooks = array();
                    foreach ($xml->hooks->hook as $hook) {
                        // Check if the hook already exists
                        array_push($allowed_hooks, (string)$hook->attributes()->trigger);
                        $check = $GLOBALS['db']->select('CubeCart_hooks', false, array('plugin' => $plugin, 'trigger' => (string)$hook->attributes()->trigger));

                        $record = array(
                            'plugin' => $plugin,
                            'hook_name' => (string)$hook,
                            'trigger' => (isset($check[0]['trigger']) && !empty($check[0]['trigger'])) ? $check[0]['trigger'] : (string)$hook->attributes()->trigger,
                            'priority' => (isset($check[0]['priority']) && !empty($check[0]['priority'])) ? $check[0]['priority'] : (int)$hook->attributes()->priority,
                            'filepath' => (isset($check[0]['filepath']) && !empty($check[0]['filepath'])) ? $check[0]['filepath'] : (string)$hook->file,
                        );

                        if ($check) {
                            $GLOBALS['db']->update('CubeCart_hooks', $record, array('plugin' => $plugin, 'trigger' => (string)$hook->attributes()->trigger));
                        } else {
                            $record['enabled'] = (int)$hook->attributes()->enabled;
                            $GLOBALS['db']->insert('CubeCart_hooks', $record);
                        }
                    }
                    // remove hooks not allowed
                    $GLOBALS['db']->misc("DELETE FROM `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_hooks` WHERE `plugin` = '".$plugin."' AND `trigger` NOT IN ('".implode("','", $allowed_hooks)."')");
                    $this->clearCache();
                    return true;
                } catch (Exception $e) {
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }
            }
        }
        return false;
    }

    /**
     * Check hook is enabled
     *
     * @param string $trigger
     * @param string $plugin
     * @return array
     */
    public function is_enabled($trigger, $plugin)
    {
        if (!empty($trigger) && !empty($plugin)) {
            $result = $GLOBALS['db']->select('CubeCart_hooks', array('enabled'), array('trigger' => $trigger, 'plugin' => $plugin));
            if ($result[0]['enabled']==1) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load hook
     *
     * @param string $trigger
     * @return array
     */
    public function load($trigger)
    {
        if (!$this->_enabled || empty($trigger)) {
            return array();
        }
        $safe_mode = $GLOBALS['config']->get('config', 'safe_mode');
        if ($safe_mode === true) {
            return array();
        }

        $entries = array();
        if ($safe_mode !== 'hooks' && isset($this->_hook_list[$trigger])) {
            foreach ($this->_hook_list[$trigger] as $hook) {
                $this->_plugin_language($hook['plugin']);
                $entries[] = $hook;
            }
        }
        if ($safe_mode !== 'snippets' && isset($this->_snippet_list[$trigger])) {
            foreach ($this->_snippet_list[$trigger] as $snippet) {
                $entries[] = $snippet;
            }
        }

        if (count($entries) > 1) {
            usort($entries, function($a, $b) { return $a['priority'] - $b['priority']; });
        }
        $return = array();
        foreach ($entries as $e) {
            $return[] = $e['fullpath'];
        }
        return $return;
    }

    /**
     * Scan for all plugs
     *
     * @param string $dir
     * @param bool $enabled
     * @return array
     */
    public function scan_all_plugins($dir = 'plugins', $enabled = false)
    {
        $plugins = array();
        $dir = ($dir=='plugins') ? $this->_hook_dir : $dir;
        if (($folders = glob($dir.'/'.'*', GLOB_ONLYDIR)) !== false) {
            foreach ($folders as $folder) {
                $basename = basename($folder);

                if ($enabled) {
                    $plugin = $GLOBALS['config']->get($basename);
                    if (!((bool)($plugin['status'] ?? false))) {
                        continue;
                    }
                }

                $plugins[$basename] = array(
                    'plugin' => $basename,
                    'name'  => str_replace('_', ' ', $basename),
                );
            }
            return $plugins;
        }
        return array();
    }

    /**
     * Scan for new plugins and install
     *
     * @return bool
     */
    public function scan_plugins()
    {
        // Scan the plugins directory for config files, and adds the hooks to the database if they don't already exist
        if (($files = glob($this->_hook_dir.'/'.'*', GLOB_ONLYDIR | GLOB_NOSORT)) !== false) {
            foreach ($files as $file) {
                $this->install(basename($file));
            }
            return true;
        }
        return false;
    }

    /**
     * Uninstall plugin
     *
     * @param string $plugin
     * @return bool
     */
    public function uninstall($plugin)
    {
        if (!empty($plugin)) {
            $deleted = $GLOBALS['db']->delete('CubeCart_hooks', array('plugin' => $plugin));
            $this->clearCache();
            return $deleted;
        }
    }

    //=====[ Private ]=======================================

    /**
     * Get hook unique ID
     *
     * @param int code snippet id
     * @return string/false
     */
    private function _get_unique_id($snippet_id)
    {
        if ($id = $GLOBALS['db']->select('CubeCart_code_snippet', array('unique_id'), array('snippet_id' => $snippet_id))) {
            return $id[0]['unique_id'];
        } else {
            return false;
        }
    }

    /**
     * Setup plugin language
     *
     * @param string $plugin
     */
    private function _plugin_language($plugin)
    {
        $lang_dir = $this->_hook_dir.'/'.$plugin.'/'.'language';
        if (!isset($this->_loaded_lang[$plugin])) {
            if (file_exists($lang_dir)) {
                $GLOBALS['language']->loadDefinitions($plugin, $lang_dir, 'module.definitions.xml');
                $strings = $GLOBALS['language']->loadLanguageXML($plugin, '', $lang_dir);
                
                unset($strings);
                $this->_loaded_lang[$plugin] = true;
                $GLOBALS['language']->assignLang(); // Make the language strings fresh
            }
        }
    }

    /**
     * Setup plugin name
     *
     * @param string $plugin_name
     */
    private function _plugin_name(&$plugin_name)
    {
        $plugin_name = preg_replace('#[^a-z0-9]#iU', '_', $plugin_name);
        if (!in_array($plugin_name, $this->_plugin_list)) {
            $this->_plugin_list[] = $plugin_name;
            ini_set('include_path', ini_get('include_path').CC_PS.$this->_hook_dir.'/'.$plugin_name);
        }
    }

    /**
     * Check plugin
     *
     * @param string $filename
     * @return bool
     */
    private static function _security_check(&$filename)
    {
        $find  = array('#^[^a-z0-9.\\\\/_]$#iU', '#(/+)|(\\\+)#', '#\.{1,2}/#');
        $replace = array('', '/', '');
        $filename = preg_replace($find, $replace, $filename);
        $filename = ltrim($filename,"/");
        return true;
    }
}
