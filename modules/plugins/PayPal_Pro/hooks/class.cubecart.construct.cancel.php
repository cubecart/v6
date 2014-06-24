<?php
if(!defined('CC_INI_SET')) die('Access Denied');
unset($this->_basket['PayPal_Pro']);
httpredir('index.php?_a=basket');
