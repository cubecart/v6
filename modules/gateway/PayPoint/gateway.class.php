<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;

	public function __construct($module = false, $basket = false) {
		$this->_session	=& $GLOBALS['user'];

		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> 'https://www.secpay.com/java-bin/ValCard',
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'auto',
		);
		return $transfer;
	}

	public function repeatVariables() {
		return false;
	}

	public function fixedVariables() {
		
		$test_mode = $this->_module['testmode'] ? 'test_status=true,' : '';
		
		$hidden	= array(
			'trans_id' 		=> 'CC5'.md5(time().(rand(0,32000)*rand(0,32000))),
			'merchant' 		=> $this->_module['merchant'],
			'amount' 		=> $this->_basket['total'],
			'callback' 		=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=PayPoint&cart_order_id='.$this->_basket['cart_order_id'],
			'options' 		=> $test_mode.'currency='.$GLOBALS['config']->get('config', 'default_currency').',cart=cubecart',
		);
		if (!empty($this->_module['remote_password'])) {
			$hidden['digest'] = md5($hidden['trans_id'].$this->_basket['total'].$this->_module['remote_password']);
		}
		return $hidden;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {
		$cart_order_id = sanitizeVar($_GET['cart_order_id']);
		$order				= Order::getInstance();
		$order_summary		= $order->getSummary($cart_order_id);

		if (!empty($_GET['code'])) {
			switch($_GET['code']) {
				case 'A':
					$message = 'Transaction authorised by bank. auth_code available as bank reference';
					break;
				case 'N':
					$message = 'Transaction not authorised. Failure message text available to merchant';
					break;
				case 'C':
					$message = 'Communication problem. Trying again later may well work';
					break;
				case 'F':
					$message = 'The PayPoint.net system has detected a fraud condition and rejected the transaction. The message field will contain more details.';
					break;
				case 'P:A':
					$message = 'Pre-bank checks. Amount not supplied or invalid';
					break;
				case 'P:X':
					$message = 'Pre-bank checks. Not all mandatory parameters supplied';
					break;
				case 'P:P':
					$message = 'Pre-bank checks. Same payment presented twice';
					break;
				case 'P:S':
					$message = 'Pre-bank checks. Start date invalid';
					break;
				case 'P:E':
					$message = 'Pre-bank checks. Expiry date invalid';
					break;
				case 'P:I':
					$message = 'Pre-bank checks. Issue number invalid';
					break;
				case 'P:C':
					$message = 'Pre-bank checks. Card number fails LUHN check (the card number is wrong)';
					break;
				case 'P:T':
					$message = 'Pre-bank checks. Card type invalid - i.e. does not match card number prefix';
					break;
				case 'P:N':
					$message = 'Pre-bank checks. Customer name not supplied';
					break;
				case 'P:M':
					$message = 'Pre-bank checks. Merchant does not exist or not registered yet';
					break;
				case 'P:B':
					$message = 'Pre-bank checks. Merchant account for card type does not exist';
					break;
				case 'P:D':
					$message = 'Pre-bank checks. Merchant account for this currency does not exist';
					break;
				case 'P:V':
					$message = 'Pre-bank checks. CV2 security code mandatory and not supplied / invalid';
					break;
				case 'P:R':
					$message = 'Pre-bank checks. Transaction timed out awaiting a virtual circuit. Merchant may not have enough virtual circuits for the volume of business.';
					break;
				case 'P:#': // this won't come up needs work
					$message = 'Pre-bank checks. No MD5 hash / token key set up against account';
				break;
				default:
					$message = 'Unspecified problem. Please look in your PayPoint control panel for more information.';

			}
		}

		$transData['customer_id'] 	= $order_summary['customer_id'];
		$transData['gateway'] 		= 'PayPoint';
		$transData['trans_id'] 		= $_GET['trans_id'];
		$transData['amount'] 		= $_GET['amount'];

		if ($_GET['code'] == 'A') {
			/*
			if (isset($_GET['hash']) && !empty($this->_module['digest_key']) && preg_match('#(.*)&hash=[0-9a-f]+$#i', $_SERVER['REQUEST_URI'], $match)) {
				$hash	= md5($match[1].'&'.$module['digest_key']);
				if ($hash == $_GET['hash']) {
					$transData['status'] 	= 'Authorised';
					$transData['notes'] 	= $message;
					$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
					$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
				} else {
					$transData['status'] 	= 'Declined';
					$transData['notes'] 	= $message;
					$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
					$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
				}
			} else {
				$transData['status'] 	= 'Authorised';
				$transData['notes'] 	= $message;
				$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
			}
			*/
			$transData['status'] 	= 'Authorised';
			$transData['notes'] 	= $message;
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
		} else {
			$transData['status'] 	= 'Declined';
			$transData['notes'] 	= $message;
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
		}
		$order->logTransaction($transData);
		## Rdirect using HTML meta refresh to lose domain masking
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')),null,true);
		return false;
	}

	public function form() {
		return false;
	}
}