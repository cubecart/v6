<?php

require_once('libwebtopay/WebToPay.php');

class Gateway {
	private $_module;
	
	private $_basket;
	
	private $redirectForm = array(
		'action'	=> WebToPay::PAY_URL,
		'method'	=> 'post',
		'target'	=> '_self',
		'submit'	=> 'auto',
	);
	
	private $payMethodForm = array(
		'action' => '',
		'method' => 'post',
		'target' => '_self',
		'submit' => 'manual',
	);

	public function __construct($module = false, $basket = false) {
		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	public function transfer() {
		if (
				(isset($_REQUEST['makeRequest']) && $_REQUEST['makeRequest'] == 1)
			|| $this->_module['paymentMethods'] == 0
		) {
			return $this->redirectForm;
		} else {
			return $this->payMethodForm;
		}
	}

	public function repeatVariables() {
		return true;
	}

	public function fixedVariables() {
		try {
            $request = WebToPay::buildRequest(array(
            	'projectid'     => $this->_module['projectid'],
                'sign_password' => $this->_module['projectpass'],

                'orderid'       => $this->_basket['cart_order_id'],
                'amount'        => ($this->_basket['total']*100),
                'currency'      => $GLOBALS['config']->get('config', 'default_currency'),

                'accepturl'     => $GLOBALS['storeURL'].'/index.php?_a=complete',
                'cancelurl'     => $GLOBALS['storeURL'].'/index.php?_a=gateway',
                'callbackurl'   => $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=call&module=WebToPay',
                'payment'       => (isset($_REQUEST['payment'])) ? $_REQUEST['payment'] : '',
                'country'       => $this->_basket['billing_address']['country_iso'],
                
                'p_firstname'   => $this->_basket['billing_address']['first_name'],
                'p_lastname'    => $this->_basket['billing_address']['last_name'],
                'p_email'       => $this->_basket['billing_address']['email'],
                'p_street'      => $this->_basket['billing_address']['line1'].' '.$this->_basket['billing_address']['line2'],
                'p_city'        => $this->_basket['billing_address']['town'],
                'p_state'       => $this->_basket['billing_address']['state'],
                'p_zip'         => $this->_basket['billing_address']['postcode'],
                'p_countrycode' => $this->_basket['billing_address']['country_iso'],
                'test'          => $this->_module['testMode'],
            ));
        } catch (WebToPayException $e) {
            echo get_class($e).': '.$e->getMessage();
        }
	    
		return $request;
	}
	
	//calback
	public function call() {
		
        try {
            $response = WebToPay::checkResponse($_REQUEST, array(
                'projectid'     => $this->_module['projectid'],
                'sign_password' => $this->_module['projectpass']
            ));
        
            if ($response['type'] !== 'macro') {
                throw new Exception('Only macro payment callbacks are accepted');
            }
            
            $Order = Order::getInstance();
            $orderSummary = $Order->getSummary($response['orderid']);
            
            //$this->d($response);
            //$this->d($orderSummary);
            //die();
            
            if (($orderSummary['total']*100) != $response['amount']) {
                throw new Exception('Amounts don\'t macth');
            }
            
            if ($GLOBALS['config']->get('config', 'default_currency') != $response['currency']) {
                throw new Exception('Currencies don\'t macth'); 
            }
            
            $Order->paymentStatus(Order::PAYMENT_SUCCESS, $response['orderid']);
            $Order->orderStatus(Order::ORDER_PROCESS, $response['orderid']);
            
            $transData['notes'][]	  = "Payment successful. <br />Address: ".$_POST['address_status']."<br />Payer Status: ".$_POST['payer_status'];
            $transData['gateway']	  = $_REQUEST['module'];
			$transData['order_id']    = $response['orderid'];
			$transData['trans_id']	  = $response['requestid'];
			$transData['amount']	  = $orderSummary['total'];
			//$transData['status']      = $_POST['payment_status'];
			$transData['customer_id'] = $orderSummary['customer_id'];
			//$transData['extra']       = $_POST['pending_reason'];
			$Order->logTransaction($transData);
        
            echo 'OK';
            
        } catch (Exception $e) {
            echo get_class($e) . ': ' . $e->getMessage();
        }

		return false;
	}

	public function process() {
		## We're being returned from WebToPay - This function can do some pre-processing, but must assume NO variables are being passed around
		## The basket will be emptied when we get to _a=complete, and the status isn't Failed/Declined

		## Redirect to _a=complete, and drop out unneeded variables
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
	}

	public function form() {
        $methods = WebToPay::getPaymentMethodList($this->_module['projectid'], $GLOBALS['config']->get('config', 'default_currency'))
        ->filterForAmount(($this->_basket['total']*100), $GLOBALS['config']->get('config', 'default_currency'))
        ->setDefaultLanguage('en')
        ->getCountries();
        
	    $GLOBALS['smarty']->assign('payMethods', $methods);
	    $GLOBALS['smarty']->assign('defaultCountry', 'lt');
	    $GLOBALS['smarty']->assign('gateway', 'WebToPay');
	    
	    $file_name = 'form.tpl';
		$form_file = $GLOBALS['gui']->getCustomModuleSkin('gateway', dirname(__FILE__), $file_name);
		$GLOBALS['gui']->changeTemplateDir($form_file);
		$ret = $GLOBALS['smarty']->fetch($file_name);
		$GLOBALS['gui']->changeTemplateDir();
		
		return $ret;
	}
	
	public function d($var) {
	    echo '<pre>';
	    print_r($var);
	    echo '</pre>';
	}
}