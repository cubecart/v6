<?php
$cache_defined = false;
foreach ($glob as $key => $value) {
	$config[] = sprintf("\$glob['%s'] = '%s';", $key, addslashes($value));
	if($key == 'cache') {
		$cache_defined = true;
	}
}

if(!$cache_defined) {

	if(class_exists('memcached')) {
		$cache_name = 'memcached';	
	} elseif(class_exists('Memcache')) {
		$cache_name = 'memcache';	
	} elseif (extension_loaded('APC') && ini_get('apc.enabled')) {
		$cache_name = 'apc';	
	} elseif (extension_loaded('XCache') && ini_get('xcache.size') > 0) {
		$enable_auth = ini_get('xcache.admin.enable_auth');
		if (!$enable_auth || $enable_auth == strtolower('off')) {
			$cache_name = 'xcache';
		} else {
			$cache_name = 'file';
		}
	} else {
		$cache_name = 'file';
	}

	$config[] =sprintf("\$glob['cache'] = '%s';", $cache_name);

	$config = sprintf("<?php\n%s\n?>", implode("\n", $config));
	rename($global_file, $global_file.'-'.date('Ymdgis').'.php');
	file_put_contents($global_file, $config);
}