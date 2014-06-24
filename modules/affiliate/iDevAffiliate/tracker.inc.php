<?php
if(!defined('CC_INI_SET')) die('Access Denied');
$amount 	= ($module['price_mode'] == 'subtotal') ? $this->_basket['subtotal'] : $this->_basket['total'];
$affCode	= sprintf('<!-- iDevAffiliate tracking code --><img src="%s/sale.php?idev_cube_1=%s&idev_cube_2=%s" width="0" height="0" border="0" alt="" />', $module['URL'], $amount, $this->_basket['cart_order_id']);
