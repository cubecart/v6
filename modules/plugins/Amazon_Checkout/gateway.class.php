<?php
class Gateway {

	private $_module;

	##################################################

	public function __construct($module = false) {
		$this->_module	= $module;
	}

	public function __destruct() {
		## Nowt
	}

	##################################################

	public function transfer() {
		return false;
	}

	public function repeatVariables() {
		return false;
	}

	public function fixedVariables() {
		## This functionality has been moved to 'cart.gateways' hook
		return false;
	}

	##################################################

	public function call() {
		
		if($_POST['AWSAccessKeyId']!==$this->_module['access_key']) {
			die('Access Denied');
		}
		
		$order	= Order::getInstance();
		
		if($_POST['NotificationType']=='NewOrderNotification') {	
			
			$data = new SimpleXMLElement(stripslashes($GLOBALS['RAW']['POST']['NotificationData']));
		
			$cart_order_id = $order->createOrderId(true, false);
			
			$sub_total 	= 0;
			$shipping 	= 0;
			$discount 	= 0;
			
			foreach($data->ProcessedOrder->ProcessedOrderItems->ProcessedOrderItem as $item) {
				
				$custom_data = json_decode(base64_decode($item->ItemCustomData),true);
				$total_tax = (float)$custom_data['total_tax']; // This is a bit on the filthy side but works well
				
				$order_inventory = array(
					'name' => (string)$item->Title,
					'price' => (float)$custom_data['total_price_each'],
					'quantity' => (int)$item->Quantity,
					'product_code' => (string)$item->SKU,
					'cart_order_id' => $cart_order_id,
					'product_options' => base64_decode($custom_data['product_options']),
				);
				
				$GLOBALS['db']->insert('CubeCart_order_inventory',$order_inventory);
				
				foreach($item->ItemCharges->Component as $component) {
					if((string)$component->Type=='PrincipalPromo') {
						$discount += (float)$component->Charge->Amount;
					} elseif((string)$component->Type=='Shipping') {
						$shipping += (float)$component->Charge->Amount;
					}
				}

				$sub_total += (float)$custom_data['total_price_each']*(int)$item->Quantity;
			}
			
			
			$name_parts = explode(' ',(string)$data->ProcessedOrder->ShippingAddress->Name);
			
			$last_name = '';
			
			foreach($name_parts as $name_part) {
				if(!isset($first_name)) {
					$first_name = $name_part;
				} else {
					$last_name .= $name_part.' '; 
				}
			}
			
			$total = ($sub_total+$shipping+$total_tax)-$discount;
			
			$billing_country = getCountryFormat((string)$data->ProcessedOrder->ShippingAddress->CountryCode, 'iso', 'numcode');
			
			$order_summary = array(		
				'cart_order_id' 		=> $cart_order_id,	 	 
				'order_date' 			=> strtotime((string)$data->ProcessedOrder->OrderDate),
				'customer_id' 			=> $custom_data['customer_id'],
				'status' 				=> 1,
				'subtotal' 				=> $sub_total,
				'discount' 				=> $discount,
				'shipping' 				=> $shipping,
				'total_tax' 			=> $total_tax,
				'total' 				=> $total, 	 
				'ship_method' 			=> (string)$data->ProcessedOrder->DisplayableShippingLabel,
				'ship_date' 			=> null,
				'ship_tracking' 		=> null,
				'gateway' 				=> (string)$data->ProcessedOrder->OrderChannel, 	 
				'title' 				=> null,	 
				'first_name' 			=> $first_name, 	 	 
				'last_name' 			=> $last_name, 	 	 
				'company_name' 			=> null, 	 
				'line1' 				=> (string)$data->ProcessedOrder->ShippingAddress->AddressFieldOne,	 
				'line2' 				=> (string)$data->ProcessedOrder->ShippingAddress->AddressFieldTwo, 	 
				'town' 					=> (string)$data->ProcessedOrder->ShippingAddress->City, 	 	 
				'state' 				=> (string)$data->ProcessedOrder->ShippingAddress->State, 	 	 
				'postcode' 				=> (string)$data->ProcessedOrder->ShippingAddress->PostalCode, 	 	 
				'country' 				=> $billing_country, 
				'title_d' 				=> null, 	 	 
				'first_name_d' 			=> $first_name, 
				'last_name_d' 			=> $last_name, 	 
				'company_name_d' 		=> null, 	 
				'line1_d' 				=> (string)$data->ProcessedOrder->ShippingAddress->AddressFieldOne,	 
				'line2_d' 				=> (string)$data->ProcessedOrder->ShippingAddress->AddressFieldTwo, 	 
				'town_d' 				=> (string)$data->ProcessedOrder->ShippingAddress->City, 	 	 
				'state_d' 				=> (string)$data->ProcessedOrder->ShippingAddress->State, 	 	 
				'postcode_d' 			=> (string)$data->ProcessedOrder->ShippingAddress->PostalCode, 	 	 
				'country_d' 			=> $billing_country, 	 	 
				'phone' 				=> null, 	  	 
				'email' 				=> (string)$data->ProcessedOrder->BuyerInfo->BuyerEmailAddress, 	 
				'customer_comments' 	=> null,	 
				'ip_address' 			=> get_ip_address(),
				'dashboard' 			=> null, 	 
				'discount_type' 		=> null,	 
				'basket' 				=> null, 	 
				'lang' 					=> $GLOBALS['config']->get('config', 'default_language')
			);
			if(is_array($custom_data['order_taxes'])) {
				foreach($custom_data['order_taxes'] as $order_tax) {
					$GLOBALS['db']->insert('CubeCart_order_tax',array('cart_order_id' => $cart_order_id, 'tax_id' => $order_tax['tax_id'], 'amount' => $order_tax['amount']));
				}
			}
			$GLOBALS['db']->insert('CubeCart_order_summary',$order_summary);
			$GLOBALS['db']->insert('CubeCart_order_history',array('cart_order_id' => $cart_order_id, 'status' => 1, 'updated' => time()));

			$transData['notes']			= 'This order is not ready to ship. Order Acknowledgement submission ID '.$feedSubmissionId;
			$transData['order_id']		= $cart_order_id;
			$transData['trans_id']		= (string)$data->ProcessedOrder->AmazonOrderID;
			$transData['amount']		= $total;
			$transData['status']		= "Payment Uncleared";
			$transData['customer_id']	= $custom_data['customer_id'];
			$transData['extra']			= '';
			$transData['gateway']		= (string)$data->ProcessedOrder->OrderChannel;
			$order->logTransaction($transData);
			
		} elseif(!empty($_POST['NotificationType']) && in_array($_POST['NotificationType'], array('OrderCancelledNotification','OrderReadyToShipNotification'))) {
			
			$data = new SimpleXMLElement(stripslashes($GLOBALS['RAW']['POST']['NotificationData']));
			$past_data = $this->getOrderTrans((string)$data->ProcessedOrder->AmazonOrderID);
			
			if($_POST['NotificationType']=='OrderCancelledNotification') {
				$order->orderStatus(Order::ORDER_CANCELLED, $past_data['order_id']);
				$order->paymentStatus(Order::PAYMENT_CANCEL, $past_data['order_id']);
				
				$transData['notes']			= 'Order cancelled, do not ship.';
				$transData['status']		= 'Cancelled';
				
			} elseif ($_POST['NotificationType']=='OrderReadyToShipNotification') {
				$order->orderStatus(Order::ORDER_PROCESS, $past_data['order_id']);
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $past_data['order_id']);
				
				$transData['notes']			= 'Order now ready to ship.';
				$transData['status']		= 'Payment Cleared';
				
					require_once('modules/plugins/Amazon_Checkout/library/MarketplaceWebService/config.inc.php'); 
	
				$feeds = new MarketplaceWebService_MWSFeedsClient();
				$MWSProperties = new MarketplaceWebService_MWSProperties();
				
				$envelope = new SimpleXMLElement("<AmazonEnvelope></AmazonEnvelope>");
				$envelope->Header->DocumentVersion = $MWSProperties->getDocumentVersion();
				$envelope->Header->MerchantIdentifier = $MWSProperties->getMerchantToken();
		
				$envelope->MessageType = "OrderAcknowledgement";
			
				$envelope->Message[0] ->MessageID = 1;
				$envelope->Message[0] ->OrderAcknowledgement->AmazonOrderID = (string)$data->ProcessedOrder->AmazonOrderID;
				$envelope->Message[0] ->OrderAcknowledgement->MerchantOrderID = $cart_order_id;
				$envelope->Message[0] ->OrderAcknowledgement->StatusCode = "Success";
				
				$feedSubmissionId = $feeds->acknowledgeOrder($envelope, 'cache');
				
			} 
			
			$transData['order_id']		= $past_data['order_id'];
			$transData['trans_id']		= (string)$data->ProcessedOrder->AmazonOrderID;
			$transData['amount']		= $past_data['amount'];
			$transData['customer_id']	= 0;
			$transData['extra']			= '';
			$transData['gateway']		= (string)$data->ProcessedOrder->OrderChannel;
			
			$order->logTransaction($transData);
		}
	}

	public function process() {
		return false;
	}

	public function form() {
		return false;
	}
	
	public function getOrderTrans($needle, $from = 'amazon') {
		$where = ($from == 'amazon') ? array('trans_id'=> $needle) : array($from => $needle);
		$order_data = $GLOBALS['db']->select('CubeCart_transactions', '*',$where);
		return $order_data[0];
	}
}