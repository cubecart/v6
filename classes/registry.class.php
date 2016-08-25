<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2015. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * Registry
 * The Registry class is used to store values in a global fashion
 *
 * @author Travis Crowder
 * @since 6.0.13
 *
 * @final
 * @see ArrayObject
 */
final class Registry extends ArrayObject {

    /**
     * Instance object
     *
     * @staticvar object Registry
     */
    static private $_instance = null;

    /**
     * @access private
     * @var mixed Array to hold the values.
     */
    private $_registry;

    /**
     * Using Singleton pattern
     *
     * @access private
     * @return void
     */
    public function __construct($array = array(), $flags = parent::ARRAY_AS_PROPS) {
        parent::__construct($array, $flags);
    }

    /**
     * Singleton accessor to the object
     *
     * @access public
     * @return Registry $instance Instance of the object
     */
    static public function getInstance() {
        if (self::$_instance === null) {
            self::$_instance = new Registry;
        }
        return self::$_instance;
    }

    /**
     *  Get method
     *
     * @access public
     * @return mixed Element in the registry
     */
    static public function get($key) {
        $instance = self::getInstance();
        if (!$instance->checkOffset($key)) {
            return false;
        } #throw new Exception($key . ' is not set in the registry');
        return $instance->returnOffset($key);
    }

    /**
     *  Set method
     *
     *  Accessor method used to set a value to a registry key.
     * @access public
     * @static
     * @return void
     */
    static public function set($key, $value) {
        $instance = self::getInstance();
        $instance->setOffset($key, $value);
    }

    /**
     *  Method to set ArrayObject offset
     *
     *  This method sets the ArrayObjects value
     * @access protected
     * @return void
     */
    protected function setOffset($key, $value) {
        $this->_registry[$key] = $value;
    }

    /**
     *  Check ArrayObject offset
     *
     *  Method to check if an ArrayObject Registry index has a value.
     * @param string $key Index of Registry to access
     * @access protected
     * @return bool
     */
    protected function checkOffset($key) {
        if (isset($this->_registry[$key])) {
            return true;
        }

        return false;
    }

    /**
     *  Return method
     *
     *  Method to return the value of a Registry index
     * @access protected
     * @return mixed Value of Registry index.
     */
    protected function returnOffset($key) {
        return $this->_registry[$key];
    }

    /**
     *  Dump method
     *
     *  Method to dump the contents of the Registry to the screen via print_r
     * @access public
     * @return void
     */
    public function dump() {
        echo '<pre>' . print_r($this->_registry, true) . '</pre>';
    }

}