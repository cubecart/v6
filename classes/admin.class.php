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
 * Admin controller
 */
class Admin
{
    const OTP_LIFETIME = 600;

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
     * @var self
     */
    protected static $_instance;

    ##############################################

    final private function __construct()
    {

        // Logout requests
        if (isset($_GET['_g']) && $_GET['_g'] == 'logout') {
            $redirect = (isset($_GET['r']) && !empty($_GET['r'])) ? $_GET['r'] : '';
            $this->logout($redirect);
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
        $email = preg_replace('/[^a-z0-9.@_\-\+]/i', '', $email);
        $validation = preg_replace('/[^a-z0-9]/i', '', $validation);
        if ($GLOBALS['session']->has('recover_login') && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($validation) == $this->_validate_key_len && !empty($password['new']) && !empty($password['confirm']) && ($password['new'] === $password['confirm'])) {
            if (($check = $GLOBALS['db']->select('CubeCart_admin_users', array('admin_id', 'username', 'verify_expires'), array('email' => $email, 'verify' => $validation, 'status' => 1))) !== false) {
                // Check token expiry
                if (!empty($check[0]['verify_expires']) && strtotime($check[0]['verify_expires']) < time()) {
                    $GLOBALS['db']->update('CubeCart_admin_users', array('verify' => null, 'verify_expires' => null), array('admin_id' => $check[0]['admin_id']));
                    $GLOBALS['session']->delete('recover_login');
                    return false;
                }
                // Remove any blocks
                $GLOBALS['db']->delete('CubeCart_blocker', array('username' => $email));

                $record = array(
                    'salt'  => '',
                    'password' => Password::getInstance()->hashPassword($password['new']),
                    'verify' => null,
                    'verify_expires' => null,
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
     * @param string $email
     * @return bool
     */
    public function passwordRequest($email)
    {
        $email = preg_replace('/[^a-z0-9.@_\-\+]/i', '', $email);
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($check = $GLOBALS['db']->select('CubeCart_admin_users', array('admin_id', 'email', 'language', 'name'), array('email' => $email, 'status' => 1))) {
                // Generate validation key
                $validation = bin2hex(random_bytes(16));
                $verify_expires = date('Y-m-d H:i:s', time() + 3600);
                if ($GLOBALS['db']->update('CubeCart_admin_users', array('verify' => $validation, 'verify_expires' => $verify_expires), array('admin_id' => (int)$check[0]['admin_id']))) {
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
     * @param int $level
     * @param bool $halt
     * @return bool
     */
    public function permissions($sections, $level = 4, $halt = false, $message = true)
    {

        // Are they a Superuser? If so, they get automatic authorization
        if ($this->superUser()) {
            return true;
        }
        // Lets update permissions to handle an array sections
        $departments = [];
        if (is_array($sections)) {
            foreach ($sections as $section) {
                $departments[] = (!is_numeric($section)) ? $this->_getSectionId($section) : (int)$section;
            }
        } else {
            // Get integers for section and permission level
            $departments[] = (!is_numeric($sections)) ? $this->_getSectionId($sections) : (int)$sections;
        }
        $level = (!is_numeric($level)) ? $this->_convertPermission($level) : (int)$level;

        $allowed = false;
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
        return !empty($this->_admin_data['super_user']);
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
            // Fetch user record
            if (($user = $GLOBALS['db']->select('CubeCart_admin_users', array('admin_id', 'password', 'salt', 'new_password'), array('username' => $username, 'status' => '1'), null, 1)) !== false) {
                $pwd = Password::getInstance();
                if ($pwd->isBcrypt($user[0]['password'])) {
                    // Modern hash (bcrypt or Argon2id) verification
                    if ($pwd->verifyPassword($password, $user[0]['password'])) {
                        $hash_password = $user[0]['password'];
                        // Upgrade hash if algorithm/cost has been strengthened
                        if ($pwd->needsRehash($hash_password)) {
                            $upgraded = $pwd->hashPassword($password);
                            $GLOBALS['db']->update('CubeCart_admin_users', array('password' => $upgraded, 'salt' => '', 'new_password' => 1), array('admin_id' => (int)$user[0]['admin_id']));
                            $hash_password = $upgraded;
                        }
                    }
                } elseif (empty($user[0]['salt'])) {
                    // Legacy: no salt - oldest format
                    $salt = $pwd->createSalt();
                    $pass = $pwd->updateOld($user[0]['password'], $salt);
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
                        $hash_password = $pwd->getSalted($password, $user[0]['salt']);
                    } else {
                        $hash_password = $pwd->getSaltedOld($password, $user[0]['salt']);
                    }
                }
                // Migrate to bcrypt on successful legacy login
                if (!empty($hash_password) && $hash_password === $user[0]['password'] && !$pwd->isBcrypt($hash_password)) {
                    $bcrypt_hash = $pwd->hashPassword($password);
                    $GLOBALS['db']->update('CubeCart_admin_users', array('password' => $bcrypt_hash, 'salt' => '', 'new_password' => 1), array('admin_id' => (int)$user[0]['admin_id']));
                    $hash_password = $bcrypt_hash;
                }
            } else {
                foreach ($GLOBALS['hooks']->load('admin.authenticate.failed_invalid_admin') as $hook) {
                    include $hook;
                }
                $GLOBALS['gui']->setError($GLOBALS['language']->account['error_login']);
                return false;
            }
            $result = $GLOBALS['db']->select('CubeCart_admin_users', array('admin_id', 'customer_id', 'logins', 'new_password', 'password', 'name', 'email', 'language', 'twofa_enabled', 'twofa_method', 'twofa_secret', 'ip_address', 'browser'), array('username' => $username, 'password' => $hash_password, 'status' => '1'));
            $GLOBALS['session']->blocker($username, 0, (bool)$result, Session::BLOCKER_BACKEND, $GLOBALS['config']->get('config', 'bfattempts'), $GLOBALS['config']->get('config', 'bftime'));
            if ($result) {
                if (!$GLOBALS['session']->blocked()) {
                    $update = array(
                        'blockTime'  => 0,
                        'browser'  => htmlspecialchars($_SERVER['HTTP_USER_AGENT']),
                        'failLevel'  => 0,
                        'ip_address' => get_ip_address(),
                        'verify'  => '',
                        'lastTime'  => time(),
                        'logins'  => $result[0]['logins'] +1,
                    );
                    if ($result[0]['new_password'] != 1 || !Password::getInstance()->isBcrypt($result[0]['password'])) {
                        $pass = Password::getInstance()->hashPassword($password);
                        $update = array_merge($update, array(
                                'salt'   => '',
                                'password'  => $pass,
                                'new_password' => 1,
                            ));
                    }
                    $GLOBALS['db']->update('CubeCart_admin_users', $update, array('admin_id' => (int)$result[0]['admin_id']));
                    $this->_sendNewDeviceNotification($result[0]);
                    if (!empty($result[0]['twofa_enabled'])) {
                        // 2FA required – store pending state and redirect to challenge page (exits)
                        $this->_initiate2FA($result[0]);
                    }
                    // No 2FA – establish session directly (sets _logged_in, admin_id in session, calls _load)
                    $this->_establishSession((int)$result[0]['admin_id']);
                } else {
                    foreach ($GLOBALS['hooks']->load('admin.authenticate.failed_valid_admin') as $hook) {
                        include $hook;
                    }
                    $minutes_blocked = ceil(($GLOBALS['config']->get('config', 'bftime')/60));
                    $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->account['error_login_blocked'], $minutes_blocked));
                }
            } else {
                if (!$GLOBALS['session']->blocked()) {
                    if (($user = $GLOBALS['db']->select('CubeCart_admin_users', false, array('username' => $username))) !== false) {
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
                        }
                        if (isset($newdata)) {
                            $newdata['lastTime'] = time();
                            $GLOBALS['db']->update('CubeCart_admin_users', $newdata, array('admin_id' => $user[0]['admin_id']));
                        }
                    }
                    $GLOBALS['gui']->setError($GLOBALS['language']->account['error_login']);
                } else {
                    $minutes_blocked = ceil(($GLOBALS['config']->get('config', 'bftime')/60));
                    $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->account['error_login_blocked'], $minutes_blocked));
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
                        trigger_error(sprintf("Possible Phishing attack - Redirection to '%s' is not allowed. Please check the value of 'Store URL' in the SSL section of your store settings.", $redir), E_USER_WARNING);
                        if ($GLOBALS['session']->has('back') && $redir == $GLOBALS['session']->get('back')) {
                            $GLOBALS['session']->delete('back');
                        }
                        if ($GLOBALS['session']->has('redir') && $redir == $GLOBALS['session']->get('redir')) {
                            $GLOBALS['session']->delete('redir');
                        }
                        $redir = '';
                    }
                }

                httpredir((isset($redir) && !empty($redir)) ? $redir : $GLOBALS['rootRel'].$GLOBALS['config']->get('config', 'adminFile'));
            } else {
                $minutes_blocked = ceil(($GLOBALS['config']->get('config', 'bftime')/60));
                $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->account['error_login_blocked'], $minutes_blocked));
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
     * @param string $name
     * @return int|false
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

    /**
     * Verify a 2FA code (TOTP, email OTP, or backup code) for a pending login
     *
     * @param string $code
     * @return bool  Always redirects on success; returns false on failure
     */
    public function verify2FA($code)
    {
        $admin_id = (int)$GLOBALS['session']->get('twofa_pending_admin_id', 'client', 0);
        if (!$admin_id) {
            return false;
        }

        // IP binding – prevent session token theft between credential check and 2FA entry
        $pending_ip = $GLOBALS['session']->get('twofa_pending_ip', 'client');
        if ($pending_ip !== md5(get_ip_address())) {
            $this->_clearPending2FA();
            $GLOBALS['gui']->setError($GLOBALS['language']->account['error_twofa_session']);
            httpredir($GLOBALS['rootRel'].$GLOBALS['config']->get('config', 'adminFile'));
        }

        $admin = $GLOBALS['db']->select('CubeCart_admin_users', false, array('admin_id' => $admin_id, 'status' => 1), false, 1, false, false);
        if (!$admin) {
            $this->_clearPending2FA();
            httpredir($GLOBALS['rootRel'].$GLOBALS['config']->get('config', 'adminFile'));
        }
        $admin = $admin[0];

        $code     = trim((string)$code);
        $verified = false;

        // Check backup codes (8 uppercase hex chars)
        if (strlen($code) === 8 && preg_match('/^[A-F0-9]+$/i', $code)) {
            $backup_codes = json_decode($admin['twofa_backup_codes'] ?? '[]', true);
            if (is_array($backup_codes)) {
                foreach ($backup_codes as $i => $hash) {
                    if (password_verify(strtoupper($code), $hash)) {
                        unset($backup_codes[$i]);
                        $GLOBALS['db']->update('CubeCart_admin_users',
                            array('twofa_backup_codes' => json_encode(array_values($backup_codes))),
                            array('admin_id' => $admin_id));
                        $verified = true;
                        break;
                    }
                }
            }
        }

        if (!$verified) {
            if ($admin['twofa_method'] === 'email') {
                if (!empty($admin['twofa_otp_hash']) && (int)$admin['twofa_otp_expires'] > time()) {
                    if (password_verify($code, $admin['twofa_otp_hash'])) {
                        $GLOBALS['db']->update('CubeCart_admin_users',
                            array('twofa_otp_hash' => null, 'twofa_otp_expires' => 0),
                            array('admin_id' => $admin_id));
                        $verified = true;
                    }
                }
            } elseif ($admin['twofa_method'] === 'totp') {
                if (!empty($admin['twofa_secret'])) {
                    require_once CC_ROOT_DIR.CC_DS.'classes'.CC_DS.'totp.class.php';
                    $verified = TOTP::verifyCode($admin['twofa_secret'], $code);
                }
            }
        }

        if ($verified) {
            $redir = (string)$GLOBALS['session']->get('twofa_pending_redir', 'client', '');
            $this->_clearPending2FA();
            $this->_establishSession($admin_id);
            if (!empty($redir) && $GLOBALS['ssl']->validRedirect($redir)) {
                httpredir($redir);
            }
            httpredir($GLOBALS['rootRel'].$GLOBALS['config']->get('config', 'adminFile'));
        }

        // Failed attempt – session-based rate limiting
        $fail_count = (int)$GLOBALS['session']->get('twofa_fail_count', 'client', 0) + 1;
        $max_attempts = (int)$GLOBALS['config']->get('config', 'bfattempts') ?: 5;
        if ($fail_count >= $max_attempts) {
            $this->_clearPending2FA();
            $minutes = ceil($GLOBALS['config']->get('config', 'bftime') / 60);
            $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->account['error_twofa_locked'], $minutes));
            httpredir($GLOBALS['rootRel'].$GLOBALS['config']->get('config', 'adminFile'));
        }
        $GLOBALS['session']->set('twofa_fail_count', $fail_count, 'client');
        $GLOBALS['gui']->setError($GLOBALS['language']->account['error_twofa_invalid']);
        return false;
    }

    /**
     * Resend the email OTP for a pending 2FA login (rate-limited to once per 60 s)
     *
     * @return bool
     */
    public function resend2FACode()
    {
        $admin_id = (int)$GLOBALS['session']->get('twofa_pending_admin_id', 'client', 0);
        if (!$admin_id) {
            return false;
        }
        $admin = $GLOBALS['db']->select('CubeCart_admin_users',
            array('admin_id', 'name', 'email', 'language', 'twofa_method', 'twofa_otp_expires'),
            array('admin_id' => $admin_id, 'status' => 1, 'twofa_method' => 'email'),
            false, 1, false, false);
        if (!$admin) {
            return false;
        }
        $admin = $admin[0];
        // Allow resend only if at least 60 seconds have elapsed since last send
        $sent_at = (int)$admin['twofa_otp_expires'] - self::OTP_LIFETIME;
        if ((time() - $sent_at) < 60) {
            $GLOBALS['gui']->setError($GLOBALS['language']->account['error_twofa_wait']);
            return false;
        }
        $this->_send2FACode($admin);
        $GLOBALS['gui']->setNotify($GLOBALS['language']->account['notify_twofa_resent']);
        return true;
    }

    /**
     * Establish an authenticated admin session after credentials (and 2FA) are verified
     *
     * @param int $admin_id
     */
    private function _establishSession($admin_id)
    {
        $GLOBALS['session']->regenerateSessionId();
        $GLOBALS['db']->update('CubeCart_admin_users',
            array('session_id' => $GLOBALS['session']->getId()),
            array('admin_id' => $admin_id));
        $GLOBALS['session']->set('admin_id', $admin_id, 'client');
        $this->_logged_in = true;
        $this->_load();
    }

    /**
     * Initiate the 2FA challenge: store pending state, send email OTP if needed, then redirect
     *
     * @param array $admin  admin record from DB (must include admin_id, twofa_method, name, email, language)
     */
    private function _initiate2FA($admin)
    {
        $GLOBALS['session']->set('twofa_pending_admin_id', (int)$admin['admin_id'], 'client');
        $GLOBALS['session']->set('twofa_pending_ip', md5(get_ip_address()), 'client');

        // Preserve any post-login redirect destination
        $redir = '';
        if (isset($_POST['redir']) && !empty($_POST['redir'])) {
            $redir = $_POST['redir'];
        } elseif (isset($_GET['redir']) && !empty($_GET['redir'])) {
            $redir = $_GET['redir'];
        }
        if (!empty($redir)) {
            $GLOBALS['session']->set('twofa_pending_redir', $redir, 'client');
        }

        if ($admin['twofa_method'] === 'email') {
            $this->_send2FACode($admin);
        }

        httpredir($GLOBALS['rootRel'].$GLOBALS['config']->get('config', 'adminFile').'?_g=twofa');
    }

    /**
     * Generate and email a 6-digit OTP to the admin
     *
     * @param array $admin  must include admin_id, name, email, language
     */
    private function _send2FACode($admin)
    {
        $code    = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hash    = password_hash($code, PASSWORD_DEFAULT);
        $expires = time() + self::OTP_LIFETIME;

        $GLOBALS['db']->update('CubeCart_admin_users',
            array('twofa_otp_hash' => $hash, 'twofa_otp_expires' => $expires),
            array('admin_id' => (int)$admin['admin_id']));

        $mailer = new Mailer();
        $data   = array(
            'code'    => $code,
            'name'    => isset($admin['name']) ? $admin['name'] : '',
            'expires' => '10 minutes',
        );
        $content = $mailer->loadContent('admin.two_factor_code', $admin['language'], $data);
        if ($content) {
            $GLOBALS['smarty']->assign('DATA', $data);
            $mailer->sendEmail($admin['email'], $content);
        }
    }

    /**
     * Send notification email if admin is logging in from a new IP or device
     *
     * @param array $admin  Admin record (must include ip_address, browser, name, email, language)
     */
    private function _sendNewDeviceNotification($admin)
    {
        if ($GLOBALS['config']->get('config', 'admin_login_notify') === '0') {
            return;
        }

        $prev_ip      = isset($admin['ip_address']) ? trim($admin['ip_address']) : '';
        $prev_browser = isset($admin['browser']) ? trim($admin['browser']) : '';

        // Skip first-ever login — nothing to compare against
        if (empty($prev_ip) && empty($prev_browser)) {
            return;
        }

        $current_ip      = get_ip_address();
        $current_browser = htmlspecialchars($_SERVER['HTTP_USER_AGENT']);

        $ip_changed      = (!empty($prev_ip) && $prev_ip !== $current_ip);
        $browser_changed = (!empty($prev_browser)
            && $this->_normalizeBrowser($prev_browser) !== $this->_normalizeBrowser($current_browser));

        if (!$ip_changed && !$browser_changed) {
            return;
        }

        $mailer = new Mailer();
        $data   = array(
            'name'             => isset($admin['name']) ? $admin['name'] : '',
            'new_ip'           => $current_ip,
            'previous_ip'      => $prev_ip,
            'new_browser'      => htmlspecialchars_decode($current_browser),
            'previous_browser' => htmlspecialchars_decode($prev_browser),
            'login_time'       => date('Y-m-d H:i:s T'),
            'ip_changed'       => $ip_changed,
            'browser_changed'  => $browser_changed,
        );
        $content = $mailer->loadContent('admin.new_device_login', $admin['language'], $data);
        if ($content) {
            $GLOBALS['smarty']->assign('DATA', $data);
            $mailer->sendEmail($admin['email'], $content);
        }
    }

    /**
     * Normalize a user agent string by stripping version numbers
     * so that browser auto-updates don't trigger false positives
     *
     * @param string $ua
     * @return string
     */
    private function _normalizeBrowser($ua)
    {
        return preg_replace('/\/[\d]+[\d.]*/', '/', (string)$ua);
    }

    /**
     * Clear all pending 2FA session state
     */
    private function _clearPending2FA()
    {
        $GLOBALS['session']->delete('twofa_pending_admin_id', 'client');
        $GLOBALS['session']->delete('twofa_pending_ip', 'client');
        $GLOBALS['session']->delete('twofa_pending_redir', 'client');
        $GLOBALS['session']->delete('twofa_fail_count', 'client');
    }
}
