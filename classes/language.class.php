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

/**
 * Language controller
 */
class Language
{

    /**
     * Exported Language File
     *
     * @var string
     */
    public $exported_lang_file     = '';
    /**
     * Current language
     *
     * @var string
     */
    private $_language     = '';
    /**
     * Custom language
     *
     * @var array
     */
    private $_language_custom   = array();
    /**
     * Language data
     *
     * @var array
     */
    private $_language_data    = array();
    /**
     * Language definitions
     *
     * @var array
     */
    private $_language_definitions  = array();
    /**
     * Language definitions data
     *
     * @var array
     */
    private $_language_definition_data = array();
    /**
     * The definitions file last loaded, which getDefinitions() reads the string attributes from
     *
     * @var string
     */
    private $_definition_file = '';
    /**
     * The installed languages, once worked out, for the rest of the request (#4755)
     *
     * @var array|null
     */
    private $_language_list = null;
    /**
     * How long a compiled language file is trusted before it is built again (#4753). A day, which
     * is how long the cache keeps a value: a change made behind the store's back, such as a phrase
     * edited straight in the database, is picked up within the time it always was.
     */
    const COMPILED_LIFETIME = 86400;
    /**
     * Language groups
     *
     * @var array
     */
    private $_language_groups   = array();
    /**
     * Language strings
     *
     * @var array
     */
    private $_language_strings   = array();
    /**
     * Language strings definitions
     *
     * @var array
     */
    private $_language_strings_def   = array();

    const LANG_REGEX = '#^([a-z]{2})\-([A-Z]{2})?$#';
    const EMAIL_FILE = '#^email_(([a-z]{2})(\-[A-Z]{2})?(\-custom)?)\.[a-z]+(\.gz)?$#';

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################

    final protected function __construct()
    {
        $d = array();
        $cached_domains = (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) ? $GLOBALS['cache']->read('lang.domain.list') : false;
        if (!empty($cached_domains) && is_array($cached_domains)) {
            $d = $cached_domains;
        } elseif(isset($GLOBALS['db']) && method_exists($GLOBALS['db'],'select')) {
            if($domains = $GLOBALS['db']->select('CubeCart_domains')) {
                foreach($domains as $domain) {
                    if(empty($domain['domain'])) continue;
                    $d[$domain['domain']] = $domain['language'];
                }
                $GLOBALS['cache']->write($d, 'lang.domain.list');
            }
            
        }
        // Domain-based language selection keys off the actual request host, not
        // CC_STORE_URL — the latter is pinned to standard_url for email-link
        // security (GHSA-7pvc-gxc4-chmc) and so no longer reflects language
        // subdomains. The host is only ever matched against the CubeCart_domains
        // whitelist, so a forged Host header cannot do anything beyond (at worst)
        // selecting an already-configured language.
        $req_host = !empty($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : parse_url(CC_STORE_URL, PHP_URL_HOST);

        if(defined('ADMIN_CP') && ADMIN_CP == false && !empty($d) && isset($d[$req_host]) && !empty($d[$req_host])) {
            $this->_language = $d[$req_host];
        } else if (isset($GLOBALS['session'])) {
            //If the language is trying to be changed try to change it
            if (((isset($_POST['set_language']) && ($switch = $_POST['set_language']) || isset($_GET['set_language']) && ($switch = $_GET['set_language']))) && $this->_valid($switch)) {
                $customer_id = (int)$GLOBALS['session']->getSessionTableData('customer_id');
                if ($customer_id>0) {
                    $GLOBALS['db']->update('CubeCart_customer', array('language' => $switch), array('customer_id' => $customer_id));
                }
                $GLOBALS['session']->set('language', $switch, 'client');
                // Return to the pretty URL. currentPage() rebuilds from PHP_SELF and
                // $_GET, and a rewritten request still carries seo_path there, so it
                // would drop the shopper on index.php?seo_path=... instead of the
                // address they were reading. Other parameters, paging for instance,
                // are carried across.
                if (!empty($_GET['seo_path'])) {
                    $query = $_GET;
                    unset($query['set_language'], $query['seo_path'], $query['token']);
                    $destination = $GLOBALS['storeURL'].'/'.ltrim(str_replace(array("\r", "\n"), '', (string)$_GET['seo_path']), '/');
                    if (!empty($query)) {
                        $destination .= '?'.http_build_query($query);
                    }
                } else {
                    $destination = currentPage(array('set_language'));
                }
                httpredir($destination);
            } else {
                //See if the language is set in the session
                if (!CC_IN_ADMIN && $GLOBALS['session']->has('language', 'client')) {
                    $this->_language = $GLOBALS['session']->get('language', 'client');
                } elseif (CC_IN_ADMIN) {
                    $admin_lang = $GLOBALS['session']->get('user_language', 'admin');
                    $this->_language = (!empty($admin_lang)) ? $admin_lang : $GLOBALS['config']->get('config', 'default_language');
                } else {
                    //Try the default config language
                    $cl = $GLOBALS['config']->get('config', 'default_language');
                    $this->_language = (!empty($cl) && file_exists(CC_ROOT_DIR.'/language/'.$cl.'.xml') && $this->_valid($cl)) ? $cl : 'en-GB';
                    
                    if (file_exists(CC_ROOT_DIR.'/language/'.$this->_language.'.xml')) {
                        //Set the language to the session
                        $GLOBALS['session']->set('language', $this->_language, 'client');
                    } else {
                        trigger_error('No valid language found!', E_USER_ERROR);
                    }
                }
            }
        } else {
            $this->_language = 'en-GB';
        }
        if(!defined('CC_IN_SETUP') && $this->_language !== $GLOBALS['config']->get('config', 'default_language')) {
            header("X-Robots-Tag: noindex");
        }
        $GLOBALS['smarty']->assign("CURRENT_LANGUAGE", $this->_language);
        $this->loadLang();
    }

    /**
     * Setup the instance (singleton)
     *
     * @return Language
     */
    public static function getInstance($admin = false)
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($admin);
        }

        return self::$_instance;
    }

    //=====[ Public ]=======================================

    /**
     * Magic get of a string value
     *
     * @param string $name
     * @return array/false
     */
    public function __get($name)
    {
        $name = strtolower($name);
        if (isset($this->_language_strings[$name])) {
            return $this->_language_strings[$name];
        }
        return false;
    }

    /**
     * Magic isset of a string value
     *
     * @param string $name
     * @return string
     */
    public function __isset($name)
    {
        return isset($this->_language_strings[strtolower($name)]);
    }

    /**
     * Magic set of a string value
     *
     * @param string $name
     * @param array $value
     */
    public function __set($name, $value)
    {
        $name = strtolower($name);
        if (!isset($this->_language_strings[$name]) && is_array($value)) {
            $this->_language_custom[$name] = $name;
            $this->_language_strings[$name] = $value;
        }
    }

    /**
     * Magic unset of a string value
     *
     * @param string $name
     */
    public function __unset($name)
    {
        $name = strtolower($name);
        if (isset($this->_language_custom[$name])) {
            unset($this->_language_strings[$name], $this->_language_custom[$name]);
        }
    }

    /**
     * Add strings to the language
     *
     * @param array $strings
     */
    public function addStrings($strings)
    {
        if (!empty($strings) && is_array($strings)) {
            $this->_language_strings = merge_array($strings, $this->_language_strings);
        }
    }

    /**
     * Assign language to template
     */
    public function assignLang()
    {
        $GLOBALS['smarty']->assignByRef('LANG', $this->_language_strings);
    }

    /**
     * Change language
     *
     * @param string $language
     */
    public function change($language)
    {
        if ($this->_valid($language)) {
            $this->_language = $language;
            //Load the core language files
            $this->loadLanguageXML('lang.core');

            //Set the system locale
            $this->_setLocale();
        }
    }

    /**
     * Clone language for translation
     *
     * @param string $from
     * @param string $language
     */
    public function cloneModuleLanguage($from, $language)
    {
        $from = CC_ROOT_DIR.'/'.$from;
        $to = str_replace('module.definitions.xml', $language.'.xml', $from);

        if (file_exists($from) && !file_exists($to) && ($content = simplexml_load_file($from)) !== false) {
            if (!isset($content->group) && !isset($content->group[0]->attributes()->name)) {
                return false;
            }

            $output =  '<?xml version="1.0" encoding="UTF-8" ?>
<language version="1.0">
	<info>
		<title>'.$language.'</title>
		<code>'.$language.'</code>
		<character_set>utf-8</character_set>
		<version>1.0.0</version>
		<minVersion>5.0.0</minVersion>
		<maxVersion>6.*.*</maxVersion>
	</info>
	<translation>';

            if (!isset($content->group->string) && !isset($content->group[0]->attributes()->name)) {
                return false;
            }

            $output .= "\r\n\t\t<group name=\"".(string)$content->group[0]->attributes()->name."\">";
            
            foreach ($content->group->string as $key) {
                $output .= "\r\n\t\t\t<string name=\"".(string)$key->attributes()->name."\"><![CDATA[".$key."]]></string>";
            }
            
            $output .= "\r\n\t\t</group>\r\n\t</translation>
</language>";
            
            return file_put_contents($to, $output);
        }
    }

    /**
     * Create language file
     *
     * @param array $array
     * @return array/false
     */
    public function create($array)
    {
        $this->_language_list = null; // the list of languages is about to change
        if (is_array($array) && isset($array['code'])) {
            // Check for required values
            if (preg_match(self::LANG_REGEX, $array['code'])) {
                $xml = new XML();
                $xml->startElement('language', array('version' => '2.0'));
                $xml->startElement('info');
                // Info
                $xml->setElement('title', $array['title']);
                $xml->setElement('code', $array['code'], false, false);
                $xml->setElement('character_set', 'utf-8', false, false);
                $xml->setElement('version', '1.0.0', false, false);
                // Set min/max versions
                $xml->setElement('minVersion', '5.0.0', false, false);
                $xml->setElement('maxVersion', '6.*.*', false, false);
                $xml->setElement('default_currency', $array['currency_iso'], false, false);
                $xml->setElement('text_direction', $array['text_direction'], false, false);

                // Close
                $xml->endElement();
                $xml->endElement();
                //Write file
                $filename = CC_ROOT_DIR.'/language/'.$array['code'].'.xml';
                return (bool)file_put_contents($filename, $xml->getDocument());
            }
        }
        return false;
    }

    /**
     * Current language
     *
     * @return string
     */
    public function current()
    {
        return $this->_language;
    }

    /**
     * Delete a langauge
     *
     * @param string $code
     * @param string $path
     * @return bool
     */
    public function deleteLanguage($code, $path = CC_LANGUAGE_DIR)
    {
        $this->_language_list = null; // the list of languages is about to change
        //Make sure the path is valid
        if (!$this->_checkPath($path)) {
            trigger_error('Invalid language path '.$path, E_USER_ERROR);
        }
        //Check to see if the path is the current language directory
        if ($path !== CC_LANGUAGE_DIR) {
            $path = appendDS($path);
        }
        if (!empty($code) && preg_match(self::LANG_REGEX, $code)) {
            $default_language = $GLOBALS['config']->get('config', 'default_language');
            // Correct customer preference
            $GLOBALS['db']->update('CubeCart_customer', array('language' => $default_language), array('language' => $code));
            
            // Purge database
            $GLOBALS['db']->delete('CubeCart_lang_strings', array('language' => $code));
            $GLOBALS['db']->delete('CubeCart_email_content', array('language' => $code));
            // Delete language files
            $files = array();
            //Find every file file
            if (($search = glob($path.$code.'*', GLOB_NOSORT)) !== false) {
                $files = array_merge($files, $search);
            }
            //Find every email file
            if (($search = glob($path.'email_'.$code.'*', GLOB_NOSORT)) !== false) {
                $files = array_merge($files, $search);
            }
            if (!empty($files)) {
                //Delete them
                foreach ($files as $file) {
                    unlink($file);
                }
                // Don't for get the flag!
                $flag_path = CC_LANGUAGE_DIR.'flags/'.$code.'.png';
                if (file_exists($flag_path)) {
                    unlink($flag_path);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Check to see if a product, document or category has been translated in all languages
     * @param string $type
     * @param int $id
     * @return bool
     */
    public function fullyTranslated($type, $id)
    {
        switch ($type) {
        case 'document':
            $data = array(
                'table'  => 'CubeCart_documents',
                'id_column' => 'doc_id',
                'language'  => 'doc_lang',
                'parent_id' => 'doc_parent_id',
                'ignore_default' => false // documents acts differently using main table
            );

            break;
        case 'product':
            $data = array(
                'table'  => 'CubeCart_inventory_language',
                'id_column' => 'product_id',
                'language'  => 'language',
                'parent_id' => false,
                'ignore_default' => true // documents acts differently using main table
            );
            break;
        case 'category':
            $data = array(
                'table'  => 'CubeCart_category_language',
                'id_column' => 'cat_id',
                'language'  => 'language',
                'parent_id' => false,
                'ignore_default' => true // documents acts differently using main table
            );
            break;
        }
        $result = true;
        // Break on false
        if (($languages = $this->listLanguages()) !== false) {

            foreach ($languages as $language) {
                // skip default language if languages are stores in same table
                if ($data['ignore_default'] && $language['code'] == $GLOBALS['config']->get('config', 'default_language')) {
                    continue;
                }
                $where = ($data['parent_id']) ? '`'.$data['language'].'` = \''.$language['code'].'\' AND (`'.$data['parent_id'].'` = '.(int)$id.' || `'.$data['id_column'].'`= '.(int)$id.')' : array($data['language'] => $language['code'], $data['id_column'] => (int)$id);

                if ($result && !$GLOBALS['db']->select($data['table'], false, $where)) {
                    $result = false;
                }
            }
        }
        return $result;
    }

    /**
     * Get custom language strings
     *
     * @param string $group
     * @param string $language
     * @return array
     */
    public function getCustom($group, $language = '')
    {
        if (!empty($group)) {
            if (empty($language)) {
                $language = $this->_language;
            }
            if (($custom = $GLOBALS['db']->select('CubeCart_lang_strings', false, array('type' => $group, 'language' => $language))) !== false) {
                foreach ($custom as $string) {
                    $strings[$string['name']] = $string['value'];
                }
                natsort($strings);
                return $strings;
            }
        }
        return array();
    }

    /**
     * Get language data
     *
     * @param string $element
     * @return array/false
     */
    public function getData($element = '')
    {
        if (empty($element)) {
            return $this->_language_data;
        } elseif (isset($this->_language_data[$element])) {
            return $this->_language_data[$element];
        }

        return false;
    }

    /**
     * Get language definitions
     *
     * @param string $group
     * @return array
     */
    public function getDefinitions($group)
    {
        // A request that started from the compiled file has not read the definitions (#4753)
        if (empty($this->_language_definitions)) {
            $this->_language_definitions = $this->_coreDefinitions();
            $this->_language_definition_data = array();
            $this->_definition_file = CC_LANGUAGE_DIR.'definitions.xml';
        }
        // Every string's attributes (its name, the version it was introduced in) come to nearly
        // four times the size of the strings, and this method is their only reader: the
        // Languages screen in the admin. They used to be cached and loaded on every request.
        // They are now read from the definitions file last loaded, when they are asked for (#4752).
        if (!empty($group) && isset($this->_language_definitions[$group]) && empty($this->_language_definition_data)
                && $this->_definition_file !== '' && file_exists($this->_definition_file)
                && ($parsed = $this->_parseDefinitions($this->_definition_file)) !== false) {
            $this->_language_definition_data = $parsed[1];
        }
        if (!empty($group) && isset($this->_language_definitions[$group]) && isset($this->_language_definition_data[$group])) {
            foreach ($this->_language_definition_data[$group] as $name => $data) {
                $definition[$name] = array_merge($data, array('value' => $this->_language_definitions[$group][$name]));
            }
            ksort($definition);
            return $definition;
        }
        return array();
    }

    /**
     * Get friendly module path from relative path
     *
     * @param string $path
     * @param bool $name_only
     * @return friendly path
     */
    public function getFriendlyModulePath($path, $name_only = false)
    {
        $path_parts = explode('/', $path);
        if ($name_only) {
            return strtolower($path_parts[2]);
        }
        return ucfirst($path_parts[0]).' - '.ucfirst($path_parts[1]).' - '.$path_parts[2];
    }

    /**
     * Get language groups
     *
     * @return array/false
     */
    public function getGroups()
    {
        // A request that started from the compiled file has not read the definitions (#4753)
        if (empty($this->_language_strings_def)) {
            $this->_language_strings_def = $this->_coreDefinitions();
        }
        //  if (!empty($this->_language_strings)) {
        if (!empty($this->_language_strings_def)) { // $this->_language_strings_def in method loadLang() defined
            if (empty($this->_language_groups)) {
                foreach ($this->_language_strings_def as $group => $strings) { // $this->_language_strings_def in method loadLang() defined
                    //    foreach ($this->_language_strings as $group => $strings) {
                    $this->_language_groups[$group] = $group;
                }
                unset($group, $strings);
            }
            natsort($this->_language_groups);
            return $this->_language_groups;
        }
        return false;
    }

    /**
     * Get current language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * Get langauge information
     *
     * @param string $language
     * @return array/false
     */
    public function getLanguageInfo($language)
    {
        $list = $this->listLanguages();
        if (is_array($list) && !empty($language) && isset($list[$language])) {
            return $list[$language];
        }
        return false;
    }

    /**
     * Get all the language strings
     *
     * @return array/false
     */
    public function getLanguageStrings()
    {
        return (!empty($this->_language_strings)) ? $this->_language_strings : false;
    }

    /**
     * Get language strings
     *
     * @param string $group
     * @return array/false
     */
    public function getStrings($group = false)
    {
        if (!empty($group)) {
            if (isset($this->_language_strings[$group])) {
                ksort($this->_language_strings[$group]);
                return (array)$this->_language_strings[$group];
            } else {
                return false;
            }
        } else {
            // return all strings?
            return (array)$this->_language_strings;
        }
    }

    /**
     * Put every email in a language back to the wording its pack came with. The subject,
     * preview text and body are replaced; whether each email is switched on is kept. An
     * email the pack does not have (one added by an extension) is left alone.
     *
     * @param string $language e.g. de-DE
     * @return int How many emails were replaced or added
     */
    public function resetEmails($language)
    {
        $file = CC_LANGUAGE_DIR.'email_'.$language.'.xml';
        if (!preg_match(self::LANG_REGEX, (string)$language) || !file_exists($file)) {
            return 0;
        }
        $data = file_get_contents($file);
        if (($gz = @gzuncompress($data)) !== false) {
            $data = $gz;
        }
        try {
            $xml = new SimpleXMLElement($data);
        } catch (Exception $e) {
            trigger_error($e->getMessage());
            return 0;
        }
        $count = 0;
        foreach ($xml->email as $email) {
            $type  = (string)$email->attributes()->name;
            $stock = $this->stockEmail($type, $language);
            if ($type === '' || !$stock) {
                continue;
            }
            // The database layer strips backslashes from every value, so shield any the wording has
            $record = array('subject' => addslashes($stock['subject']), 'preheader' => '', 'content_html' => addslashes($stock['content_html']));
            if ($GLOBALS['db']->select('CubeCart_email_content', array('content_id'), array('content_type' => $type, 'language' => $language), false, 1, false, false)) {
                $GLOBALS['db']->update('CubeCart_email_content', $record, array('content_type' => $type, 'language' => $language), true, array('subject', 'preheader', 'content_html'));
            } else {
                // Switched on or off as the same email is in the store's other languages
                $other = $GLOBALS['db']->select('CubeCart_email_content', array('enabled'), array('content_type' => $type), false, 1, false, false);
                $GLOBALS['db']->insert('CubeCart_email_content', $record + array(
                    'content_type' => $type,
                    'language'     => $language,
                    'description'  => $type,
                    'enabled'      => $other ? (int)$other[0]['enabled'] : 1,
                ));
            }
            $count++;
        }
        return $count;
    }

    /**
     * Import email language
     *
     * @param string $source
     * @param string $path
     * @return bool
     */
    public function importEmail($source, $path = CC_LANGUAGE_DIR, $content_type = '')
    {
        //Make sure the path is valid
        if (!$this->_checkPath($path)) {
            trigger_error('Invalid language path '.$path, E_USER_ERROR);
        }
        //Check to see if the path is the current language directory
        if ($path !== CC_LANGUAGE_DIR) {
            $path = appendDS($path);
        }
        if (!empty($source) && preg_match(self::EMAIL_FILE, $source, $match)) {
            $file = $path.$source;
            if (file_exists($file)) {
                //Get the file data
                $data = file_get_contents($file);
                if (($gz = @gzuncompress($data)) !== false) {
                    $data = $gz;
                    unset($gz);
                }
                try {
                    $xml = new simpleXMLElement($data);
                    if ($xml->email) {
                        $traditional_oid_col = 'cart_order_id';
                        $config_oid_col = defined('CC_IN_SETUP') ? $traditional_oid_col : $GLOBALS['config']->get('config', 'oid_col');
                        $oid_col = empty($config_oid_col) ? $traditional_oid_col : $config_oid_col;
                        foreach ($xml->email as $email) {
                            if (!empty($content_type) && $content_type !== (string)$email->attributes()->name) {
                                continue;
                            }
                            if ($email->content) {
                                $record = $this->_emailRecord($email, (string)$xml->attributes()->language, $oid_col);
                                // Only insert rows that don't yet exist. Existing rows are preserved
                                // because merchants may have intentionally edited them; we don't want
                                // a language-pack upgrade or self-heal to silently overwrite their copy.
                                if (!$GLOBALS['db']->select('CubeCart_email_content', array('content_id'), array('language' => $record['language'], 'content_type' => $record['content_type']), false, 1, false, false)) {
                                    $GLOBALS['db']->insert('CubeCart_email_content', $record);
                                }
                            }
                            unset($record);
                        }
                        return true;
                    }
                } catch (Exception $e) {
                    trigger_error($e->getMessage());
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * The wording an email came with, read from the language pack on disk. This is
     * what importEmail() would insert for a store that did not have the email yet.
     *
     * @param string $content_type e.g. cart.order_confirmation
     * @param string $language e.g. en-GB
     * @return array|false 'subject' and 'content_html', or false when the pack has no such email
     */
    public function stockEmail($content_type, $language)
    {
        if (empty($content_type) || !preg_match(self::LANG_REGEX, (string)$language)) {
            return false;
        }
        $files = array(CC_LANGUAGE_DIR.'email_'.$language.'.xml');
        // English is what the definitions are written in
        if (strpos($language, 'en-') === 0) {
            $files[] = CC_LANGUAGE_DIR.'email_definitions.xml';
        }
        $config_oid_col = $GLOBALS['config']->get('config', 'oid_col');
        $oid_col = empty($config_oid_col) ? 'cart_order_id' : $config_oid_col;
        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }
            $data = file_get_contents($file);
            if (($gz = @gzuncompress($data)) !== false) {
                $data = $gz;
            }
            try {
                $xml = new simpleXMLElement($data);
            } catch (Exception $e) {
                trigger_error($e->getMessage());
                continue;
            }
            foreach ($xml->email as $email) {
                if ($content_type === (string)$email->attributes()->name && $email->content) {
                    $record = $this->_emailRecord($email, $language, $oid_col);
                    if (!empty($record['content_html'])) {
                        return array('subject' => $record['subject'], 'content_html' => $record['content_html']);
                    }
                }
            }
        }
        return false;
    }

    /**
     * Turn one <email> of a language pack into a row for CubeCart_email_content
     *
     * @param SimpleXMLElement $email
     * @param string $language
     * @param string $oid_col The order number column this store shows its customers
     * @return array
     */
    private function _emailRecord($email, $language, $oid_col)
    {
        $traditional_oid_col = 'cart_order_id';
        $record = array();
        $record['content_type'] = (string)$email->attributes()->name;
        $record['language']  = $language;
        $record['subject']  = str_replace('DATA.'.$traditional_oid_col, 'DATA.'.$oid_col, (string)$email->subject);
        foreach ($email->content as $content) {
            // Plain text is auto-generated from HTML; skip legacy text entries
            if ((string)$content->attributes()->type === 'text') {
                continue;
            }
            // See GitHub #1511
            $record['content_'.(string)$content->attributes()->type] = str_replace(array('empty({$','})}','DATA.'.$traditional_oid_col), array('empty($',')}','DATA.'.$oid_col), trim((string)$content));
        }
        return $record;
    }

    /**
     * Import language file if valid
     *
     * @param file $file
     * @param bool $overwrite
     * @return bool
     */
    public function importLanguage($file, $overwrite = false)
    {
        $this->_language_list = null; // the list of languages is about to change
        if (!preg_match('/.xml$/', $file['name']['file'])) {
            trigger_error('Please upload a valid XML file.');
            return false;
        }

        $temp_name   = $file['tmp_name']['file'];
        $destination  = CC_LANGUAGE_DIR.$file['name']['file'];
        $file_content  = file_get_contents($temp_name);

        // Validate file
        libxml_use_internal_errors(true);
        $sxe = simplexml_load_string($file_content);
        if (!$sxe) {
            foreach (libxml_get_errors() as $error) {
                trigger_error('Failed loading XML:'. $error->message);
            }
            return false;
        } else {
            try {
                $xml = new SimpleXMLElement($file_content);
                if (empty($xml->info->title) || empty($xml->info->code)) {
                    return false;
                }
            } catch (Exception $e) {
                trigger_error($e->getMessage());
                return false;
            }
            // Check if file already exists and if we are allowed to overwrite
            if ($overwrite) {
                unlink($destination);
            } elseif (file_exists($destination)) {
                return false;
            }
            return move_uploaded_file($file['tmp_name']['file'], $destination);
        }
    }

    /**
     * List availible languages
     *
     * @param bool $cache
     * @return array/false
     */
    public function listLanguages($cache = true)
    {
        // Worked out once for a request. Every language file is read and parsed to make the
        // list, and in the admin, where the cache is off, that used to happen on each call:
        // once for every row of the products, categories and documents lists (#4755).
        if ($cache && is_array($this->_language_list)) {
            return $this->_language_list;
        }
        //Try cache first
        if ($cache && ($cached_list = $GLOBALS['cache']->read('lang.list')) !== false && is_array($cached_list)) {
            $this->_language_list = $cached_list;
            return $cached_list;
        } else {
            //Get all langauge files
            if (($files = cc_glob(CC_LANGUAGE_DIR.'*.{xml,gz}')) !== false) {
                $d = array();
                if(isset($GLOBALS['db']) && method_exists($GLOBALS['db'],'select') && $domains = $GLOBALS['db']->select('CubeCart_domains')) {
                    foreach($domains as $domain) {
                        $d[$domain['language']] = $domain['domain'];
                    }
                }
                $list = array();
                foreach ($files as $file) {
                    // Get the language code from the filename
                    $elements = explode('.', basename($file));
                    //Validate the name
                    if (preg_match(self::LANG_REGEX, $elements[0], $match)) {
                        //Get content
                        if ((($xml = file_get_contents($file, true)) === false) || empty($xml)) {
                            continue;
                        }
                        //Get the file extension
                        $ext = strtolower(substr(strrchr($file, '.'), 1));
                        //Make sure the headers for gz are there first
                        if ($ext == 'gz' && $xml[0] == 'x' && $xml[1] == "\x9c") {
                            if (($gz = gzuncompress($xml)) !== false) {
                                $xml = $gz;
                                unset($gz);
                            }
                        }
                        try {
                            $data = new simpleXMLElement($xml);
                            foreach ((array)$data->info as $key => $value) {
                                $c = trim((string)$data->info->code);
                                $list[$c][trim((string)$key)] = trim((string)$value);
                            }
                        } catch (Exception $e) {
                            trigger_error($e->getMessage());
                            return false;
                        }
                        $list[$c]['domain'] = (isset($d[$c]) && !empty($d[$c])) ? $d[$c] : '';
                        unset($data, $xml);
                    }
                }
                if (!empty($list) && is_array($list)) {
                    ksort($list);
                    $GLOBALS['cache']->write($list, 'lang.list');
                    $this->_language_list = $list;
                    return $list;
                }
            }
            return false;
        }
    }

    /**
     * Load language definitions
     *
     * @param string $name
     * @param string $path
     * @param string $file_name
     * @param bool $cache
     */
    public function loadDefinitions($name, $path = CC_LANGUAGE_DIR, $file_name = 'definitions.xml', $cache = true, $merge = true)
    {
        if (!$this->_checkPath($path)) {
            trigger_error("Invalid language path: $path - $name - $file_name", E_USER_ERROR);
        }
        if ($path !== CC_LANGUAGE_DIR) {
            $path = appendDS($path);
        }
        // Load basic language string data into a multi-dimensional array. The strings alone:
        // their attributes are read by getDefinitions() when it is asked for them (#4752)
        $file = $path.$file_name;
        $cached_def = (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) ? $GLOBALS['cache']->read($name.'.definitions') : false;
        if (is_array($cached_def)) {
            $this->_language_definitions = $cached_def;
            $this->_language_definition_data = array();
            $this->_definition_file = $file;
        } elseif (file_exists($file)) {
            // Load language definitions
            if (($parsed = $this->_parseDefinitions($file)) === false) {
                return false;
            }
            if (!empty($parsed[0])) {
                $GLOBALS['cache']->write($parsed[0], $name.'.definitions');
                $this->_language_definitions = $parsed[0];
                $this->_language_definition_data = array();
                $this->_definition_file = $file;
            }
            unset($parsed);
        }
        if($merge) {
            $this->_language_strings = merge_array($this->_language_definitions, $this->_language_strings);
        } else {
            $this->_language_strings = $this->_language_definitions;
        }
    }

    /**
     * Read a definitions file
     *
     * @param string $file
     * @return array|false The strings by group, then every string's attributes by group. False if the file cannot be read
     */
    private function _parseDefinitions($file)
    {
        $definition_data = $definition = array();
        try {
            $xml = new SimpleXMLElement(file_get_contents($file));
            foreach ($xml->group as $group) {
                $group_name = (string)$group->attributes()->name;
                if (empty($group_name)) {
                    continue;
                }
                foreach ($group->string as $string) {
                    // Get attributes of the string definition
                    $attributes = $string->attributes();
                    if (empty($attributes->name)) {
                        continue;
                    }
                    //Loop through each attributes
                    foreach ($attributes as $attr_name => $attr_value) {
                        $data[(string)$attr_name] = (string)$attr_value;
                    }
                    $definition_data[$group_name][(string)$attributes->name] = $data;
                    // String was introduced in this version, or earlier
                    if (isset($attributes->deprecated) && !empty($attributes->deprecated) && version_compare((string)$attributes->deprecated, CC_VERSION, '<=')) {
                        continue;
                    }
                    // String has not been deprecated, so add this string to the list, and set the default value
                    $definition[$group_name][(string)$attributes->name] = (string)$string;
                }
                unset($group);
            }
        } catch (Exception $e) {
            trigger_error($e->getMessage());
            return false;
        }
        return array($definition, $definition_data);
    }

    /**
     * Load language
     */
    public function loadLang()
    {
        // The strings a request starts with (the definitions, the language's own files and the
        // phrases customised in the admin, merged) are kept as a PHP file where opcache is on.
        // Opcache hands every request the same copy from shared memory, so there is nothing to
        // read, parse or merge. In the admin, where the cache is off, they were read from the
        // XML files on every page (#4753).
        if (empty($this->_language_strings) && $this->_compiledAllowed() && ($this->_loadCompiled() || $this->_buildCompiled())) {
            $this->_setLocale();
            return;
        }

        //Load the core language files
        $this->loadDefinitions('lang.core');
        $this->_language_strings_def = $this->_language_strings; // Admin Langs - show default core groups only

        $this->loadLanguageXML('lang.core');

        //Set the system locale
        $this->_setLocale();
    }

    /**
     * Whether the core strings can be kept as a compiled PHP file. Opcache has to be on for this
     * kind of request: without it, reading the file back costs as much as building the strings.
     *
     * @return bool
     */
    private function _compiledAllowed()
    {
        return !(defined('CC_IN_SETUP') && CC_IN_SETUP)
            && extension_loaded('Zend OPcache') && (bool)ini_get('opcache.enable')
            && (PHP_SAPI !== 'cli' || (bool)ini_get('opcache.enable_cli'))
            && preg_match('/^[A-Za-z0-9_-]{2,20}$/', (string)$this->_language) === 1;
    }

    /**
     * The compiled file for the language in use. It sits in the cache folder, where every
     * clearing of the cache removes it along with everything else: the Clear Cache button, a
     * phrase saved on the Languages screen and a language pack imported all do.
     *
     * @return string
     */
    private function _compiledFile()
    {
        return CC_CACHE_DIR.'lang.'.$this->_language.'.php';
    }

    /**
     * What a source file looked like, to tell whether it has changed since
     *
     * @param string $path
     * @return string
     */
    private function _stamp($path)
    {
        return file_exists($path) ? filemtime($path).':'.filesize($path) : '';
    }

    /**
     * Start from the compiled file, if there is one that can be trusted: built by this version,
     * within the last day, from language files that are still as they were.
     *
     * @return bool
     */
    private function _loadCompiled()
    {
        $file = $this->_compiledFile();
        if (!is_file($file) || ($built = filemtime($file)) === false || $built < time() - self::COMPILED_LIFETIME) {
            return false;
        }
        try {
            $compiled = include $file;
        } catch (Throwable $e) {
            return false; // a damaged file is no reason to stop: it is built again below
        }
        if (!is_array($compiled) || !isset($compiled['version'], $compiled['sources'], $compiled['info'], $compiled['strings'])
                || $compiled['version'] !== CC_VERSION || !is_array($compiled['sources']) || !is_array($compiled['info'])
                || !is_array($compiled['strings']) || empty($compiled['strings'])) {
            return false;
        }
        foreach ($compiled['sources'] as $path => $stamp) {
            if ($this->_stamp($path) !== $stamp) {
                return false;
            }
        }
        $this->_language_strings = $compiled['strings'];
        $this->_language_data = $compiled['info'];
        return true;
    }

    /**
     * Build the core strings from the language files and the database themselves, never from
     * the cache, which may hold them as they were up to a day ago, and keep the result as the
     * compiled file.
     *
     * @return bool False when it cannot be done here, and the strings are loaded the old way
     */
    private function _buildCompiled()
    {
        $file = $this->_compiledFile();
        $definition_file = CC_LANGUAGE_DIR.'definitions.xml';
        if (!is_writable(CC_CACHE_DIR) || !file_exists($definition_file)) {
            return false;
        }
        // Taken before anything is read, so a file changed meanwhile is noticed next time. The
        // folder is among them: a file added to it beside the others changes what is read.
        $sources = array(CC_LANGUAGE_DIR => '', $definition_file => '');
        $own_files = $this->_languageFiles(CC_LANGUAGE_DIR.$this->_language);
        if (is_array($own_files)) {
            foreach ($own_files as $own_file) {
                $sources[$own_file] = '';
            }
        }
        foreach ($sources as $path => $stamp) {
            $sources[$path] = $this->_stamp($path);
        }

        if (($definitions = $this->_parseDefinitions($definition_file)) === false || empty($definitions[0])
                || ($own = $this->_parseLanguage(CC_LANGUAGE_DIR, $this->_language, true, true)) === false) {
            return false;
        }
        // What loadDefinitions() and loadLanguageXML() leave behind, in the same order
        $this->_language_definitions = $definitions[0];
        $this->_language_definition_data = array();
        $this->_definition_file = $definition_file;
        $this->_language_strings_def = $definitions[0];
        $this->_language_strings = empty($own[0]) ? $definitions[0] : merge_array($definitions[0], $own[0]);
        if (is_array($own[1])) {
            $this->_language_data = $own[1];
        }
        // The cache is brought up to date as well: change() and the Languages screen read from it
        if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
            $GLOBALS['cache']->write($definitions[0], 'lang.core.definitions');
            $GLOBALS['cache']->write($own[0], 'lang.lang.core.xml.'.$this->_language);
            if (is_array($own[1])) {
                $GLOBALS['cache']->write($own[1], 'lang.info.'.$this->_language);
            }
        }

        $php = "<?php\n"
             . "// The core language strings for ".$this->_language.", built by CubeCart from the files in /language and the\n"
             . "// phrases customised in the admin. Not for editing: it is built again whenever the cache is cleared.\n"
             . "if (!defined('CC_INI_SET')) {\n    die('Access Denied');\n}\n"
             . 'return '.var_export(array('version' => CC_VERSION, 'sources' => $sources, 'info' => $this->_language_data, 'strings' => $this->_language_strings), true).";\n";
        // Written beside it and moved into place, so no request ever reads half a file
        $partial = $file.'.'.bin2hex(random_bytes(4)).'.tmp';
        if (file_put_contents($partial, $php) === strlen($php) && rename($partial, $file)) {
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate($file, true); // where opcache does not look at file times
            }
        } elseif (file_exists($partial)) {
            unlink($partial);
        }
        return true;
    }

    /**
     * The core definitions, for a request that started from the compiled file and now wants them
     *
     * @return array
     */
    private function _coreDefinitions()
    {
        $cached = (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) ? $GLOBALS['cache']->read('lang.core.definitions') : false;
        if (is_array($cached)) {
            return $cached;
        }
        $file = CC_LANGUAGE_DIR.'definitions.xml';
        if (file_exists($file) && ($parsed = $this->_parseDefinitions($file)) !== false && !empty($parsed[0])) {
            if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
                $GLOBALS['cache']->write($parsed[0], 'lang.core.definitions');
            }
            return $parsed[0];
        }
        return array();
    }

    /**
     * Read a language's own files, and the phrases customised for it in the admin
     *
     * @param string $path
     * @param string $language
     * @param bool $load_custom
     * @param bool $fresh Read the customised phrases from the database itself, not from a kept query result
     * @return array|false The strings by group, then the language's details (false where its files give none). False if the files cannot be read
     */
    private function _parseLanguage($path, $language, $load_custom = true, $fresh = false)
    {
        $strings = array();
        $info = false;
        $data = $this->_extractXML($path.$language);
        if (!empty($data)) {
            try {
                $xml = new SimpleXMLElement($data);
                if (!empty($xml)) {
                    if (!empty($xml->info)) {
                        $info = array();
                        foreach ((array)$xml->info as $key => $value) {
                            $info[$key] = (string)$value;
                        }
                    }
                    switch (floor((float)$xml->attributes()->version)) {
                    case 2:
                        // New format - Similar layout to the definition file
                        if ($xml->translation && $xml->translation->group) {
                            foreach ($xml->translation->group as $groups) {
                                $group = (string)$groups->attributes()->name;
                                foreach ($groups->string as $string) {
                                    $xml_name = $string->attributes()->name;
                                    $strings[(string)$group][(string)$xml_name] = trim((string)$string);
                                }
                            }
                            unset($groups, $group, $xml_name, $string);
                        }
                        break;
                    default:
                        trigger_error('Language format error - exiting.', E_USER_WARNING);
                        die;
                    }
                }
            } catch (Exception $e) {
                trigger_error($e->getMessage());
                return false;
            }

            // Load custom strings from database
            if ($load_custom && isset($GLOBALS['db']) && ($custom = $GLOBALS['db']->select('CubeCart_lang_strings', false, array('language' => $language), false, false, false, !$fresh)) !== false) {
                foreach ($custom as $string) {
                    $strings[(string)$string['type']][(string)$string['name']] = $string['value'];
                }
            }
        }
        return array($strings, $info);
    }

    /**
     * Load language XML file(s)
     *
     * @param string $name
     * @param string $language
     * @param string $path
     * @param bool $merge
     * @param bool load_custom
     * @return array/false
     */
    public function loadLanguageXML($name, $language = '', $path = CC_LANGUAGE_DIR, $merge = true, $load_custom = true)
    {
        $language = (empty($language)) ? $this->_language : $language;
        if (!$this->_checkPath($path)) {
            trigger_error('Invalid language path '.$path, E_USER_ERROR);
        }
        if ($path !== CC_LANGUAGE_DIR) {
            $path = appendDS($path);
        }
        $cache_name_info = 'lang.info.'.$language;
        $cache_name_strings = 'lang.'.$name.'.xml.'.$language;

        $cached_strings = (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) ? $GLOBALS['cache']->read($cache_name_strings) : false;
        $cached_info = (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) ? $GLOBALS['cache']->read($cache_name_info) : false;
        if (is_array($cached_strings) && is_array($cached_info)) {
            $strings = $cached_strings;
            $this->_language_data = $cached_info;
        } else {
            if (($parsed = $this->_parseLanguage($path, $language, $load_custom)) === false) {
                return false;
            }
            $strings = $parsed[0];
            if (is_array($parsed[1])) {
                $GLOBALS['cache']->write($parsed[1], $cache_name_info);
                $this->_language_data = $parsed[1];
            }
            unset($parsed);
            if (!empty($this->_language_strings)) {
                $GLOBALS['cache']->write($strings, $cache_name_strings);
            }
        }

        if ($merge && !empty($strings)) {
            $this->_language_strings = merge_array($this->_language_strings, $strings);
        }

        return (!empty($strings)) ? $strings : false;
    }

    /**
     * Save XML file
     *
     * @param string $language
     * @param bool $compress
     * @param bool $replace
     * @param string $path
     * @return bool
     */
    public function saveLanguageXML($language, $compress = false, $replace = false, $path = CC_LANGUAGE_DIR)
    {
        if (!$this->_checkPath($path)) {
            trigger_error('Invalid language path '.$path, E_USER_ERROR);
        }
        if ($path !== CC_LANGUAGE_DIR) {
            $path = appendDS($path);
        }

        if (!empty($language)) {
            // Load in existing file
            $source = $path.$language.'.xml';
            $this->exported_lang_file = ($replace) ? $source : $path.$language.'-custom.xml';
            $strings = array();

            if (file_exists($source)) {
                $data = file_get_contents($source);
                try {
                    $xml = new SimpleXMLElement($data);
                    foreach ($xml->info as $values) {
                        foreach ($values as $key => $value) {
                            $info[$key] = $value;
                        }
                    }
                    if ($xml->translation && $xml->translation->group) {
                        foreach ($xml->translation->group as $groups) {
                            $group = $groups->attributes()->name;
                            foreach ($groups->string as $string) {
                                $name = $string->attributes()->name;
                                $strings[(string)$group][(string)$name] = $string;
                            }
                        }
                    }
                } catch (Exception $e) {
                    trigger_error($e->getMessage());
                    return false;
                }
                unset($data, $xml);

                // Fetch Database Results
                if (($custom = $GLOBALS['db']->select('CubeCart_lang_strings', false, array('language' => $language))) !== false) {
                    foreach ($custom as $row) {
                        $strings[$row['type']][$row['name']] = $row['value'];
                    }
                } else {
                    trigger_error('No custom strings exist', E_USER_NOTICE);
                }
                $xml = new XML();
                $xml->startElement('language', array('version' => '2.0'));
                $xml->startElement('info');
                foreach ($info as $key => $value) {
                    $xml->setElement($key, $value, false, false);
                }
                $xml->endElement();
                $xml->startElement('translation');
                foreach ($strings as $group => $values) {
                    $xml->startElement('group', array('name' => $group));
                    foreach ($values as $name => $value) {
                        $xml->setElement('string', $value, array('name' => $name));
                    }
                    $xml->endElement();
                }
                $xml->endElement();
                $xml->endElement();
                if ($compress) {
                    $output = gzencode($xml->getDocument(), 9, FORCE_GZIP);
                    $this->exported_lang_file .= '.gz';
                } else {
                    $output = $xml->getDocument();
                }

                return (bool)file_put_contents($this->exported_lang_file, $output);
            }
        }
        return false;
    }

    /**
     * Sets all the LANG variables in the templates
     */
    public function setTemplate()
    {
        $lang_data = $this->getData();

        //Assign left to right or right to left
        $text_dir = isset($lang_data['text-direction']) ? $lang_data['text-direction'] : 'ltr';
        $GLOBALS['smarty']->assign('TEXT_DIRECTION', $text_dir);

        //Assign character set
        $char_set = isset($lang_data['character_set']) ? $lang_data['character_set'] : 'utf-8';
        $GLOBALS['smarty']->assign('CHARACTER_SET', $char_set);

        //Assign all language values
        $this->assignLang();
    }

    /**
     * Translate a category
     *
     * @param array $category
     * @return array/false
     */
    public function translateCategory(&$category)
    {
        if (!empty($category)) {
            if ($this->_language != $GLOBALS['config']->get('config', 'default_language')) {
                if (($translation = $GLOBALS['db']->select('CubeCart_category_language', false, array('cat_id' => $category['cat_id'], 'language' => $this->_language))) !== false) {
                    $category = array_merge($category, $translation[0]);
                }
            }
            return $category;
        }
        return false;
    }

    /**
     * Translate a document
     *
     * @param array $document
     * @return array/false
     */
    public function translateDocument(&$document)
    {
        if (!empty($document)) {
            if ($this->_language != $GLOBALS['config']->get('config', 'default_language')) {
                if (($translation = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_parent_id' => $document['doc_id'], 'doc_lang' => $this->_language))) !== false) {
                    $document = array_merge($document, $translation[0]);
                }
            }
            return $document;
        }
        return false;
    }

    /**
     * Translate a product
     *
     * @param array $product
     * @return array/false
     */
    public function translateProduct(&$product)
    {
        if (!empty($product)) {
            if ($this->_language != $GLOBALS['config']->get('config', 'default_language')) {
                if (($translation = $GLOBALS['db']->select('CubeCart_inventory_language', false, array('product_id' => (int)$product['product_id'], 'language' => $this->_language))) !== false) {
                    $product = array_merge($product, $translation[0]);
                }
            }
            return $product;
        }
        return false;
    }

    //=====[ Private ]=======================================

    /**
     * Checks to make sure the path passed in is valid
     *
     * @param string $path
     * @return bool
     */
    private function _checkPath($path)
    {
        if (empty($path)) {
            return false;
        }
        //If the path is the Language directory it should already be valid
        if ($path == CC_LANGUAGE_DIR) {
            return true;
        }

        //Append the DS if needed
        $path = appendDS($path);

        return is_dir($path) && file_exists($path);
    }

    /**
     * Extract XML information
     *
     * @param string $language
     * @return array/false
     */
    /**
     * The files that make up a language: its own, a gzipped copy, and a -custom file beside them
     *
     * @param string $language The language code, with the folder in front of it
     * @return array|false
     */
    private function _languageFiles($language)
    {
        return cc_glob($language.'*{-custom,}.xml*', GLOB_NOSORT);
    }

    private function _extractXML($language)
    {
        if ((($files = $this->_languageFiles($language)) !== false) && !empty($files)) {
            $merged_addon_strings = '<?xml version="1.0"?><language version="2.0">';
            foreach ($files as $file) {
                if (substr($file, -3) == '.gz') {
                    // Extract GZipped content
                    $xml_data = simplexml_load_file(gzuncompress($file.'.gz'));
                } else {
                    $xml_data = simplexml_load_file($file);
                }
                if (is_object($xml_data->info)) {
                    foreach ($xml_data->info as $element) {
                        $merged_addon_strings .= $element->asXML();
                    }
                }
                $merged_addon_strings .= '<translation>';
                if (is_object($xml_data->translation->group)) {
                    foreach ($xml_data->translation->group as $element) {
                        $merged_addon_strings .= $element->asXML();
                    }
                }
                if (is_object($xml_data->translation->translate)) {
                    foreach ($xml_data->translation->translate as $element) {
                        $merged_addon_strings .= $element->asXML();
                    }
                }
                $merged_addon_strings .= '</translation>';
            }
            $merged_addon_strings .= '</language>';
            return (!empty($merged_addon_strings)) ? $merged_addon_strings : false;
        }
        return false;
    }

    /**
     * Set Locale
     */
    private function _setLocale()
    {
        setlocale(LC_ALL, 'en_GB.UTF-8');
    }

    /**
     * Validate language
     *
     * @param string $language
     * @return bool
     */
    private function _valid($language)
    {
        if (!preg_match(self::LANG_REGEX, $language)) {
            return false;
        }
        return file_exists(CC_LANGUAGE_DIR.$language.'.xml');
    }
}
