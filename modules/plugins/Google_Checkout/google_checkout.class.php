<?php
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