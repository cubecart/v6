<?php
/* Chronopay malforms the return URL so we have to bring it to a *static* URL. */
$_GET = array(
	'_g' => 'rm',
	'type' => 'gateway',
	'cmd' => 'process',
	'module' => 'Realex'
);

require('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'index.php');