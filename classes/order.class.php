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
 * Order controller
 *
 * @author Technocrat
 * @author Al Brookbanks
 * @since 5.0.0
 */
class Order {

	private $_order_id;
	private $_basket;

	## Compatibility mode
	private $_order_inventory;
	private $_order_summary;

	private $_email_enabled   = true;
	private $_email_admin_enabled = true;

	private $_skip_order_complete_email = false;

	private static $_instance;

	## Order status constants
	const ORDER_PENDING  = 1;
	const ORDER_PROCESS  = 2;
	const ORDER_COMPLETE = 3;
	const ORDER_DECLINED = 4;
	const ORDER_FAILED  = 5; # Fraudulent
	const ORDER_CANCELLED = 6;

	## Payment Constants
	const PAYMENT_PENDING = 1;
	const PAYMENT_PROCESS = 2;
	const PAYMENT_SUCCESS = 3;
	const PAYMENT_DECLINE = 4;
	const PAYMENT_FAILED = 5;
	const PAYMENT_CANCEL = 6;

	##############################################

	public function __construct() {
		// Define some order-status constants
		// These are deprecated, in favour of the static constants, and can probably be removed
		if (!defined('ORDER_PENDING')) define('ORDER_PENDING', self::ORDER_PENDING);
		if (!defined('ORDER_PROCESS')) define('ORDER_PROCESS', self::ORDER_PROCESS);
		if (!defined('ORDER_COMPLETE')) define('ORDER_COMPLETE', self::ORDER_COMPLETE);
		if (!defined('ORDER_DECLINED')) define('ORDER_DECLINED', self::ORDER_DECLINED);
		if (!defined('ORDER_FAILED')) define('ORDER_FAILED', self::ORDER_FAILED);
		if (!defined('ORDER_CANCELLED')) define('ORDER_CANCELLED', self::ORDER_CANCELLED);

		// Load the cart class
		$this->_basket = &$GLOBALS['cart']->basket;
		if (isset($this->_basket['cart_order_id'])) {
			$this->_order_id = $this->_basket['cart_order_id'];
		}
		// Expire old orders
		$this->_tidyOrders();
	}

	/**
	 * Setup the instance (singleton)
	 *
	 * @return Order
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	//=====[ Public ]=======================================

	/**
	 * Add order note
	 *
	 * @param string $order_id
	 * @param string $note
	 * @return bool
	 */
	public function addNote($order_id = null, $note = null) {
		if (!empty($order_id) && !empty($note)) {
			$record = array(
				'cart_order_id' => $order_id,
				'time'   => date('Y-m-d H:i:s'),
				'content'  => $note
			);
			// Check for duplicates...
			if ($GLOBALS['db']->select('CubeCart_order_notes', 'note_id', array('cart_order_id' => $order_id, 'content' => $note))) {
				return false;
			}

			return (bool)$GLOBALS['db']->insert('CubeCart_order_notes', $record);
		}
		return false;
	}

	/**
	 * Assign order details to smarty template
	 *
	 * @param array $values
	 * @param any $admin
	 */
	public function assignOrderDetails($values = null, $admin = null) {

		$this->_email_details = (is_null($values)) ? $this->_email_details : $values;
		$order_id = $this->_email_details['order_summary']['cart_order_id'];
		$this->_email_details['order_summary']['link'] = (is_null($admin)) ? $GLOBALS['storeURL'].'/index.php?_a=vieworder&cart_order_id='.$order_id : $GLOBALS['storeURL'].'/'.$GLOBALS['config']->get('config', 'adminFile').'?_g=orders&action=edit&order_id='.$order_id;

		foreach ($GLOBALS['hooks']->load('class.order.assign_order_details') as $hook) include $hook; // custom made details

		$GLOBALS['smarty']->assign('DATA', $this->_email_details['order_summary']);
		$GLOBALS['smarty']->assign('BILLING', $this->_email_details['billing'] );
		$GLOBALS['smarty']->assign('SHIPPING', $this->_email_details['shipping']);
		$GLOBALS['smarty']->assign('TAXES', $this->_email_details['taxes']);
		$GLOBALS['smarty']->assign('PRODUCTS', $this->_email_details['products']);
	}

	/**
	 * Create digital download for order ready to be sent later (Public)
	 *
	 * @param int $product_id
	 * @param int $order_inv_id
	 * @param int $customer_id
	 * @return bool
	 */
	public function createDownload($product_id, $order_inv_id, $customer_id = '') {
		if(empty($product_id) || empty($order_inv_id)) return false;
		$this->_order_summary['customer_id'] = $customer_id;
		return $this->_createDownload($product_id, $order_inv_id);
	}

	/**
	 * Create the order number
	 *
	 * @param bool $return
	 * @param bool $set_basket
	 * @return string/true
	 */
	public function createOrderId($return = false, $set_basket = true) {
		// Self explainitory really...
		$this->_order_id = date('ymd-His-').rand(1000, 9999);

		if ($set_basket) {
			$this->_basket['cart_order_id'] = $this->_order_id;
			/* fix for admin generated orders */
			if (method_exists($GLOBALS['cart'], 'save')) {
				$GLOBALS['cart']->save();
			}
		}
		return ($return) ? $this->_order_id : true;
	}

	/**
	 * Delete encrypted credit card
	 *
	 * @param string $cart_order_id
	 * @return bool
	 */
	public function deleteCard($cart_order_id) {
		return (bool)$GLOBALS['db']->update('CubeCart_order_summary', array('offline_capture' => null), array('cart_order_id' => $cart_order_id));
	}

	/**
	 * Delete order
	 *
	 * @param string $cart_order_id
	 * @return bool
	 */
	public function deleteOrder($order_id) {
		// Delete the order from the system
		$deleted = false;
		if (!empty($order_id)) {
			$where = array('cart_order_id' => $order_id);
			if ($GLOBALS['db']->delete('CubeCart_order_summary', $where)) {
				$deleted = true;
			}
			// No checking required, because they would be useless without order_sum anyway
			$GLOBALS['db']->delete('CubeCart_order_inventory', $where);
			$GLOBALS['db']->delete('CubeCart_downloads', $where);
			$GLOBALS['db']->delete('CubeCart_order_tax', $where);
			$GLOBALS['db']->delete('CubeCart_order_notes', $where);
			$GLOBALS['db']->delete('CubeCart_order_history', $where);
			foreach ($GLOBALS['hooks']->load('class.order.delete') as $hook) include $hook;
		}

		return $deleted;
	}

	/**
	 * Disable admin email notification
	 */
	public function disableAdminEmail() {
		$this->_email_admin_enabled = false;
	}

	/**
	 * Get order details (summary & line items)
	 *
	 * @param string $order_id
	 * @return array
	 */
	public function getOrderDetails($order_id) {

		$order_summary = $this->getSummary($order_id);

		// Format prices etc for order emails...
		$order_summary['subtotal']  = Tax::getInstance()->priceFormat($order_summary['subtotal'], true);
		$order_summary['total']  = Tax::getInstance()->priceFormat($order_summary['total'], true);
		$order_summary['discount']  = Tax::getInstance()->priceFormat($order_summary['discount'], true);
		$order_summary['shipping'] = Tax::getInstance()->priceFormat($order_summary['shipping'], true);
		// Get taxes
		$order_taxes = $GLOBALS['db']->select('CubeCart_order_tax', array('tax_id', 'amount'), array('cart_order_id' => $order_id));

		// Put in items
		$vars = array();
		foreach ($this->_order_inventory as $item) {
			if ($item['product_id']>0) {
				$product    = array_merge($GLOBALS['catalogue']->getProductData($item['product_id']), $item);
				$product['item_price'] = Tax::getInstance()->priceFormat($product['price']);
				$product['price']   = Tax::getInstance()->priceFormat($product['price']*$product['quantity']);
				if (!empty($product['product_options']))  $product['product_options'] = implode(' ', unserialize($item['product_options']));
				$vars['products'][] = $product;
			} else {
				$item['price'] = Tax::getInstance()->priceFormat($item['price']);
				$vars['products'][] = $item;
			}
		}

		// Put tax in
		if ($order_taxes) {
			foreach ($order_taxes as $order_tax) {
				$tax_data = Tax::getInstance()->fetchTaxDetails($order_tax['tax_id']);
				$tax['tax_name']  = $tax_data['name'];
				//$tax['tax_percent'] = sprintf('%.3F',$tax_data['tax_percent']);
				$tax['tax_percent'] = floatval($tax_data['tax_percent']); // get rid of zeroes
				$tax['tax_amount']  = Tax::getInstance()->priceFormat($order_tax['amount']);
				$vars['taxes'][] = $tax;
			}
		}

		$billing = array (
			'first_name'  => $order_summary['first_name'],
			'last_name'  => $order_summary['last_name'],
			'company_name'  => $order_summary['company_name'],
			'line1'   => $order_summary['line1'],
			'line2'   => $order_summary['line2'],
			'town'    => $order_summary['town'],
			'state'   => getStateFormat($order_summary['state']),
			'postcode'   => $order_summary['postcode'],
			'country'   => getCountryFormat($order_summary['country']),
			'phone'   => $order_summary['phone'],
			'email'   => $order_summary['email']
		);
		$shipping = array (
			'first_name'  => $order_summary['first_name_d'],
			'last_name'  => $order_summary['last_name_d'],
			'company_name'  => $order_summary['company_name_d'],
			'line1'   => $order_summary['line1_d'],
			'line2'   => $order_summary['line2_d'],
			'town'    => $order_summary['town_d'],
			'state'   => getStateFormat($order_summary['state_d']),
			'postcode'   => $order_summary['postcode_d'],
			'country'   => getCountryFormat($order_summary['country_d'])
		);

		// Format data
		$order_summary['order_date'] = formatTime($order_summary['order_date'],false,true);
		$order_summary['ship_date']  = ((int)(str_replace('-', '', $order_summary['ship_date'])) > 0) ? formatDispatchDate($order_summary['ship_date']) : "";
		$order_summary['gateway']    = str_replace('_', ' ', $order_summary['gateway']);


		$values['order_summary'] = $order_summary;
		$values['billing']       = $billing;
		$values['shipping']      = $shipping;
		$values['taxes']         = $vars['taxes'];
		$values['products']      = $vars['products'];

		foreach ($GLOBALS['hooks']->load('class.order.get_order_details') as $hook) include $hook;

		$this->_email_details    = $values;
		return $this->_email_details;

	}

	/**
	 * Get the order summary
	 *
	 * @param string $cart_order_id
	 * @return array/false
	 */
	public function getSummary($order_id = null) {
		// Returns the order summary data
		$this->_order_id = (is_null($order_id)) ? $this->_order_id : $order_id;
		$order = $GLOBALS['db']->select('CubeCart_order_summary', false, array('cart_order_id' => $order_id));

		if ($order) {
			$this->_order_summary = $order[0];
			return $this->_order_summary;
		}

		return false;
	}

	/**
	 * Log payment transaction
	 *
	 * @param array $log
	 * @param bool $force_log
	 * @return bool
	 */
	public function logTransaction($log, $force_log = false) {
		// Log the transaction data returned from the payment gateways
		if (is_array($log) && !empty($log)) {
			$log['notes'] = (isset($log['notes'])) ? $log['notes'] : '';
			$record = array(
				'time'   => time(),
				'order_id'  => isset($log['order_id']) ? $log['order_id'] : $this->_order_id,
				'gateway'  => isset($log['gateway']) ? $log['gateway'] : '',

				'trans_id'  => isset($log['trans_id']) ? $log['trans_id'] : '',
				'amount'  => isset($log['amount']) ? $log['amount'] : $this->_basket['total'],
				'status'  => isset($log['status']) ? $log['status'] : '',
				'customer_id' => isset($log['customer_id']) ? $log['customer_id'] : '',
				'extra'   => isset($log['extra']) ? $log['extra'] : '',

				'notes'   => is_array($log['notes']) ? implode(PHP_EOL, $log['notes']) : $log['notes'],
			);
			$record['amount'] = preg_replace('/[^0-9.]*/', '', $record['amount']);
			if ($force_log || !empty($record['order_id']) && !empty($record['gateway'])) {
				$GLOBALS['db']->insert('CubeCart_transactions', $record);
				return true;
			}
		}
		return false;
	}

	/**
	 * Change the order status
	 *
	 * @param int $status_id
	 * @param string $order_id
	 * @param bool $force
	 * @return bool
	 */
	public function orderStatus($status_id, $order_id, $force = false) {
		global $glob;

		// Update order status, manage stock, and email if required
		if (!empty($status_id) && !empty($order_id)) {
			$currentStatus = $GLOBALS['db']->select('CubeCart_order_summary', array('status'), array('cart_order_id' => $order_id), false, false, false, false);
			
			if ((int)$currentStatus[0]['status'] == 0) return false; // no order record
			
			// Insert order status if it's changed
			if ((int)$status_id !== (int)$currentStatus[0]['status'] || $force) {
				$this->_addHistory($order_id, $status_id);
			} else { // Don't send out emails already sent!
				$this->_email_enabled = false;
				return false;
			}

			// Retrieve order details
			$this->getSummary($order_id);
			$this->_getInventory($order_id, true);

			foreach ($GLOBALS['hooks']->load('class.order.order_status') as $hook) include $hook;
			$mailer = Mailer::getInstance();
			$order_summary = $this->_order_summary;

			$this->getOrderDetails($order_id);

			switch ($status_id) {

			case self::ORDER_PENDING;
				// Send email to store admins (yes 1 is a string)
				if ($GLOBALS['config']->get('config', 'admin_notify_status')=="1" && $this->_email_admin_enabled && $admin_notify = $this->_notifyAdmins()) {

					$admin_mailer = Mailer::getInstance();

					$message_id = md5('admin.order_received'.$status_id.$order_id);

					if (!$GLOBALS['session']->has($message_id, 'email') && ($content = $admin_mailer->loadContent('admin.order_received')) !== false) {
						$this->assignOrderDetails(null, true);
						$admin_mailer->sendEmail($admin_notify, $content);
						$GLOBALS['session']->set($message_id, true, 'email');
						unset($content);
					}
				}
				
				break;

			case self::ORDER_PROCESS;
				$complete = true;
				$this->_getInventory($order_id, true);
				// Look for digital items
				foreach ($this->_order_inventory as $item) {
					if ($item['digital']) {
						continue;
					}
					$complete = false;
					break;
				}
				// Compose the Order Confirmation email to the customer
				if ($this->_email_enabled && ($content = $mailer->loadContent('cart.order_confirmation', $order_summary['lang'])) !== false) {
					$this->assignOrderDetails();
					$mailer->sendEmail($this->_order_summary['email'], $content);
					unset($content);
				}

				// Send email to store admins (2 IS a string) :)
				if ($GLOBALS['config']->get('config', 'admin_notify_status')=="2" && $this->_email_enabled && $this->_email_admin_enabled && $admin_notify = $this->_notifyAdmins()) {
					$admin_mailer = Mailer::getInstance();
					
					$message_id = md5('admin.order_received'.$status_id.$order_id);

					if (!$GLOBALS['session']->has($message_id, 'email') && ($content = $admin_mailer->loadContent('admin.order_received')) !== false) {
						$this->assignOrderDetails(null, true);
						$admin_mailer->sendEmail($admin_notify, $content);
						$GLOBALS['session']->set($message_id, true, 'email');
						unset($content);
					}
				}

				// Send digital files
				$this->_digitalDelivery($order_id, $this->_order_summary['email']);

				break;

			case self::ORDER_COMPLETE:
				// Check that we have not skipped processing if not already disabled
				/*
					if (!$GLOBALS['config']->get('config','no_skip_processing_check') && ($GLOBALS['db']->select('CubeCart_order_history', array('status'), array('cart_order_id' => $order_id, 'status' => 2))) === false) {
						$this->orderStatus(2, $order_id);
					}
					*/

				if ( $GLOBALS['db']->select('CubeCart_order_history', array('status'), array('cart_order_id' => $order_id, 'status' => 2)) === false) {
					if (!$GLOBALS['config']->get('config', 'no_skip_processing_check')) {
						$this->orderStatus(2, $order_id);
					} else {
						// Send digital files when order status hasn't never been processing amd we always skip processing status
						$this->_digitalDelivery($order_id, $this->_order_summary['email']);
					}
				}

				// Load up the inventory, if we haven't already
				if (!isset($complete)) {
					$this->_getInventory($order_id);
				}
				if (!$this->_skip_order_complete_email && $this->_email_enabled) {
					foreach ($this->_order_inventory as $item) {
						// Send Gift Certificate
						if (!empty($item['custom']) && !empty($item['coupon_id'])) {
							$this->_sendCoupon($item['coupon_id'], unserialize($item['custom']));
						}
					}
				}
				/* no need to send this email for digital only orders */
				if (!$this->_skip_order_complete_email && $this->_email_enabled && ($content = $mailer->loadContent('cart.order_complete', $order_summary['lang'])) !== false) {
					$this->assignOrderDetails();
					$mailer->sendEmail($this->_order_summary['email'], $content);
					unset($content);
				}

				break;

			case self::ORDER_DECLINED:
				// Nothing to do, but leave the option here for hooks & such
				break;

			case self::ORDER_FAILED:
				// Email the customer to explain their order failed fraud review
				$content = $mailer->loadContent('cart.payment_fraud', $order_summary['lang'], $this->_order_summary);
				break;

			case self::ORDER_CANCELLED:
				// Cancelled
				$content = $mailer->loadContent('cart.order_cancelled', $order_summary['lang'], $this->_order_summary);
				break;
			}
			if ($this->_email_enabled && isset($content)) {
				$mailer->sendEmail($this->_order_summary['email'], $content);
			}

			// Update the status level
			$data['status'] = (int)$status_id;
			$this->updateSummary($order_id, $data);

			// Update Stock Levels
			$this->_manageStock($status_id, $order_id);

			// Set status to complete if it is digital only
			if ($complete) {
				if ($GLOBALS['config']->get('config', 'force_completed')!="1" ) $this->_skip_order_complete_email = true;
				//$status_id = self::ORDER_COMPLETE;
				$this->orderStatus(3, $order_id);
			}
			return true;
		}
		return false;
	}

	/**
	 * Update payment status for order
	 *
	 * @param int $status_id
	 * @param string $order_id
	 */
	public function paymentStatus($status_id, $order_id) {
		if (!empty($status_id) && !empty($order_id)) {
			$this->getSummary($order_id);
			
			if ((int)$this->_order_summary['status'] == 0) return false; // no order record
			
			$mailer = Mailer::getInstance();
			switch ($status_id) {
			case self::PAYMENT_PENDING:
				/* $content = $mailer->loadContent('cart.payment_pending', $this->_order_summary['lang'], $this->_order_summary);*/
				break;
			case self::PAYMENT_PROCESS:
				break;
			case self::PAYMENT_SUCCESS:
				$content = $mailer->loadContent('cart.payment_received', $this->_order_summary['lang'], $this->_order_summary);
				break;
			case self::PAYMENT_DECLINE:
				break;
			case self::PAYMENT_FAILED:
				break;
			case self::PAYMENT_CANCEL:
				break;
			}
			if ($this->_email_enabled && isset($content)) {
				$mailer->sendEmail($this->_order_summary['email'], $content);
			}
		}
	}

	/**
	 * Create order
	 *
	 * @param bool $force
	 * @return bool
	 */
	public function placeOrder($force_order = false) {
		foreach ($GLOBALS['hooks']->load('class.order.place_order') as $hook) include $hook;

		if ($_GET['retrieve'] && isset($_GET['cart_order_id']) && !empty($_GET['cart_order_id'])) {
			// Order retrieval
			if ($this->_retrieveOrder($_GET['cart_order_id'])) {
				httpredir(currentPage(array('cart_order_id', 'retrieve'), array('_a' => 'confirm')));
			}
		} else if (!empty($this->_basket)) {
				// Order Creation/Updating
				$this->_saveAddresses();
				if (isset($this->_basket['cart_order_id']) && !empty($this->_basket['cart_order_id'])) {
					// Order has already been placed, so we only need to update
					$this->_updateOrder();
					$update = true;
				} else {
					// Create a new order
					$this->createOrderId();
					// Take basket data from session, and insert into database
					foreach ($this->_basket['contents'] as $key => $item) {
						$product = $this->_orderAddProduct($item, $key);
						$this->_basket['contents'][$key] = (is_array($product)) ? array_merge($product, $item) : $item;
					}
					$update = false;
				}
				// Shipping - calculate taxes (if any)
				if (isset($this->_basket['shipping']) && is_array($this->_basket['shipping'])) {
					Tax::getInstance()->productTax($this->_basket['shipping']['value'], (int)$this->_basket['shipping']['tax_id'], false, 0, 'shipping');
				}

				// Insert Taxes
				$GLOBALS['db']->delete('CubeCart_order_tax', array('cart_order_id' => $this->_order_id));

				if (is_array($this->_basket['order_taxes'])) {
					foreach ($this->_basket['order_taxes'] as $order_tax) {
						$order_tax['cart_order_id'] = $this->_order_id;
						$GLOBALS['db']->insert('CubeCart_order_tax', $order_tax);
					}
				}
				if (isset($this->_basket['coupons']) && is_array($this->_basket['coupons'])) {
					$codes_used = array();
					foreach ($this->_basket['coupons'] as $key => $data) {
						if ($data['gc']) {
							// Update gift certificate balance
							$GLOBALS['db']->update('CubeCart_coupons', array('discount_price' => $data['remainder']), array('code' => $data['voucher']));
							$certificates_used[] = $data['voucher'];
						} else {
							$vouchers_used[] = $data['voucher'];
							// Update usage count
							$product_data = unserialize($data['product']);
							if (is_array($product_data) && count($product_data) > 1) {
								## Per product coupons
								foreach ($this->_basket['contents'] as $hash => $item) {

									if (!in_array($item['id'], $product_data)) {
										continue;
									}
									$GLOBALS['db']->update('CubeCart_coupons', array('count' => '+'.(int)$item['quantity']), array('code' => $data['voucher']));
								}
							} else {
								$GLOBALS['db']->update('CubeCart_coupons', array('count' => '+1'), array('code' => $data['voucher']));
							}
						}
					}
					$note_content = '';
					if (is_array($certificates_used)) {
						$note_content .= "\r\n".$GLOBALS['language']->orders['certificate_codes_used']."\r\n".implode("\r\n", $certificates_used);
					}
					if (is_array($vouchers_used)) {
						$note_content .= "\r\n".$GLOBALS['language']->orders['discount_codes_used']."\r\n".implode("\r\n", $vouchers_used);
					}
					$this->addNote($this->_order_id, $note_content);
				}
				// Set order as 'Pending'
				$this->_basket['order_status'] = constant('ORDER_PENDING');
				foreach ($GLOBALS['hooks']->load('class.order.place_order.basket') as $hook) include $hook;
				// Insert/Update the order summary
				$this->_orderSummary($update, $force_order);

				$this->_manageStock(self::ORDER_PENDING, $this->_basket['cart_order_id']);

				$this->orderStatus(self::ORDER_PENDING, $this->_basket['cart_order_id'], true);

				if ($this->_basket['total'] == 0) {
					$this->orderStatus(self::ORDER_PROCESS, $this->_basket['cart_order_id']);
					httpredir(currentPage(null, array('_a' => 'complete')));
				}
				return true;
			}
		// Go back to the basket page
		httpredir(currentPage(array('cart_order_id'), array('_a' => 'basket')));
		return false;
	}

	/**
	 * Create serialized array of product options
	 *
	 * @param array $options
	 * @param int $product_id
	 * @return string
	 */
	public function serializeOptions($options, $product_id) {
		if (isset($options) && !empty($options)) {
			foreach ($options as $option_id => $assign_id) {
				if (!is_array($assign_id)) {
					if (($value = $GLOBALS['catalogue']->getOptionData((int)$option_id, (int)$assign_id)) !== false) {
						$value['price_display'] = '';
						if (isset($value['option_price']) && $value['option_price']>0) { // record option price but not zero
							if ($value['option_negative']) {
								//$record['price'] -= $value['option_price'];
								$value['price_display'] = ' (-';
							} else {
								//$record['price'] += $value['option_price'];
								$value['price_display'] = ' (+';
							}
							$value['price_display'] .= Tax::getInstance()->priceFormat($value['option_price'], true, true).')';
						}
						$option[$assign_id] = $value['option_name'].': '.$value['value_name'].$value['price_display'];
					}
				} else {
					foreach ($assign_id as $id => $option_value) {
						if (($assign_id = $GLOBALS['db']->select('CubeCart_option_assign', array('assign_id'), array('option_id' => (int)$option_id, 'product' => $product_id))) !== false) {
							$assign_id = (int)$assign_id[0]['assign_id'];
						} else {
							$assign_id = 0;
						}
						
						if (($value = $GLOBALS['catalogue']->getOptionData((int)$option_id, $assign_id)) !== false) {
							$value['price_display'] = '';
							if (isset($value['option_price']) && $value['option_price']>0) { // record option price but not zero
								if ($value['option_negative']) {
									//$record['price'] -= $value['option_price'];
									$value['price_display'] = ' (-';
								} else {
									//$record['price'] += $value['option_price'];
									$value['price_display'] = ' (+';
								}
								$value['price_display'] .= Tax::getInstance()->priceFormat($value['option_price'], true, true).')';
							}
							$option[$assign_id] = $value['option_name'].': '.$option_value.$value['price_display'];
						}
					}

				}
			}
			if (is_array($option)) {
				return serialize($option);
			}
		}
		return '';
	}

	/**
	 * Store transaction data for payment
	 *
	 * @param array $transData
	 * @param bool $forceLog
	 * @return bool
	 */
	public function storeTrans($transData, $forceLog = false) {
		return $this->logTransaction($transData, $forceLog);
	}

	/**
	 * Update the order summary
	 *
	 * @param string $order_id
	 * @param array $dataArray
	 * @return bool
	 */
	public function updateSummary($order_id, $dataArray) {
		## Add notes, update status, gateway, shipping date, courier tracking url
		if (!empty($dataArray) && is_array($dataArray)) {
			$GLOBALS['db']->update('CubeCart_order_summary', $dataArray, array('cart_order_id' => $order_id));
			return true;
		}
		return false;
	}

	//=====[ Private ]=======================================

	/**
	 * Log order status history with timestamp
	 *
	 * @param string $order_id
	 * @param int $status_id
	 * @return bool
	 */
	private function _addHistory($order_id, $status_id) {
		if (!empty($order_id) && !empty($status_id)) {
			$record = array(
				'cart_order_id' => $order_id,
				'updated'  => time(),
				'status'  => $status_id
			);
			return (bool)$GLOBALS['db']->insert('CubeCart_order_history', $record);
		}
		return false;
	}

	/**
	 * Create gift certificate
	 *
	 * @param float $value
	 * @param int $blocks
	 * @param int $bsize
	 * @param string $glue
	 * @return bool
	 */
	private function _createCertificate($value, $blocks = 5, $bsize = 4, $glue = '-') {
		// Create Certificate Code
		$length = ($blocks*$bsize)+($blocks-1);
		$seed = hash('whirlpool', time().rand().microtime());
		$code = '';
		for ($i = 1; $i <= $length; ++$i) {
			$code .= ($i%($bsize+1)) ? substr($seed, rand(0, strlen($seed)-1), 1) : trim($glue);
		}
		$gc = $GLOBALS['config']->get('gift_certs');
		## Insert the Certificate Record
		$record = array(
			'cart_order_id'  => $this->_order_id,
			'discount_price' => $value,
			'code'    => strtoupper($code),
		);
		if (isset($gc['expires']) && (int)$gc['expires'] > 0) {
			$record['expires'] = date('Y-m-d', strtotime((int)$gc['expires'].' months'));
		}
		return (int)$GLOBALS['db']->insert('CubeCart_coupons', $record);
	}

	/**
	 * Create digital download for order ready to be sent later
	 *
	 * @param int $product_id
	 * @param string $order_inv_id
	 * @return bool
	 */
	private function _createDownload($product_id, $order_inv_id) {
		// Create a reference for a download
		$accesskey = md5($this->_order_id.$product_id.date('cZ@u').mt_rand());

		$expire = ($GLOBALS['config']->get('config', 'download_expire')>0) ? time() + $GLOBALS['config']->get('config', 'download_expire') : 0;

		$record		= array(
			'cart_order_id' => (isset($this->_order_summary['cart_order_id'])) ? $this->_order_summary['cart_order_id'] : $this->_order_id , // $this->_order_id,
			'order_inv_id'	=> $order_inv_id,
			'customer_id' => (isset($this->_order_summary['customer_id'])) ? $this->_order_summary['customer_id'] : $GLOBALS['user']->getId(), // $GLOBALS['user']->getId(),
			'product_id'	=> (int)$product_id,
			'expire'		=> $expire,
			'accesskey'		=> $accesskey,
		);
		return $GLOBALS['db']->insert('CubeCart_downloads', $record);
	}

	/**
	 * Deliver digital download from _createDownload
	 *
	 * @param string $order_id
	 * @param string $email
	 * @return bool
	 */
	private function _digitalDelivery($order_id, $email) {
		if (!empty($order_id) && !empty($email)) {
			if (($digital = $GLOBALS['db']->select('CubeCart_downloads', array('digital_id', 'accesskey', 'order_inv_id'), array('cart_order_id' => $order_id))) !== false) {
				foreach ($digital as $offset => $download) {
					// Get product name
					$product = $GLOBALS['db']->select('CubeCart_order_inventory', array('name'), array('id' => $download['order_inv_id']));
					// Set minimum expiry time (min 30 mins = 1800 seconds)
					if (!$GLOBALS['config']->isEmpty('config', 'download_expire')) {
					  $validity_time = ($GLOBALS['config']->get('config', 'download_expire') > 1800) ? $GLOBALS['config']->get('config', 'download_expire') : 1800;
					  $expire = time() + $validity_time;
					} else {
					  $expire = 0;
					}
					$GLOBALS['db']->update('CubeCart_downloads', array('expire' => $expire), array('digital_id' => $download['digital_id']));
					$dkeys[] = array(
						'accesskey' => $download['accesskey'],
						'name'  => $product[0]['name'],
						'expire'    => ($expire > 0) ? formatTime($expire, false, true) : $GLOBALS['language']->common['never']
					);
				}

				$mailer = Mailer::getInstance();
				if ($this->_email_enabled && ($contents = $mailer->loadContent('cart.digital_download', $this->_order_summary['lang'], $this->_order_summary))) {

					$storeURL = (CC_SSL) ? $GLOBALS['config']->get('config', 'ssl_url') : $GLOBALS['storeURL'];

					foreach ($dkeys as $dkey) {
						$download['url']  = $storeURL.'/index.php?_a=download&accesskey='.$dkey['accesskey'];
						$download['name']  = $dkey['name'];
						$download['expire'] = $dkey['expire'];
						$downloads[] = $download;
					}
					$GLOBALS['smarty']->assign('DOWNLOADS', $downloads);
					return $mailer->sendEmail($email, $contents);
				}
			}
		}
		return false;
	}

	/**
	 * Get order line items only
	 *
	 * @param string $order_id
	 * @param bool $force_db
	 * @return bool
	 */
	private function _getInventory($order_id = null, $force_db = false) {
		// Returns a list of products from the order
		if (!$force_db && !empty($this->_basket['contents']) && $this->_basket['cart_order_id'] == $order_id) {
			foreach ($this->_basket['contents'] as $hash => $item) {
				$product = ($item['id']>0) ? $GLOBALS['catalogue']->getProductData($item['id']) : $item;
				$product['quantity'] = $item['quantity']; // Otherwise its wrong!
				$products[] = $product;
			}
			$this->_order_inventory = $products;
			return $this->_order_inventory;
		} else {
			if (!is_null($order_id)) {
				if (($products = $GLOBALS['db']->select('CubeCart_order_inventory', false, array('cart_order_id' => $order_id))) !== false) {
					$this->_order_inventory = $products;
					return $this->_order_inventory;
				}
			}
		}
		return false;
	}

	/**
	 * Manage stock level for order inventory items
	 *
	 * @param int $status_id
	 * @param string $order_id
	 * @return bool
	 */
	private function _manageStock($status_id, $order_id) {

		foreach ($GLOBALS['hooks']->load('class.order.manage_stock') as $hook) include $hook;

		$matrix_prod = array();

		if (($items = $GLOBALS['db']->select('CubeCart_order_inventory', false, array('cart_order_id' => $order_id), false, false, false, false)) !== false) {

			$stock_change_time = (int)$GLOBALS['config']->get('config', 'stock_change_time');

			foreach ($items as $item) {

				// Check stock on options first
				if (!empty($item['options_identifier']) && $options_stock = $GLOBALS['db']->select('CubeCart_option_matrix', array('stock_level', 'matrix_id'), array('product_id' => (int)$item['product_id'], 'options_identifier' => $item['options_identifier'], 'status' => 1, 'use_stock' => 1))) {

					$stock = $options_stock[0]['stock_level'];

					$matrix_prod[] = (int)$item['product_id'];

					switch ($status_id) {
					case self::ORDER_PENDING:
						// Update stock on order creation
						if (!$item['stock_updated'] && $stock_change_time === 2) {
							$stock = $stock-$item['quantity'];
							$update = 1;
						}
						break;
					case self::ORDER_PROCESS:
						// Update stock on order payment
						if (!$item['stock_updated'] && $stock_change_time === 1) {
							$stock = $stock-$item['quantity'];
							$update = 1;
						}
						break;
					case self::ORDER_COMPLETE:
						// Update stock on order completion
						if (!$item['stock_updated'] && $stock_change_time === 0) {
							$stock = $stock-$item['quantity'];
							$update = 1;
						}
						break;
					case self::ORDER_DECLINED:
					case self::ORDER_FAILED:
						break;
					case self::ORDER_CANCELLED:
						## Restore stock
						if ($item['stock_updated']) {
							$stock = $stock+$item['quantity'];
							$update = 0;
						}
						break;
					}
					if (isset($stock) && isset($update)) {
						// Update store inventory
						$GLOBALS['db']->update('CubeCart_option_matrix', array('stock_level' => $stock), array('product_id' => (int)$item['product_id'], 'options_identifier' => $item['options_identifier']));
						// Update order inventory information
						$GLOBALS['db']->update('CubeCart_order_inventory', array('stock_updated' => (int)$update), array('id' => $item['id'], 'cart_order_id' => $order_id));
						// Unset variables
						unset($stock, $update);
					}
					// skip to the next item
					continue;

				}
				// Traditonal stock if the product opts are not set or not set to use stock
				if (($product = $GLOBALS['db']->select('CubeCart_inventory', array('stock_level'), array('product_id' => (int)$item['product_id'], 'use_stock_level' => 1), false, false, false, false)) !== false) {

					$stock = $product[0]['stock_level'];

					switch ($status_id) {
					case self::ORDER_PENDING:
						// Update stock on order creation
						if (!$item['stock_updated'] && $stock_change_time === 2) {
							$stock = $stock-$item['quantity'];
							$update = 1;
						}
						break;
					case self::ORDER_PROCESS:
						// Update stock on order payment
						if (!$item['stock_updated'] && $stock_change_time === 1) {
							$stock = $stock-$item['quantity'];
							$update = 1;
						}
						break;
					case self::ORDER_COMPLETE:
						// Update stock on order completion
						if (!$item['stock_updated'] && $stock_change_time === 0) {
							$stock = $stock-$item['quantity'];
							$update = 1;
						}
						break;
					case self::ORDER_DECLINED:
					case self::ORDER_FAILED:
						break;
					case self::ORDER_CANCELLED:
						## Restore stock
						if ($item['stock_updated']) {
							$stock = $stock+$item['quantity'];
							$update = 0;
						}
						break;
					}
					if (isset($stock) && isset($update)) {
						// Update store inventory
						$GLOBALS['db']->update('CubeCart_inventory', array('stock_level' => $stock), array('product_id' => (int)$item['product_id']));
						// Update order inventory information
						$GLOBALS['db']->update('CubeCart_order_inventory', array('stock_updated' => (int)$update), array('id' => $item['id'], 'cart_order_id' => $order_id));
						// Unset variables
						unset($stock, $update);
					}
				}
			}
			if ($GLOBALS['config']->get('config', 'update_main_stock')) {
				$matrix_prods = array_unique($matrix_prod);

				foreach ($matrix_prods as $prod_id) {
					$options_stock = $GLOBALS['db']->select('CubeCart_option_matrix', 'SUM(stock_level) AS stock', array('product_id' => (int)$prod_id, 'status' => 1, 'use_stock' => 1));
					$GLOBALS['db']->update('CubeCart_inventory', array('stock_level' => $options_stock[0]['stock']), array('product_id' => (int)$prod_id));
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * Get list of admin email addresses to recieve order notification
	 *
	 * @return string 
	 */
	private function _notifyAdmins() {
		if (($admins = $GLOBALS['db']->select('CubeCart_admin_users', array('email'), array('status' => 1, 'order_notify' => 1))) !== false) {
			## Get their email addresses
			foreach ($admins as $admin) {
				if (filter_var($admin['email'], FILTER_VALIDATE_EMAIL)) $list[] = $admin['email'];
			}
			## Add master email, while avoiding duplications
			$list = array_merge($list, array($GLOBALS['config']->get('config', 'email_address')));
			return implode(',', array_unique($list));
		} else {
			return $GLOBALS['config']->get('config', 'email_address');
		}
	}

	/**
	 * Add product to order line items
	 *
	 * @param array $item
	 * @param string $hash
	 * @return array/false
	 */
	private function _orderAddProduct($item, $hash = '') {
		// Add an item to the order - fetch the details from the database
		if (is_array($item)) {
			if (isset($item['certificate'])) {
				$gc = $GLOBALS['config']->get('gift_certs');
				$product = array(
					'name'   => $GLOBALS['language']->catalogue['gift_certificate'],
					'price'   => $item['certificate']['value'],
					'product_code' => $gc['product_code'],
					'digital'  => (bool)$item['digital'],
					'coupon_id'  => $this->_createCertificate($item['certificate']['value']),
					'custom'  => serialize($item['certificate']),
					'hash'   => $hash
				);
			} else {
				$product = $GLOBALS['catalogue']->getProductData($item['id'], 1, false, 10, 1, false, $item['options_identifier']);
			}

			$record = array(
				'cart_order_id'  => $this->_order_id,
				'product_id'  => (int)$item['id'],
				'quantity'   => $item['quantity'],
				'price'    => (!isset($item['certificate'])) ? $item['total_price_each'] : $item['certificate']['value'],
				'product_code'  => (!empty($product['product_code'])) ? $product['product_code'] : $item['product_code'],
				'name'    => (!empty($product['name'])) ? $product['name'] : $item['name'],
				'digital'   => (!empty($product['digital']) || !empty($product['digital_path'])) ? 1 : 0,
				'custom'   => (isset($product['custom'])) ? $product['custom'] : null,
				'coupon_id'   => (isset($product['coupon_id'])) ? $product['coupon_id'] : 0,
				'hash'    => $hash,
				'options_identifier' => $item['options_identifier'],
				'options_array' => serialize($item['options']),
				'product_options' => $this->serializeOptions($item['options'], $item['id'])
			);

			foreach ($GLOBALS['hooks']->load('class.order.products.add.pre') as $hook) include $hook;

			$insert_id = $GLOBALS['db']->insert('CubeCart_order_inventory', $record);
			// Taxes
			$tax_on = ($GLOBALS['config']->get('config', 'basket_tax_by_delivery')) ? 'delivery_address' : 'billing_address';
			$tax_state_id = is_numeric($this->_basket[$tax_on]['state_id']) ? $this->_basket[$tax_on]['state_id'] : getStateFormat($this->_basket[$tax_on]['state_id'], 'name', 'id');

			$tax_amount = ($record['price'] * $record['quantity']);
			// By this stage product prices will always be excluding tax!
			if (isset($tax_state_id)) {
				Tax::getInstance()->productTax($tax_amount, (int)$product['tax_type'], false, $tax_state_id);
			} else {
				Tax::getInstance()->productTax($tax_amount, (int)$product['tax_type'], false);
			}

			if ($record['digital'] && !isset($item['certificate'])) {
				// If digital, create a download code
				$this->_createDownload($item['id'], (int)$insert_id);
			}
			return $product;
		}
		return false;
	}

	/**
	 * Update or insert order summary
	 *
	 * @param bool $update
	 * @param bool $force_order
	 * @param bool $suppress_email
	 * @return nothing/false
	 */
	private function _orderSummary($update = false, $force_order = false, $suppress_email = false) {
		// Populate the order summary table
		$userdata = $GLOBALS['user']->get();

		if (isset($userdata) && !empty($userdata)) {
			$customer_id = $userdata['customer_id'];
			$email   = $userdata['email'];
			$phone   = $userdata['phone'];
			$mobile    = $userdata['mobile'];
		} else if (isset($this->_basket['customer'])) {
				$customer_id = $this->_basket['customer']['customer_id'];
				$email    = $this->_basket['customer']['email'];
				$phone    = $this->_basket['customer']['phone'];
				$mobile    = $this->_basket['customer']['mobile'];
		} else {
			// Erm, oops?
			if (!$force_order) {
				trigger_error('No customer information detected. Order summary was not built or inserted.', E_USER_WARNING);
				return false;
			}
		}
		// This will make life easier for some payment modules
		$this->_basket['billing_address']['email'] = $email;
		$this->_basket['billing_address']['phone'] = $phone;
		$GLOBALS['cart']->save();

		$record = array(
			## Order Details
			'cart_order_id' => $this->_order_id,
			'order_date' => time(),
			'customer_id' => (int)$customer_id,
			'status'  => (int)$this->_basket['order_status'],
			# Prices
			'subtotal'  => $this->_basket['subtotal'],
			'discount'  => (isset($this->_basket['discount'])) ? $this->_basket['discount'] : 0,
			'discount_type' => (isset($this->_basket['discount_type'])) ? $this->_basket['discount_type'] : '',
			'total_tax'  => $this->_basket['total_tax'],
			'total'   => $this->_basket['total'],
			## Shipping
			'ship_method' => $this->_basket['shipping']['name'],
			'ship_product' => $this->_basket['shipping']['product'],
			'shipping'  => ($this->_basket['shipping']['value']>0) ? $this->_basket['shipping']['value'] : '0.00',
			# Misc
			'phone'   => $phone,
			'mobile'   => $mobile,
			'email'   => $email,
			'customer_comments' => (isset($this->_basket['comments'])) ? $this->_basket['comments'] : null,
			## Billing Details
			'title'   => $this->_basket['billing_address']['title'],
			'first_name' => $this->_basket['billing_address']['first_name'],
			'last_name'  => $this->_basket['billing_address']['last_name'],
			'company_name' => $this->_basket['billing_address']['company_name'],
			'line1'   => $this->_basket['billing_address']['line1'],
			'line2'   => $this->_basket['billing_address']['line2'],
			'town'   => $this->_basket['billing_address']['town'],
			'state'   => $this->_basket['billing_address']['state_id'],
			'postcode'  => $this->_basket['billing_address']['postcode'],
			'country'  => $this->_basket['billing_address']['country_id'],
			## Delivery Details
			'title_d'  => $this->_basket['delivery_address']['title'],
			'first_name_d' => $this->_basket['delivery_address']['first_name'],
			'last_name_d' => $this->_basket['delivery_address']['last_name'],
			'company_name_d'=> $this->_basket['delivery_address']['company_name'],
			'line1_d'  => $this->_basket['delivery_address']['line1'],
			'line2_d'  => $this->_basket['delivery_address']['line2'],
			'town_d'  => $this->_basket['delivery_address']['town'],
			'state_d'  => $this->_basket['delivery_address']['state_id'],
			'postcode_d' => $this->_basket['delivery_address']['postcode'],
			'country_d'  => $this->_basket['delivery_address']['country_id'],
			'basket'  => serialize($this->_basket),
			'lang'   => $GLOBALS['language']->current(),
			'ip_address' => get_ip_address()
		);

		foreach ($GLOBALS['hooks']->load('class.order.order_summary') as $hook) include $hook;

		if (!$check = $GLOBALS['db']->select('CubeCart_order_summary', array('cart_order_id'), array('cart_order_id' => $this->_order_id), false, false, false, false)) {
			$update = false;
		}

		if ($update) {
			// Update Summary
			$GLOBALS['db']->update('CubeCart_order_summary', $record, array('cart_order_id' => $this->_basket['cart_order_id']));
		} else {
			// Insert Summary
			if ($order_id = $GLOBALS['db']->insert('CubeCart_order_summary', $record)) {
				// Insert history
				$this->_addHistory($this->_order_id, 1);
				if ($GLOBALS['user']->is()) {
					//If they are a user add an order
					$GLOBALS['user']->addOrder();
				}
			}
		}
	}

	/**
	 * Repurchase an existing order
	 *
	 * @param string $order_id
	 * @return bool
	 */
	private function _retrieveOrder($order_id) {
		// Retrieve an order from the database, and put it back into the session
		if (!empty($order_id)) {
			// Fetch summary
			if (($summary = $GLOBALS['db']->select('CubeCart_order_summary', 'basket', array('cart_order_id' => (string)$order_id), false, false, false, false)) !== false) {
				if ($this->_basket = unserialize($summary[0]['basket'])) {
					$GLOBALS['cart']->save();
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Save customers billing/delivery address
	 */
	private function _saveAddresses() {
		if (($addresses = $GLOBALS['user']->getAddresses()) !== false) {
			if (isset($_POST['delivery_address']) && is_numeric($_POST['delivery_address'])) {
				$selected = (int)$_POST['delivery_address'];
			} else if (isset($this->_basket['delivery_address']) && isset($this->_basket['delivery_address']['address_id'])) {
					$selected = (is_array($this->_basket['delivery_address'])) ? (int)$this->_basket['delivery_address']['address_id'] : (int)$this->_basket['delivery_address'];
				} else {
				$selected = false;
			}
			foreach ($addresses as $address) {
				## Billing address?
				if ($address['billing']) {
					$this->_basket['billing_address'] = $address;
				}
				## Delivery address
				if ($selected && (int)$selected === (int)$address['address_id'] || !$selected && $address['default']) {
					$this->_basket['delivery_address'] = $address;
				}
			}
			## Shipping lock enabled?
			if (!$GLOBALS['config']->get('config', 'basket_allow_non_invoice_address')) {
				$this->_basket['delivery_address'] = $this->_basket['billing_address'];
			}
		}
	}

	/**
	 * Send gift certificate va email
	 *
	 * @param int $coupon_id
	 * @param array $data
	 * @return bool
	 */
	private function _sendCoupon($coupon_id, $data) {
		if (!empty($coupon_id)) {
			if (($coupon = $GLOBALS['db']->select('CubeCart_coupons', false, array('coupon_id' => (int)$coupon_id))) !== false) {
				$mailer = Mailer::getInstance();
				if (isset($coupon[0]['value'])) {
					$coupon[0]['value'] = Tax::getInstance()->priceFormat($coupon[0]['value']);
				}
				$data['storeURL']  = $this->config['storeURL'];
				if (($content = $mailer->loadContent('cart.gift_certificate', $this->_order_summary['lang'], array_merge($this->_order_summary, $data, $coupon[0]))) !== false) {
					return $mailer->sendEmail($data['email'], $content);
				}
			}
		}
		return false;
	}

	/**
	 * Auto cancel orders over x seconds of age
	 */
	private function _tidyOrders() {
		$expire = $GLOBALS['config']->get('config', 'basket_order_expire');
		if (!empty($expire) && is_numeric($expire)) {
			$expire = time()-$expire;
			if (($orders = $GLOBALS['db']->select('CubeCart_order_summary', array('cart_order_id'), array('status' => 1, 'order_date' => '<'.$expire), false, false, false, false)) !== false) {
				foreach ($orders as $order) {
					// Manage stock
					$this->_manageStock(self::ORDER_CANCELLED, $order['cart_order_id']);
					// Cancel the order
					$GLOBALS['db']->update('CubeCart_order_summary', array('status' => self::ORDER_CANCELLED), array('cart_order_id' => $order['cart_order_id']));
				}
			}
		}
	}

	/**
	 * Update order inventory from basket changes
	 *
	 * @return bool
	 */
	private function _updateOrder() {
		// Add new items to the order, as long as its only 'Pending'
		if (!isset($this->_basket['order_status']) || $this->_basket['order_status'] < self::ORDER_PROCESS) {
			$order_items = $GLOBALS['db']->select('CubeCart_order_inventory', array('id', 'digital', 'hash', 'quantity'), array('cart_order_id' => $this->_order_id));
			if ($order_items) {
				foreach ($order_items as $order_item) {
					$stored_items[$order_item['hash']] = array(
						'id'    => $order_item['id'],
						'digital'   => $order_item['digital'],
						'quantity'   => $order_item['quantity']
					);
					if ($order_item['digital']) $digital[] = $order_item['hash'];
				}
			}

			// Add products
			foreach ($this->_basket['contents'] as $hash => $item) {
				$basket_items[] = $hash;
				if (is_array($stored_items[$hash]) && $stored_items[$hash]['quantity']!==$item['quantity']) {
					$GLOBALS['db']->update('CubeCart_order_inventory', array('quantity' => $item['quantity']), array('id' => $stored_items[$hash]['id'], 'cart_order_id' => $this->_order_id));
				} elseif (!isset($stored_items[$hash])) {
					$product = $this->_orderAddProduct($item, $hash);
					$this->_basket['contents'][$hash] = (is_array($product)) ? array_merge($product, $item) : $item;
				}
			}
			## Remove products
			foreach ($stored_items as $hash => $data) {
				if (!in_array($hash, $basket_items)) {
					if (in_array($hash, $digital)) {
						## Remove digital download record
						$GLOBALS['db']->delete('CubeCart_downloads', array('order_inv_id' => $data['id'], 'cart_order_id' => $this->_order_id));
					}
					## Remove product order record
					$GLOBALS['db']->delete('CubeCart_order_inventory', array('id' => $data['id'], 'cart_order_id' => $this->_order_id));
				}
			}
			return true;
		}
		return false;
	}
}