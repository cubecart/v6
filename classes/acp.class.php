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
 * ACP controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class ACP
{

    /**
     * Hide navigation on the admin screen
     *
     * @var bool
     */
    private $_hide_navigation = false;
    /**
     * Navigation
     *
     * @var array
     */
    private $_navigation  = array();
    /**
     * Tabs
     *
     * @var array
     */
    private $_tabs    = array();
    /**
     * Tab key prefix
     *
     * @var string
     */
    private $_tab_key_prefix    = 'k_';
    /**
     * Tabs Priority
     *
     * @var int
     */
    private $_tab_priority    = 0;
    /**
     * Wiki namespace
     *
     * @var string
     */
    private $_wiki_namespace = 'ACP';
    /**
     * Wiki page
     *
     * @var string
     */
    private $_help_doc   = null;

    /**
     * Class instance
     *
     * @var self
     */
    protected static $_instance;

    ##############################################

    final private function __construct()
    {
    }

    /**
     * Setup the instance (singleton)
     *
     * @return ACP
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
     * Add a admin navigation item
     *
     * @param string $group
     * @param array $array (name => url)
     */
    public function addNavItem($group, $array)
    {
        if (!empty($array)) {
            foreach ($array as $name => $url) {
                if (is_array($url)) {
                    $this->_navigation[$group][] = array(
                        'name' => strip_tags($name),
                        'url' => $url['address'],
                        'target' => (isset($url['target']) && !empty($url['target'])) ? $url['target'] : '_self',
                        'id' => (isset($url['id']) && !empty($url['id'])) ? $url['id'] : '',
                        'icon' => (isset($url['icon']) && !empty($url['icon'])) ? $url['icon'] : ''
                    );
                } else {
                    $this->_navigation[$group][] = array(
                        'name' => strip_tags($name),
                        'url' => $url,
                        'target' => '_self',
                        'id' => ''
                    );
                }
            }
        }
    }

    /**
     * Add a tab control
     *
     * @param string $name
     * @param string $target
     * @param string $url
     * @param string $accesskey
     * @param string $notify_count
     * @return bool
     */
    public function addTabControl($name, $target = '', $url = null, $accesskey = null, $notify_count = false, $a_target = '_self', $priority = null, $onclick = '')
    {
        if (!empty($name)) {
            $url = (!empty($url) && is_array($url)) ? currentPage(null, $url) : $url;
            $url = is_null($url) ? '' : preg_replace('/(#.*)$/i', '', $url);
            $priority = $this->_setTabPriority($priority);
            $this->_tabs[$this->_tab_key_prefix.$priority] = array(
                'name'  => $name,
                'target' => $target,
                'url'  => $url,
                'accesskey' => $accesskey,
                'notify' => number_format((float)$notify_count),
                'a_target' => $a_target,
                'onclick' => $onclick
            );
            return true;
        }
        return false;
    }

    /**
     * Log admin message
     *
     * @param string $message
     */
    public function adminLog($message)
    {
        $item_id = null;
        $item_type = null;
        if(isset($_REQUEST['order_id'])) {
            $item_id = $_REQUEST['order_id'];
            $item_type = 'oid';
        }
        if(isset($_REQUEST['product_id'])) {
            $item_id = $_REQUEST['product_id'];
            $item_type = 'prod';
        }
        if(isset($_REQUEST['cat_id'])) {
            $item_id = $_REQUEST['cat_id'];
            $item_type = 'cat';
        }
        if(isset($_REQUEST['doc_id'])) {
            $item_id = $_REQUEST['doc_id'];
            $item_type = 'doc';
        }

        if (!empty($message)) {
            $record = array(
                'admin_id'  => Admin::getInstance()->getId(),
                'ip_address' => get_ip_address(),
                'time'   => time(),
                'description' => $message,
                'item_id' => $item_id,
                'item_type' => $item_type
            );
            
            $log_days = $GLOBALS['config']->get('config', 'r_admin_activity');
            if (ctype_digit((string)$log_days) &&  $log_days > 0) {
                $GLOBALS['db']->insert('CubeCart_admin_log', $record);
                if (executionChance(10)) { // 10% probability
                    $GLOBALS['db']->delete('CubeCart_admin_log', 'time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL '.$log_days.' DAY))', 500);
                }
            } elseif (empty($log_days) || !$log_days) {
                $GLOBALS['db']->insert('CubeCart_admin_log', $record);
            }
        }
    }

    /**
     * Get category path
     *
     * @param int $cat_id
     * @param int $i
     * @return array|false
     */
    public function getCategoryPath($cat_id, $i = 0)
    {
        // get the path for a single category
        if (is_numeric($cat_id)) {
            if (($parent = $GLOBALS['db']->select('CubeCart_category', array('cat_id', 'cat_parent_id', 'cat_name'), array('cat_id' => (int)$cat_id))) !== false) {
                $data = [];
                $data[$i] = $parent[0];
                if (((int)$parent[0]['cat_parent_id']) != 0) {
                    ++$i;
                    $data = array_merge($data, $this->getCategoryPath($parent[0]['cat_parent_id'], $i));
                }
                sort($data);
                return $data;
            }
        }
        return false;
    }

    /**
     * Hide admin navigation
     *
     * @param bool $status
     */
    public function hideNavigation($status = false)
    {
        $this->_hide_navigation = (bool)$status;
    }

    /**
     * Import admin node
     *
     * @param string $request
     * @param string $node
     * @return string
     */
    public function importNode($request, $node = false)
    {
        $base = CC_ROOT_DIR.'/'.$GLOBALS['config']->get('config', 'adminFolder').'/'.'sources'.'/';
        $node = (!empty($node)) ? $node : 'index';

        $source = implode('.', array($request, $node, 'inc.php'));

        if (file_exists($base.$source)) {
            return $base.$source;
        } else {
            if (!is_dir($base.$request) && file_exists($base.$request.'inc.php')) {
                $source = CC_ROOT_DIR.'/'.$GLOBALS['config']->get('config', 'adminFolder').'/'.'sources/'.$request.'.inc.php';
            } else {
                $source = CC_ROOT_DIR.'/'.$GLOBALS['config']->get('config', 'adminFolder').'/'.'sources/'.$request.'/'.$node.'.inc.php';
            }
            return $source;
        }
    }

    /**
     * Amount of items to list per page
     *
     * @param string $list_name
     * @param int $requested_amount
     * @param int $default_amount
     * @return int
     */
    public function itemsPerPage($list_name, $requested_amount = 0, $default_amount = 25) {
        $cookie_name = 'cc_rs_limit';
        if(isset($_COOKIE[$cookie_name])) {
            $rs_limit = json_decode(html_entity_decode($_COOKIE[$cookie_name],ENT_QUOTES), true);
            if(!is_array($rs_limit)) {
                $rs_limit = array();
            }
        } else {
            $rs_limit = array();
        }
        if($requested_amount>0) {
            $cookie_value = (count($rs_limit) > 0) ? array_merge($rs_limit, array((string)$list_name => (int)$requested_amount)) : array((string)$list_name => (int)$requested_amount);
            $GLOBALS['session']->set_cookie($cookie_name, json_encode($cookie_value), time() + (3600*24*30));
            return (int)$requested_amount;
        } else if(is_array($rs_limit) && isset($rs_limit[$list_name]) && $rs_limit[$list_name]>0) {
            return (int)$rs_limit[$list_name];
        } else {
            return (int)$default_amount;
        }
    }
    
    /**
     * Get latest release URL
     *
     * @return string
     */
    public function newFeatureRedir() {
        $release_notes_path = CC_ROOT_DIR.'/'.$GLOBALS['config']->get('config', 'adminFolder').'/sources/release_notes/*.inc.php';
        $list = glob($release_notes_path);
        arsort($list, SORT_NATURAL);
        foreach ($list as $filename) {
            $version = basename($filename);
            $version = str_replace('.inc.php', '', $version);
            return '?_g=release_notes&node='.$version;
        }
    }
    
    /**
     * Get release notes
     *
     * @return string
     */
    public function newFeatures($version, $features, $total, $notes = '', $security = array()) {
        $li = '';
        $release_notes_path = CC_ROOT_DIR.'/'.$GLOBALS['config']->get('config', 'adminFolder').'/sources/release_notes/*.inc.php';
        $options = '';
        $list = glob($release_notes_path);
        arsort($list, SORT_NATURAL);
        foreach ($list as $filename) {
            $version = basename($filename);
            $version = str_replace('.inc.php', '', $version);
            $selected = (isset($_GET['node']) && $_GET['node'] == $version) ? ' selected="selected"' : '';
            $options .= '<option value="?_g=release_notes&node='.$version.'"'.$selected.'>'.$version.'</option>';
        }
        $switcher = "<select name=\"version\" class=\"select_url\">".$options."</select>";
        if(!empty($features)) {
            foreach($features as $id => $feature) {
                $security_class = in_array($id, $security) ? 'security' : '' ;
                if (preg_match('/^GHSA-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}$/i', $id)) {
                    $url = 'https://github.com/cubecart/v6/security/advisories/'.$id;
                    $security_class = 'security';
                } else {
                    $url = 'https://github.com/cubecart/v6/issues/'.$id;
                    $id = '#'.$id;
                }
                
                $li .= "<tr><td class=\"text-left $security_class\" valign=\"top\"><a href=\"$url\" target=\"_blank\">$id</a></td><td valign=\"top\">$feature</td></tr>";
            }
        } else {
            $li = "<tr><td  colspan=\"2\">This is a maintenance release with no new features of any significance.</td></tr>";
        }
        $node_escaped = htmlspecialchars($_GET['node'] ?? '', ENT_QUOTES, 'UTF-8');
        $page_content = <<<END
        <div id="general" class="tab_content">
            <h3 style="clear: right;">CubeCart {$node_escaped}</h3>
            <p>The table below shows changes in this version.</p>
            $notes
            <table class="new_features">
            <thead><tr><th>Issue</th><th><span>Version: $switcher</span>Description</th></tr></thead>
            <tbody>
            $li
            <tr><td colspan="2" class="text-center"><a href="https://github.com/cubecart/v6/issues?q=is%3Aclosed+milestone%3A{$node_escaped}" target="_blank" class="button">View all $total closed issues for {$node_escaped}</a> <a href="?" target="_self" class="button">Dashboard</a></td></tr>
            </tbody>
            </table>
        </div>
        END;
        return $page_content;
    }

    /**
     * Remove tab control
     *
     * @param string $name
     * @return self
     */
    public function removeTabControl($name)
    {
        if (!empty($name)) {
            foreach ($this->_tabs as $key => $tab) {
                if ($tab['name'] == $name) {
                    unset($this->_tabs[$key]);
                }
            }
        }
        return $this;
    }

    /**
     * Setup admin data
     */
    public function setTemplate()
    {
        if (Admin::getInstance()->is()) {
            $full_name = trim(Admin::getInstance()->get('name'));
            $names = explode(' ', $full_name);

            $GLOBALS['smarty']->assign('ADMIN_USER_FIRST_NAME', $names[0]);
            $GLOBALS['smarty']->assign('ADMIN_USER', $full_name);
            $GLOBALS['smarty']->assign('ADMIN_UID', Admin::getInstance()->getId());

            if (Admin::getInstance()->get('tour_shown')=='0') {
                $GLOBALS['smarty']->assign('TOUR_AUTO_START', 'true');
            } else {
                $GLOBALS['smarty']->assign('TOUR_AUTO_START', 'false');
            }
        }
    }

    /**
     * Set admin error message
     * AN ALIAS TO A BADLY NAMED METHOD (setACPWarning)
     *
     * @param string $message
     */
    public function errorMessage($message, $show_once = false, $display = true)
    {
        $this->setACPWarning($message, $show_once, $display);
    }

    /**
     * Set admin success message
     * AN ALIAS TO A BADLY NAMED METHOD (setACPNotify)
     *
     * @param string $message
     */
    public function successMessage($message, $log = true)
    {
        $this->setACPNotify($message, $log);
    }

    /**
     * Set admin success notice
     * LEFT FOR BACKWARD COMPATIBILITY
     *
     * @param string $message
     */
    public function setACPNotify($message, $log = true)
    {
        $GLOBALS['gui']->setNotify($message);
        // Add record to admin log
        if($log) {
            $this->adminLog($message);
        }
    }

    /**
     * Set admin error notice
     * LEFT FOR BACKWARD COMPATIBILITY
     *
     * @param string $message
     */
    public function setACPWarning($message, $show_once = false, $display = true)
    {
        if (empty($message)) {
            return;
        }
        // Log message and don't show again to current staff member
        if ($show_once) {
            if (!$GLOBALS['db']->select('CubeCart_admin_error_log', 'log_id', array('message' => $message, 'admin_id' => Admin::getInstance()->get('admin_id')))) {
                $log_days = $GLOBALS['config']->get('config', 'r_admin_error');
                if (ctype_digit((string)$log_days) &&  $log_days > 0) {
                    $GLOBALS['db']->insert('CubeCart_admin_error_log', array('message' => $message, 'admin_id' => Admin::getInstance()->get('admin_id'), 'time' => time()));
                    if (executionChance(10)) { // 10% probability
                        $GLOBALS['db']->delete('CubeCart_admin_error_log', 'time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL '.$log_days.' DAY))', 500);
                    }
                } elseif (empty($log_days) || !$log_days) {
                    $GLOBALS['db']->insert('CubeCart_admin_error_log', array('message' => $message, 'admin_id' => Admin::getInstance()->get('admin_id'), 'time' => time()));
                }

                if ($display) {
                    $GLOBALS['gui']->setError($message);
                }
            }
        } elseif ($display) {
            $GLOBALS['gui']->setError($message);
        }
    }

    /**
     * Show help
     */
    public function showHelp()
    {
        if (Admin::getInstance()->is()) {
            if (empty($this->_help_doc)) {
                if (isset($_GET['_g']) && !empty($_GET['_g'])) {
                    $pages = [];
                    $pages[] = $_GET['_g'];
                    if (isset($_GET['node']) && !empty($_GET['node']) && strtolower($_GET['node']) != 'index') {
                        $pages[] = $_GET['node'];
                    }
                    if (isset($_GET['action']) && !empty($_GET['action'])) {
                        $pages[] = $_GET['action'];
                    }
                    $this->_help_doc = implode(' ', $pages);
                }
            }

            $this->_help_doc = preg_replace('#\W#', '_', ucwords($this->_help_doc));
            $help_file = CC_ROOT_DIR.'/admin/docs/content/'.$this->_help_doc.'.md';
            if (file_exists($help_file)) {
                $GLOBALS['smarty']->assign('HELP_URL', $GLOBALS['rootRel'].'admin/docs/?page='.$this->_help_doc);
            }
            $GLOBALS['smarty']->assign('STORE_STATUS', !(bool)Config::getInstance()->get('config', 'offline'));
        }
    }

    /**
     * Show admin tabs
     *
     * @return bool
     */
    public function showTabs()
    {
        if (Admin::getInstance()->is() && !empty($this->_tabs) && is_array($this->_tabs)) {
            ksort($this->_tabs);
            $tabs = [];
            foreach ($this->_tabs as $tab) {
                $tab['name'] = ucfirst($tab['name']);
                $tab['tab_id'] = empty($tab['target']) ? '' : 'tab_'.str_replace(' ', '_', $tab['target']);
                $tab['target'] = (!empty($tab['target'])) ? '#'.$tab['target'] : '';
                $tabs[] = $tab;
            }
            foreach ($GLOBALS['hooks']->load('admin.tabs') as $hook) {
                include $hook;
            }
            $GLOBALS['smarty']->assign('TABS', $tabs);
            return true;
        }
        return false;
    }

    /**
     * Show admin navigation
     *
     * @return bool
     */
    public function showNavigation()
    {
        if (Admin::getInstance()->is() && is_array($this->_navigation) && !$this->_hide_navigation) {
            //Try cache first
            $admin_session_language = Admin::getInstance()->get('language');
            $GLOBALS['smarty']->assign('val_admin_lang', $admin_session_language);
            if (($navigation = $GLOBALS['cache']->read('acp.showNavigation.'.$admin_session_language)) === false) {
                $navigation = array();
                foreach ($this->_navigation as $group => $menu) {
                    $title = $group;
                    $group = str_replace(' ', '_', $group);
                    
                    if (isset($_COOKIE['cc_nav_'.$group])) {
                        $visible = $_COOKIE['cc_nav_'.$group];
                    } else {
                        $visible = 'true';
                    }

                    $item = array(
                        'title' => $title,
                        'group' => $group,
                        'visible' => $visible
                    );
                    
                    foreach ($menu as $submenu) {
                        $item['members'][] = array(
                            'title' => ucwords($submenu['name']),
                            'url' => $submenu['url'],
                            'target' => isset($submenu['target']) ? $submenu['target'] : '',
                            'id' => isset($submenu['id']) ? $submenu['id'] : '',
                            'icon' => isset($submenu['icon']) ? $submenu['icon'] : ''
                        );
                    }
                    $navigation[] = $item;
                }
                $GLOBALS['cache']->write($navigation, 'acp.showNavigation');
            }
            $GLOBALS['smarty']->assign('NAVIGATION', $navigation);
            return true;
        }
        return false;
    }

    /**
     * Setup wiki
     *
     * @param string $ns
     * @param string $page
     */
    public function wiki($ns = null, $page = null)
    {
        $this->wikiNamespace($ns);
        $this->wikiPage($page);
    }

    /**
     * Setup wiki namespace
     *
     * @param string $ns
     */
    public function wikiNamespace($ns)
    {
        if (!empty($ns)) {
            $this->_wiki_namespace = ucfirst($ns);
        }
    }

    /**
     * Setup wiki page
     *
     * @param string $page
     */
    public function wikiPage($page = null)
    {
        $this->_help_doc = (!empty($page)) ? ucwords($page) : 'Index';
    }

    //=====[ Private ]=======================================

    /**
     * Set tab priority
     *
     * @param null/int/float $priority
     */
    private function _setTabPriority($priority = null)
    {
        if ($priority>0) {
            if (isset($this->_tabs[$this->_tab_key_prefix.$priority])) {
                $priority += 0.01;
                return $this->_setTabPriority($priority);
            } else {
                return $priority;
            }
        } else {
            $priority = $this->_tab_priority;
            $this->_tab_priority++;
            return $priority;
        }
    }
}
