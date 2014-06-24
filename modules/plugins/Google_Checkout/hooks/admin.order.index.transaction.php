<?php
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