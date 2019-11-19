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
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

/**
 * Language controller
 *
 * @author Al Brookbanks
 * @since 5.0.0
 */

class Mailer extends PHPMailer\PHPMailer\PHPMailer
{
    private $_debugger;

    private $_html;
    private $_text;
    private $_email_content_id;
    private $_import_new = false;

    protected static $_instance;

    ##############################################

    public function __construct()
    {
        // Configure PHPMailer variables
        $this->From   = $GLOBALS['config']->get('config', 'email_address');
        $this->FromName  = html_entity_decode($GLOBALS['config']->get('config', 'email_name'), ENT_QUOTES);
        $this->CharSet   = 'UTF-8';

        switch ($GLOBALS['config']->get('config', 'email_method')) {
        case 'smtp':
        case 'smtp_ssl':
        case 'smtp_tls':
            $this->IsSMTP(true);
            $this->Host = $GLOBALS['config']->get('config', 'email_smtp_host');
            $this->Port = $GLOBALS['config']->get('config', 'email_smtp_port');
            if ($GLOBALS['config']->get('config', 'email_method')=='smtp_ssl') {
                $this->SMTPSecure = 'ssl';
            } elseif ($GLOBALS['config']->get('config', 'email_method')=='smtp_tls') {
                $this->SMTPSecure = 'tls';
            }
            if ($GLOBALS['config']->get('config', 'email_smtp')) {
                $this->SMTPAuth = true;
                $this->Username = $GLOBALS['config']->get('config', 'email_smtp_user');
                $this->Password = $GLOBALS['config']->get('config', 'email_smtp_password');
            }
            break;
        case 'mail':
        default:
            $this->IsMail(true);
        }
    }

    /**
     * Setup the instance (singleton)
     *
     * @return Mailer
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
     * Load email content
     *
     * @param string $content_type
     * @param string $language
     * @param bool $data
     * @return array/false
     */
    public function loadContent($content_type, $language = null, $data = false, $default = false, $panic = false)
    {
        $language = preg_match(Language::LANG_REGEX, $language) ? $language : $GLOBALS['language']->current();
        $language = ($language == 'en') ? 'en-GB' : $language;

        if (!empty($content_type)) {
            $where = array('content_type' => (string)$content_type, 'language' => $language);
            if ($panic) { // Default language doesn't have this content type!
                unset($where['language']);
            }
            if (($contents =  $GLOBALS['db']->select('CubeCart_email_content', false, $where, false, 1)) !== false) {
                $this->_email_content_id = $contents[0]['content_id'];
                $elements = array(
                    'subject'  => $contents[0]['subject'],
                    'content_html' => $contents[0]['content_html'],
                    'content_text' => $contents[0]['content_text'],
                );
                if ($data) {
                    $GLOBALS['smarty']->assign('DATA', $data);
                }
                if (!empty($elements['content_html']) && !empty($elements['content_text'])) {
                    return $elements;
                }
            } else {
                if ($panic) { // Content type doesn't exist for any language
                    trigger_error('Email content for '.$content_type.' doesn\'t exist in any lamguage.');
                    return false;
                } elseif ($default) {
                    trigger_error('Email content for '.$content_type.' doesn\'t exist in default lamguage.');
                    return $this->loadContent($content_type, $GLOBALS['config']->get('config', 'default_language'), $data, true, true);
                }
                // No results!
                if (!$this->_import_new) {
                    ## Check for new language packs in this version and install email templates if required
                    $existing_languages = $GLOBALS['db']->select('CubeCart_email_content', 'DISTINCT `language`');
                    $missing_languages  = $GLOBALS['language']->listLanguages();

                    ## Loop existing languages and remove to leave missing languages array with the ones we need to import
                    if ($existing_languages) {
                        foreach ($existing_languages as $key => $value) {
                            unset($missing_languages[$value['language']]);
                        }
                    }
                    ## Import missing language email templates if they exist... pukka
                    if (is_array($missing_languages)) {
                        foreach ($missing_languages as $code => $lang) {
                            $GLOBALS['language']->importEmail('email_'.$code.'.xml');
                        }
                    }
                    $this->_import_new = true;
                    return $this->loadContent($content_type, $language, $data);
                } else {
                    // Try loading the default language content
                    return $this->loadContent($content_type, $GLOBALS['config']->get('config', 'default_language'), $data, true);
                }
            }
        }
        return false;
    }

    /**
     * Send the email
     *
     * @param string $email
     * @param string $contents
     * @param int $template_id
     * @return bool
     */
    public function sendEmail($email = false, $contents = false, $template_id = false)
    {
        foreach ($GLOBALS['hooks']->load('class.mailer.send') as $hook) {
            include $hook;
        }
        $this->ClearAddresses();
        if (strstr($email, ',')) {
            $emails = explode(',', $email);
            foreach ($emails as $mail) {
                if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                    $this->AddAddress($mail);
                }
            }
            $email_param = '';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->AddAddress($email, (isset($contents['to'])) ? $contents['to'] : '');
            $email_param = '&amp;unsubscribe='.urlencode($email);
        } else {
            return false;
        }
        $contents = $this->_parseContents($contents);
        if (is_array($contents)) {
            // Load template from specified id or default if not set
            $where = (!$template_id) ? array('template_default' => 1) : array('template_id' => (int)$template_id);
            if (($templates = $GLOBALS['db']->select('CubeCart_email_template', array('content_html', 'content_text'), $where)) !== false) {
                foreach ($contents as $key => $string) {
                    if (strtolower($key) == 'subject') {
                        $this->Subject = strip_tags($string);
                        continue;
                    } elseif (in_array($key, array('content_html', 'content_text'))) {
                        // define macros
                        $data['logoURL']  = $GLOBALS['gui']->getLogo(true, 'emails');
                        $data['store_name'] = $GLOBALS['config']->get('config', 'store_name');
                        $data['storeName']  = $GLOBALS['config']->get('config', 'store_name');
                        $data['storeURL']  = $GLOBALS['storeURL'];
                        $data['unsubscribeURL'] = $GLOBALS['storeURL'].'/index.php?_a=unsubscribe'.$email_param;

                        $template = $this->_parseTemplate($templates[0], $data, $string);
                        // assign to right variable
                        switch ($key) {
                            case 'content_html':
                                $this->_html = $template['content_html'];
                                break;
                            case 'content_text':
                                $this->_text = $template['content_text'];
                                break;
                            }
                    }
                }
            } else {
                $this->Subject = $contents['subject'];
                $this->_html = $contents['content_html'];
                $this->_text = $contents['content_text'];
            }

            $this->Body   = $this->_html;
            $this->AltBody  = $this->_text;

            if (isset($contents['email'])) {
                $this->addReplyTo($contents['email'], (isset($contents['from'])) ? $contents['from'] : '');
                $from = $contents['email'];
            } else {
                $from = $GLOBALS['config']->get('config', 'email_address');
            }
            $this->Sender = $GLOBALS['config']->get('config', 'email_address');

            foreach ($GLOBALS['hooks']->load('class.mailer.presend') as $hook) {
                include $hook;
            }

            // Send email
            $result = $this->Send();
            // Log email
            $email_data = array(
                'subject' => $this->Subject,
                'content_html' => $this->_html,
                'content_text' => $this->_text,
                'to' => $email,
                'from' => $from,
                'result' => $result,
                'email_content_id' => $this->_email_content_id,
                'fail_reason' => !empty($this->ErrorInfo) ? htmlentities($this->ErrorInfo, ENT_QUOTES) : ''
            );
            $log_days = $GLOBALS['config']->get('config', 'r_email');
            if (ctype_digit((string)$log_days) &&  $log_days > 0) {
                $GLOBALS['db']->insert('CubeCart_email_log', $email_data);
                $GLOBALS['db']->delete('CubeCart_email_log', 'date < DATE_SUB(NOW(), INTERVAL '.$log_days.' DAY)');
            } elseif (empty($log_days) || !$log_days) {
                $GLOBALS['db']->insert('CubeCart_email_log', $email_data);
            }
            return $result;
        }

        return false;
    }

    //=====[ Private ]=======================================

    /**
     * Parse contents though Smarty
     *
     * @param string $contents
     * @return string
     */
    private function _parseContents($contents)
    {
        if (is_string($contents)) {
            return $GLOBALS['smarty']->fetch('string:'.$contents);
        } elseif (is_array($contents)) {
            $out = array();
            foreach ($contents as $key => $content) {
                $out[$key] = $GLOBALS['smarty']->fetch('string:'.$content);
            }
            return $out;
        }
        return false;
    }

    /**
     * Parse template though Smarty
     *
     * @param array $templates
     * @param string $data
     * @param string $email_content
     * @return string
     */
    private function _parseTemplate($templates, $data, $email_content = '')
    {
        $GLOBALS['smarty']->assign('DATA', $data);
        $GLOBALS['smarty']->assign('EMAIL_CONTENT', $email_content);
        if (is_array($templates)) {
            foreach ($templates as $key => $template) {
                $out[$key] = $GLOBALS['smarty']->fetch('string:'.$template);
            }
        }
        return $out;
    }
}
