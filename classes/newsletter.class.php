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
 * Newsletter management
 *
 * @author Martin Purcell
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Newsletter {

	private $_mailer;

	private $_html;
	private $_text;

	public $_newsletter_id;

	protected static $_instance;

	##############################################

	public function __construct() {
		$this->_mailer = new Mailer();
	}

	/**
	 * Setup the instance (singleton)
	 *
	 * @return Newsletter
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	//=====[ Public ]=======================================

	/**
	 * Delete newsletter
	 *
	 * @param int $newsletter_id
	 * @return bool
	 */
	public function deleteNewsletter($newsletter_id = false) {
		if ($newsletter_id && is_numeric($newsletter_id)) {
			$GLOBALS['db']->delete('CubeCart_newsletter', array('newsletter_id' => (int)$newsletter_id));
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Generate validaton key for email verification
	 *
	 * @param string $key
	 * @return string
	 */
	private function generateValidation($email) {
		// Generate a validation key for the specified email address
		$string = sprintf('%s@%s', crypt($email, (string)time()), date('U.u'));
		return md5($string);
	}

	/**
	 * Save newsletter
	 *
	 * @param array $newsletter
	 * @return bool
	 */
	public function saveNewsletter($newsletter = false) {
		$result = false;
		if (!empty($newsletter) && is_array($newsletter)) {
			if (!empty($newsletter['newsletter_id']) && is_numeric($newsletter['newsletter_id'])) {
				$result = $GLOBALS['db']->update('CubeCart_newsletter', $newsletter, array('newsletter_id' => $newsletter['newsletter_id']));
				$this->_newsletter_id = $newsletter['newsletter_id'];
			} else {
				$result = $GLOBALS['db']->insert('CubeCart_newsletter', $newsletter);
				$this->_newsletter_id = $GLOBALS['db']->insertid();
			}
		}
		return $result;
	}

	/**
	 * Send newsletter
	 *
	 * @param int $newsletter_id
	 * @param int $cycle
	 * @param bool $test
	 * @return bool
	 */
	public function sendNewsletter($newsletter_id = false, $cycle = 1, $test = false) {
		// Load newsletter from database, and send
		if ($newsletter_id && is_numeric($newsletter_id)) {
			if (($contents = $GLOBALS['db']->select('CubeCart_newsletter', false, array('newsletter_id' => (int)$newsletter_id))) !== false) {
				$content = $contents[0];
				$this->_html = $content['content_html'];
				$this->_text = $content['content_text'];

				if (!empty($content['sender_name'])) {
					$this->_mailer->FromName = $content['sender_name'];
				}
				if (!empty($content['sender_email'])) {
					$this->_mailer->From = $content['sender_email'];
				}
				if ($test) {
					// Send test email only
					if (filter_var($test, FILTER_VALIDATE_EMAIL)) {
						$this->_mailer->sendEmail($test, $content, $contents[0]['template_id']);
						return true;
					}
				} else {
					ini_set('ignore_user_abort', true);
					// Send to all subscribers
					$limit = 20;
					$total = (int)$GLOBALS['db']->count('CubeCart_newsletter_subscriber', 'status', array('status' => '1'));
					if (($subscribers = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', array('email'), array('status' => '1'), false, $limit, $cycle)) !== false) {
						foreach ($subscribers as $subscriber) {
							if (filter_var($subscriber['email'], FILTER_VALIDATE_EMAIL)) {
								$content = array(
									'subject'  => $content['subject'],
									'content_html' => $content['content_html'],
									'content_text' => $content['content_text'],
								);
								$this->_mailer->sendEmail($subscriber['email'], $content, $contents[0]['template_id']);
							} else {
								// Flag for deletion
								$GLOBALS['db']->update('CubeCart_newsletter_subscriber', array('status' => '9'), array('email' => $subscriber['email']));
							}
						}
						$sent_to = $limit * $cycle;
						if ($total > $sent_to) {
							$data = array(
								'count'  => $sent_to,
								'total'  => $total,
								'percent' => ($sent_to/$total)*100,
							);
							return $data;
						} else {
							// Delete flagged subscribers
							$GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('status' => '9'));
							// Update newsletter record
							$GLOBALS['db']->update('CubeCart_newsletter', array('date_sent' => 'CURRENT_TIMESTAMP', 'status' => 1), array('newsletter_id' => (int)$newsletter_id));
							return true;
						}
					} else {
						return false;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Subscribe to newsletter
	 *
	 * @param string $email
	 * @return bool
	 */
	public function subscribe($email = false, $customer_id = null) {
		$skin_data = GUI::getInstance()->getSkinData('newsletter_recaptcha');
		$error = false;
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$GLOBALS['gui']->setError(sprintf($GLOBALS['language']->newsletter['email_invalid'],$email));
			$error = true;
		} elseif($skin_data['info']['newsletter_recaptcha'] && GUI::getInstance()->recaptchaRequired() && $GLOBALS['session']->get('error', 'recaptcha')) {
			$GLOBALS['gui']->setError($GLOBALS['session']->get('error', 'recaptcha'));
			$error = true;
		}

		if($error) {
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
			if((bool)$GLOBALS['config']->get('config', 'dbl_opt')) {
				$mailer = new Mailer();
				if (($content = $mailer->loadContent('newsletter.verify_email', $GLOBALS['language']->current())) !== false) {
					$GLOBALS['smarty']->assign('DATA', array('email' => $email, 'link' => CC_STORE_URL.'?_a=newsletter&do='.$record['validation']));
					$mailer->sendEmail($email, $content);
				}
				$GLOBALS['gui']->setNotify($GLOBALS['language']->newsletter['notify_subscribed'].' '.$GLOBALS['language']->newsletter['notify_subscribed_opt_in']);
			} else {
				$GLOBALS['gui']->setNotify($GLOBALS['language']->newsletter['notify_subscribed']);
			}

			foreach ($GLOBALS['hooks']->load('class.newsletter.subscribe') as $hook) include $hook;
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
	public function unsubscribe($email = false, $customer_id = false) {
		// Unsubscribe the user
		$removed = false;
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$removed = $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('email' => $email));
			foreach ($GLOBALS['hooks']->load('class.newsletter.unsubscribe') as $hook) include $hook;
		}
		if(ctype_digit($customer_id) && $customer_id > 0) {
			$removed = $GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('customer_id' => $customer_id));
		}
		if($removed) {
			$GLOBALS['gui']->setNotify($GLOBALS['language']->newsletter['notify_unsubscribed']);
		}
		return $removed;
	}

	/**
	 * Double opt in newsletter subscription
	 *
	 * @param string $validation
	 * @return bool
	 */
	public function doubleOptIn($validation = false) {
		// Verify the validation email
		if (!empty($validation)) {
			$validate = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', array('subscriber_id'), array('validation' => $validation));
			if ($validate) {
				$GLOBALS['db']->update('CubeCart_newsletter_subscriber', array('double_opt' => '1'), array('subscriber_id' => $validate[0]['subscriber_id']));
				return true;
			}
		}
		return false;
	}	
}