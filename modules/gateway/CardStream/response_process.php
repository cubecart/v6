<?php
if(isset($_GET['VPResponseCode'])) {
	$controller_vars = array(
		'_g' => 'rm',
		'type' => 'gateway',
		'cmd' => 'process',
		'module' => 'CardStream'
	);
	$_GET = array_merge($controller_vars, $_GET);
}
include('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'index.php');