<?php
/* Chronopay malforms the return URL so we have to bring it to a *static* URL. */
$_GET = array(
	'_g' => 'rm',
	'type' => 'gateway',
	'cmd' => 'call',
	'module' => 'Chronopay'
);
require('../../../index.php');