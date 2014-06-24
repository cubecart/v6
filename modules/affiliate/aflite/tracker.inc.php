<?php
if(!defined('CC_INI_SET')) die('Access Denied');
$testVar = ($module['testMode'] == 1) ? '&trace=1' : '';
$amount = ($module['price_mode'] == 'subtotal') ? $this->_basket['subtotal'] : $this->_basket['total'];
$affCode = '<!-- aflite tracking code --><img src="http://aflite.co.uk/modules/track/goal.php?value='.$amount.'&ref='.$this->_basket['cart_order_id'].'&mid='.$module['mid'].'&goalid='.$module['goalid'].$testVar.'" />';
?>