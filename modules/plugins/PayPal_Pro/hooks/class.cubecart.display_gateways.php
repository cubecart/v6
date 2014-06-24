<?php
/*
$Date: 2010-06-08 17:11:38 +0100 (Tue, 08 Jun 2010) $
$Rev: 1169 $
*/
# Enable specific gateways for Website Payments Pro (Post-Checkout)
$settings	= $GLOBALS['config']->get('PayPal_Pro');
if ($settings['status']) {
	if (isset($_POST['gateway'])) {
		switch ($_POST['gateway']) {
			case 'PayPal_Pro_Direct':
				## Direct Payments
				$gateways[0]	= array(
					'plugin'	=> true,
					'base_folder' => 'PayPal_Pro',
					'folder'	=> 'PayPal_Pro',
					'desc'		=> 'Direct Payments',
					'wpp_mode'	=> 'DP',
				);
				break;
			case 'PayPal_Pro_Mark':
				## Mark payments
				$gateways[0]	= array(
					'plugin'	=> true,
					'base_folder' => 'PayPal_Pro',
					'folder'	=> 'PayPal_Pro',
					'desc'		=> 'Mark Payments',
					'wpp_mode'	=> 'MP',
				);
				break;
			case 'PayPal_Pro_Hosted':
				## Mark payments
				$gateways[0]	= array(
					'plugin'	=> true,
					'base_folder' => 'PayPal_Pro',
					'folder'	=> 'PayPal_Pro',
					'desc'		=> 'Hosted Payment',
					'wpp_mode'	=> 'HP',
				);
				break;
		}
	} else {
		## Which options are being displayed?
		switch (true) {
			case ((int)$settings['mode'] == 1 && (int)$GLOBALS['config']->get('config','store_country') == 840):
				## Direct Payments only (US only!)
				$gateways[99]	= array(
					'plugin'	=> true,
					'base_folder' => 'PayPal_Pro',
					'folder'	=> 'PayPal_Pro_Direct',
					'desc'		=> 'modules/plugins/PayPal_Pro/images/cards_us.png',
				#	'help'		=> '',
				);
				break;
			case ((int)$settings['mode'] == 2):
				## Express Checkout only (Use Mark)
				$gateways[99]	= array(
					'plugin'	=> true,
					'base_folder' => 'PayPal_Pro',
					'folder'	=> 'PayPal_Pro_Mark',
					'desc'		=> 'modules/plugins/PayPal_Pro/images/PayPal_mark_36x23.png',
				#	'help'		=> '',
				);
				break;
			case ((int)$settings['mode'] == 4):
				## PayPal Pro Hosted (UK Only!)
				$gateways[99]	= array(
					'plugin'	=> true,
					'base_folder' => 'PayPal_Pro',
					'folder'	=> 'PayPal_Pro_Hosted',
					'desc'		=> 'modules/plugins/PayPal_Pro/images/hosted_pro.png',
				#	'help'		=> '',
				);
				break;
			case ((int)$settings['mode'] == 3):
			default:
				switch ((int)$GLOBALS['config']->get('config','store_country')) {
					case 124:
						$code	= 'ca';
						break;
					case 826:
						$code	= 'uk';
						break;
					case 840:
					default:
						$code	= 'us';
				}
				## Website Payments Pro
				$gateways[98]	= array(
					'plugin'	=> true,
					'base_folder' => 'PayPal_Pro',
					'folder'	=> 'PayPal_Pro_Mark',
					'desc'		=> 'modules/plugins/PayPal_Pro/images/PayPal_mark_36x23.png',
				#	'help'		=> 'https://www.paypal.com/'.$code.'/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside',
				);
				$gateways[99]	= array(
					'plugin'	=> true,
					'base_folder' => 'PayPal_Pro',
					'folder'	=> 'PayPal_Pro_Direct',
					'desc'		=> 'modules/plugins/PayPal_Pro/images/cards_'.$code.'.png',
				#	'help'		=> '',
				);
		}
	}
}