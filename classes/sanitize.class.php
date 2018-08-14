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
 * Santize class
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Sanitize
{

    /**
     * Checks GET & POSTs for valid security token
     */
    public static function checkToken()
    {
        // Check defined CSRF on speficfied GET
        if (ADMIN_CP) {
            global $glob;
            $csrf_path = CC_ROOT_DIR.'/'.$glob['adminFolder'].'/skins/'.$GLOBALS['config']->get('config', 'admin_skin').'/csrf.inc.php';
            if (file_exists($csrf_path)) {
                require_once($csrf_path);
                if (is_array($csrf_maps)) {
                    foreach ($csrf_maps as $csrf_map) {
                        if (is_array($csrf_map)) {
                            $csrf_check = false;
                            foreach ($csrf_map as $key => $value) {
                                if ((!$value && isset($_GET[$key])) || (isset($_GET[$key]) && $_GET[$key]==$value)) {
                                    $csrf_check = true;
                                } else {
                                    $csrf_check = false;
                                    break;
                                }
                            }

                            if ($csrf_check) {
                                if (!isset($_GET['token']) || !$GLOBALS['session']->checkToken($_GET['token'])) {
                                    //Make a new token just to insure that it doesn't get used again
                                    $GLOBALS['session']->getToken(true);
                                    self::_stopToken();
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        if (!empty($_POST)) {
            $csrf_exception = false;
            // Exception for payment gateways
            if (!isset($_GET['_a']) && isset($_GET['_g'], $_GET['type'], $_GET['cmd'], $_GET['module']) && in_array($_GET['_g'], array('remote','rm')) && $_GET['type']=='gateway' && in_array($_GET['cmd'], array('call', 'process')) && !empty($_GET['module'])) {
                $csrf_exception = true;
            } elseif (isset($_GET['_a']) && $_GET['_a']=='complete' && !isset($_GET['_g'])) {
                $csrf_exception = true;
            }

            //Validate the POST token
            if (!$csrf_exception && (!isset($_POST['token']) || !$GLOBALS['session']->checkToken($_POST['token']))) {
                //Make a new token just to insure that it doesn't get used again
                $GLOBALS['session']->getToken(true);
                self::_stopToken();
            }
        }
    }

    /**
     * Clean all the global varaibles
     */
    public static function cleanGlobals()
    {
        $GLOBALS['RAW'] = array(
            'GET' 		=> $_GET,
            'POST' 		=> $_POST,
            'COOKIE' 	=> $_COOKIE,
            'REQUEST' 	=> $_REQUEST
        );

        self::_clean($_GET);
        self::_clean($_POST);
        self::_clean($_COOKIE);
        self::_clean($_REQUEST);
    }

    //=====[ Private ]=======================================

    /**
     * Clean a variable
     *
     * @param array $data
     */
    private static function _clean(&$data)
    {
        if (empty($data)) {
            return;
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                //Make sure the variable's key name is a valid one
                if (preg_match('#([^a-z0-9\-\_\:\@\|])#i', urldecode($key))) {
                    trigger_error('Security Warning: Illegal array key "'.htmlentities($key).'" was detected and was removed.', E_USER_WARNING);
                    unset($data[$key]);
                    continue;
                } else {
                    if (is_array($value)) {
                        self::_clean($data[$key]);
                    } else {
                        // If your HTML content isn't in a field with one of the following names, it's going!
                        // We shold probably standardise the field names in the future
                        if (!empty($value)) {
                            $data[$key] = self::_safety($value);
                        }
                    }
                }
            }
        } else {
            $data = self::_safety($data);
        }
    }

    /**
     * Sanitize a string for HTML
     *
     * @param string $value
     * @return string
     */
    private static function _safety($value)
    {
        return filter_var($value, FILTER_SANITIZE_STRING);
    }

    /**
     * Clears POST and triggers error
     * Used when the POST token is not valid
     */
    private static function _stopToken()
    {
        unset($_POST, $_GET);
        $message = 'Security Alert: Possible Cross-Site Request Forgery (CSRF). Please do not use multiple tabs/windows or the browser back button. <a href="https://support.cubecart.com/Knowledgebase/Article/View/240/45/">Learn more</a>.';
        $gui_message['error'][md5($message)] = $message;
        $GLOBALS['session']->set('GUI_MESSAGE', $gui_message);
        trigger_error('Invalid Security Token', E_USER_WARNING);
    }
}
