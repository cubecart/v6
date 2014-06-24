<?php
if(!defined('CC_INI_SET')) die('Access Denied');
$amount 	= ($module['price_mode'] == 'subtotal') ? $this->_basket['subtotal'] : $this->_basket['total'];
$affCode	= sprintf('<!-- Begin OSIaffiliate Affiliate Tracker --><img src="http://staff.hostcontroladmin.com/demo_cube/sale.php?amount=%s&transaction=%s" height="0" width="0" border="0" />', $amount, $this->_basket['cart_order_id']);
