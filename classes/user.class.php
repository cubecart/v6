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
 * User controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class User
{

    /**
     * Is bot?
     *
     * @var bool
     */
    private $_bot   = null;
    /**
     * Bot signatures
     *
     * @var array of strings
     */
    protected $_bot_sigs =  array(
        'alexa',
        'appie',
        'archiver',
        'ask jeeves',
        'baiduspider',
        'bot',
        'crawl',
        'crawler',
        'curl',
        'eventbox',
        'facebookexternal',
        'fast',
        'firefly',
        'froogle',
        'gigabot',
        'girafabot',
        'google',
        'googlebot',
        'infoseek',
        'inktomi',
        'java',
        'larbin',
        'looksmart',
        'mechanize',
        'monitor',
        'msnbot',
        'nambu',
        'nationaldirectory',
        'novarra',
        'pear',
        'perl',
        'python',
        'rabaz',
        'radian',
        'rankivabot',
        'scooter',
        'slurp',
        'sogou web spider',
        'spade',
        'sphere',
        'spider',
        'technoratisnoop',
        'tecnoseek',
        'teoma',
        'toolbar',
        'transcoder',
        'twitt',
        'url_spider_sql',
        'webalta',
        'webbug',
        'webfindbot',
        'wordpress',
        'www.galaxy.com',
        'yahoo',
        'yandex',
        'zyborg',
    );
    /**
     * Has the user data changed
     *
     * @var bool
     */
    private $_changed  = false;
    /**
     * Logged in
     *
     * @var bool
     */
    private $_logged_in  = false;
    /**
     * Users data
     *
     * @var array
     */
    private $_user_data  = array();

    /**
     * Class instance
     *
     * @var instance
     */
    protected static $_instance;

    ##############################################

    final protected function __construct()
    {

        //If there is a login attempt
        if (isset($_POST['username']) && isset($_POST['password']) && !empty($_POST['username']) && !empty($_POST['password'])) {

            //Did they check the remember me box
            $remember = (isset($_POST['remember']) && !empty($_POST['remember'])) ? true : false;
            $this->authenticate($_POST['username'], $_POST['password'], $remember);
        } else {
            //If there is a cookie for the username and they are not logged in
            if (isset($_COOKIE['username']) && !empty($_COOKIE['username']) && !$this->is()) {
                //If we haven't pushed the user to the login
                if (!$GLOBALS['session']->get('login_push')) {
                    $GLOBALS['session']->set('login_push', true);
                    //Try to have them login
                    if (!isset($_GET['_a']) || $_GET['_a'] != 'login') {
                        httpredir('index.php?_a=login');
                    }
                }
            }

            $this->_load();
            //IS_USER defines if a the user is a valid user on the template
            $GLOBALS['smarty']->assign('IS_USER', $this->is());

            if ($this->is() && isset($_POST['mailing_list'])) {
                Newsletter::getInstance()->subscribe($this->get('email'), $this->getId());
            }

            $this->isBot();
        }
    }

    public function __destruct()
    {
    }

    /**
     * Setup the instance (singleton)
     *
     * @return User
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
     * Increment customer order count by 1
     *
     * @param integer $customer_id
     * @return bool
     */
    public function addOrder($customer_id)
    {
        return (bool)$GLOBALS['db']->misc('UPDATE `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_customer` SET `order_count` = `order_count` + 1 WHERE `customer_id` = '.(int)$customer_id, false);
    }

    /**
     * Authenticate a user (ie login)
     *
     * @param string $username
     * @param string $password
     * @param bool $remember
     * @param bool $from_cookie
     * @param bool $redirect
     * @return bool
     */
    public function authenticate($username, $password, $remember = false, $from_cookie = false, $is_openid = false, $redirect = true)
    {
        $username = (string)$username;
        $password = (string)$password;

        //Check we are not upgrading an unregistered account
        if ($unregistered = $GLOBALS['db']->select('CubeCart_customer', array('customer_id'), array('type' => 2, 'email' => $username, 'status' => true), false, 1, false, false)) {
            $record = array(
                'type' => 1,
                'new_password' => 0,
                'password' => md5($password)
            );
            $GLOBALS['db']->update('CubeCart_customer', $record, array('customer_id' => (int)$unregistered[0]['customer_id']));
            $this->authenticate($username, $password);
        }

        $hash_password = '';
        //Get customer_id, password, and salt for the user
        if (($user = $GLOBALS['db']->select('CubeCart_customer', array('customer_id', 'password', 'salt', 'new_password'), array('type' => 1, 'email' => $username, 'status' => true), false, 1, false, false)) !== false) {
            //If there is no salt we need to make it
            if (empty($user[0]['salt'])) {
                //Get the salt
                $salt = Password::getInstance()->createSalt();
                //Update it to the newer MD5 so we can fix it later
                $pass = Password::getInstance()->updateOld($user[0]['password'], $salt);
                $record = array(
                    'salt'   => $salt,
                    'password'  => $pass,
                );

                //Update the DB with the new salt and salted password
                if ($GLOBALS['db']->update('CubeCart_customer', $record, array('customer_id' => (int)$user[0]['customer_id']))) {
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
        }

        //Try to get the user data with the username and salted password
        $where = array(
            'email'  => $username,
            'password' => $hash_password,
        );
        $user = $GLOBALS['db']->select('CubeCart_customer', array('language', 'customer_id', 'email', 'password', 'salt', 'new_password'), $where, false, 1, false, false);

        $GLOBALS['session']->blocker($username, $user[0]['customer_id'], (bool)$user, Session::BLOCKER_FRONTEND, $GLOBALS['config']->get('config', 'bfattempts'), $GLOBALS['config']->get('config', 'bftime'));
        if (!$user) {
            $GLOBALS['gui']->setError($GLOBALS['language']->account['error_login']);
        } else {
            $user[0]['language'] = $this->_validLanguage($user[0]['language']);
            if ($user[0]['new_password'] != 1) {
                $salt = Password::getInstance()->createSalt();
                $pass = Password::getInstance()->getSalted($password, $salt);
                $record = array(
                    'salt'   => $salt,
                    'password'  => $pass,
                    'new_password' => 1,
                );

                //Update the DB with the new salt and salted password
                if (($GLOBALS['db']->update('CubeCart_customer', $record, array('customer_id' => (int)$user[0]['customer_id']))) === false) {
                    trigger_error('Could not update password', E_USER_ERROR);
                }
            }
            //If we are a user
            if (!empty($user[0]['customer_id']) && is_numeric($user[0]['customer_id'])) {
                /**
                 * Set the cookie for the username
                 * The password cookie is not stored to make stores more secure
                 */
                if ($remember || $from_cookie) {
                    $GLOBALS['session']->set_cookie('username', $user[0]['email'], time() + (3600*24*30));
                }
                if (!$GLOBALS['session']->blocked()) {
                    // possibly replaceable with session_set_save_handler?
                    $GLOBALS['db']->update('CubeCart_sessions', array('customer_id' => $user[0]['customer_id']), array('session_id' => $GLOBALS['session']->getId()));
                    $GLOBALS['db']->update('CubeCart_cookie_consent', array('customer_id' => $user[0]['customer_id']), array('session_id' => $GLOBALS['session']->getId()));
                    $GLOBALS['session']->set('language', $user[0]['language'], 'client');
                    // Load user data
                    $this->_load();

                    $pass_len = strlen($password);

                    if ($pass_len > 0 && $pass_len < 6) {
                        $GLOBALS['gui']->setInfo($GLOBALS['language']->account['error_pass_length']);
                    }

                    $GLOBALS['session']->set('check_autoload', true);

                    if ($redirect) {
                        //Check for a redirect
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

                        foreach ($GLOBALS['hooks']->load('class.user.preredirect') as $hook) {
                            include $hook;
                        }

                        //If there is a redirect
                        if (!empty($redir)) {
                            // Prevent phishing attacks, or anything untoward, unless it's redirecting back to this store
                            if(!$GLOBALS['ssl']->validRedirect($redir)) {
                               trigger_error("Possible Phishing attack - Redirection to '".$redir."' is not allowed. Please check the value of 'Store URL' in the SSL section of your store settings.", E_USER_ERROR);
                            }
                        } else {
                            $remove = array('redir');
                        }

                        if (!empty($redir)) {
                            //Clean up
                            if ($GLOBALS['session']->has('back')) {
                                $GLOBALS['session']->delete('back');
                            }
                            if ($GLOBALS['session']->has('redir')) {
                                $GLOBALS['session']->delete('redir');
                            }
                            //Send to redirect
                            httpredir($redir);
                        } else {
                            httpredir(currentPage($remove));
                        }
                    }
                    return true;
                } else {
                    $GLOBALS['gui']->setError($GLOBALS['language']->account['error_login_block']);
                }
            }
        }
        return false;
    }

    public function addressCompare($address1, $address2) {
        $allowed_keys = array('line1','line2','town','postcode','state_id','state','state_abbrev','country','country_id','country_iso','country_name');
        $address1_filtered = array();
        foreach($address1 as $key => $value) {
            if(in_array($key, $allowed_keys)) $address1_filtered[$key] = strtolower($value);
        }
        $address2_filtered = array();
        foreach($address2 as $key => $value) {
            if(in_array($key, $allowed_keys)) $address2_filtered[$key] = strtolower($value);
        }
        return md5(serialize($address1_filtered).serialize($address2_filtered));
    }

    /**
     * Change a user password
     *
     * @return bool
     */
    public function changePassword()
    {
        //If everything lines up
        if (Password::getInstance()->getSalted($_POST['passold'], $this->_user_data['salt']) == $this->_user_data['password'] && $_POST['passnew'] === $_POST['passconf']) {
            //Change it
            $record = array('password' => Password::getInstance()->getSalted($_POST['passnew'], $this->_user_data['salt']));
            if ($GLOBALS['db']->update('CubeCart_customer', $record, array('customer_id' => (int)$this->_user_data['customer_id']), true)) {
                $this->_user_data['password'] = $record['password'];
                return true;
            } else {
                $GLOBALS['gui']->setError($GLOBALS['language']->account['error_password_update']);
            }
        } else {
            $GLOBALS['gui']->setError($GLOBALS['language']->account['error_password_update_mismatch']);
        }

        return false;
    }

    /**
     * Manually create a user
     *
     * @param array $data
     * @param bool $login
     * @param int $type
     * @return customer_id/false
     */
    public function createUser($data, $login = false, $type = 1)
    {
        if (!empty($data)) {
            // Insert record(s)
            $data['new_password'] = '0';
            $data['ip_address']  = get_ip_address();

            $data = array_map('trim', $data);

            foreach ($data as $key => $value) {
                $data[$key] = filter_var($value, FILTER_SANITIZE_STRING);
            }

            if ($existing = $GLOBALS['db']->select('CubeCart_customer', 'customer_id', array('email' => $data['email']), false, 1, false, false)) {
                $GLOBALS['db']->update('CubeCart_customer', $data, array('email' => $data['email']));
                $customer_id = $existing[0]['customer_id'];
            } else {
                $data['registered']  = time();
                $data['type']    = $type;
                $data['language']    = $GLOBALS['language']->current();

                $customer_id = $this->_validCustomerId();
                if ($customer_id) {
                    $data['customer_id']  = $customer_id;
                }
                $customer_id = $GLOBALS['db']->insert('CubeCart_customer', $data);
                if($type==2) $this->setGhostId($customer_id);
            }
            if ($login) {
                // Automatically log 'em in
                $this->authenticate($data['email'], $data['password']);
            }
            return $customer_id;
        }
        return false;
    }

    /**
     * Delete an address from the address book
     *
     * @param array/address_id $delete
     * @return bool
     */
    public function deleteAddress($delete)
    {
        if ($this->is()) {
            $where['customer_id'] = $this->_user_data['customer_id'];
            if (is_array($delete)) {
                foreach ($delete as $address) {
                    $where['address_id'] = $address;
                    $GLOBALS['db']->delete('CubeCart_addressbook', $where);
                    $this->_deleteBasketAddress($address);
                }
            } else {
                $where['address_id'] = $delete;
                $GLOBALS['db']->delete('CubeCart_addressbook', $where);
                $this->_deleteBasketAddress($address);
            }

            return true;
        }

        return false;
    }

    /**
     * Format address array
     *
     * @param array
     * @return array
     */
    public function formatAddress($address = array(), $user_defined = true, $estimate = false)
    {
        if (!$user_defined && !is_array($address)) {
            if ($GLOBALS['config']->get('config', 'disable_estimates')=='1') {
                $address = array(
                    'postcode' => '',
                    'country' => '',
                    'state' => ''
                );
            } else {
                $address = array(
                    'postcode' => $GLOBALS['config']->get('config', 'store_postcode'),
                    'country' => $GLOBALS['config']->get('config', 'store_country'),
                    'state' => $GLOBALS['config']->get('config', 'store_zone')
                );
            }
        }

        $state_field = is_numeric($address['state']) ? 'id' : 'name';

        // Check state
        $country_id = getCountryFormat($address['country'], 'numcode', 'id');
        // Is state required for this country?!
        if ($GLOBALS['db']->select('CubeCart_geo_country', false, array('id' => $country_id, 'status' => 1))) {
            if ($user_defined && !CC_IN_ADMIN && $_GET['_a']!=='addressbook' && ((empty($address['state']) && !empty($address['country'])) || ($GLOBALS['db']->select('CubeCart_geo_zone', false, array($state_field => $address['state'], 'status' => 1))==false) && $GLOBALS['db']->select('CubeCart_geo_zone', false, array('country_id' => $country_id, 'status' => 1)))) {
                $address_description = empty($address['description']) ? '' : ' (&quot;'.$address['description'].'&quot;)';
                $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->address['check_state'], $address_description));
                httpredir("?_a=addressbook&action=edit&address_id=".$address['address_id']);
                return false;
            }
        }

        $address['state_id']  = getStateFormat($address['state'], $state_field, 'id');
        $address['country_id']  = $address['country'];
        $address['country']   = getCountryFormat($address['country_id']);
        $address['state_abbrev'] = getStateFormat($address['state'], $state_field, 'abbrev');
        $address['country_iso']  = getCountryFormat($address['country_id'], 'numcode', 'iso');
        $address['country_iso3'] = getCountryFormat($address['country_id'], 'numcode', 'iso3');
        $address['state']   = getStateFormat($address['state_id']);
        $address['user_defined'] = $user_defined;
        $address['estimate'] = $estimate;
        return $address;
    }


    /**
     * Get an element or all the user data
     *
     * @param string $field
     * @return string/false
     */
    public function get($field = '')
    {
        if (!$this->is()) {
            return false;
        }

        //If there is a field
        if (!empty($field)) {
            //Send just that field
            return (isset($this->_user_data[$field])) ? $this->_user_data[$field] : false;
        } else {
            //Send all the user data
            return $this->_user_data;
        }
    }

    /**
     * Convert John Smith <john.smith@example.org> to array of parts
     *
     * @param string $input
     * @return false/array
     */
    public static function getEmailAddressParts($input)
    {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $email = $input;
            $name = $input;
        } else {
            preg_match('#\<(.*?)\>#', $input, $match);
            if(filter_var($match[1], FILTER_VALIDATE_EMAIL)) {
                $email = $match[1];
                $name = trim(strip_tags($input));
            } else {
                return false;
            }
        }
        return array('name' => $name, 'email' => $email);
    }

    /**
     * Get address information
     *
     * @param int $address_id
     * @return array/false
     */
    public function getAddress($address_id, $format = false)
    {
        if ($this->is()) {
            if (($raw_address = $GLOBALS['db']->select('CubeCart_addressbook', false, array('customer_id' => $this->_user_data['customer_id'], 'address_id' => $address_id), false, false, false, false)) !== false) {
                if ($format) {
                    return $this->formatAddress($raw_address[0]);
                } else {
                    return $raw_address[0];
                }
            }
        }
        return false;
    }

    /**
     * Get all addresses
     *
     * @param bool $show_all
     * @return array/false
     */
    public function getAddresses($show_all = true)
    {
        if ($this->is()) {
            $where['customer_id'] = $this->_user_data['customer_id'];
            if (!$show_all) {
                $where['billing'] = '1';
            }
            if (($addresses = $GLOBALS['db']->select('CubeCart_addressbook', false, $where, 'billing DESC', false, false, false)) !== false) {
                foreach ($addresses as $address) {
                    $addressArray[] = $this->formatAddress($address);
                }
                return $addressArray;
            }
        }

        return false;
    }

    /**
     * Get the default shipping address
     * @return array/false
     */
    public function getDefaultAddress()
    {
        if ($this->is()) {
            $where['customer_id'] = $this->_user_data['customer_id'];
            
            if ($GLOBALS['config']->get('config', 'basket_allow_non_invoice_address')) {
                $where['default'] = '1';
            } else {
                $where['billing'] = '1';
            }
            
            if (($addresses = $GLOBALS['db']->select('CubeCart_addressbook', false, $where, 'billing DESC', false, false, false)) !== false) {
                foreach ($addresses as $address) {
                    $addressArray[] = $this->formatAddress($address);
                }
                return $addressArray;
            }
        }
        return false;
    }

    /**
     * Get customer id for unregistered customers
     *
     * @return integer/bool
     */
    public function getGhostId()
    {
        return $GLOBALS['session']->get('ghost_customer_id');
    }

    /**
     * Get customer_id
     * @return customer_id/0
     */
    public function getId()
    {
        if (!$this->is()) {
            return 0;
        } else {
            return $this->_user_data['customer_id'];
        }
    }

    /**
     * Get customer group memberships
     * @param int $customer_id
     * @return false/array
     */
    public function getMemberships($customer_id = null)
    {
        if($customer_id === 0) return false;

        if (is_null($customer_id)) {
            $customer_id = $this->getId();
        }
        
        if (ctype_digit((string)$customer_id)) {
            return $GLOBALS['db']->select('CubeCart_customer_membership', false, array('customer_id' => $customer_id));
        }
        return false;
    }

    /**
     * Get required fields for state
     * @param int $country_id
     * @return array
     */
    public function getRequiredAddressFields($country_id)
    {
        $fields = array('first_name','last_name','line1','town','country','postcode');
        if (ctype_digit($country_id)) {
            $result = $GLOBALS['db']->select('CubeCart_geo_country', 'status', array('numcode' => $country_id));
            if ($result && $result[0]['status']=='1') {
                array_push($fields, 'state');
            }
        }
        return $fields;
    }

    /**
     * Is a customer
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
     * Is the user a bot?
     *
     * @return bool
     */
    public function isBot()
    {
        if (is_null($this->_bot)) {
            $this->_bot = false;
            $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
            foreach ($this->_bot_sigs as $signature) {
                if (strpos($agent, $signature) !== false) {
                    $this->_bot = true;
                }
            }
        }
        return $this->_bot;
    }

    /**
     * Logout
     */
    public function logout()
    {
        foreach ($GLOBALS['hooks']->load('class.user.logout') as $hook) {
            include $hook;
        }

        if (isset($_COOKIE['username'])) {
            // Unset the 'Remember Me' cookies
            $GLOBALS['session']->set_cookie('username', '', time()-3600);
        }
        //Destory the session
        $GLOBALS['session']->destroy();
        // Redirect to login
        httpredir(currentPage(null, array('_a' => 'login')));
    }

    /**
     * Request password
     *
     * @param string $email
     * @return bool
     */
    public function passwordRequest($email)
    {
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (($check = $GLOBALS['db']->select('CubeCart_customer', false, "`email` = '$email' AND `type` = 1", false, 1, false, false)) !== false) {
                // Generate validation key
                $validation = Password::getInstance()->createSalt();
                if (($GLOBALS['db']->update('CubeCart_customer', array('verify' => $validation), array('customer_id' => (int)$check[0]['customer_id']))) !== false) {
                    // Send email
                    if (($user = $GLOBALS['db']->select('CubeCart_customer', false, array('customer_id' => (int)$check[0]['customer_id']), false, 1, false, false)) !== false) {
                        $mailer = new Mailer();
                        $link['reset_link'] = CC_STORE_URL.'/index.php?_a=recovery&validate='.$validation;
                        $data = array_merge($user[0], $link);
                        $content = $mailer->loadContent('account.password_recovery', $GLOBALS['language']->current(), $data);
                        $mailer->sendEmail($user[0]['email'], $content);
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Reset password
     *
     * @param email $email
     * @param string $verification
     * @param string $password
     */
    public function passwordReset($email, $verification, $password)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($verification) && !empty($password['password']) && !empty($password['passconf']) && ($password['password'] === $password['passconf'])) {
            if (($check = $GLOBALS['db']->select('CubeCart_customer', array('customer_id', 'email'), "`email` = '$email' AND `verify` = '$verification'", false, 1, false, false)) !== false) {
                // Remove any blocks
                $GLOBALS['db']->delete('CubeCart_blocker', array('username' => $email));

                $salt = Password::getInstance()->createSalt();

                $record = array(
                    'salt'   => $salt,
                    'password'  => Password::getInstance()->getSalted((string)$password['password'], $salt),
                    'verify'  => null,
                    'new_password' => 1
                );
                $where = array(
                    'customer_id' => $check[0]['customer_id'],
                    'email'   => $email,
                    'verify'  => $verification,
                );
                if ($GLOBALS['db']->update('CubeCart_customer', $record, $where)) {
                    if ($this->authenticate($check[0]['email'], (string)$password['password'], false, false, false, false)) {
                        $GLOBALS['gui']->setNotify(($GLOBALS['language']->account['notify_password_recovery_success']));
                        httpredir('?_a=profile');
                    }
                }
            }
        }

        $GLOBALS['gui']->setError($GLOBALS['language']->account['error_password_recover']);
        return false;
    }

    /**
     * Register a new user
     *
     * @return bool
     */
    public function registerUser()
    {
        // Validation
        $error = false;
        foreach ($GLOBALS['hooks']->load('class.user.register_user') as $hook) {
            include $hook;
        }

        //Validate email
        if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL)) {
            $GLOBALS['gui']->setError($GLOBALS['language']->common['error_email_invalid']);
            $error['email'] = true;
        } else {
            // check for duplicates
            if ($existing = $GLOBALS['db']->select('CubeCart_customer', array('email', 'type', 'customer_id'), array('email' => strtolower($_POST['email'])))) {
                if ($existing[0]['type']==1) {
                    $GLOBALS['gui']->setError($GLOBALS['language']->account['error_email_in_use']);
                    $error['dupe'] = true;
                }
            }
        }

        if (!empty($_POST['password'])) {
            if ($_POST['password'] !== $_POST['passconf']) {
                $GLOBALS['gui']->setError($GLOBALS['language']->account['error_password_mismatch']);
                $error['pass'] = true;
            }
        } else {
            $GLOBALS['gui']->setError($GLOBALS['language']->account['error_password_empty']);
            $error['nopass'] = true;
        }

        if (empty($_POST['first_name']) || empty($_POST['last_name'])) {
            $GLOBALS['gui']->setError($GLOBALS['language']->account['error_name_required']);
            $error['name'] = true;
        }

        if ($GLOBALS['gui']->recaptchaRequired()) {
            if (($message = $GLOBALS['session']->get('error', 'recaptcha')) === false) {
                //If the error message from recaptcha fails for some reason:
                $GLOBALS['gui']->setError($GLOBALS['language']->form['verify_human_fail']);
            } else {
                $GLOBALS['gui']->setError($GLOBALS['session']->get('error', 'recaptcha'));
            }
            $error['recaptcha'] = true;
        }
        
        if ($terms = $GLOBALS['db']->select('CubeCart_documents', false, array('doc_terms' => '1'))) {
            if (isset($_POST['terms_agree']) !== true && !$GLOBALS['config']->get('config', 'disable_checkout_terms')) {
                $GLOBALS['gui']->setError($GLOBALS['language']->account['error_terms_agree']);
                $error['terms'] = true;
            }
        }

        if (!$error) {
            // Format data nicely from mr barney brimstock to Mr Barney Brimstock
            $_POST['title']   = ucwords($_POST['title']);
            $_POST['first_name']  = ucwords($_POST['first_name']);
            $_POST['last_name']  = ucwords($_POST['last_name']);

            // Register the user
            $_POST['salt']  = Password::getInstance()->createSalt();
            $_POST['password'] = Password::getInstance()->getSalted($_POST['password'], $_POST['salt']);
            $_POST['registered']= time();
            if (($_POST['ip_address'] = get_ip_address()) === false) {
                $_POST['ip_address'] = 'Unknown';
            } // Get IP Address

            foreach ($GLOBALS['hooks']->load('class.user.register_user.insert') as $hook) {
                include $hook;
            }
            
            foreach ($_POST as $key => $value) {
                $_POST[$key] = filter_var($value, FILTER_SANITIZE_STRING);
            }

            $_POST['language'] = $GLOBALS['language']->current();
            
            if ($existing[0]['type']==2) {
                $_POST['type'] = 1;
                $_POST['new_password'] = 1;
                $GLOBALS['db']->update('CubeCart_customer', $_POST, array('email' => strtolower($_POST['email'])));
                $insert = $existing[0]['customer_id'];
            } else {
                $insert = $GLOBALS['db']->insert('CubeCart_customer', $_POST);
            }

            foreach ($GLOBALS['hooks']->load('class.user.register_user.inserted') as $hook) {
                include $hook;
            }

            if (isset($_POST['mailing_list'])) {
                $newsletter = Newsletter::getInstance();
                $newsletter->subscribe($_POST['email'], $insert);
            }

            $this->authenticate($_POST['email'], $_POST['passconf']);

            return true;
        }

        return false;
    }

    /**
     * Save address to the addressbook
     *
     * @param array $array
     * @param bool $new_user
     * @return bool
     */
    public function saveAddress($array, $new_user = false)
    {
        $array = array_map('trim', $array);

        if ($this->is() || $new_user) {
            if ($array['billing']) {
                $reset['billing'] = '0';
            } else {
                $array['billing'] = '0';
            }
            if ($array['default']) {
                $reset['default'] = '0';
            } else {
                $array['default'] = '0';
            }
            $user_id = ($new_user) ? $new_user : $this->_user_data['customer_id'];

            foreach ($GLOBALS['hooks']->load('class.user.saveaddress') as $hook) {
                include $hook;
            }

            if (isset($reset)) {
                // "There can only be one"
                $GLOBALS['db']->update('CubeCart_addressbook', $reset, array('customer_id' => $user_id), true);
            }

            // Format data nicely from mr barney brimstock to Mr Barney Brimstock & Post/Zip code to uppercase
            $array['title'] = ucwords($array['title']);
            $array['first_name'] = ucwords($array['first_name']);
            $array['last_name'] = ucwords($array['last_name']);
            $array['postcode'] = strtoupper($array['postcode']); // e.g. ab12 34cd to  AB12 34CD
            if (!isset($array['state'])) {
                $array['state'] = '';
            }

            $hash_values = '';
            $checked_keys = array('billing', 'title', 'first_name', 'last_name', 'company_name', 'line1', 'line2', 'town', 'state', 'postcode', 'country');
            foreach ($array as $key => $value) {
                if (in_array($key, $checked_keys)) {
                    $hash_values .= $value;
                }
            }

            $array['hash'] = md5($hash_values);

            if ($result = $GLOBALS['db']->select('CubeCart_addressbook', array('address_id'), array('hash' => $array['hash'], 'customer_id' => $user_id), false, 1, false, false)) {
                $array['address_id'] = $result[0]['address_id'];
            }

            if (isset($array['address_id']) && is_numeric($array['address_id'])) {
                // Update
                $result = $GLOBALS['db']->update('CubeCart_addressbook', $array, array('address_id' => $array['address_id'], 'customer_id' => $user_id), true);
                $this->_updateBasketAddress($array['address_id']);
                return $result;
            } else {
                // Insert
                $array['customer_id'] = $user_id;
                return $GLOBALS['db']->insert('CubeCart_addressbook', $array);
            }
        }
        return false;
    }

    /**
     * Set customer id for unregistered customers
     *
     * @param int $customer_id
     * @return bool
     */
    public function setGhostId($customer_id = '')
    {
        return $GLOBALS['session']->set('ghost_customer_id', $customer_id);
    }

    /**
     * Update customer data
     *
     * @param array $update
     * @return bool
     */
    public function update($update = null)
    {
        if (!empty($update) && is_array($update)) {
            unset($update['customer_id']);
            foreach ($update as $k => $v) {
                if (isset($this->_user_data[$k]) && $this->_user_data[$k] != $v) {
                    $this->_user_data[$k] = $v;
                    $this->_changed = true;
                }
            }
            if ($this->_changed) {
                return $this->_update();
            }
        } elseif (isset($_POST['update'])) {
            $remove = array_diff_key($_POST, $this->_user_data);
            $update = $_POST;
            //Remove different keys
            foreach ($remove as $k => $v) {
                unset($update[$k]);
            }
            //Remove things that shouldn't be updated by post
            unset($update['salt']);
            unset($update['customer_id']);
            unset($update['status']);
            unset($update['type']);

            //Check of any acutal changes
            $diff = arrayRecursiveDiff($update, $this->_user_data);
            if (!empty($diff)) {
                $this->_user_data = array_merge($this->_user_data, $update);
                $this->_changed = true;
                return $this->_update();
            }
        }
        return false;
    }

    public function load()
    {
        $this->_load();
    }

    //=====[ Private ]=======================================

    /**
     * Delete address from basket
     *
     * @param int $id
     * @return bool
     */
    private function _deleteBasketAddress($id)
    {
        $match = false;
        if (isset($GLOBALS['cart']->basket['delivery_address']['address_id']) && $GLOBALS['cart']->basket['delivery_address']['address_id']==$id) {
            unset($GLOBALS['cart']->basket['delivery_address']);
            $GLOBALS['cart']->save();
            $match = true;
        }
        if (isset($GLOBALS['cart']->basket['billing_address']['address_id']) && $GLOBALS['cart']->basket['billing_address']['address_id']==$id) {
            unset($GLOBALS['cart']->basket['billing_address']);
            $GLOBALS['cart']->save();
            $match = true;
        }
        return $match;
    }

    /**
     * Load customer data
     */
    private function _load()
    {
        foreach ($GLOBALS['hooks']->load('class.user.load') as $hook) {
            include $hook;
        }

        if (!isset($GLOBALS['session']->session_data['customer_id']) || $GLOBALS['session']->session_data['customer_id'] == '0') {
            return;
        }
        if ($GLOBALS['session']->session_data['customer_id'] && $result = $GLOBALS['db']->select('CubeCart_customer', false, array('customer_id' => (int)$GLOBALS['session']->session_data['customer_id']), false, 1, false, false)) {
            $result[0]['language'] = $this->_validLanguage($result[0]['language']);
            $this->_user_data = $result[0];
            foreach ($GLOBALS['hooks']->load('class.user.load.user') as $hook) {
                include $hook;
            }
            $this->_logged_in = true;
            if (!$GLOBALS['session']->has('user_language', 'client')) {
                $GLOBALS['session']->set('user_language', $result[0]['language'], 'client');
            }
            if ((empty($this->_user_data['email']) || !filter_var($this->_user_data['email'], FILTER_VALIDATE_EMAIL) || empty($this->_user_data['first_name']) || empty($this->_user_data['last_name'])) && !in_array(strtolower($_GET['_a']), array('profile', 'logout'))) {
                // Force account details page
                $GLOBALS['session']->set('temp_profile_required', true);
                httpredir(currentPage(null, array('_a' => 'profile')));
            }
        }
    }

    /**
     * Update db
     */
    private function _update()
    {
        return Database::getInstance()->update('CubeCart_customer', $this->_user_data, array('customer_id' => $this->_user_data['customer_id']), true);
    }

    /**
     * Update address from basket
     *
     * @param int $id
     * @return bool
     */
    private function _updateBasketAddress($id)
    {
        $match = false;

        $updated_address = $this->getAddress($id, true);

        if (isset($GLOBALS['cart']->basket['delivery_address']['address_id']) && $GLOBALS['cart']->basket['delivery_address']['address_id']==$id) {
            $GLOBALS['cart']->basket['delivery_address'] = array_merge($GLOBALS['cart']->basket['delivery_address'], $updated_address);
            $GLOBALS['cart']->save();
            $match = true;
        }
        if (isset($GLOBALS['cart']->basket['billing_address']['address_id']) && $GLOBALS['cart']->basket['billing_address']['address_id']==$id) {
            $GLOBALS['cart']->basket['billing_address'] = array_merge($GLOBALS['cart']->basket['billing_address'], $updated_address);
            $GLOBALS['cart']->save();
            $match = true;
        }
        return $match;
    }
    /**
     * New Customer ID must not be less than max order summary customer ID
     *
     * @return false/int
     */
    private function _validCustomerId()
    {
        return false;
        /* Kept for hiistorical purposes
        $customers = $GLOBALS['db']->misc("SHOW TABLE STATUS LIKE '".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_customer'", false);
        
        $orders = $GLOBALS['db']->misc("SELECT MAX(`customer_id`) as `max_id` FROM `".$GLOBALS['config']->get('config', 'dbprefix')."CubeCart_order_summary`", false);
        
        // Do we have any orders yet and is the max customer_id > 0?
        if ($orders && $orders[0]['max_id'] > 0) {
            // Do we have any customers yet and is the auto increment > 0?
            if ($customers && $customers[0]['Auto_increment'] > 0) {
                // Are there existing customers orders with higher customer id than next customer id?
                if ($orders[0]['max_id'] >= $customers[0]['Auto_increment']) {
                    // Finally be sure proposed ID isn't in use
                    $id = $orders[0]['max_id']+1;
                    if($GLOBALS['db']->select('CubeCart_customer', false, array('customer_id' => $id), false, 1, false, false) == false) {
                        return $id;
                    }
                }
            }
        }
        return false;
        */
    }

    /**
     * Validate users language string
     *
     * @return string
     */
    private function _validLanguage($language)
    {
        $default_language = $GLOBALS['config']->get('config', 'default_language');
        if (!preg_match(Language::LANG_REGEX, $language)) {
            return $default_language;
        } elseif ($language!==$default_language) {
            if ($enabled_languages = $GLOBALS['config']->get('languages')) {
                if (!isset($enabled_languages[$language])) {
                    return $default_language;
                } elseif ($enabled_languages[$language]=='0') {
                    return $default_language;
                }
            } else {
                return $default_language;
            }
        }
        return $language;
    }
}
