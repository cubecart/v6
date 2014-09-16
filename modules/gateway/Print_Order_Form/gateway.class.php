<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
class Gateway {
	private $_config;
	private $_module;
	private $_basket;
	private $_result_message;

	public function __construct($module = false, $basket = false) {
		$this->_config	= $GLOBALS['config']->get('config');

		$this->_module	= $module;
		$this->_basket	= $GLOBALS['cart']->basket;
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> 'index.php?_g=rm&type=gateway&cmd=call&module=Print_Order_Form&cart_order_id='.$this->_basket['cart_order_id'],
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	##################################################

	public function repeatVariables() {
		return (isset($hidden)) ? $hidden : false;
	}

	public function fixedVariables() {
		$hidden['gateway']	= basename(dirname(__FILE__));
		return (isset($hidden)) ? $hidden : false;
	}

	public function call() {
		//@todo everything
		$form_file	= dirname(__FILE__).'/'.'skin';

		$GLOBALS['gui']->changeTemplateDir(dirname(__FILE__).'/'.'skin/');

		$GLOBALS['smarty']->assign('MODULE', $this->_module);

		$order				= Order::getInstance();
		$cart_order_id		= sanitizeVar($_GET['cart_order_id']);
		$order_summary		= $order->getSummary($cart_order_id);

		$transData['trans_id'] 		= null;
		$transData['notes']			= 'Print order form displayed to customer with payment instructions. Do not dispatch until postal payment has been received and cleared.';
		$transData['gateway']		= 'Print Order Form';
		$transData['order_id']		= $order_summary['cart_order_id'];
		$transData['amount']		= $order_summary['total'];
		$transData['status']		= 'Pending';
		$transData['customer_id']	= $order_summary['customer_id'];
		$order->logTransaction($transData);

		if ($order_summary) {
			$GLOBALS['smarty']->assign('VAL_ORDER_DATE',formatTime($order_summary['order_date']));
			if (($inventory = $GLOBALS['db']->select('CubeCart_order_inventory', false, array('cart_order_id' => $order_summary['cart_order_id']))) !== false) {
				foreach ($inventory as $item) {
					if (!empty($item['product_options'])) {
						if (($list = unserialize($item['product_options'])) !== false) {
							foreach ($list as $value) {
								$item['options'][] = $value;
							}
						} else {
							$options = explode("\n", $item['product_options']);
							foreach ($options as $option) {
								$value	= trim($option);
								if (empty($value)) continue;
								$item['options'][] = $value;
							}
						}
					}
					$item['price_total'] = $GLOBALS['tax']->priceFormat(($item['price'] * $item['quantity']), true);
					$item['price'] = $GLOBALS['tax']->priceFormat($item['price']);
					$smarty_data['items'][] = $item;
				}
				$GLOBALS['smarty']->assign('ITEMS', $smarty_data['items']);
			}

			// Taxes
			$taxes	= $GLOBALS['db']->select('CubeCart_order_tax', false, array('cart_order_id' => $order_summary['cart_order_id']));
			if ($taxes) {
				Tax::getInstance()->loadTaxes($order_summary['country']);
				foreach ($taxes as $vat) {
					$detail	= Tax::getInstance()->fetchTaxDetails($vat['tax_id']);
					$smarty_data['taxes'][] = array('value' => $GLOBALS['tax']->priceFormat($vat['amount']), 'name' => $detail['name']);
				}
				$GLOBALS['smarty']->assign('TAXES', $smarty_data['taxes']);
			}
			
			$order_summary['percent'] = '';
			if ($order_summary['discount_type'] == 'p') {
				$order_summary['percent'] = number_format(($order_summary['discount']/$order_summary['subtotal'])*100) . '%';
			}
			
			// Price Formatting
			$format	= array('discount','shipping','subtotal','total_tax','total');
			foreach ($format as $field) {
				if (isset($order_summary[$field])) $order_summary[$field] = $GLOBALS['tax']->priceFormat($order_summary[$field]);
			}
			// Delivery Address
			$elements	= array('title_d', 'first_name_d', 'last_name_d', 'company_name_d', 'line1_d', 'line2_d', 'town_d', 'state_d', 'postcode_d', 'country_d');
			foreach ($elements as $key) {
				if (isset($order_summary[$key]) && !empty($order_summary[$key])) {
					if ($key == 'country_d') $order_summary[$key] = getCountryFormat($order_summary[$key]);
					if ($key == 'state_d') $order_summary[$key] = getStateFormat($order_summary[$key]);
					$address[str_replace('_d','',$key)] = strip_tags($order_summary[$key]);
				}
			}

			$GLOBALS['smarty']->assign('ADDRESS_DELIVERY', $address);
			// Invoice Address
			unset($address);
			$elements	= array('title', 'first_name', 'last_name', 'company_name', 'line1', 'line2', 'town', 'state', 'postcode', 'country');
			foreach ($elements as $key) {
				if (isset($order_summary[$key]) && !empty($order_summary[$key])) {
					if ($key == 'country') $order_summary[$key] = getCountryFormat($order_summary[$key]);
					if ($key == 'state') $order_summary[$key] = getStateFormat($order_summary[$key]);
					$address[$key] = strip_tags($order_summary[$key]);
				}
			}
			$GLOBALS['smarty']->assign('ADDRESS_INVOICE', $address);
			$GLOBALS['smarty']->assign('ORDER_DATE', formatTime($order_summary['order_date'],false,true));
			// Store logo
			if (isset($this->_config['skin_style']) && !empty($this->_config['skin_style'])) {
				$skin_logo	= $this->_config['skin_folder'].'-'.$this->_config['skin_style'];
			} else {
				$skin_logo	= $this->_config['skin_folder'];
			}
			$store_logo = $GLOBALS['gui']->getLogo(true, 'invoices');
			$GLOBALS['smarty']->assign('STORE_LOGO', $store_logo);
			// Store Address
			$GLOBALS['smarty']->assign('STORE', array(
				'address' => $GLOBALS['config']->get('config', 'store_address'),
				'county' => getStateFormat($this->_config['store_zone']),
				'country' => getCountryFormat($this->_config['store_country']),
				'postcode' => $GLOBALS['config']->get('config', 'store_postcode'),
				'url' => CC_STORE_URL,
				'name' => $GLOBALS['config']->get('config', 'store_name'))
			);
			$GLOBALS['smarty']->assign('SUM', $order_summary);

			// Payment Methods
			if ($this->_module['cheque']) $GLOBALS['smarty']->assign('CHEQUE', true); // I know, I know us stupid brits spell it the right way?! :)

			if ($this->_module['card']) {
				$cards = explode(',',$this->_module['cards']);
				if (is_array($cards)) {
					$GLOBALS['smarty']->assign('CARDS', $cards);
				}
			}
			if ($this->_module['bank']) {
				$GLOBALS['smarty']->assign('BANK', true);
			}
			
			$GLOBALS['smarty']->display('print.tpl');
			
			if($this->_module['confirmation_email']) {
				
				// Compose the Order Confirmation email to the customer
				if ($content = Mailer::getInstance()->loadContent('cart.order_confirmation', $order_summary['lang'])) {
	
						// Put in items
						foreach ($inventory as $item) {
							if($item['product_id']>0){
								$product			= array_merge($GLOBALS['catalogue']->getProductData($item['product_id']),$item);
								$product['item_price']	= Tax::getInstance()->priceFormat($product['price']);
								$product['price'] 	= Tax::getInstance()->priceFormat($product['price']*$product['quantity']);
								if (!empty($item['product_options'])) $product['product_options'] = implode(' ',unserialize($item['product_options']));
								$vars['products'][]	= $product;
							} else {
								$item['price']	= Tax::getInstance()->priceFormat($item['price']);
								$vars['products'][]	= $item;
							}
						}
						
						if (isset($vars['products']) && !empty($vars['products'])) {
							$GLOBALS['smarty']->assign('PRODUCTS', $vars['products']);
						}
	
						// Put tax in
						if ($taxes) {
							foreach($taxes as $order_tax) {
								$tax_data = Tax::getInstance()->fetchTaxDetails($order_tax['tax_id']);
								$tax['tax_name'] 	= $tax_data['name'];
								$tax['tax_percent'] = sprintf('%.3f',$tax_data['tax_percent']);
								$tax['tax_amount'] 	= Tax::getInstance()->priceFormat($order_tax['amount']);
								$vars['taxes'][]	= $tax;
							}
							if (isset($vars['taxes']) && !empty($vars['taxes'])) {
								$GLOBALS['smarty']->assign('TAXES', $vars['taxes']);
							}
						}
						
						$billing = array (
							'first_name' 	=> $order_summary['first_name'],
							'last_name' 	=> $order_summary['last_name'],
							'company_name' 	=> $order_summary['company_name'],
							'line1' 		=> $order_summary['line1'],
							'line2' 		=> $order_summary['line2'],
							'town' 			=> $order_summary['town'],
							'state' 		=> getStateFormat($order_summary['state']),
							'postcode' 		=> $order_summary['postcode'],
							'country' 		=> getCountryFormat($order_summary['country']),
							'phone' 		=> $order_summary['phone'],
							'email' 		=> $order_summary['email']
						);
						$shipping = array (
							'first_name' 	=> $order_summary['first_name_d'],
							'last_name' 	=> $order_summary['last_name_d'],
							'company_name' 	=> $order_summary['company_name_d'],
							'line1' 		=> $order_summary['line1_d'],
							'line2' 		=> $order_summary['line2_d'],
							'town' 			=> $order_summary['town_d'],
							'state' 		=> getStateFormat($order_summary['state_d']),
							'postcode' 		=> $order_summary['postcode_d'],
							'country' 		=> getCountryFormat($order_summary['country_d'])
						);	

						// Format data
						$order_summary['order_date'] = formatTime($order_summary['order_date'],false,true);
						
						$order_summary['link'] 		= $GLOBALS['storeURL'].'/index.php?_a=vieworder&cart_order_id='.$order_summary['cart_order_id'];
						$GLOBALS['smarty']->assign('DATA', $order_summary);
						$GLOBALS['smarty']->assign('BILLING', $billing);
						$GLOBALS['smarty']->assign('SHIPPING', $shipping);
						$GLOBALS['smarty']->assign('TAXES', $vars['taxes']);
						$GLOBALS['smarty']->assign('PRODUCTS', $vars['products']);
						Mailer::getInstance()->sendEmail($order_summary['email'], $content);
				}
				
			}

			$GLOBALS['cart']->clear();
		}
	}

	public function process() {
		return false;
	}

	##################################################

	public function form() {
		return false;
	}
}