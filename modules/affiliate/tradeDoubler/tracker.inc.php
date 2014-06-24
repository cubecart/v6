<?php
if(!defined('CC_INI_SET')) die('Access Denied');
$amount = ($module['price_mode'] == 'subtotal') ? $this->_basket['subtotal'] : $this->_basket['total'];
$affCode	= '<!-- TradeDoubler tracking code --><img src="http://tbs.tradedoubler.com/report?organization='.$module['organization'].'&event='.$module['event'].'&orderNumber='.$this->_basket['cart_order_id'].'&orderValue='.$amount.'&currency='.$GLOBALS['config']->get('config', 'default_currency').'" width="0" height="0" alt="" />';





