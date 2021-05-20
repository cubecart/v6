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
 * Admin controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Admin
{
    /**
     * Admin's data
     *
     * @var array
     */
    private $_admin_data = array();
    /**
     * Logged in?
     *
     * @var bool
     */
    private $_logged_in  = false;
    /**
     * Permission array
     *
     * @var array
     */
    private $_permissions = array();
    /**
     * Permissions sections
     *
     * @var array
     */
    private $_sections  = array();
    /**
     * Length of validation key
     *
     * @var int
     */
    private $_validate_key_len  = 32;

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################

    final private function __construct()
    {

        // Logout requests
        if (isset($_GET['_g']) && $_GET['_g'] == 'logout') {
            $this->logout($_GET['r']);
        }

        // Ensure the ACP is only ever using the default currency
        if (ADMIN_CP==true) {
            $GLOBALS['session']->set('currency', $GLOBALS['config']->get('config', 'default_currency'), 'client');
        }

        // Action Auto-Handlers
        if (isset($_POST['username']) && isset($_POST['password']) && !empty($_POST['username']) && !empty($_POST['password'])) {
            // Login requests
            $this->_authenticate($_POST['username'], $_POST['password']);
        }
        // Load admin data
        $this->_load();
    }

    /**
     * Setup the instance (singleton)
     *
     * @return Admin
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
     * Get admin data element or the entire array if element is empty
     *
     * @param string $element
     * @return mixed
     */
    public function get($element)
    {
        if (!empty($element)) {
            return isset($this->_admin_data[$element]) ? $this->_admin_data[$element] : false;
        } else {
            return $this->_admin_data;
        }
    }

    /**
     * Get the admin id
     *
     * @return int
     */
    public function getId()
    {
        return isset($this->_admin_data['admin_id']) ? $this->_admin_data['admin_id'] : 0;
    }

    /**
     * Is admin user
     *
     * @param bool $force_login
     * @return bool
     */
    public function is($force_login = false)
    {
        if (!$force_login) {
            return $this->_logged_in;
        } else {
            if (!$this->_logged_in) {
                httpredir('?_a=login');
            }
            return true;
        }
    }

    /**
     * Logout of admin
     */
    public function logout($redirect = '')
    {
        $this->_load();
        $GLOBALS['db']->update('CubeCart_admin_users', array('session_id' => ''), array('admin_id' => (int)$this->_admin_data['admin_id']));
        $GLOBALS['session']->destroy();
        if ($redirect=='front') {
            httpredir($GLOBALS['rootRel']);
        } else {
            httpredir($GLOBALS['rootRel'] . $GLOBALS['config']->get('config', 'adminFile'));
        }
    }

    /**
     * Reset password
     *
     * @param string $email
     * @param string $validation
     * @param string $password
     * @return bool
     */
    public function passwordReset($email, $validation, $password)
    {
        if ($GLOBALS['session']->has('recover_login') && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($validation) == $this->_validate_key_len && !empty($password['new']) && !empty($password['confirm']) && ($password['new'] === $password['confirm'])) {
            if (($check = $GLOBALS['db']->select('CubeCart_admin_users', array('admin_id', 'username'), "`email` = '$email' AND `verify` = '$validation' AND `status` = 1")) !== false) {

                // Remove any blocks
                $GLOBALS['db']->delete('CubeCart_blocker', array('username' => $email));
                
                $salt = Password::getInstance()->createSalt();
                $record = array(
                    'salt'  => $salt,
                    'password' => Password::getInstance()->getSalted($password['new'], $salt),
                    'verify' => null,
                    'new_password' => 1
                );
                $where = array(
                    'admin_id' => $check[0]['admin_id'],
                    'email'  => $email,
                    'verify' => $validation,
                );

                $GLOBALS['session']->delete('recover_login');

                if ($GLOBALS['db']->update('CubeCart_admin_users', $record, $where)) {
                    return $this->_authenticate($check[0]['username'], $password['new']);
                }
            }
        }
        return false;
    }

    /**
     * Request password
     *
     * @param string $username
     * @param string $email
     * @return bool
     */
    public function passwordRequest($username, $email)
    {
        if (!empty($username) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (($check = $GLOBALS['db']->select('CubeCart_admin_users', array('admin_id', 'email', 'language', 'name'), "`username` = '$username' AND `email` = '$email' AND `status` = 1")) !== false) {
                // Generate validation key
                $validation = randomString($this->_validate_key_len);
                if ($GLOBALS['db']->update('CubeCart_admin_users', array('verify' => $validation), array('admin_id' => (int)$check[0]['admin_id']))) {
                    // Send email
                    $mailer = new Mailer();
                    $data['link'] = $GLOBALS['storeURL'].'/'.$GLOBALS['config']->get('config', 'adminFile').'?_g=recovery&email='.$check[0]['email'].'&validate='.$validation;
                    $data['name'] = $check[0]['name'];

                    $content = $mailer->loadContent('admin.password_recovery', $check[0]['language'], $data);
                    if ($content) {
                        $GLOBALS['smarty']->assign('DATA', $data);
                        $GLOBALS['session']->set('recover_login', true);
                        return $mailer->sendEmail($check[0]['email'], $content);
                    }
                }
            }
        }
        return false;
    }

    /**
     * Check admin permissions
     *
     * @param mixed $sections
     * @param unknown_type $level
     * @param unknown_type $halt
     * @return bool
     */
    public function permissions($sections, $level = 4, $halt = false, $message = true)
    {

        // Are they a Superuser? If so, they get automatic authorization
        if ($this->superUser()) {
            return true;
        }
        // Lets update permissions to handle an array sections
        if (is_array($sections)) {
            foreach ($sections as $section) {
                $departments[] = (!is_numeric($section)) ? $this->_getSectionId($section) : (int)$section;
            }
        } else {
            // Get integers for section and permission level
            $departments[] = (!is_numeric($sections)) ? $this->_getSectionId($sections) : (int)$sections;
        }
        $level = (!is_numeric($level)) ? $this->_convertPermission($level) : (int)$level;

        if (is_array($departments)) {
            foreach ($departments as $section_id) {
                // Do they have permission to be here?
                if (isset($this->_permissions[$section_id])) {
                    // Check Section specific permissions
                    if ($this->_permissions[$section_id] & $level) {
                        $allowed = true;
                        continue;
                    }
                } elseif (isset($this->_permissions[0])) {
                    // Check global permissions
                    if ($this->_permissions[0] & $level) {
                        $allowed = true;
                        continue;
                    }
                }
                $allowed = false;
                break;
            }
        }

        // Are they authorized?
        if ($allowed) {
            return true;
        }
        // Unauthorized - do we redirect, or just return false?
        if ($message) {
            $GLOBALS['main']->errorMessage($GLOBALS['language']->notification['error_privileges']);
        }
        if ($halt) {
            httpredir($GLOBALS['rootRel'].$GLOBALS['config']->get('config', 'adminFile')."?_g=401");
        }
        return false;
    }

    /**
     * Is a super user
     *
     * @return bool
     */
    public function superUser()
    {
        return ($this->_admin_data['super_user']) ? true : false;
    }

    //=====[ Private ]=======================================

    /**
     * Authenticate user as admin
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    private function _authenticate($username, $password)
    {
        $username = (string)$username;
        $password = (string)$password;
        $hash_password = '';

        if (!empty($username)) {
            // Fetch salt
            if (($user = $GLOBALS['db']->select('CubeCart_admin_users', array('admin_id', 'password', 'salt', 'new_password'), array('username' => $username, 'status' => '1'), null, 1)) !== false) {
                if (empty($user[0]['salt'])) {
                    // Generate Salt
                    $salt = Password::getInstance()->createSalt();
                    //Update it to the newer MD5 so we can fix it later
                    $pass = Password::getInstance()->updateOld($user[0]['password'], $salt);
                    $update = array(
                        'salt'  => $salt,
                        'password' => $pass,
                        'new_password' => 0
                    );
                    if ($GLOBALS['db']->update('CubeCart_admin_users', $update, array('admin_id' => (int)$user[0]['admin_id']))) {
                        $hash_password = $pass;
                    }
                } else {
                    if ($user[0]['new_password'] == 1) {
                        //Get the salted new password
                        $hash_password = Password::getInstance()->getSalted($password, $user[0]['salt']);
                    } else {
                        //Get the salted old password
                        $hash_password = Password::getInstance()->getSaltedOld($password, $user[0]['salt']);
                    }
                }
            } else {
                foreach ($GLOBALS['hooks']->load('admin.authenticate.failed_invalid_admin') as $hook) {
                    include $hook;
                }
                $GLOBALS['gui']->setError($GLOBALS['language']->account['error_login']);
                return false;
            }
            $result = $GLOBALS['db']->select('CubeCart_admin_users', array('admin_id', 'customer_id', 'logins', 'new_password'), array('username' => $username, 'password' => $hash_password, 'status' => '1'));
            $GLOBALS['session']->blocker($username, 0, (bool)$result, Session::BLOCKER_BACKEND, $GLOBALS['config']->get('config', 'bfattempts'), $GLOBALS['config']->get('config', 'bftime'));
            if ($result) {
                if (!$GLOBALS['session']->blocked()) {
                    $this->_logged_in = true;
                    $GLOBALS['session']->regenerateSessionId();
                    $update = array(
                        'blockTime'  => 0,
                        'browser'  => htmlspecialchars($_SERVER['HTTP_USER_AGENT']),
                        'failLevel'  => 0,
                        'session_id' => $GLOBALS['session']->getId(),
                        'ip_address' => get_ip_address(),
                        'verify'  => '',
                        'lastTime'  => time(),
                        'logins'  => $result[0]['logins'] +1,
                    );
                    if ($result[0]['new_password'] != 1) {
                        $salt = Password::getInstance()->createSalt();
                        $pass = Password::getInstance()->getSalted($password, $salt);
                        $update = array_merge($update, array(
                                'salt'   => $salt,
                                'password'  => $pass,
                                'new_password' => 1,
                            ));
                    }
                    $GLOBALS['db']->update('CubeCart_admin_users', $update, array('admin_id' => $result[0]['admin_id']));
                    $GLOBALS['session']->set('admin_id', $result[0]['admin_id'], 'client');
                    $this->_load();
                } else {
                    foreach ($GLOBALS['hooks']->load('admin.authenticate.failed_valid_admin') as $hook) {
                        include $hook;
                    }
                    $minutes_blocked = ceil(($GLOBALS['config']->get('config', 'bftime')/60));
                    $GLOBALS['gui']->setError(sprintf('Too many invalid logins have been made. Access has been blocked for %s minutes.', $minutes_blocked));
                }
            } else {
                if (!$GLOBALS['session']->blocked()) {
                    if (($user = $GLOBALS['db']->select('CubeCart_admin_users', false, array('username' => $_POST['username']))) !== false) {
                        if ($user[0]['blockTime']>0 && $user[0]['blockTime'] < time()) {
                            // reset fail level and time
                            $newdata['failLevel'] = 1;
                            $newdata['blockTime'] = 0;
                        } elseif ($user[0]['failLevel'] == ($GLOBALS['config']->get('config', 'bfattempts') - 1)) {
                            $timeAgo = time() - $GLOBALS['config']->get('config', 'bftime');
                            if ($user[0]['lastTime'] < $timeAgo) {
                                $newdata['failLevel'] = 1;
                                $newdata['blockTime'] = 0;
                            } else {
                                // block the account
                                $newdata['failLevel'] = $GLOBALS['config']->get('config', 'bfattempts');
                                $newdata['blockTime'] = time() + $GLOBALS['config']->get('config', 'bftime');
                            }
                        } elseif ($user[0]['blockTime'] < time()) {
                            $timeAgo    = time() - $GLOBALS['config']->get('config', 'bftime');
                            $newdata['failLevel'] = ($user[0]['lastTime']<$timeAgo) ? 1 : $user[0]['failLevel'] + 1;
                            $newdata['blockTime'] = 0;
                        } else {
                            // Display Blocked message
                            $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->account['error_login_block'],($GLOBALS['config']->get('config', 'bftime') / 60)));
                            $this->_blocked = true;
                        }
                        if (isset($newdata)) {
                            $newdata['lastTime'] = time();
                            $GLOBALS['db']->update('CubeCart_admin_users', $newdata, array('admin_id' => $user[0]['admin_id']));
                        }
                    }
                    $GLOBALS['gui']->setError($GLOBALS['language']->account['error_login']);
                } else {
                    $minutes_blocked = ceil(($GLOBALS['config']->get('config', 'bftime')/60));
                    $GLOBALS['gui']->setError(sprintf('Too many invalid logins have been made. Access has been blocked for %s minutes.', $minutes_blocked));
                }
                foreach ($GLOBALS['hooks']->load('admin.authenticate.failed_valid_admin') as $hook) {
                    include $hook;
                }
            }
            if (!$GLOBALS['session']->blocked()) {
                $redir = '';
                if (isset($_GET['redir']) && !empty($_GET['redir'])) {
                    $redir = $_GET['redir'];
                } elseif (isset($_POST['redir']) && !empty($_POST['redir'])) {
                    $redir = $_POST['redir'];
                } elseif ($GLOBALS['session']->has('redir')) {
                    $redir = $GLOBALS['session']->get('redir');
                } elseif ($GLOBALS['session']->has('back')) {
                    $redir = $GLOBALS['session']->get('back');
                }

                if (!empty($redir)) {
                    // Prevent phishing attacks, or anything untoward, unless it's redirecting back to this store
                    if(!$GLOBALS['ssl']->validRedirect($redir)) {
                        trigger_error(sprintf("Possible Phishing attack - Redirection to '%s' is not allowed. Please check the value of 'Store URL' in the SSL section of your store settings.", $redir));
                        $redir = '';
                        if ($GLOBALS['session']->has('back') && $redir == $GLOBALS['session']->get('back')) {
                            $GLOBALS['session']->delete('back');
                        }
                        if ($GLOBALS['session']->has('redir') && $redir == $GLOBALS['session']->get('redir')) {
                            $GLOBALS['session']->delete('redir');
                        }
                    }
                }

                httpredir((isset($redir) && !empty($redir)) ? $redir : $GLOBALS['rootRel'].$GLOBALS['config']->get('config', 'adminFile'));
            } else {
                $minutes_blocked = ceil(($GLOBALS['config']->get('config', 'bftime')/60));
                $GLOBALS['gui']->setError(sprintf('Too many invalid logins have been made. Access has been blocked for %s minutes.', $minutes_blocked));
            }
        } else {
            $GLOBALS['gui']->setError($GLOBALS['language']->account['error_login']);
        }
        return false;
    }

    /**
     * Convert permissions
     *
     * @param string $name
     * @return int
     */
    private function _convertPermission($name = null)
    {
        switch (strtolower($name)) {
        case 'delete':
            $value = CC_PERM_DELETE;
            break;
        case 'edit':
        case 'write':
            $value = CC_PERM_EDIT;
            break;
        case 'read':
            $value = CC_PERM_READ;
            break;
        default:
            $value = 0;
        }
        return $value;
    }

    /**
     * Get the admin section id
     *
     * @param unknown_type $name
     * @return int/false
     */
    private function _getSectionId($name)
    {
        if (!empty($name)) {
            foreach ($GLOBALS['hooks']->load('class.admin.get_section_id') as $hook) {
                include $hook;
            }
            $sections = array(
                'categories' => 3,
                'customers'  => 5,
                'documents'  => 4,
                'filemanager' => 7,
                'offers'  => 11,
                'orders'  => 10,
                'products'  => 2,
                'users'   => 1,
                'shipping'  => 6,
                'statistics' => 8,
                'settings'  => 9,
                'reviews'  => 12,
            );
            if (isset($sections[$name])) {
                return (int)$sections[$name];
            }

            foreach ($this->_sections as $section) {
                if ($section['name'] == strtolower($name)) {
                    return $section['section_id'];
                }
            }
        }
        return false;
    }

    /**
     * Load admin data
     *
     * @return bool
     */
    private function _load()
    {
        //Try to get the admin_id from the sessions
        $admin_id = $GLOBALS['session']->get('admin_id', 'client', 0);
        //If there is one
        if ($admin_id != 0) {
            //Try to get the admin_data from the sessions
            if ($GLOBALS['session']->has('', 'admin_data')) {
                $data = $GLOBALS['session']->get('', 'admin_data');
            }
            if (!isset($data) || empty($data) || !isset($data['admin_id'])) {
                //Load from the DB
                if (($data = $GLOBALS['db']->select('CubeCart_admin_users', false, array('admin_id' => $admin_id, 'status' => '1'), false, 1, false, false)) !== false) {
                    //Unset these for security reasons
                    unset($data[0]['password']);
                    unset($data[0]['salt']);
                    unset($data[0]['session_id']);
                    $GLOBALS['session']->set('', $data[0], 'admin_data');
                    $data = $data[0];
                    $GLOBALS['db']->update('CubeCart_sessions', array('admin_id' => $data['admin_id']), array('session_id' => $GLOBALS['session']->getId()));
                }
            }
            if (!empty($data)) {
                $this->_logged_in = true;
                $this->_admin_data = $data;
                $GLOBALS['session']->set('user_language', (!empty($data['language'])) ? $data['language'] : $GLOBALS['config']->get('config', 'default_language'), 'admin');
                // Load Permission Rules
                if (($permissions = $GLOBALS['db']->select('CubeCart_permissions', false, array('admin_id' => $this->_admin_data['admin_id']))) !== false) {
                    foreach ($permissions as $permission) {
                        $this->_permissions[$permission['section_id']] = $permission['level'];
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Magic get
     *
     * @param string $name
     */
    public function __get($name)
    {
        return (isset($this->_admin_data[$name])) ? $this->_admin_data[$name] : false;
    }
}
