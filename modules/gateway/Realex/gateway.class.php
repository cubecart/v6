<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;

	public function __construct($module = false, $basket = false) {
		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
		$this->_url = 'https://redirect.globaliris.com/epage.cgi';
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> $this->_url,
			'method'	=> 'get',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		return false;
	}

	public function fixedVariables() {
		
		$timestamp 	= strftime("%Y%m%d%H%M%S");
		$amount 	= $this->_basket['total']*100;
		
		$order_id = $this->_basket['cart_order_id'].'_'.time();
		
		$hidden = array(			
			'MERCHANT_ID' 	=> $this->_module['merchant_id'],
			//'ACCOUNT'		=> 'test',
			'ORDER_ID' 		=> $order_id,
			'CURRENCY' 		=> $GLOBALS['config']->get('config', 'default_currency'),
			'AMOUNT' 		=> $amount,
			'TIMESTAMP' 	=> $timestamp,
			'MD5HASH' 		=> md5(md5($timestamp.'.'.$this->_module['merchant_id'].'.'.$order_id.'.'.$amount.'.'.$GLOBALS['config']->get('config', 'default_currency')).'.'.$this->_module['secret_word']),
			'AUTO_SETTLE_FLAG' => 1
		);
		return $hidden;
	}
	
	public function iframeURL() {
		$repeat_vars 	= $this->repeatVariables();
		if(is_array($repeat_vars)) {
			$request_vars = array_merge($this->fixedVariables(),$repeat_vars);
		} else {
			$request_vars = $this->fixedVariables();
		}
		return ($request_vars) ? $this->_url.'?'.http_build_query($request_vars, '', '&') : false;	
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {
		
		$GLOBALS['storeURL'] = str_replace('/modules/gateway/Realex','',$GLOBALS['storeURL']);
		
		$order				= Order::getInstance();
		$parts 				= explode('_',$_POST['ORDER_ID']);
		$cart_order_id		= $parts[0];
		$order_summary		= $order->getSummary($cart_order_id);
	
		if($_POST['RESULT'] == '00'){
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
			$status = 'Success';
			$smarty_data = array(
				'message' 	 		=> 'Many thanks, your payment has been made successfully.',
				'redirect_text' 	=> 'Return to Store',
				'return_url' 		=> $GLOBALS['storeURL'].'/index.php?_a=complete'
			);
		} else {
			$status = 'Fail';
			$smarty_data = array(
				'message' 	 		=> 'Sorry but the payment has been unsuccessful.',
				'redirect_text' 	=> 'Try Again',
				'return_url' 		=> $GLOBALS['storeURL'].'/index.php?_a=gateway'
			);
		}
		
		$transData['notes']			= $_POST['MESSAGE'];
		$transData['gateway']		= 'Realex';
		$transData['order_id']		= $cart_order_id;
		$transData['trans_id']		= $_POST['AUTHCODE'];
		$transData['amount']		= $order_summary['total'];
		$transData['status']		= $status;
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['extra']			= '';
		$order->logTransaction($transData);
		
		unset($GLOBALS['seo']);
		
		
		$GLOBALS['smarty']->assign("DATA", $smarty_data);
						
		## Check for custom template for module in skin folder
		$file_name = 'confirm.tpl';
		$form_file = $GLOBALS['gui']->getCustomModuleSkin('gateway', dirname(__FILE__), $file_name);
		$GLOBALS['gui']->changeTemplateDir($form_file);
		$ret = $GLOBALS['smarty']->fetch($file_name);
		$GLOBALS['gui']->changeTemplateDir();
		die($ret);	
	}

	public function form() {
		return false;
	}
}