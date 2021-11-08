<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * Module controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Module
{
    /**
     * Module settings
     *
     * @var array
     */
    public $_settings;

    /**
     * Module content
     *
     * @var string
     */
    private $_content;
    /**
     * Module info
     *
     * @var array
     */
    private $_info   = false;
    /**
     * Module local name
     *
     * @var string
     */
    private $_local_name;
    /**
     * Module name
     *
     * @var string
     */
    private $_module_name;
    /**
     * Module package file
     *
     * @var string
     */
    private $_package_file = 'package.conf.inc';
    /**
     * Module config dile
     *
     * @var string
     */
    private $_package_xml = 'config.xml';
    /**
     * Module path
     *
     * @var string
     */
    private $_path;
    /**
     * RAW Post Array
     *
     * @var array of strings
     */
    private $_rawvarsout = array();
    /**
     * Module language strings
     *
     * @var array of strings
     */
    private $_strings;
    /**
     * Taxes
     *
     * @var array
     */
    private $_taxes;
    /**
     * Template to load in the module
     *
     * @var string
     */
    private $_template;
    /**
     * Template data
     *
     * @var array
     */
    private $_template_data = array();

    ##############################################

    public function __construct($path = false, $local_name = false, $template = 'index.tpl', $zones = false, $fetch = true)
    {
        $this->_template = $template;
        if ($path) {
            // Load Package info
            $this->_module_data($path, $local_name);
            // Include module classes
            $this->_module_classes();
            if (isset($_POST['module']['status']) && is_array($_POST['module'])) {
                // Automatically handle module save requests
                $this->_info['name'] = $this->_info['name'] ?: $this->_settings['folder'];
                $this->_info['name'] = str_replace('_', ' ', $this->_info['name']);

                $this->_enumerateRawVars();
                foreach ($this->_rawvarsout as $key => $key_name) {
                    $_POST['module'][$key_name] = $GLOBALS['RAW']['POST']['module'][$key_name];
                }
                if ($this->module_settings_save($_POST['module'])) {
                    $GLOBALS['main']->successMessage(sprintf($GLOBALS['language']->notification['notify_module_settings'], $this->_info['name']));
                } else {
                    $GLOBALS['main']->errorMessage(sprintf($GLOBALS['language']->notification['error_module_settings'], $this->_info['name']));
                }
                // Install hooks if required
                if ($_POST['module']['status']) {
                    $GLOBALS['hooks']->install($this->_module_name);
                } else {
                    $GLOBALS['hooks']->uninstall($this->_module_name);
                }
                // Reload package data after save
                $this->_module_data($path, $local_name);
            }
            // Add default tab
            $GLOBALS['main']->addTabControl($GLOBALS['language']->common['general'], $_GET['module']);
            $GLOBALS['smarty']->assign('GENERAL_TAB_ID', $_GET['module']);

            // Include module language strings - use Language class
            $GLOBALS['language']->loadDefinitions($this->_module_name, $this->_path.'/language', 'module.definitions.xml');
            // Load other lang either customized ones
            $GLOBALS['language']->loadLanguageXML($this->_module_name, '', $this->_path.'/language');

            // Enable this class as an ACP interface
            if ($template) {
                $GLOBALS['gui']->changeTemplateDir($this->_path.'/skin');
                $module_lang_node = str_replace('_', '', strtolower($this->_module_name));
                $lang = $GLOBALS['language']->getStrings($module_lang_node);
                $GLOBALS['smarty']->assign('TITLE', $this->module_fetch_logo($this->_info['type'], $this->_module_name, $lang['module_title']));

                // Get tax types for modules drop down box
                if (($this->_taxes = $GLOBALS['db']->select('CubeCart_tax_class', array('id', 'tax_name'), false, array('tax_name' => 'ASC'))) !== false) {
                    $inherited_tax[] = array(
                        'id' => 999999,
                        'tax_name' => $GLOBALS['language']->common['inherit']
                    );
                    $this->_taxes = array_merge($this->_taxes, $inherited_tax);
                    foreach ($this->_taxes as $tax) {
                        $tax['selected'] = (isset($this->_settings['tax']) && $this->_settings['tax'] == $tax['id']) ? "selected='selected'" : "";
                        $taxes[] = $tax;
                    }
                    $GLOBALS['smarty']->assign('TAXES', $taxes);
                }

                // Assign settings
                if (!empty($this->_settings)) {
                    $GLOBALS['debug']->debugTail($this->_settings, $this->_module_name.': settings');

                    if ($this->_info['type'] == 'gateway') {
                        $this->_settings['processURL']  = $this->communicateURL('process');
                        $this->_settings['callURL']  = $this->communicateURL('call');
                        $this->_settings['fromURL']  = $this->communicateURL('from');
                    }
                    // Allow for 3d arrays, key is subsistuted after MODULE_ in upper case
                    foreach ($this->_settings as $key => $value) {
                        if (is_array($value)) {
                            $GLOBALS['smarty']->assign('MODULE_'.strtoupper($key), $value);
                        } else {
                            $basesettings[$key] = $value;
                        }
                    }
                    $GLOBALS['smarty']->assign('MODULE', $basesettings);
                    // Assign checked & selects
                    if (is_array($this->_settings)) {
                        foreach ($this->_settings as $setting => $value) {
                            $value = str_replace(array('.', '-'), '_', $value);
                            $GLOBALS['smarty']->assign('SELECT_'.$setting.'_'.$value, 'selected="selected"');
                            $GLOBALS['smarty']->assign('CHECKED_'.$setting.'_'.$value, 'checked="checked"');
                        }
                    }
                }
                // Assign config settings regardless
                $GLOBALS['smarty']->assign('CONFIG', $GLOBALS['config']->get('config'));
                // Zone selector
                if ($zones) {
                    $this->_module_zones();
                    $GLOBALS['gui']->changeTemplateDir($this->_path.'/skin');
                }
                $GLOBALS['language']->setTemplate();

                if ($fetch) {
                    $this->fetch();
                }
            }
        }
        return false;
    }

    //=====[ Public ]=======================================

    /**
     * Get a module value
     *
     * @param string $key
     * @return string
     */
    public function __get($key)
    {
        return (array_key_exists($key, $this->_settings)) ? $this->_settings[$key] : false;
    }

    /**
     * Assign data to the template
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function assign_to_template($name, $value=null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->_template_data[$key] = $value;
            }
        } elseif (!empty($name) && !is_null($value)) {
            $this->_template_data[$name] = $value;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Generate URL
     *
     * @param string $method
     * @return string
     */
    public function communicateURL($method = 'process')
    {
        // SSL is preferred
        if ($method == 'from') {
            return $GLOBALS['storeURL'].'/index.php?_a=gateway';
        } else {
            return $GLOBALS['storeURL'].'/index.php?_g=rm&type='.$this->_info['type'].'&cmd='.$method.'&module='.$this->_module_name;
        }
    }

    /**
     * Display module content
     *
     * @param bool $return
     * @return string
     */
    public function display($return = true)
    {
        if ($return) {
            return $this->_content;
        } else {
            echo $this->_content;
        }
    }

    /**
     * Send template date to the screen
     */
    public function fetch()
    {
        if (!$GLOBALS['smarty']->templateExists($this->_template)) {
            return false;
        }

        if (!empty($this->_template_data)) {
            foreach ($this->_template_data as $key => $value) {
                $GLOBALS['smarty']->assign($key, $value);
            }
        }
        $this->_content = $GLOBALS['smarty']->fetch($this->_template);
        $GLOBALS['gui']->changeTemplateDir();
    }

    /**
     * Get module logo
     *
     * @param string $type
     * @param string $name
     * @param string $module_title
     * @return string
     */
    public function module_fetch_logo($type, $name, $module_title = '')
    {
        $images = glob(CC_ROOT_DIR.'/modules/'.$type.'/'.$name.'/'.'admin/logo.{gif,jpg,png,svg}', GLOB_BRACE);
        // $name is the module folder name, $module_title is the title set in the module lang file which is preferable
        if (is_array($images) && isset($images[0])) {
            $title = (empty($module_title)) ? $name : $module_title;
            return '<img src="modules/'.$type.'/'.$name.'/admin/'.basename($images[0]).'" alt="'.$title.'" title="'.$title.'" width="114" />';
        } elseif (!empty($module_title)) {
            return $module_title;
        } else {
            return str_replace('_', ' ', $name);
        }
    }

    /**
     * Get module logo
     *
     * @param string $label
     * @return serialized string/empty
     */
    public function module_fetch_zones($label)
    {
        if (!isset($_POST[$label]) || !is_array($_POST[$label])) {
            return '';
        }

        foreach ($_POST[$label] as $zone) {
            if (!empty($zone)) {
                $zones[$zone] = $zone;
            }
        }
        return (isset($zones)) ? serialize($zones) : '';
    }

    /**
     * Get module language strings
     *
     * @return array of strings
     */
    public function module_language()
    {
        return $this->_strings;
    }

    /**
     * Get module name
     *
     * @param string $module_name
     * @return string
     */
    public static function module_name(&$module_name)
    {
        $module_name = preg_replace('#[^\w\-]#iU', '_', (string)$module_name);
        return $module_name;
    }

    /**
     * Save module settings
     *
     * @param array $settings
     * @return bool
     */
    public function module_settings_save($settings)
    {
        if (!empty($settings) && is_array($settings)) {
            $updated = false;

            $settings['countries']    = $this->module_fetch_zones('zones');
            $settings['disabled_countries'] = $this->module_fetch_zones('disabled_zones');
            $data = array(
                'status' => $settings['status'],
                'position' => (isset($settings['position']) && $settings['position'] > 0) ? $settings['position'] : 0
            );
            if (isset($settings['default'])) {
                $data['default'] = $settings['default'];
            }

            if ($GLOBALS['config']->set($this->_local_name, '', $settings)) {
                $updated = true;
            }
            if (isset($settings['default']) && $settings['default']) {
                // If this is to be set as default then the others need to be unset
                if ($GLOBALS['db']->update('CubeCart_modules', array('default' => 0), array('module' => $this->_info['type']))) {
                    $updated = true;
                }
            }
            // Delete to prevent potential duplicate nightmare
            $GLOBALS['db']->delete('CubeCart_modules', array('module' => $this->_info['type'], 'folder' => $this->_local_name));
            $data['folder'] = $this->_local_name;
            $data['module'] = $this->_info['type'];
            if ($GLOBALS['db']->insert('CubeCart_modules', $data)) {
                $updated = true;
            }
            return $updated;
        }
        return false;
    }

    //=====[ Private ]=======================================

    /**
     * Allow specified raw POST variables
     */
    private function _enumerateRawVars()
    {
        if (file_exists($this->_path.'/'.$this->_package_xml)) {
            $xml = new SimpleXMLElement(file_get_contents($this->_path.'/'.$this->_package_xml, true));
            ## Parse and handle XML data
            foreach ((array)$xml->rawvars->var as $value) {
                $this->_rawvarsout[] = (string)$value;
            }
        }
    }
    
    /**
     * Load module classes
     *
     * @return bool
     */
    private function _module_classes()
    {
        // Include all classes for the module
        if (is_dir($this->_path.'/'.'classes')) {
            foreach (glob($this->_path.'/'.'classes'.DIRECTORY_SEPARATOR.'*.inc.php', GLOB_NOSORT) as $include) {
                if (!is_dir($include)) {
                    require $include;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Get module data
     *
     * @param string $path
     * @param string $local_name
     */
    private function _module_data($path = false, $local_name = false)
    {
        // Set Module Path
        if ($path) {
            $drop = array( CC_DS.'admin',  CC_DS.'classes',  CC_DS.'skin',  CC_DS.'language');
            $this->_path = CC_ROOT_DIR.str_replace($drop, '', dirname(str_replace(CC_ROOT_DIR, '', $path)));
            // Drop trailing slashes
            if (substr($this->_path, -1) == '/') {
                $this->_path = substr($this->_path, 0, -1);
            }
        }
        // Load package configuration data
        if (file_exists($this->_path.'/'.$this->_package_xml)) {
            $xml = new SimpleXMLElement(file_get_contents($this->_path.'/'.$this->_package_xml, true));
            ## Parse and handle XML data
            foreach ((array)$xml->info as $key => $value) {
                $this->_info[$key] = (string)$value;
            }
            //$this->_module_name = (isset($this->_info['folder']) && !empty($this->_info['folder'])) ? $this->_info['folder'] : str_replace(' ', '_', $this->_info['name']);
        } elseif (file_exists($this->_path.'/'.$this->_package_file)) {
            $this->_info  = unserialize(file_get_contents($this->_path.'/'.$this->_package_file, true));
        //$this->_module_name = str_replace(' ', '_', $this->_info['name']);
        } else {
            $pathFolders = explode('/', $this->_path);
            $noFolders = count($pathFolders);
            $this->_info['type'] = $pathFolders[($noFolders-2)];
            //$this->_module_name = $pathFolders[($noFolders-1)];
        }

        $this->_module_name = str_replace(' ', '_', $local_name);
        $this->_local_name  = ($local_name) ? $local_name : $this->_module_name;

        // Load module configuration
        if (!empty($this->_module_name)) {
            $config = $GLOBALS['config']->get($this->_local_name);
            $module = $GLOBALS['db']->select('CubeCart_modules', false, array('folder' => $this->_module_name));
            //unset($config['status'], $config['default']);
            $this->_settings = ($module) ? array_merge($module[0], $config) : $config;
        }
    }

    /**
     * Load module zones
     */
    private function _module_zones()
    {
        if (($countries = $GLOBALS['db']->select('CubeCart_geo_country', array('numcode', 'name', 'status'), 'status > 0', array('name' => 'ASC'))) !== false) {
            $enabled_countries = array();
            $disabled_countries = array();
            
            $enabled = (!empty($this->_settings['countries'])) ? unserialize($this->_settings['countries']) : false;
            foreach ($countries as $country) {
                $options[$country['numcode']] = $country;
                $all_countries[] = $country;
            }

            $GLOBALS['smarty']->assign('ALL_COUNTRIES', $all_countries);
            if (is_array($enabled)) {
                sort($enabled);
                foreach ($enabled as $country) {
                    if(isset($options[$country]) && !empty($options[$country])) {
                        $enabled_countries[] = $options[$country];
                    }
                }
                $GLOBALS['smarty']->assign('ENABLED_COUNTRIES', $enabled_countries);
            }

            $GLOBALS['main']->addTabControl($GLOBALS['language']->settings['allowed_zones'], 'zone-list', null, null, count($enabled_countries), '', 999999);
            $GLOBALS['gui']->changeTemplateDir();
            $GLOBALS['smarty']->assign('LANG', $GLOBALS['lang']);
            $zone_tabs = $GLOBALS['smarty']->fetch('templates/modules.zones.php');

            $disabled = (!empty($this->_settings['disabled_countries'])) ? unserialize($this->_settings['disabled_countries']) : false;

            if (is_array($disabled)) {
                sort($disabled);
                foreach ($disabled as $country) {
                    if(isset($options[$country]) && !empty($options[$country])) {
                        $disabled_countries[] = $options[$country];
                    }
                }
                $GLOBALS['smarty']->assign('DISABLED_COUNTRIES', $disabled_countries);
            }

            $GLOBALS['main']->addTabControl($GLOBALS['language']->settings['disabled_zones'], 'disabled-zone-list', null, null, count($disabled_countries), '', 999999);
            $zone_tabs .= $GLOBALS['smarty']->fetch('templates/modules.zones-disabled.php');

            $GLOBALS['smarty']->assign('MODULE_ZONES', $zone_tabs);
        }
    }
}
