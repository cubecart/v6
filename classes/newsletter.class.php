<?php
/**
 * CubeCart v5
 * ========================================
 * CubeCart is a registered trade mark of Devellion Limited
 * Copyright Devellion Limited 2010. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  http://www.cubecart.com/v5-software-license
 * ========================================
 * CubeCart is NOT Open Source.
 * Unauthorized reproduction is not allowed.
 */

class Newsletter {

	private $_mailer;

	private $_html;
	private $_text;

	public $_newsletter_id;

	protected static $_instance;

	public function __construct() {
		$this->_mailer = Mailer::getInstance();
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


	public function subscribe($email = false) {
		// Subscribe, generate validation email, and send
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			// Valid email, but is it a dupe...
			if (!$dupes = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', array('email'), array('email' => strtolower($email)))) {

				$record = array(
					'status'  => true,
					'email'   => $email,
					'validation' => $this->generateValidation($email),
				);
				$GLOBALS['db']->insert('CubeCart_newsletter_subscriber', $record);

				return true;
			}
			return false;
		}
		$GLOBALS['gui']->setError($GLOBALS['language']->newsletter['email_invalid']);
		return false;
	}

	private function generateValidation($email) {
		// Generate a validation key for the specified email address
		$string = sprintf('%s@%s', crypt($email), date('U.u'));
		return md5($string);
	}

	public function verify($validation = false) {
		// Verify the validation email
		if (!empty($validation)) {
			$validate = $GLOBALS['db']->select('CubeCart_newsletter_subscriber', array('subscriber_id', 'email'), array('validation' => $validation));
			if ($validate) {
				$GLOBALS['db']->update('CubeCart_newsletter_subscriber', array('status' => '1'), array('subscriber_id' => $validate[0]['subscriber_id']));
				return true;
			}
		}
		return false;
	}

	public function unsubscribe($email = false) {
		// Unsubscribe the user
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$GLOBALS['db']->delete('CubeCart_newsletter_subscriber', array('email' => $email));
			return true;
		}
		return false;
	}

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

	public function deleteNewsletter($newsletter_id = false) {
		if ($newsletter_id && is_numeric($newsletter_id)) {
			$GLOBALS['db']->delete('CubeCart_newsletter', array('newsletter_id' => (int)$newsletter_id));
			return true;
		} else {
			return false;
		}
	}

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
}