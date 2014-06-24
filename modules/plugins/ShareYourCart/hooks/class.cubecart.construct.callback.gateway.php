<?php
require_once(dirname(__FILE__).'/../class.shareyourcart-cubecart.php');
$cubecart = new ShareYourCartCubeCartPlugin();

switch ($_GET['action'])
{
	case 'buttonCallback':
		$cubecart->buttonCallBack();
		break;
	case 'couponCallback':
		$cubecart->couponCallback();
		break;
}
