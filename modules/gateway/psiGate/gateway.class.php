<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;
	private $_result_message;

	public function __construct($module = false, $basket = false) {
		$this->_config	=& $GLOBALS['config'];
		$this->_session	=& $GLOBALS['user'];

		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> currentPage(),
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'manual',
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
		return false;
	}

	public function process() {

		$order				= Order::getInstance();
		$cart_order_id 		= $this->_basket['cart_order_id'];
		$order_summary		= $order->getSummary($cart_order_id);

		## Process the payment
		if($this->_module['test']) {
			$url 		= 'https://dev.psigate.com:7989/Messenger/XMLMessenger';
			$StoreID 	= 'teststore';
			$Passphrase = 'psigate1234';
		} else {
			$url 		= $this->_module['url'];
			$StoreID 	= $this->_module['acNo'];
			$Passphrase = $this->_module['passPhrase'];
		}
		## if card action hasn't been set default to 0 for sale, 1 = preauth
		$CardAction = !$this->_module['CardAction'] ? '0' : '1';

		$xml	= new XML(true);
		$xml->startElement('Order');
			$xml->writeElement('StoreID',$StoreID);
			$xml->writeElement('Passphrase',$Passphrase);
			$xml->writeElement('Subtotal',$this->_basket['total']);
			$xml->writeElement('PaymentType','CC');
			$xml->writeElement('CardAction',$CardAction);
			$xml->writeElement('CardNumber',trim($_POST["cardNumber"]));
			$xml->writeElement('CardExpMonth',str_pad($_POST["expirationMonth"], 2, '0', STR_PAD_LEFT));
			$xml->writeElement('CardExpYear',substr($_POST["expirationYear"],2,2));
			$xml->writeElement('CardIDCode',1);
			$xml->writeElement('CardIDNumber',trim($_POST['cvc2']));
			$xml->writeElement('CustomerIP',get_ip_address());
			$xml->startElement('Item');
				$xml->writeElement('ItemID',$cart_order_id);
				$xml->writeElement('ItemDescription','Order #'.$cart_order_id);
				$xml->writeElement('ItemQty',1);
				$xml->writeElement('ItemPrice',$this->_basket['total']);
			$xml->endElement();
			$xml->writeElement('Bname',trim($_POST['firstName'])." ".$_POST['lastName']);
			$xml->writeElement('Baddress1',trim($_POST['addr1']));
			$xml->writeElement('Baddress2',trim($_POST['addr2']));
			$xml->writeElement('Bcity',trim($_POST['city']));
			$xml->writeElement('Bprovince',trim($_POST['state']));
			$xml->writeElement('Bpostalcode',trim($_POST['postcode']));
			$xml->writeElement('Bcountry',trim($_POST['country']));
			$xml->writeElement('Email',trim($_POST['emailAddress']));
		$xml->endElement();

		$data 		= $xml->getDocument();
		$urlparts 	= parse_url($url); ## e.g. [host] => dev.psigate.com [port] => 7989 [path] => /Messenger/XMLMessenger
		$request	= new Request($urlparts['host'].$urlparts['path'],"",$urlparts['port']);
		$request->setSSL();
		$request->setData($data);
		$resp		= $request->send();
		$xmldata = new SimpleXMLElement($resp);
		if($xmldata->Approved=="APPROVED") {
			$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
		} 
		## change for example "PSI-0103:The Credit Card Number is invalid." to array split on : to "The Credit Card Number is invalid."
		$humanError = explode(":", $xmldata->ErrMsg,2);
		$this->_result_message		= $humanError[1];

		$transData['notes']			= (!empty($humanError[1])) ? $humanError[1] : '';
		$transData['gateway']		= 'psiGate';
		$transData['order_id']		= $cart_order_id;
		$transData['trans_id']		= (!empty($xmldata->TransRefNumber)) ? $xmldata->TransRefNumber : '';
		$transData['amount']		= $this->_basket['total'];
		$transData['status']		= ucfirst(strtolower($xmldata->Approved));
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['extra']			= '';
		$order->logTransaction($transData);

		if($xmldata->Approved=="APPROVED"){
			httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		}

	}

	##################################################

	private function formatMonth($val) {
		return $val." - ".strftime("%b", mktime(0,0,0,$val,1 ,2009));
	}

	public function form() {
		
		## Process transaction
		if (isset($_POST['cardNumber'])) {
			$return	= $this->process();
		}
		// Display payment result message
		if (!empty($this->_result_message))	{
			$GLOBALS['gui']->setError($this->_result_message);
		}

		//Show Expire Months
		$selectedMonth	= (isset($_POST['expirationMonth'])) ? $_POST['expirationMonth'] : date('m');
		for($i = 1; $i <= 12; ++$i) {
			$val = sprintf('%02d',$i);
			$smarty_data['card']['months'][]	= array(
				'selected'	=> ($val == $selectedMonth) ? 'selected="selected"' : '',
				'value'		=> $val,
				'display'	=> $this->formatMonth($val),
			);
		}

		## Show Expire Years
		$thisYear = date("Y");
		$maxYear = $thisYear + 10;
		$selectedYear = isset($_POST['expirationYear']) ? $_POST['expirationYear'] : ($thisYear+2);
		for($i = $thisYear; $i <= $maxYear; ++$i) {
			$smarty_data['card']['years'][]	= array(
				'selected'	=> ($i == $selectedYear) ? 'selected="selected"' : '',
				'value'		=> $i,
			);
		}
		$GLOBALS['smarty']->assign('CARD', $smarty_data['card']);
		
		$smarty_data['customer'] = array(
			'first_name' => isset($_POST['firstName']) ? $_POST['firstName'] : $this->_basket['billing_address']['first_name'],
			'last_name'	 => isset($_POST['lastName']) ? $_POST['lastName'] : $this->_basket['billing_address']['last_name'],
			'email'      => isset($_POST['emailAddress']) ? $_POST['emailAddress'] : $this->_basket['billing_address']['email'],
			'add1'		 => isset($_POST['addr1']) ? $_POST['addr1'] : $this->_basket['billing_address']['line1'],
			'add2'		 => isset($_POST['addr2']) ? $_POST['addr2'] : $this->_basket['billing_address']['line2'],
			'city'		 => isset($_POST['city']) ? $_POST['city'] : $this->_basket['billing_address']['town'],
			'state'		 => isset($_POST['state']) ? $_POST['state'] : $this->_basket['billing_address']['state'],
			'postcode'	 => isset($_POST['postcode']) ? $_POST['postcode'] : $this->_basket['billing_address']['postcode']
		);

		$GLOBALS['smarty']->assign('CUSTOMER', $smarty_data['customer']);

		## Country list
		$countries	= $GLOBALS['db']->select('CubeCart_geo_country', false, false, array('name' => 'ASC'));
		if ($countries) {
			$currentIso = isset($_POST['country']) ? $_POST['country'] : $this->_basket['billing_address']['country_iso'];
			foreach ($countries as $country) {
				$country['selected']	= ($country['iso'] == $currentIso) ? 'selected="selected"' : '';
				$smarty_data['countries'][]	= $country;
			}
			$GLOBALS['smarty']->assign('COUNTRIES',$smarty_data['countries']);
		}
		## Check for custom template for module in skin folder
		$file_name = 'form.tpl';
		$form_file = $GLOBALS['gui']->getCustomModuleSkin('gateway', dirname(__FILE__), $file_name);
		$GLOBALS['gui']->changeTemplateDir($form_file);
		$ret = $GLOBALS['smarty']->fetch($file_name);
		$GLOBALS['gui']->changeTemplateDir();
		return $ret;
	}
}