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
if(!defined('CC_INI_SET')) die('Access Denied');
$module	= new Module(__FILE__, $_GET['module'], 'admin/index.tpl', true, false);

## Modes
$modes	= array(
	'sandbox'	=> $GLOBALS['language']->google_checkout['mode_sandbox'],
	'live'		=> $GLOBALS['language']->google_checkout['mode_live'],
);
foreach ($modes as $value => $title) {
	$modes_types[]	= array(
		'value'		=> $value,
		'title'		=> $title,
		'selected'	=> ($value == $module->mode) ? ' selected="selected"' : '',
	);
}

## Button Sizes
$sizes	= array(
	'small'		=> $lang['common']['small'].' (160px &times; 43px)',
	'medium'	=> $lang['common']['medium'].' (168px &times; 44px)',
	'large'		=> $lang['common']['large'].' (180px &times; 46px)',
);
foreach ($sizes as $value => $title) {
	$buttons[]	= array(
		'value'		=> $value,
		'title'		=> $title,
		'selected'	=> ($value == $module->size) ? ' selected="selected"' : '',
	);
}

if ($GLOBALS['config']->get('config','ssl')) {
	$template_vars = array(
		'API_URL'	=> $GLOBALS['config']->get('config','ssl_url').'/index.php?_g=remote&type=gateway&module=Google_Checkout&cmd=call',
		'show_ssl'	=> true
	);
}

$template_vars['modes'] = $modes_types;
$template_vars['buttons'] = $buttons;

$module->assign_to_template($template_vars);
$module->fetch();
$page_content = $module->display();