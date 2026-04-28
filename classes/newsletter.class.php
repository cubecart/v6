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
 * Newsletter management
 *
 * @author Martin Purcell
 */
class Newsletter
{
    private $_mailer;

    private $_validated_domain = array();

    public $_newsletter_id;

    protected static $_instance;

    ##############################################

    public function __construct()
    {
        $this->_mailer = new Mailer();
    }

    /**
     * Setup the instance (singleton)
     *
     * @return Newsletter
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Clean up mailing list
     *
     * @return array('deleted' => int, 'unsubscribed' => int);
     */
    public function cleanList() {
        $rows = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', array('subscriber_id','email'));
        $return = array('deleted' => 0, 'unsubscribed' => 0);
        if($rows) {
            foreach($rows as $row) {
                $validation_result = $this->validateEmail($row['email']);
                if($validation_result==2) {
                    if($GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('subscriber_id' => $row['subscriber_id']))) {
                        $this->_subscriberLog($row['email'], 'Invalid email address deleted from mailing list.');
                        $return['deleted']++;
                    }
                } else if($validation_result==0) {
                    if($GLOBALS['db']->update('CubeCart_newsletter_subscriber', array('status' => 0), array('subscriber_id' => $row['subscriber_id']))) {
                        $this->_subscriberLog($row['email'], 'No valid MX record found. Status set to disabled.');
                        $return['unsubscribed']++;
                    }
                }
            }
        }
        return $return;
    }

    //=====[ Public ]=======================================

    /**
     * Delete newsletter
     *
     * @param int $newsletter_id
     * @return bool
     */
    public function deleteNewsletter($newsletter_id = false)
    {
        if ($newsletter_id && is_numeric($newsletter_id)) {
            $GLOBALS['db']->delete('CubeCart_newsletter', array('newsletter_id' => (int)$newsletter_id));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Empty newsletter
     *
     * @return bool
     */
    public function emptyList() {
        return $GLOBALS['db']->misc('TRUNCATE TABLE `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_newsletter_subscriber`;');
    }

    /**
     * Generate validaton key for email verification
     *
     * @param string $key
     * @return string
     */
    private function generateValidation($email)
    {
        // Generate a validation key for the specified email address
        return bin2hex(random_bytes(16));
    }

    /**
     * Save newsletter
     *
     * @param array $newsletter
     * @return bool
     */
    public function saveNewsletter($newsletter = false)
    {
        $result = false;
        if (!empty($newsletter) && is_array($newsletter)) {
            if (!empty($newsletter['newsletter_id']) && is_numeric($newsletter['newsletter_id'])) {
                $result = $GLOBALS['db']->update('CubeCart_newsletter', $newsletter, array('newsletter_id' => $newsletter['newsletter_id']));
                $this->_newsletter_id = $newsletter['newsletter_id'];
            } else {
                $this->_newsletter_id = $result = $GLOBALS['db']->insert('CubeCart_newsletter', $newsletter);
            }
        }
        return $result;
    }

    /**
     * Send a single test newsletter email to the supplied address. Bypasses subscriber
     * filters, hourly throttle and queue state — used from the admin compose form.
     *
     * @param int    $newsletter_id
     * @param string $email
     * @return bool
     */
    public function sendTest($newsletter_id, $email)
    {
        $newsletter_id = (int)$newsletter_id;
        if ($newsletter_id <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $rows = $GLOBALS['db']->select('CubeCart_newsletter', false, array('newsletter_id' => $newsletter_id));
        if (!$rows) {
            return false;
        }
        $content = $rows[0];

        if (!empty($content['sender_name'])) {
            $this->_mailer->FromName = $content['sender_name'];
        }
        if (!empty($content['sender_email'])) {
            $this->_mailer->From = $content['sender_email'];
        }
        $this->unsubscribeHeader($email);
        if ($this->_mailer->sendEmail($email, $content, $content['template_id'])) {
            $log = sprintf($GLOBALS['language']->newsletter['test_subscriber_log'], $content['subject'], $this->_mailer->getTemplateTitle());
            $this->_subscriberLog($email, $log);
            return true;
        }
        return false;
    }

    /**
     * Mark a newsletter as queued for sending. The cron task processNewsletters() will
     * pick it up and send in throttled batches. Captures total_subscribers at queue time
     * so progress display is stable even if subscribers join/leave mid-send.
     *
     * @param int $newsletter_id
     * @return bool true if queued, false if newsletter missing or no subscribers
     */
    public function queueNewsletter($newsletter_id)
    {
        $newsletter_id = (int)$newsletter_id;
        if ($newsletter_id <= 0) {
            return false;
        }
        $rows = $GLOBALS['db']->select('CubeCart_newsletter', false, array('newsletter_id' => $newsletter_id));
        if (!$rows) {
            return false;
        }
        $content = $rows[0];

        $where = array('status' => '1');
        if ($content['dbl_opt'] == 1) {
            $where['dbl_opt'] = 1;
        }
        $total = (int)$GLOBALS['db']->count('CubeCart_newsletter_subscriber', 'status', $where);
        if ($total === 0) {
            return false;
        }

        return (bool)$GLOBALS['db']->update('CubeCart_newsletter', array(
            'status'             => 2,
            'last_subscriber_id' => 0,
            'sent_count'         => 0,
            'total_subscribers'  => $total,
            'date_sent'          => 'NULL',
        ), array('newsletter_id' => $newsletter_id));
    }

    /**
     * Send the next batch for a queued newsletter. Called from the processNewsletters
     * cron task with $batch_size already capped to per-tick + hourly-remaining quota.
     *
     * Cursor-paginated by subscriber_id so it works at any subscriber count without
     * OFFSET cost. Logs each successful send to CubeCart_newsletter_send_log so the
     * cron can compute the rolling-hour throttle.
     *
     * @param int $newsletter_id
     * @param int $batch_size    Max emails this tick should send
     * @return int|false  Number actually sent (0 valid, false on hard failure)
     */
    public function processQueue($newsletter_id, $batch_size)
    {
        $newsletter_id = (int)$newsletter_id;
        $batch_size    = (int)$batch_size;
        if ($newsletter_id <= 0 || $batch_size <= 0) {
            return false;
        }
        // cache=false: this is the hot path that races with itself across cron ticks.
        // CubeCart's UPDATE doesn't invalidate cached SELECT results, so a cached row
        // would mean tick 2 reads tick 1's pre-update state and re-sends to the same
        // subscribers (cursor never advances). Always read fresh.
        $rows = $GLOBALS['db']->select('CubeCart_newsletter', false, array('newsletter_id' => $newsletter_id), false, 1, false, false);
        if (!$rows) {
            return false;
        }
        $content = $rows[0];
        if (!in_array((int)$content['status'], array(2, 3), true)) {
            return false;
        }

        if (!empty($content['sender_name'])) {
            $this->_mailer->FromName = $content['sender_name'];
        }
        if (!empty($content['sender_email'])) {
            $this->_mailer->From = $content['sender_email'];
        }

        // Move to "sending" on first tick that processes work
        if ((int)$content['status'] !== 3) {
            $GLOBALS['db']->update('CubeCart_newsletter', array('status' => 3), array('newsletter_id' => $newsletter_id));
        }

        // Cursor-paginated subscriber fetch — no OFFSET, scales to any list size.
        // cache=false on misc() for the same race-staleness reason as above.
        $pfx = $GLOBALS['config']->get('config', 'dbprefix');
        $where_sql = sprintf("`status`='1' AND `subscriber_id` > %d", (int)$content['last_subscriber_id']);
        if ($content['dbl_opt'] == 1) {
            $where_sql .= " AND `dbl_opt`='1'";
        }
        $sql = sprintf(
            "SELECT `subscriber_id`, `email` FROM `%sCubeCart_newsletter_subscriber` WHERE %s ORDER BY `subscriber_id` LIMIT %d",
            $pfx, $where_sql, $batch_size
        );
        $subscribers = $GLOBALS['db']->misc($sql, false);

        if (!$subscribers) {
            // Nothing left — finalise
            $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('status' => '9'));
            $GLOBALS['db']->update('CubeCart_newsletter', array(
                'status'    => 1,
                'date_sent' => 'CURRENT_TIMESTAMP',
            ), array('newsletter_id' => $newsletter_id));
            return 0;
        }

        $sent    = 0;
        $last_id = (int)$content['last_subscriber_id'];
        foreach ($subscribers as $subscriber) {
            $sub_id = (int)$subscriber['subscriber_id'];
            if ($sub_id > $last_id) {
                $last_id = $sub_id;
            }
            if (filter_var($subscriber['email'], FILTER_VALIDATE_EMAIL)) {
                $send_content = array(
                    'subject'      => $content['subject'],
                    'content_html' => $content['content_html'],
                );
                $this->unsubscribeHeader($subscriber['email']);
                if ($this->_mailer->sendEmail($subscriber['email'], $send_content, $content['template_id'])) {
                    $log = sprintf($GLOBALS['language']->newsletter['subscriber_log'], $content['subject'], $this->_mailer->getTemplateTitle());
                    $this->_subscriberLog($subscriber['email'], $log);
                    // sent_at relies on the column's DEFAULT CURRENT_TIMESTAMP — passing
                    // 'CURRENT_TIMESTAMP' here would be quoted as a literal string by
                    // insert() (which, unlike update(), doesn't honour _allowed_exceptions).
                    $GLOBALS['db']->insert('CubeCart_newsletter_send_log', array(
                        'newsletter_id' => $newsletter_id,
                    ));
                    $sent++;
                }
            } else {
                // Flag for deletion at the end of the run
                $GLOBALS['db']->update('CubeCart_newsletter_subscriber', array('status' => '9'), array('email' => $subscriber['email']));
            }
        }

        $GLOBALS['db']->update('CubeCart_newsletter', array(
            'last_subscriber_id' => $last_id,
            'sent_count'         => (int)$content['sent_count'] + $sent,
        ), array('newsletter_id' => $newsletter_id));

        // Short batch means we ran out — finalise on next tick (no extra round-trip needed
        // since the empty-subscribers branch above will handle it, but we shortcut here too)
        if (count($subscribers) < $batch_size) {
            $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('status' => '9'));
            $GLOBALS['db']->update('CubeCart_newsletter', array(
                'status'    => 1,
                'date_sent' => 'CURRENT_TIMESTAMP',
            ), array('newsletter_id' => $newsletter_id));
        }

        return $sent;
    }

    /**
     * Pause an in-flight newsletter. Cursor + sent_count are preserved so resumeNewsletter()
     * can pick up exactly where it left off. Only legal from status=3 (sending) — pausing a
     * still-queued (2) newsletter is a no-op since nothing has gone out yet.
     *
     * @param int $newsletter_id
     * @return bool
     */
    public function pauseNewsletter($newsletter_id)
    {
        $newsletter_id = (int)$newsletter_id;
        if ($newsletter_id <= 0) {
            return false;
        }
        return (bool)$GLOBALS['db']->update(
            'CubeCart_newsletter',
            array('status' => 5),
            "`newsletter_id` = {$newsletter_id} AND `status` = 3"
        );
    }

    /**
     * Resume a paused newsletter — flips status back to 3 so the cron picks it up
     * on its next tick.
     *
     * @param int $newsletter_id
     * @return bool
     */
    public function resumeNewsletter($newsletter_id)
    {
        $newsletter_id = (int)$newsletter_id;
        if ($newsletter_id <= 0) {
            return false;
        }
        return (bool)$GLOBALS['db']->update(
            'CubeCart_newsletter',
            array('status' => 3),
            "`newsletter_id` = {$newsletter_id} AND `status` = 5"
        );
    }

    /**
     * Cancel — terminal state. Cursor + sent_count are preserved so the list keeps the
     * audit trail ("Cancelled — 47 of 312 sent"). Legal from queued (2), sending (3),
     * or paused (5). The cron's `status IN (2,3)` filter naturally stops touching it.
     *
     * @param int $newsletter_id
     * @return bool
     */
    public function cancelNewsletter($newsletter_id)
    {
        $newsletter_id = (int)$newsletter_id;
        if ($newsletter_id <= 0) {
            return false;
        }
        return (bool)$GLOBALS['db']->update(
            'CubeCart_newsletter',
            array('status' => 4),
            "`newsletter_id` = {$newsletter_id} AND `status` IN (2, 3, 5)"
        );
    }

    /**
     * Subscribe to newsletter
     *
     * @param string $email
     * @return bool
     */
    public function subscribe($email = false, $customer_id = null)
    {
        if($GLOBALS['config']->get('config', 'newsletter_status')==='0') {
            return false;
        }
        $checkout = in_array($_GET['_a'], array('confirm','checkout','basket')) ? true : false;
        if ($checkout && $GLOBALS['config']->get('config', 'dbl_opt')=='1' && $GLOBALS['session']->has('dbl_opted') && $GLOBALS['session']->get('dbl_opted')==$email) {
            return false;
        }
        $skin_data = GUI::getInstance()->getSkinData();
        $error = false;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $GLOBALS['gui']->setError(sprintf($GLOBALS['language']->newsletter['email_invalid'], $email));
            $error = true;
        } else {
            // Avoid recursion: Newsletter::subscribe() can be called during User construction,
            // so DO NOT call User::getInstance() here.
            $logged_in = !empty($GLOBALS['session']->session_data['customer_id'])
                && (int)$GLOBALS['session']->session_data['customer_id'] > 0;

            if (!$logged_in
                && !empty($skin_data['info']['newsletter_recaptcha'])
                && GUI::getInstance()->recaptchaRequired()
                && $GLOBALS['session']->get('error', 'recaptcha')
            ) {
                $GLOBALS['gui']->setError($GLOBALS['session']->get('error', 'recaptcha'));
                $error = true;
            }
        }

        if ($error) {
            httpredir(currentPage());
        } else {
            $email = strtolower($email);
            $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('email' => $email));

            $record = array(
                'status'  => true,
                'email'   => $email,
                'customer_id'   => $customer_id,
                'validation' => $this->generateValidation($email),
                'ip_address' => get_ip_address(),
                'date' => date('c')
            );
            $GLOBALS['db']->insert('CubeCart_newsletter_subscriber', $record);
        
            if ((bool)$GLOBALS['config']->get('config', 'dbl_opt')) {
                $mailer = new Mailer();
                if (($content = $mailer->loadContent('newsletter.verify_email', $GLOBALS['language']->current())) !== false) {
                    $GLOBALS['smarty']->assign('DATA', array('email' => $email, 'link' => CC_STORE_URL.'?_a=newsletter&do='.$record['validation']));
                    $mailer->sendEmail($email, $content);
                    $GLOBALS['session']->set('dbl_opted', $email);
                }
                $this->_subscriberLog($email, 'Subscribed pending double opt-in verification');
                if (!$checkout) {
                    $GLOBALS['gui']->setNotify($GLOBALS['language']->newsletter['notify_subscribed_opt_in']);
                }
            } else {
                $this->_subscriberLog($email, 'Subscribed without double opt-in.');
                if (!$checkout) {
                    $GLOBALS['gui']->setNotify($GLOBALS['language']->newsletter['notify_subscribed']);
                }
            }

            foreach ($GLOBALS['hooks']->load('class.newsletter.subscribe') as $hook) {
                include $hook;
            }
            return true;
        }
        return false;
    }

    /**
     * Unsubscribe from newsletter
     *
     * @param string $email
     * @return bool
     */
    public function unsubscribe($email = false, $customer_id = false)
    {
        if($GLOBALS['config']->get('config', 'newsletter_status')==='0') {
            return false;
        }
        // Unsubscribe the user
        $removed = false;
        $remove_token = null;
        if (ctype_digit($customer_id) && $customer_id > 0) {
            $removed = $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('customer_id' => $customer_id));
        } else if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if((defined('ADMIN_CP') && ADMIN_CP === true) || (isset($_GET['rt']) && !empty($_GET['rt']))) {
                $where = isset($_GET['rt']) ? array('remove_token' => $_GET['rt']) : array('email' => $email);
                $removed = $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', $where);
            } else {
                $remove_token = md5(uniqid((string)time(), true));
                $remove_possible = $GLOBALS['db']->update('CubeCart_newsletter_subscriber', array('remove_token' => $remove_token), array('email' => $email));
            }
            foreach ($GLOBALS['hooks']->load('class.newsletter.unsubscribe') as $hook) {
                include $hook;
            }
        }
        if(!empty($remove_token)) {
            $this->_subscriberLog($email, 'Removal requested. Pending confirmation.');
            $mailer = new Mailer();
            if ($remove_possible && ($content = $mailer->loadContent('newsletter.remove_request', $GLOBALS['language']->current())) !== false) {
                $GLOBALS['smarty']->assign('DATA', array('email' => $email, 'link' => CC_STORE_URL."/index.php?_a=unsubscribe&unsubscribe=".urlencode($email)."&rt=".$remove_token));
                $mailer->sendEmail($email, $content); 
                $this->_subscriberLog($email, 'Removal requested email sent.');
            }
            $GLOBALS['gui']->setNotify($GLOBALS['language']->newsletter['notify_remove_request']);
            return true;
        }
        if ($removed) {
            $this->_subscriberLog($email, 'Removed from mailing list');
            $GLOBALS['gui']->setNotify($GLOBALS['language']->newsletter['notify_unsubscribed']);
        } else {
            $GLOBALS['gui']->setError($GLOBALS['language']->newsletter['notify_not_subscribed']);
        }
        return $removed;
    }

    /**
     * Set unsubscribe headers
     *
     * @param string $email
     */
    public function unsubscribeHeader($email)
    {
        $this->_mailer->clearCustomHeaders();
        $this->_mailer->addCustomHeader("List-Unsubscribe","<".$GLOBALS['storeURL']."/index.php?_a=unsubscribe&unsubscribe=".urlencode($email).">");
        $this->_mailer->addCustomHeader("List-Unsubscribe-Post","List-Unsubscribe=One-Click");
    }

    /**
     * Double opt in newsletter subscription
     *
     * @param string $validation
     * @return bool
     */
    public function doubleOptIn($validation = false)
    {
        // Verify the validation email
        if (!empty($validation)) {
            $validate = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', array('subscriber_id', 'email'), array('validation' => $validation), false, 1, false, false);
            if ($validate) {
                $this->_subscriberLog($validate[0]['email'], 'Double opt-in verified');
                $GLOBALS['db']->update('CubeCart_newsletter_subscriber', array('dbl_opt' => '1', 'date' => date('c'), 'ip_address' => get_ip_address()), array('subscriber_id' => $validate[0]['subscriber_id']));
                foreach ($GLOBALS['hooks']->load('class.newsletter.validated') as $hook) {
                    include $hook;
                }
                return true;
            }
        }
        return false;
    }

    /** 
     * Validate email address and MX record
     *
     * @param string $email
     * @return 0, 1, 2
     */
    public function validateEmail($email) {
        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
            list($user, $domain) = explode('@', $email);
            if(!isset($this->_validated_domain[$domain])) {
                return $this->_validated_domain[$domain] = (int)checkdnsrr($domain, 'MX');
            }
            return $this->_validated_domain[$domain];
        }
        return 2;
    }

    /**
     * Log subscription status
     *
     * @param string $email
     * @param string $log
     * @return bool
     */
    private function _subscriberLog($email, $log)
    {
        if (!empty($email) && !empty($log)) {
            return $GLOBALS['db']->insert('CubeCart_newsletter_subscriber_log', array('email' => htmlentities((string)$email, ENT_QUOTES, 'UTF-8'), 'log' => $log, 'ip_address' => get_ip_address()));
        }
        return false;
    }
}
