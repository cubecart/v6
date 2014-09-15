<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@devellion.com
 * License:  GPL-2.0 http://opensource.org/licenses/GPL-2.0
 */
if ($transaction['gateway'] == 'Google_Checkout') {
	switch ($transaction['status']) {
		case 'CHARGEABLE':
			if ($transaction['captured'] < $transaction['amount']) {
				$action[]	= array(
					'title'	=> $language->google['action_capture'],
					'icon'	=> 'arrow_in.png',
					'url'	=> currentPage(null, array('Google_Checkout' => 'Capture', 'transaction_id' => $transaction['id'])),
				);
			}
			break;
		case 'CHARGED':
			$action[]	= array(
				'title'	=> $language->google['action_refund'],
				'icon'	=> 'arrow_out.png',
				'url'	=> currentPage(null, array('Google_Checkout' => 'Refund', 'transaction_id' => $transaction['id'])),
			);
			break;
		case 'REFUNDED':
			# nothing
			break;
		default:
			## Nowt
	}
	$history[$transaction['id']]	= array(
		'transaction'	=> $transaction,
	);
	$google_order_id	= $transaction['trans_id'];
	if (isset($action) && is_array($action)) {
		$transaction['actions']	= $action;
		unset($data, $action);
	}
}