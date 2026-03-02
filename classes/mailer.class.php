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
    private $_html;
    private $_text;
    private $_template_title;
    private $_email_content_id;
    private $_content_type = '';
    private $_import_new = false;
    private $_sendgrid = false;
    private $_sendgrid_key = '';
    private $_method = '';

    protected static $_instance;

    ##############################################

    public function __construct()
    {
        // Configure PHPMailer variables
        $this->From   = $GLOBALS['config']->get('config', 'email_address');
        $this->FromName  = html_entity_decode($GLOBALS['config']->get('config', 'email_name'), ENT_QUOTES);
        $this->CharSet   = 'UTF-8';
        $this->_method = $GLOBALS['config']->get('config', 'email_method');
        switch ($this->_method) {
            case 'sendgrid':
                require_once CC_ROOT_DIR.'/classes/sendgrid/sendgrid-php.php';
                $this->_sendgrid_key = $GLOBALS['config']->get('config', 'sendgrid_key');
                $this->_sendgrid = new \SendGrid\Mail\Mail();
            break;
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
     * Setup the instance (singleton)
     *
     * @return Mailer
     */
    public function getTemplateTitle()
    {
        return $this->_template_title;
    }
    /**
     * Load email content
     *
     * @param string $content_type
     * @param string $language
     * @param bool $data
     * @return array/false
     */
    public function loadContent($content_type, $language = '', $data = false, $default = false, $panic = false)
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
                $this->_content_type = $content_type;
                $elements = array(
                    'subject'  => $contents[0]['subject'],
                    'content_html' => $contents[0]['content_html'],
                );
                if ($data) {
                    $GLOBALS['smarty']->assign('DATA', $data);
                }
                if (!empty($elements['content_html'])) {
                    return $elements;
                }
            } else {
                if ($panic) { // Content type doesn't exist for any language
                    trigger_error('Email content for '.$content_type.' doesn\'t exist in any language.');
                    return false;
                } elseif ($default) {
                    // Self-heal: try importing this content type from the default language's XML
                    $GLOBALS['language']->importEmail('email_'.$language.'.xml', CC_LANGUAGE_DIR, $content_type);
                    if (($contents = $GLOBALS['db']->select('CubeCart_email_content', false, $where, false, 1, false, false)) !== false) {
                        $this->_email_content_id = $contents[0]['content_id'];
                        $this->_content_type = $content_type;
                        if ($data) {
                            $GLOBALS['smarty']->assign('DATA', $data);
                        }
                        if (!empty($contents[0]['content_html'])) {
                            return array(
                                'subject'  => $contents[0]['subject'],
                                'content_html' => $contents[0]['content_html'],
                            );
                        }
                    }
                    trigger_error('Email content for '.$content_type.' doesn\'t exist in default language.');
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
                    ## Self-heal: language exists but specific content type is missing - try importing from XML
                    $GLOBALS['language']->importEmail('email_'.$language.'.xml', CC_LANGUAGE_DIR, $content_type);
                    if (($contents = $GLOBALS['db']->select('CubeCart_email_content', false, $where, false, 1, false, false)) !== false) {
                        $this->_email_content_id = $contents[0]['content_id'];
                        $this->_content_type = $content_type;
                        if ($data) {
                            $GLOBALS['smarty']->assign('DATA', $data);
                        }
                        if (!empty($contents[0]['content_html'])) {
                            return array(
                                'subject'  => $contents[0]['subject'],
                                'content_html' => $contents[0]['content_html'],
                            );
                        }
                    }
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
    public function sendEmail($email = false, $contents = array(), $template_id = false)
    {
        foreach ($GLOBALS['hooks']->load('class.mailer.send') as $hook) {
            include $hook;
        }
        $this->ClearAddresses();
        $send_grid_to = array();
        if (strstr($email, ',')) {
            $emails = explode(',', $email);
            foreach ($emails as $mail) {
                if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                    $send_grid_to[] = $mail;
                    $this->AddAddress($mail);
                }
            }
            $email_param = '';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $send_grid_to[] = $email;
            $this->AddAddress($email, (isset($contents['to'])) ? $contents['to'] : '');
            $email_param = '&amp;unsubscribe='.urlencode($email);
        } else {
            return false;
        }
        $contents = $this->_parseContents($contents);
        if (is_array($contents)) {
            // Load template from specified id or default if not set
            $where = (!$template_id) ? array('template_default' => 1) : array('template_id' => (int)$template_id);
            if (($templates = $GLOBALS['db']->select('CubeCart_email_template', array('title', 'content_html'), $where)) !== false) {
                $this->_template_title = $templates[0]['title'];
                foreach ($contents as $key => $string) {
                    if (strtolower($key) == 'subject') {
                        $this->Subject = strip_tags($string);
                        continue;
                    } elseif ($key === 'content_html') {
                        // define macros
                        $data['logoURL']  = $GLOBALS['gui']->getLogo(true, 'emails');
                        $data['store_name'] = $GLOBALS['config']->get('config', 'store_name');
                        $data['storeName']  = $GLOBALS['config']->get('config', 'store_name');
                        $data['storeURL']  = $GLOBALS['storeURL'];
                        $data['unsubscribeURL'] = $GLOBALS['storeURL'].'/index.php?_a=unsubscribe'.$email_param;
                        $data['jsonLd'] = $this->_buildJsonLd();

                        $template = $this->_parseTemplate($templates[0], $data, $string);
                        $this->_html = $template['content_html'];
                        $this->_text = $this->_htmlToText($this->_html);
                    }
                }
            } else {
                $this->Subject = $contents['subject'];
                $this->_html = $contents['content_html'];
                $this->_text = $this->_htmlToText($this->_html);
            }

            $this->Body            = $this->_html;
            $this->AltBody         = $this->_text;
            $this->AltBodyEncoding = 'quoted-printable';

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
            if(!isset($disable_send) || !$disable_send) {
                if($this->_method=='sendgrid') {
                    $this->_sendgrid->setFrom($this->From, $this->FromName);
                    $this->_sendgrid->setSubject($this->Subject);
                    foreach($send_grid_to as $t) {
                        $this->_sendgrid->addTo($t);
                    }
                    $this->_sendgrid->addContent("text/plain", $this->_text);
                    $this->_sendgrid->addContent("text/html", $this->_html);
                    $sendgrid = new \SendGrid($this->_sendgrid_key);
                    try {
                        $response = $sendgrid->send($this->_sendgrid);
                        $result =  in_array($response->statusCode(), array(200, 202)) ? true : false;
                    } catch (Exception $e) {
                        $this->ErrorInfo = $e->getMessage();
                    }
                } else {
                    $result = $this->Send();
                }
            }
            
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
                if (executionChance(2)) { // 2% probability
                    $GLOBALS['db']->delete('CubeCart_email_log', 'date < DATE_SUB(NOW(), INTERVAL '.$log_days.' DAY)', 500);
                }
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
            return $this->_cleanseContents($GLOBALS['smarty']->fetch('string:'.$contents));
        } elseif (is_array($contents)) {
            $out = array();
            foreach ($contents as $key => $content) {
                $out[$key] = $this->_cleanseContents($GLOBALS['smarty']->fetch('string:'.$content));
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

    /**
     * Remove unwanted tags
     *
     * @param string $string
     * @return string
     */
    private function _cleanseContents($string) {
        return preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string);
    }

    /**
     * Convert HTML to plain text
     *
     * @param string $html
     * @return string
     */
    private function _htmlToText($html) {
        // Remove head/script/style sections entirely
        $html = preg_replace('#<head[^>]*>.*?</head>#is', '', $html);
        $html = preg_replace('#<script[^>]*>.*?</script>#is', '', $html);
        $html = preg_replace('#<style[^>]*>.*?</style>#is', '', $html);
        // Convert anchors: preserve URL when it differs from link text
        $html = preg_replace_callback(
            '#<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)</a>#is',
            function ($m) {
                $text = trim(strip_tags($m[2]));
                $url  = $m[1];
                return ($text && $text !== $url) ? $text.' ('.$url.')' : $url;
            },
            $html
        );
        // Block-level elements → line breaks
        $html = preg_replace('#<br\s*/?>#i', "\n", $html);
        $html = preg_replace('#</?(p|div|tr|h[1-6]|blockquote|table|tbody|thead)[^>]*>#i', "\n\n", $html);
        $html = preg_replace('#<li[^>]*>#i', "\n• ", $html);
        $html = preg_replace('#</?(td|th)[^>]*>#i', "\t", $html);
        // Strip remaining tags
        $html = strip_tags($html);
        // Decode HTML entities
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Normalise whitespace
        $html = preg_replace('/[ \t]+/', ' ', $html);
        $html = preg_replace('/\n[ \t]+/', "\n", $html);
        $html = preg_replace('/[ \t]+\n/', "\n", $html);
        $html = preg_replace('/\n{3,}/', "\n\n", $html);
        return trim($html);
    }

    /**
     * Build JSON-LD schema markup for email
     *
     * @return string Script tag with JSON-LD or empty string
     */
    private function _buildJsonLd()
    {
        $data = $GLOBALS['smarty']->getTemplateVars('DATA');
        $products = $GLOBALS['smarty']->getTemplateVars('PRODUCTS');
        $billing = $GLOBALS['smarty']->getTemplateVars('BILLING');
        $shipping = $GLOBALS['smarty']->getTemplateVars('SHIPPING');

        $schema = null;

        switch ($this->_content_type) {
            case 'cart.order_confirmation':
                $schema = $this->_buildOrderSchema($data, $products, $billing, $shipping, 'https://schema.org/OrderProcessing');
                break;
            case 'cart.order_complete':
                $schema = $this->_buildOrderSchema($data, $products, $billing, $shipping, 'https://schema.org/OrderDelivered');
                break;
            case 'cart.order_cancelled':
                $schema = $this->_buildOrderSchema($data, $products, $billing, $shipping, 'https://schema.org/OrderCancelled');
                break;
            case 'cart.payment_received':
                $schema = $this->_buildOrderSchema($data, $products, $billing, $shipping, 'https://schema.org/OrderProcessing');
                break;
            case 'cart.payment_fraud':
                $schema = $this->_buildOrderSchema($data, $products, $billing, $shipping, 'https://schema.org/OrderProblem');
                break;
            case 'cart.digital_download':
                $schema = $this->_buildOrderSchema($data, $products, $billing, $shipping, 'https://schema.org/OrderInTransit');
                break;
            case 'account.password_recovery':
                $link = isset($data['reset_link']) ? $data['reset_link'] : '';
                $schema = $this->_buildViewActionSchema('Reset Password', $link);
                break;
            case 'newsletter.verify_email':
                $link = isset($data['link']) ? $data['link'] : '';
                $schema = $this->_buildViewActionSchema('Confirm Subscription', $link);
                break;
            case 'newsletter.remove_request':
                $link = isset($data['link']) ? $data['link'] : '';
                $schema = $this->_buildViewActionSchema('Confirm Unsubscribe', $link);
                break;
            case 'cart.abandoned':
                $schema = $this->_buildAbandonedCartSchema($data, $products);
                break;
        }

        if (!$schema) {
            return '';
        }

        return '<script type="application/ld+json">'
            . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            . '</script>';
    }

    /**
     * Build Order schema for transactional emails
     */
    private function _buildOrderSchema($data, $products, $billing, $shipping, $orderStatus)
    {
        if (empty($data) || empty($data['cart_order_id'])) {
            return null;
        }

        $storeName = $GLOBALS['config']->get('config', 'store_name');
        $currency = !empty($data['currency']) ? $data['currency'] : $GLOBALS['config']->get('config', 'default_currency');
        $orderDate = !empty($data['raw_order_date']) ? date('c', (int)$data['raw_order_date']) : '';
        $total = isset($data['raw_total']) ? sprintf('%.2f', $data['raw_total']) : '0.00';
        $displayOrderId = !empty($data['custom_oid']) ? $data['custom_oid'] : $data['cart_order_id'];

        $schema = array(
            '@context' => 'http://schema.org',
            '@type' => 'Order',
            'orderNumber' => $displayOrderId,
            'orderStatus' => $orderStatus,
            'merchant' => array(
                '@type' => 'Organization',
                'name' => $storeName
            ),
            'priceCurrency' => $currency,
            'price' => $total
        );

        if (!empty($orderDate)) {
            $schema['orderDate'] = $orderDate;
        }

        // Line items
        if (!empty($products) && is_array($products)) {
            $offers = array();
            foreach ($products as $product) {
                $offer = array(
                    '@type' => 'Offer',
                    'itemOffered' => array(
                        '@type' => 'Product',
                        'name' => $product['name']
                    ),
                    'priceCurrency' => $currency,
                    'price' => isset($product['raw_price']) ? sprintf('%.2f', $product['raw_price']) : '0.00',
                    'eligibleQuantity' => array(
                        '@type' => 'QuantitativeValue',
                        'value' => (int)$product['quantity']
                    )
                );
                if (!empty($product['product_code'])) {
                    $offer['itemOffered']['sku'] = $product['product_code'];
                }
                $offers[] = $offer;
            }
            $schema['acceptedOffer'] = $offers;
        }

        // Billing address
        if (!empty($billing)) {
            $schema['billingAddress'] = $this->_buildPostalAddress($billing);
        }

        // View order action
        $schema['potentialAction'] = array(
            '@type' => 'ViewAction',
            'target' => $GLOBALS['storeURL'] . '/index.php?_a=vieworder&cart_order_id=' . $displayOrderId,
            'name' => 'View Order'
        );

        return $schema;
    }

    /**
     * Build ViewAction schema for non-order emails
     */
    private function _buildViewActionSchema($name, $url)
    {
        if (empty($url)) {
            return null;
        }

        return array(
            '@context' => 'http://schema.org',
            '@type' => 'EmailMessage',
            'potentialAction' => array(
                '@type' => 'ViewAction',
                'name' => $name,
                'target' => $url
            )
        );
    }

    /**
     * Build abandoned cart schema with product list and recovery action
     */
    private function _buildAbandonedCartSchema($data, $products)
    {
        if (empty($data['recovery_link'])) {
            return null;
        }

        $storeName = $GLOBALS['config']->get('config', 'store_name');
        $currency = $GLOBALS['config']->get('config', 'default_currency');

        $schema = array(
            '@context' => 'http://schema.org',
            '@type' => 'EmailMessage',
            'description' => 'You left items in your cart at ' . $storeName,
            'potentialAction' => array(
                '@type' => 'ViewAction',
                'name' => 'Complete Your Order',
                'target' => $data['recovery_link']
            )
        );

        if (!empty($products) && is_array($products)) {
            $items = array();
            foreach ($products as $product) {
                $item = array(
                    '@type' => 'Product',
                    'name' => $product['name']
                );
                if (!empty($product['image'])) {
                    $item['image'] = $product['image'];
                }
                if (!empty($product['raw_price'])) {
                    $item['offers'] = array(
                        '@type' => 'Offer',
                        'priceCurrency' => $currency,
                        'price' => sprintf('%.2f', $product['raw_price'])
                    );
                }
                $items[] = $item;
            }
            $schema['about'] = $items;
        }

        return $schema;
    }

    /**
     * Build PostalAddress schema from address array
     */
    private function _buildPostalAddress($address)
    {
        $postal = array('@type' => 'PostalAddress');
        if (!empty($address['line1'])) {
            $postal['streetAddress'] = $address['line1'] . (!empty($address['line2']) ? ', ' . $address['line2'] : '');
        }
        if (!empty($address['town'])) {
            $postal['addressLocality'] = $address['town'];
        }
        if (!empty($address['state'])) {
            $postal['addressRegion'] = $address['state'];
        }
        if (!empty($address['postcode'])) {
            $postal['postalCode'] = $address['postcode'];
        }
        if (!empty($address['country'])) {
            $postal['addressCountry'] = $address['country'];
        }
        return $postal;
    }
}
