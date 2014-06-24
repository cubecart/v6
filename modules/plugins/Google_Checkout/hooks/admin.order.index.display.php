<?php
/*
if (isset($summary[0]['gateway']) && $summary[0]['gateway'] == 'Google_Checkout' && isset($history)) {
	$main->addTabControl($language->google['module_title'], 'google_checkout');
	$google	= $GLOBALS['config']->get('Google_Checkout');
	$tmpl	= new ACP('admin.order.display.tpl', dirname(__FILE__));
*/
	## Order Processing API
	/*
	if (is_array($history)) {
		foreach ($history as $trans_id => $data) {
			$tmpl->assign('OPT', $data);
			## Enable methods
		#	$json[$data['transaction']['id']]	= array(
		#		'status'	=> $data['response']['PAYMENTSTATUS'],													## Current trasaction status
		#		'methods'	=> (is_array($methods)) ? implode(',', $methods) : $methods,							## Available methods
		#		'amount'	=> number_format($data['transaction']['amount']-$data['transaction']['captured'], 2),	## Transaction value
		#	);
			$tmpl->parse('main.transactions.transaction');
		}
		if (isset($json)) $tmpl->assign('JSON_TRANSACTIONS', json_encode($json));
		$tmpl->parse('main.transactions');
	}
	*/
	/*
	$tmpl->assign('GOOGLE_ORDER_ID', $google_order_id);
	$tmpl->parse();
	$vars['plugins_tabs'][]	= $tmpl->display();
}
*/
