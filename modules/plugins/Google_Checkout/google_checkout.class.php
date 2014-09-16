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
class Google_Checkout {

	private $_merchant_key;
	private $_merchant_id;

	private $_cart_xml;

	public function __construct() {

	}

	public function __destruct() {

	}

	public function checkoutXML() {

	}

	private function signature($data = null) {
		if (!is_null($data)) {
			return hash_hmac('sha1', $data, $this->_merchant_key, true);
		}
		return false;
	}

}
?>