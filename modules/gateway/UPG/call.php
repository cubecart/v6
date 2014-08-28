<?php
$get_merge = array(
	'_g' => 'rm',
	'type' => 'gateway',
	'cmd' => 'call',
	'module' => 'UPG'
);
$_GET = array_merge($_GET,$get_merge);
require('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'index.php');