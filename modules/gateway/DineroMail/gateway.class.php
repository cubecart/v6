<?php
class Gateway {
	private $_config;
	private $_module;
	private $_basket;
	private $_dm_set_lang;
	
	private $_dm_lang = array(
		1 => array('es' => 'Número de cuenta', 'pt' => 'Número da conta'),
		2 => array('es' => 'País', 'pt' => 'País'),
		3 => array('es' => 'Moneda', 'pt' => 'Moeda'),
		4 => array('es' => 'Medios de pago que acepta(opcional)', 'pt' => 'Meios de pagamento que aceita(opcional)'),
		5 => array('es' => 'Valores numéricos separados por coma, para obtener información sobre los códigos de los medios de pago consulte el manual de venta de DineroMail', 'pt' => 'Valores numéricos separados por vírgulas, Para obter informações sobre os códigos de os meios de pagamento ver o manual de venda de DinheiroMail'),
		6 => array('es' => 'Incluir el logo de su sitio(opcional)', 'pt' => 'Incluir o logo do seu site (opcional)'),
		7 => array('es' => '¿Quieres que tus compradores puedan incluir <br />un mensaje cuando pagan?(opcional)', 'pt' => 'O comprador pode deixar <br/>uma mensagem ao vendedor(opcional)'),
		8 => array('es' => '¿Quieres que tus compradores puedan incluir <br />la dirección de entrega del producto?(opcional)', 'pt' => 'O comprador pode indicar o endereço <br/> de entrega do produto(opcional)'),		
		9 => array('es' => 'logo.gif', 'pt' => 'logo-pt.gif'),
		10 => array('es' => 'La forma segura de pagar y recibir pagos online', 'pt' => 'A forma segura de pagar e receber pagamentos online'),
		11 => array('es' => 'DineroMail', 'pt' => 'DinheiroMail'),
		12 => array('es' => 'Orden: ', 'pt' => 'Ordem: '),
		13 => array('es' => 'Estado de la orden cuando esta pendiente', 'pt' => 'Estado da ordem quando esta pendente'),
		14 => array('es' => 'Estado de la orden cuando el proceso fue exitoso en DineroMail', 'pt' => 'Estado da ordem quando o processo foi exitoso em DinheiroMail'),
		15 => array('es' => 'Estado de la orden cuando el proceso fracaso en DineroMail', 'pt' => 'Estado da ordem quando o processo fracasso em DinheiroMail'),
		16 => array('es' => 'Opciones de configuración', 'pt' => 'Opções de configuração'),
		17 => array('es' => 'Descripción', 'pt' => 'Descrição'),
		18 => array('es' => 'Error Fatal! No es posible procesar la orden', 'pt' => 'Erro Fatal! Não é possível processar a ordem'),
		19 => array('es' => 'Proceso exitoso en los servidores de DineroMail', 'pt' => 'Processo exitoso nos servidores de DinheiroMail'),
		20 => array('es' => 'Fracaso el proceso en los servidores de DineroMail', 'pt' => 'Fracasso o processo nos servidores de DinheiroMail'),
		21 => array('es' => 'La orden fue procesada anteriormente', 'pt' => 'A ordem foi processada anteriormente')
	);

	public function __construct($module = false, $basket = false) {
		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
		$this->_dm_set_lang = ($this->_module['dm_country'] == 'BR') ? 'pt' : 'es';
	}

	##################################################

	public function transfer() {
		
		$dm_action = array (
			'AR' => 'https://argentina.dineromail.com/shop/shop_ingreso.asp', 
			'BR' => 'https://brasil.dineromail.com/dinero-tools/login/shop/shop_ingreso.asp',
			'CL' => 'https://chile.dineromail.com/shop/shop_ingreso.asp',
			'MX' => 'https://mexico.dineromail.com/shop/shop_ingreso.asp'
		);
		
		$transfer	= array(
			'action'	=> $dm_action[$this->_module['dm_country']],
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
	
		$dm_currency_set = ($this->_module['dm_currency']=='US') ? 2 : 1;
		
		$hash = md5('a6105c0a611b41b08f1209506350279e'.$this->_module['dm_account'].$this->_basket['cart_order_id']);
		
		$hidden	= array(
			'NombreItem' 		=> $this->_dm_lang[12][$this->_dm_set_lang].$orderSum['cart_order_id'],
			'TipoMoneda' 		=> $dm_currency_set,
			'PrecioItem' 		=> $this->_basket['total'],
			'E_Comercio' 		=> $this->_module['dm_account'],
			'NroItem' 			=> $this->_basket['cart_order_id'],
			'trx_id' 			=> $this->_basket['cart_order_id'],
			'MediosPago' 		=> $this->_module['dm_payment_methods'],
			'image_url' 		=> $this->_module['dm_store_logo_url'],
			'DireccionExito' 	=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=DineroMail&cart_order_id='.$this->_basket['cart_order_id'].'&dm_status='.$hash,
			'DireccionFracaso' 	=> $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=process&module=DineroMail&cart_order_id='.$this->_basket['cart_order_id'].'&dm_status='.$hash,
			'DireccionEnvio' 	=> $this->_module['dm_delivery_addres'],
			'Mensaje' 			=> $this->_module['dm_message']
		);
		
		return (isset($hidden)) ? $hidden : false;
	}

	##################################################

	public function call() {
		return false;
	}

	public function process() {
				
		$order				= Order::getInstance();
		$cart_order_id		= $_GET['cart_order_id'];
		$order_summary		= $order->getSummary($cart_order_id);
		
		$dm_status_yes = md5('a6105c0a611b41b08f1209506350279e'.$this->_module['dm_account'].$cart_order_id);
		$dm_status_not = md5('d529e941509eb9e9b9cfaeae1fe7ca23'.$this->_module['dm_account'].$cart_order_id);
		
		if($dm_status_yes == $_GET['dm_status']) {
			$notes 	= $this->_dm_lang[19][$this->_dm_set_lang];
			$status = 'Processed';
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
		} elseif($dm_status_not == $_GET['dm_status']) { /* cancelled */
			$notes 	= $this->_dm_lang[19][$this->_dm_set_lang];
			$status = 'Cancelled';
			$order->orderStatus(Order::ORDER_CANCELLED, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_CANCEL, $cart_order_id);
		} else {
			$notes = 'Card has not yet been processed and is currently pending.';
			$status = 'Pending';
			$order->orderStatus(Order::ORDER_PENDING, $cart_order_id);
			$order->paymentStatus(Order::PAYMENT_PENDING, $cart_order_id);
		}

		$transData['notes']			= $notes;
		$transData['gateway']		= $_REQUEST['module'];
		$transData['order_id']		= $cart_order_id;
		$transData['trans_id']		= $cart_order_id;
		$transData['amount']		= $order_summary['total'];
		$transData['status']		= $status;
		$transData['customer_id']	= $order_summary['customer_id'];
		$transData['extra']			= '';
		$order->logTransaction($transData);

		// Redirect to _a=complete, and drop out unneeded variables
		httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
	}

	public function form() {
		return false;
	}
}