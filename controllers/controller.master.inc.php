<?php
if (!isset($glob['encoder']) || $glob['encoder']=='ioncube' || $glob['encoder']=='zend') {
	$loader_version = @ioncube_loader_version();

	if (version_compare($loader_version, '4.4') >= 0) {
		switch (CC_PHP_ID) {
		case 54:
			require_once CC_ROOT_DIR.'/controllers/ioncube_4.4/php_5.4.php';
			break;
		case 53:
			require_once CC_ROOT_DIR.'/controllers/ioncube_4.4/php_5.3.php';
			break;
		case 52:
			require_once CC_ROOT_DIR.'/controllers/ioncube_4.4/php_5.2.php';
		}
	} elseif (version_compare($loader_version, '4.0') >= 0) {
		switch (CC_PHP_ID) {
		case 54:
		case 53:
			require_once CC_ROOT_DIR.'/controllers/ioncube_4.0/php_5.3.php';
			break;
		case 52:
			require_once CC_ROOT_DIR.'/controllers/ioncube_4.0/php_5.2.php';
		}
	} else {
		require_once CC_ROOT_DIR.'/controllers/ioncube_3.1/php_5.2.php';
	}
} elseif (isset($glob['encoder'])) {
	switch ($glob['encoder']) {
	case 'obfuscated': // Load Obfuscated file
		require_once CC_ROOT_DIR.'/CubeCart_obfuscated.php';
		break;
	default:
	case 'plain': // For internal development only
		require_once CC_ROOT_DIR.'/CubeCart_plain.php';
	}
}