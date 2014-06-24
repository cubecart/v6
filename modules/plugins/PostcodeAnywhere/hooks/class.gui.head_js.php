<?php
$capture_key =  $GLOBALS['config']->get('PostcodeAnywhere','capture_key');
$protocol = (CC_SSL) ? 's': '';
$head_js[] = '<link rel="stylesheet" type="text/css" href="http://services.postcodeanywhere.co.uk/css/address-3.20.css"><script type="text/javascript" src="http'.$protocol.'://services.postcodeanywhere.co.uk/js/address-3.20.js"></script>';
$GLOBALS['smarty']->assign('ADDRESS_LOOKUP', true);