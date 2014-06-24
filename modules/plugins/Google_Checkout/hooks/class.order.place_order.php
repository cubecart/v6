<?php
if (isset($_GET['module']) && $_GET['module'] == 'Google_Checkout') {
	$this->_email_enabled = false;
	$force_order = true;
}