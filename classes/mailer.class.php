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
 */

class Mailer extends PHPMailer\PHPMailer\PHPMailer
{
    private $_html;
    private $_text;
    private $_template_title;
    private $_email_content_id;
    private $_content_type = '';
    private $_import_new = false;
    private $_method = '';
    private $_async_queue = array();
    private $_shutdown_registered = false;

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
        $language = preg_match(Language::LANG_REGEX, (string)$language) ? $language : $GLOBALS['language']->current();
        $language = ($language == 'en') ? 'en-GB' : $language;

        if (!empty($content_type)) {
            $where = array('content_type' => (string)$content_type, 'language' => $language);
            if ($panic) { // Default language doesn't have this content type!
                unset($where['language']);
            }
            if (($contents =  $GLOBALS['db']->select('CubeCart_email_content', false, $where, false, 1)) !== false) {
                if (isset($contents[0]['enabled']) && !$contents[0]['enabled']) {
                    return false;
                }
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
    public function sendEmail($email = false, $contents = array(), $template_id = false, $pre_parsed = false)
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
        if (!$pre_parsed) {
            $contents = $this->_parseContents($contents);
        }
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

            // Open tracking: stamp a 1x1 pixel with a unique token onto the HTML body
            // so we can record the open in CubeCart_email_log when the recipient
            // (or their mail client's prefetcher) loads it. The text alternative is
            // never tracked and the toggle can be disabled per-store.
            $tracking_token = null;
            if ($GLOBALS['config']->get('config', 'email_track_opens') && !empty($this->_html)) {
                $tracking_token = bin2hex(random_bytes(16));
                $pixel_url = rtrim($GLOBALS['storeURL'], '/').'/track/open.php?t='.$tracking_token;
                $pixel_tag = '<img src="'.htmlspecialchars($pixel_url, ENT_QUOTES).'" width="1" height="1" alt="" border="0" style="display:none;max-height:0;visibility:hidden;overflow:hidden;mso-hide:all;">';
                if (stripos($this->_html, '</body>') !== false) {
                    $this->_html = str_ireplace('</body>', $pixel_tag.'</body>', $this->_html);
                } else {
                    $this->_html .= $pixel_tag;
                }
            }

            $this->Body    = $this->_html;
            $this->AltBody = $this->_text;

            if (isset($contents['email'])) {
                $this->addReplyTo($contents['email'], (isset($contents['from'])) ? $contents['from'] : '');
                $from = $contents['email'];
            } else {
                $from = $GLOBALS['config']->get('config', 'email_address');
            }
            $this->Sender = $GLOBALS['config']->get('config', 'email_address');

            $result = false;
            $disable_send = false;

            foreach ($GLOBALS['hooks']->load('class.mailer.presend') as $hook) {
                include $hook;
            }

            // Send email
            if(!$disable_send) {
                $result = $this->Send();
            }

            // Log email
            $email_data = array(
                'subject' => $this->Subject,
                'content_html' => $this->_html,
                'content_text' => $this->_text,
                'to' => $email,
                'from' => $from,
                'result' => $result,
                'email_method' => $GLOBALS['config']->get('config', 'email_method') ?: 'phpmail',
                'email_content_id' => $this->_email_content_id,
                'fail_reason' => !empty($this->ErrorInfo) ? htmlentities($this->ErrorInfo, ENT_QUOTES) : '',
                'tracking_token' => $tracking_token,
            );
            $log_days = $GLOBALS['config']->get('config', 'r_email');
            if (ctype_digit((string)$log_days) &&  $log_days > 0) {
                $GLOBALS['db']->insert('CubeCart_email_log', $email_data);
                if (executionChance(2)) { // 2% probability
                    $GLOBALS['db']->delete('CubeCart_email_log', 'date < DATE_SUB(NOW(), INTERVAL '.$log_days.' DAY)', 500);
                    // After the row delete, sweep any attachment files no longer referenced.
                    self::pruneOrphanedAttachments();
                }
            } elseif (empty($log_days) || !$log_days) {
                $GLOBALS['db']->insert('CubeCart_email_log', $email_data);
            }
            return $result;
        }

        return false;
    }

    /**
     * Queue an email to be sent after the HTTP response has been flushed to the
     * client. Use for transactional email triggered during a customer-facing request
     * (order confirmations, password resets, status changes) so the SMTP latency
     * doesn't sit on the user's checkout / login response time.
     *
     * Returns true to indicate the message was accepted into the queue. The actual
     * send happens during shutdown; the result lands in CubeCart_email_log just like
     * a synchronous send. Failures are not visible to the original caller.
     *
     * Falls back to synchronous behaviour on CLI / unsupported SAPIs (mod_php
     * without an FPM equivalent) - the response still goes out, just not as early.
     *
     * @param string $email
     * @param array $contents
     * @param int $template_id
     * @return bool
     */
    public function sendEmailAsync($email = false, $contents = array(), $template_id = false)
    {
        // Parse Smarty macros now, while order DATA/BILLING/etc. are still assigned.
        // If we defer to shutdown, normal page rendering will have reassigned $DATA
        // (cubecart.class.php assigns it for addresses, POST data, etc.) and the
        // queued email's {$DATA.*} tags would resolve to whatever was last set.
        $contents = $this->_parseContents($contents);
        $this->_async_queue[] = array(
            'email'       => $email,
            'contents'    => $contents,
            'template_id' => $template_id,
            'from'        => $this->From,
            'from_name'   => $this->FromName,
            'pre_parsed'  => true,
        );
        if (!$this->_shutdown_registered) {
            $this->_shutdown_registered = true;
            @ignore_user_abort(true);
            register_shutdown_function(array($this, '_drainAsyncQueue'));
        }
        return true;
    }

    /**
     * Shutdown handler. Flushes the response so the user sees the page immediately,
     * then drains the async queue by calling sendEmail() for each entry. The
     * session is closed first to release its lock so the customer's next request
     * isn't blocked waiting for the SMTP send to finish.
     *
     * Public so register_shutdown_function() can reach it; not part of the public API.
     */
    public function _drainAsyncQueue()
    {
        if (empty($this->_async_queue)) {
            return;
        }

        // Release the response to the client before doing slow SMTP work.
        // Order matters: close session lock, drain output buffers, then signal FPM/LiteSpeed.
        if (function_exists('session_write_close')) {
            @session_write_close();
        }
        while (ob_get_level() > 0) {
            // ob_end_flush() returns false on un-flushable handlers (e.g. zlib.output_compression
            // when the response has already been sent). Without this guard the loop spins
            // until max_execution_time and SIGKILL — drops queued mail silently. Reported on
            // LiteSpeed/LSPHP where the placeOrder request has the gzip buffer active.
            if (@ob_end_flush() === false) {
                break;
            }
        }
        @flush();
        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        } elseif (function_exists('litespeed_finish_request')) {
            @litespeed_finish_request();
        }

        $queue = $this->_async_queue;
        $this->_async_queue = array();
        foreach ($queue as $entry) {
            try {
                if (!empty($entry['from'])) {
                    $this->From = $entry['from'];
                }
                if (!empty($entry['from_name'])) {
                    $this->FromName = $entry['from_name'];
                }
                $this->sendEmail($entry['email'], $entry['contents'], $entry['template_id'], !empty($entry['pre_parsed']));
            } catch (\Throwable $e) {
                // The user has already received their response - swallow and rely on
                // CubeCart_email_log for the failure record. Trigger a notice so the
                // hosting error log shows it for debugging.
                @trigger_error('Async email send failed: '.$e->getMessage(), E_USER_NOTICE);
            }
        }
    }

    /**
     * Sweep CC_FILES_DIR/attachments/ for files no longer referenced by any
     * email_log row and delete them. Called when the log is truncated or pruned
     * by retention so the on-disk files don't outlive their log entries.
     *
     * Skips dotfiles and the standard directory-listing guards (index.html,
     * index.php, .htaccess) so they survive across sweeps.
     *
     * @return int Number of files removed.
     */
    public static function pruneOrphanedAttachments()
    {
        $dir = CC_FILES_DIR.'attachments/';
        if (!is_dir($dir)) {
            return 0;
        }

        // Build a set of every filename still referenced by any remaining log row.
        $keep = array();
        $rows = $GLOBALS['db']->select('CubeCart_email_log', array('attachment'), "`attachment` IS NOT NULL AND `attachment` <> ''");
        if (is_array($rows)) {
            foreach ($rows as $r) {
                $decoded = json_decode($r['attachment'] ?? '', true);
                if (is_array($decoded)) {
                    foreach ($decoded as $f) {
                        if (is_string($f) && $f !== '') {
                            $keep[$f] = true;
                        }
                    }
                }
            }
        }

        $deleted = 0;
        $entries = @scandir($dir);
        if ($entries === false) {
            return 0;
        }
        $reserved = array('index.html' => true, 'index.php' => true, '.htaccess' => true);
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || $entry === '' || $entry[0] === '.' || isset($reserved[$entry])) {
                continue;
            }
            $path = $dir.$entry;
            if (!is_file($path)) {
                continue;
            }
            if (!isset($keep[$entry]) && @unlink($path)) {
                $deleted++;
            }
        }
        return $deleted;
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
            '@context' => 'https://schema.org',
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
            '@context' => 'https://schema.org',
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
            '@context' => 'https://schema.org',
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
