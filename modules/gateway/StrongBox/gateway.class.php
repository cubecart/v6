<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;

	public function __construct($module = false, $basket = false) {
		$this->_session	=&  $GLOBALS['user'];

		$this->_module			= $module;
		$this->_basket			= $basket;
	}



	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> 'https://sb3.itruststrongbox.com/shop/ProcessOrder.aspx',
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

        $hidden = array(
				'x_dealer_id' 		    => $this->_module['acNo'],
				'x_amount'				=> $this->_basket['total'],
				'x_version'				=> '3.0',
				'x_custid_prefix'		=> $this->_module['custIdPrefix'],
				'x_method'				=> 'cc',
				'x_invoice_num'			=> $this->_basket['cart_order_id'],
				'x_description'			=> 'Payment for order #'.$this->_basket['cart_order_id'],
				'x_cust_id'				=> $this->_basket['billing_address']['customer_id'],
				'x_first_name'			=> $this->_basket['billing_address']['first_name'],
				'x_last_name'			=> $this->_basket['billing_address']['last_name'],
				'x_address'				=> $this->_basket['billing_address']['line1'].' '.$this->_basket['billing_address']['line2'],
				'x_city'				=> $this->_basket['billing_address']['town'],
				'x_state'				=> $this->_basket['billing_address']['state'],
				'x_zip'				    => $this->_basket['billing_address']['postcode'],
				'x_country'				=> $this->_basket['billing_address']['country_iso'],			
				'x_email'				=> $this->_basket['billing_address']['email'],
				'x_phone'				=> $this->_basket['billing_address']['phone'],				
				'x_customer_ip' 		=> get_ip_address(),
				'x_relay_url'			=> $GLOBALS['storeURL'].'/modules/gateway/StrongBox/return.php?status=Success&orderid='.$this->_basket['cart_order_id'],
				'x_relay_url_fail'		=> $GLOBALS['storeURL'].'/modules/gateway/StrongBox/return.php?status=Declined&orderid='.$this->_basket['cart_order_id']
				

			);
	
		return $hidden;		
				

 
	}

	##################################################

    public function call() {
		return false;
	}

    public function process() {

            if ($_SERVER['PHP_AUTH_USER'] == $this->_module['post_user'] && $_SERVER['PHP_AUTH_PW'] == $this->_module['post_pass']) {
			$proceed 	= true;

			if ((bool)$proceed) {
                
				$statusid = '';
				
				$order	= Order::getInstance();
				$order_summary = $order->getSummary($_GET['cart_order_id']);
				
				switch ($_GET['status']) {
					case 'Success':
						$transData['status']	= 'Success';
						$statusid = '2';
						$order->orderStatus(Order::ORDER_PROCESS, $_GET['cart_order_id']);
                        $order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
						break;
					default:
						$transData['status']	= 'Declined';
						$statusid = '4';
						$order->orderStatus(Order::ORDER_PENDING, $_GET['cart_order_id']);
                        $order->paymentStatus(Order::PAYMENT_DECLINE, $cart_order_id);
				}
				## Build the transaction log data
				$transData['gateway']		= $_GET['module'];
				$transData['order_id']		= $_GET['cart_order_id'];
				$transData['trans_id']		= '';
				$transData['amount']		= $order_summary['total'];
				$transData['customer_id']	= $order_summary['customer_id'];
				$transData['notes']			= $_GET['status'];
				$order->logTransaction($transData);

                $orderid =  $_GET['cart_order_id'];
                $order->updateSummary($orderid, array('status' => $statusid));

                httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));

				return false;
			}
		} else {
			header('WWW-Authenticate: Basic realm="Payment"');
			header('HTTP/1.0 401 Unauthorized');
		}
		
	}

	public function form() {
		return false;
	}
}