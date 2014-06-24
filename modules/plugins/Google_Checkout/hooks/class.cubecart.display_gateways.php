<?php
/* Google Checkout transfer */
if (isset($_GET['module']) && $_GET['module'] == 'Google_Checkout') {
	$google	= $GLOBALS['config']->get('Google_Checkout');
	if ($google['status']) {
		## Use server to server method - this will allow us to have a certified integration
		include 'library/googlecart.php';
		include 'library/googleitem.php';
		include 'library/googleshipping.php';
		include 'library/googletax.php';
		## Update Order Summary
		$GLOBALS['db']->update('CubeCart_order_summary', array('gateway' => 'Google_Checkout'), array('cart_order_id' => $this->_basket['cart_order_id']));

		## Generate cart xml data and signature
		$cart	= new GoogleCart($google['merchId'], $google['merchKey'], $google['mode'], $GLOBALS['config']->get('config','default_currency'));

		## Get Tax Classes
		if ($tax_classes = $GLOBALS['db']->select('CubeCart_tax_class')) {
			foreach ($tax_classes as $tax_class) {
				$class[$tax_class['id']] = strtolower(str_replace(' ', '_', $tax_class['tax_name']));
			}
		}
		## Tax rates
		if ($taxes = $GLOBALS['db']->select('CubeCart_tax_rates', false, array('active' => '1', 'country_id' => $GLOBALS['config']->get('config','store_country')))) {
			$country	= getCountryFormat($GLOBALS['config']->get('config','store_country'), 'numcode', 'iso');
			$tax_count	= count($taxes);
			if ($tax_count > 1) {
				foreach ($taxes as $tax) {
					$tax_rule	= new GoogleAlternateTaxRule(($tax['tax_percent']/100), 'true');
					$tax_rule->AddPostalArea($country);
					$tax_table_rules[$class[$tax['type_id']]][] = $tax_rule;
				}
				foreach ($tax_table_rules as $name => $rules) {
					$tax_table = new GoogleAlternateTaxTable($name, 'true');
					foreach ($rules as $rule) {
						$tax_table->AddAlternateTaxRules($rule);
					}
					$cart->AddAlternateTaxTables($tax_table);
				}
			} else {
				$tax	= $taxes[0];
				$tax_rule	= new GoogleDefaultTaxRule(($tax['tax_percent']/100), 'true');
				$tax_rule->AddPostalArea($country);
				$cart->AddDefaultTaxRules($tax_rule);
			}
		} else {
			$tax_count	= 0;
		}

		## Items
		foreach ($this->_basket['contents'] as $product) {
			//$tax	= new Tax();
			$GLOBALS['tax']->loadTaxes($GLOBALS['config']->get('config','store_country'));

			$price	= ($GLOBALS['tax']->salePrice($product['price'], $product['sale_price'], false)) ? $product['sale_price'] : $product['price'];
			$GLOBALS['tax']->productTax($price, $product['tax_type'], (bool)$product['tax_inclusive'], $GLOBALS['config']->get('config','store_zone'), 'goods');
			$taxes	= $GLOBALS['tax']->fetchTaxAmounts();
			$price	-= ($taxes['applied']+$taxes['included']);

			if ($item = new GoogleItem($product['name'], substr(strip_tags($product['description']), 0, 120).'&hellip;', $product['quantity'], $price, $GLOBALS['config']->get('config','product_weight_unit'), $product['product_weight'])) {
				$item->SetMerchantItemId($product['product_id']);
				if ($tax_count > 1) $item->SetTaxTableSelector($class[$product['tax_type']]);
				if ($product['digital']) {
					$url	= '%s/?_a=download&accesskey=%s';
					## Fetch download detail
					if ($downloads = $GLOBALS['db']->select('CubeCart_downloads', false, array('cart_order_id' => $this->_basket['cart_order_id'], 'product_id' => $product['product_id']))) {
						foreach ($downloads as $download) {
							$link	= sprintf($url, $GLOBALS['storeURL'], $download['accesskey']);
							$item->SetURLDigitalContent($link, $download['accesskey'], 'download link');
						}
					}
				}
				$cart->AddItem($item);
			}
		}
		## Coupons
		if (isset($this->_basket['coupons']) && is_array($this->_basket['coupons'])) {
			foreach ($this->_basket['coupons'] as $code => $coupon) {

				$value			= 0-$coupon['value'];
				$description	= null;

				if ($item = new GoogleItem($code, $description, 1, $value)) {
				#	$cart->AddItem($item);
				}
			}
		}
		## Shipping
		# Estimated shipping data

		#$default_shipping	= 10;

		if ($shipping = $GLOBALS['cart']->loadShippingModules()) {
			foreach ($shipping as $name => $values) {
				foreach ($values as $array) {
					#$ship	= new GoogleMerchantCalculatedShipping(str_replace('_', ' ', $name), $default_shipping);
			        $name2 =  ($array['name']) ? $array['name'] : $name;
					$ship	= new GoogleMerchantCalculatedShipping(str_replace('_', ' ', $name2), $array['value']);
					$filter = new GoogleShippingFilters();
					if ($restrict = $GLOBALS['db']->select('CubeCart_modules', array('countries'), array('module' => 'shipping', 'folder' => $name))) {
						$country = $restrict[0]['countries'];
						if (!empty($country) && ($countries = unserialize($country))) {
							foreach ($countries as $country) {
								$filter->AddAllowedPostalArea(getCountryFormat($country, 'numcode', 'iso'));
							}
						} else {
							$filter->SetAllowedWorldArea();
						}
					}
					$ship->AddAddressFilters($filter);
					$cart->AddShipping($ship);
				}
			}
		}


		## Google Analytics
		if (!empty($this->_config['google_analytics'])) $cart->AddGoogleAnalyticsTracking($this->_config['google_analytics']);
		$cart->SetContinueShoppingUrl($GLOBALS['storeURL'].'/?_a=complete');
		$cart->SetEditCartUrl($GLOBALS['storeURL'].'/?_a=basket');

		## Merchant Calculations API	
		$calculator	= sprintf('%s/index.php?_g=rm&type=gateway&cmd=call&module=Google_Checkout', $GLOBALS['config']->get('config','ssl_url'));

		$gift_certs	= $GLOBALS['config']->get('gift_certs');
		$cart->SetMerchantCalculations($calculator, 'true', 'true', (isset($gift_certs['status']) && $gift_certs['status']) ? 'true' : 'false');

		$cart->SetMerchantPrivateData(new MerchantPrivateData(array('cart-order-id' => $this->_basket['cart_order_id'])));

		## Fetch the cart XML
		$xml		= $cart->GetXML();

		## POST request to Google
		switch ($google['mode']) {
			case 'sandbox':
				$url  = 'sandbox.google.com';
				$path =	'/checkout/api/checkout/v2/request/Merchant/'.$google['merchId'];
				break;
			case 'live':
			case 'production':
			default:
				$url  = 'checkout.google.com';
				$path = '/api/checkout/v2/request/Merchant/'.$google['merchId'];
		}
		
		$request = new Request($url, $path, 443, false, true, 4, false);
		$request->authenticate($google['merchId'], $google['merchKey']);
		$request->setSSL(true,2);
		$request->customHeaders('Content-type: application/xml; charset=UTF-8');
		$request->customHeaders('Accept: application/xml; charset=UTF-8');
		$request->setData($xml);
		
		if ($response = $request->send()) {
			## Parse response
			$xml	= new SimpleXMLElement($response);
			if ((string)$xml->getName() === 'checkout-redirect') {
				## Redirect to Google
				httpredir((string)$xml->{'redirect-url'});
				exit;
			}
		} else {
			$GLOBALS['gui']->setError('Failed to connect to Google Checkout. Please use an alternative checkout method.');
			httpredir('index.php?_a=basket');
			exit;
		}
	}
}