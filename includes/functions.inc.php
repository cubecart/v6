<?php
if(!defined('CC_INI_SET')) die('Access Denied');

## AutoLoader - automatically load classes if not already included

include CC_ROOT_DIR.'/classes/autoloader.class.php';
Autoloader::autoload_register(array('Autoloader', 'autoload'));

/**
 * Fix broken serialized data from multibyte characters stored without UTF8
 *
 * @param string $data
 *
 * @return array
 */
function cc_unserialize($data) {
	$data = html_entity_decode($data, ENT_QUOTES, 'UTF-8');
	$data = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $data );
	$data = unserialize($data);
	return $data;
}

/**
 * Append DS to a path string (ie \ or /)
 *
 * @param string $path
 *
 * @return string
 */
function appendDS($path) {
	if (empty($path)) {
		return false;
	}

	//Make sure there isn't one
	if ($path[strlen($path) - 1] != '/' && $path[strlen($path) - 1] != '\\') {
		$path .= '/';
	}

	return $path;
}

/**
 * Print an array in a more readable format
 *
 * @param array $array
 *
 * @return string
 */
function cc_print_array($array) {
	if (is_array($array) && count($array) > 0) {
		$output = print_r($array, true);
		return '<pre>'.$output.'</pre>';
	} else {
		return 'No Data!';
	}
}

/**
 * Get writable chmod value
 *
 * @return string
 */
function chmod_writable() {
	if (!defined('CC_CHMOD')) {
		//Fall back mode
		$mode = 777;
		if (function_exists('posix_getuid') && function_exists('posix_getgid')) {
			//Same user
			if (fileowner(__FILE__) === posix_getuid()) {
				$mode = 755;
			//Same group
			} else if (filegroup(__FILE__) === posix_getgid()) {
				$mode = 775;
			}
		}

		if (is_writable(CC_CACHE_DIR)) {
			$tmpdir = CC_CACHE_DIR.'/'.'chmodtmp';
			mkdir($tmpdir, octdec((int)$mode));
			if (!file_exists($tmpdir)) {
				trigger_error('Error checking CHMOD', E_USER_ERROR);
			}
			if (!is_writable($tmpdir) && $mode != 777) {
				//Fall back
				$mode = 777;
			} else if (!is_writable($tmpdir)) {
				trigger_error('Cannot detect proper CHMOD', E_USER_ERROR);
			}
			rmdir($tmpdir);
		}

		define('CC_CHMOD', $mode);
	}

	return octdec((int)CC_CHMOD);
}

/**
 * Get the current page url
 *
 * @param array $excluded
 * @param array $included
 * @param bool $remove_excluded
 *
 * @return string
 */
function currentPage($excluded = null, $included = null, $remove_excluded = true) {
	
	static $base = null;

	if (is_null($base)) {
		$php_self = htmlentities($_SERVER['PHP_SELF']); // fixes XSS

		if (isset($GLOBALS['storeURL'], $GLOBALS['rootRel'])) {
			$base = $GLOBALS['storeURL'].str_replace($GLOBALS['rootRel'], '/', $php_self);
		} else {
			$base = null;
		}
	}

	$currentPage = $base;
	// If there are GET variables, strip redir and rebuild query string
	if (!empty($_GET)) {
		$array = (is_array($included) && !empty($included)) ? array_merge($_GET, $included) : $_GET;
		
		$one_time = array('added', 'completed', 'deleted', 'edited', 'failed', 'removed', 'subscribed', 'submitted', 'unsubscribed', 'updated', session_name());
		if ($excluded === true) {
			// Drop *all* $_GET vars, except $protected
			$protected	= array('_a');
			$excluded	= array();
			foreach ($array as $key => $val) {
				if (!in_array($key, $protected)) {
					$excluded[] = $key;
				}
			}
		} else {
			$excluded	= (is_array($excluded) && !empty($excluded)) ? $excluded : array();
		}

		// Delete unwanted keys
		if (!empty($array)) {
			foreach ($excluded as $key) {
				if (isset($array[$key])) {
					unset($array[$key]);
					if (!CC_IN_ADMIN && $remove_excluded) {
						unset($_GET[$key]); // fix for other areas that want exclusion
					}
				}
			}
			array_walk_recursive($array, 'custom_urlencode', $one_time);
			if (isset($array) && is_array($array)) {
				$currentPage .= '?'.http_build_query($array, '', '&');
			}
		}
	}

	// Make it SEO friendly
	if (Config::getInstance()->get('config', 'seo')) {
		// $_GET['seo_path'] should never be set... but if it is this will fix it
		if(isset($_GET['seo_path']) && !empty($_GET['seo_path'])) {
			$currentPage = SEO::getInstance()->getItem($_GET['seo_path'], true);
		}
		return SEO::getInstance()->SEOable($currentPage);
	}
	return $currentPage;
}

/**
 * URL encode a atring
 *
 * @param string $item
 * @param trash $key
 * @param trash $one_time_keys
 *
 * @return string
 */
function custom_urlencode($item, $key, $one_time_keys) {
	$item = urlencode(html_entity_decode(stripslashes($item)));
	return $item;
}

/**
 * Large file downloads - thanks to php.net and contributors
 *
 * @param string $path
 * @param string $localFile
 * @param string $data
 * @param string $fileName
 *
 * @return file data
 */
function deliverFile($path, $localFile = true, $data = null, $fileName = null) {
	## Move this to the order class
	$GLOBALS['debug']->supress();
	if (@ob_get_length()) {
		@ob_end_clean();
	}
	if (function_exists('apache_setenv')) {
		@apache_setenv('no-gzip', 1);
	}
	@ini_set('zlib.output_compression', 0);
	if ($localFile) {
		if (!is_file($path) || connection_status() != CONNECTION_NORMAL) return false;
		$fileName = empty($fileName) ? basename($path) : $fileName;
		$fileLength = filesize($path);
	} else {
		$fileLength = strlen($data);
	}
	$fileName = str_replace(' ','_',$fileName);
	header('Pragma: public');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
	header('Expires: '.gmdate('D, d M Y H:i:s', mktime(date('H')+2, date('i'), date('s'), date('m'), date('d'), date('Y'))).' GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Content-Type: application/octet-stream');
	header("Content-type: application/force-download");
	header('Content-Length: '.(string)$fileLength);
	header('Content-Disposition: inline; filename='.(string)$fileName);
	header('Content-Transfer-Encoding: binary');
	## IE 7 Fix
	header('Vary: User-Agent');

	if ($localFile) {
		if (($file = fopen($path, 'rb')) !== false) {
			while (!feof($file) && (connection_status()==0)) {
				print(fread($file, 1024*8));
				flush();
			}
			fclose($file);
		}
		return (!connection_status() && !connection_aborted());
	} else {
		echo $data;
	}
}

/**
 * Has GD
 *
 * @return bool
 */
function detectGD() {
	return (extension_loaded('gd') && function_exists('gd_info'));
}

/**
 * Recursively get the size of a directory
 *
 * @param unknown_type $path
 * @param unknown_type $total
 *
 * @return float
 */
function dirsize($path, &$total) {
	$path	= (substr($path, -1, 1) == '/') ? $path : $path.'/';
	if (($files = glob($path.'*')) !== false) {
		foreach ($files as $file) {
			if (is_dir($file)) {
				dirsize($file, $total);
			} else {
				$total += filesize($file);
			}
		}
	}
	return formatBytes($total, true);
}

/**
 * Find files
 *
 * @param array $list
 * @param string $path
 * @param bool $recursive
 */
function findFiles(&$list, $path = false, $recursive = true) {
	$path .= (substr($path, -1) == '/') ? '' : '/';
	if (file_exists($path)) {
		$files	= glob($path.'*', GLOB_MARK);
		foreach ($files as $file) {
			if (is_dir($file) && $recursive) {
				if (preg_match('#(source|thumbs)[\\\/]$#', $file)) {
					continue;
				}
				findFiles($list, $file, $recursive);
			} else {
				$list[]	= $file;
			}
		}
	}
}

/**
 * Format a byte number
 *
 * @param float $bytes
 * @param bool $implode
 *
 * @return array
 */
function formatBytes($bytes = 0, $implode = false, $decimal_places = 2) {
	$size = $bytes / 1024;
	$ext = 'bytes';
	if ($size < 1024) {
		$size = number_format($size, $decimal_places);
		$ext  = 'KB';
	} else {
		if ($size / 1024 < 1024) {
			$size = number_format($size / 1024, $decimal_places);
			$ext  = 'MB';
		} else if ($size / 1024 / 1024 < 1024) {
			$size = number_format($size / 1024 / 1024, $decimal_places);
			$ext  = 'GB';
		}
	}
	if ($implode) {
		return $size.' '.$ext;
	}

	return array('size' => $size, 'suffix' => $ext);
}

/**
 * Format time
 *
 * @param string $timestamp
 * @param bool $format
 * @param bool $dynamic
 *
 * @return string/false
 */
function formatTime($timestamp, $format = false, $static = false) {
	if (empty($timestamp)) {
		return false;
	}

	## Convert a timestamp to something legible
	if (!$format) {
		$format = $GLOBALS['config']->get('config', 'time_format');
	}
	$sign	= substr($GLOBALS['config']->get('config', 'time_offset'), 0, 1);
	$value	= substr($GLOBALS['config']->get('config', 'time_offset'), 1);
	if ($sign == '+') {
		$seconds = $timestamp+$value;
	} else if ($sign == '-') {
		$seconds = $timestamp-$value;
	} else {
		$seconds = $timestamp;
	}
	$fuzzy = true;
	if(!$date_today	= strftime('%D', time())) {
		$fuzzy = false;
	}
	$date = strftime('%D', $seconds);
	$fuzzy_time = $GLOBALS['config']->get('config', 'fuzzy_time_format');
	if(empty($fuzzy_time)) $fuzzy_time = '%H:%M';
	$time = strftime($fuzzy_time, $seconds);
	if ($fuzzy && !$static && $date_today == $date) { ## Today
		return $GLOBALS['language']->common['today'].", ".$time;
	} elseif ($fuzzy && !$static && strftime("%D", strtotime('yesterday')) == $date) { ## Yesterday
		return $GLOBALS['language']->common['yesterday'].", ".$time;
	} else {
		return strftime($format, $seconds);
	}
}

/**
 * Format Dispatch Date
 *
 * @param string $timestamp
 * @param bool $format
 *
 * @return string/false
 */
function formatDispatchDate($date, $format = '%b %d %Y') {

	if (empty($date)) {
		return false;
	}

	$seconds = strtotime($date);

	$format = $GLOBALS['config']->get('config', 'dispatch_date_format') ? $GLOBALS['config']->get('config', 'dispatch_date_format') : $format;

	return strftime($format, $seconds);
}

function generate_product_code($product_name, $cat_id = false) {
	$chars = array(
		'A','B','C','D','E','F','G','H','I','J','K','L','M',
		'O','N','P','Q','R','S','T','U','V','W','X','Y','Z',
		'0','1','2','3','4','5','6','7','8','9'
	);
	$max_chars = count($chars) - 1;
	for ($i = 0; $i < 5; ++$i) {
		$randChars = ($i == 0) ? $chars[mt_rand(0, $max_chars)] : $randChars . $chars[mt_rand(0, $max_chars)];
	}
	if (!$cat_id) {
		$cat_id = mt_rand(0,99);
	}
	$product_code = strtoupper(substr($product_name, 0, 3)).$randChars.(int)$cat_id;
	// Check it's not already in use
	if (($query	= $GLOBALS['db']->select('CubeCart_inventory', 'product_id', array('product_code' => $product_code))) !== false) {
		//If it is make it again
		$product_code = generate_product_code($product_name, $cat_id);
	}

	return $product_code;
}

/**
 * Get country format
 *
 * @param string $input
 * @param string $match
 * @param string $fetch
 *
 * @return string
 */
function getCountryFormat($input, $match = 'numcode', $fetch = 'name') {
	$country = $GLOBALS['db']->select('CubeCart_geo_country', array($fetch), array($match => $input));
	return ($country) ? utf8_encode($country[0][$fetch]) : false;
}

/**
 * Get state format
 *
 * @param string $input
 * @param string $match
 * @param string $fetch
 *
 * @return string
 */
function getStateFormat($input, $match = 'id', $fetch = 'name') {
	if (($county = $GLOBALS['db']->select('CubeCart_geo_zone', false, array($match => $input))) !== false) {
//		return ($fetch == 'abbrev' && empty($county[0][$fetch])) ? $county[0]['name'] : utf8_encode($county[0][$fetch]);
		return ($fetch == 'abbrev' && empty($county[0][$fetch])) ? $county[0]['name'] : $county[0][$fetch];
	}
	return $input;
}

/**
 * Get the user's IP address
 *
 * @return string
 */
function get_ip_address() {
	//Try the apache headers if possible since they can't be spoofed as easily
	$headers = array();
	if (function_exists('apache_request_headers')) {
		$headers = apache_request_headers();
	}

	//If not
	if (!isset($headers['X-Forwarded-For']) || empty($headers['X-Forwarded-For']) || strtolower($_SERVER['X-Forwarded-For']) == 'unknown') {
		//Try the other possible locations
		if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && !empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && strtolower($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) != 'unknown') {
			$address = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		} else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strtolower($_SERVER['HTTP_X_FORWARDED_FOR']) != 'unknown') {
			$address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if (isset($_SERVER['HTTP_CLIENT_IP'])&& !empty($_SERVER['HTTP_CLIENT_IP']) && strtolower($_SERVER['HTTP_X_FORWARDED_FOR']) != 'unknown') {
			$address = $_SERVER['HTTP_CLIENT_IP'];
		} else if (isset($_SERVER['REMOTE_ADDR'])&& !empty($_SERVER['REMOTE_ADDR']) && strtolower($_SERVER['REMOTE_ADDR']) != 'unknown') {
			$address = $_SERVER['REMOTE_ADDR'];
		} else {
			$address = '';
		}
	} else {
		$address = $headers['X-Forwarded-For'];
	}

	// Remove port if it exists
	$parts = explode(':',$address);
	$address = (empty($parts[0])) ? $address : $parts[0];
	// Remove second IP
	unset($parts);
	$parts = explode(',',$address);
	$address = trim((empty($parts[0])) ? $address : $parts[0]);

	//Try to validate the IP
	if ((filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) === false && (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) === false) {
		return false;
	}

	return $address;
}

/**
 * Get all files and folders for a directory recursively
 *
 * @return array
 */
function glob_recursive($pattern, $flags = 0) {
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
    }
    return $files;
}

/**
 * Has ioncube
 *
 * @return bool
 */
function has_ioncube_loader() {
	return (bool)extension_loaded('ionCube Loader');
}

/**
 * Redirect to a page
 *
 * @param string $destination
 * @param string $anchor
 * @param bool $meta_refresh
 */

function httpredir($destination = '', $anchor = '', $meta_refresh = false, $status = 302) {
	if (empty($destination)) {
		$destination = currentPage();
	}

	## We could have just used header('Location: *'), but then we wouldn't be able to sanitize the requests
	## Check for spoofing
	## Remove multiple slashes (i.e. '//' becomes '/')

	$base = '';
	$destination	= preg_replace('#([^:])/{2,}#', '$1/', urldecode($destination));
	$destination	= str_replace('amp;', '', html_entity_decode($destination, ENT_COMPAT, 'UTF-8'));

	if (preg_match('#^http#i', $destination)) {
		$URL = parse_url($destination);
		$base = sprintf('%s://%s',$URL['scheme'],$URL['host'],$URL);
	}
	//SEO Redirect
	if (!preg_match('#^https#i', $destination) && isset($GLOBALS['seo']) && $GLOBALS['seo'] instanceof SEO) { // added !preg_match('#^https#i', $destination) to prevent SEO lookup on SSL redirect to basket breaking on shared SSL like https://xxx.xxx.co.uk/yyy.co.uk/index.php?_a=basket
		// make the seo class rewrite the URL
		$rewrite = $GLOBALS['seo']->rewriteUrls(sprintf('href="%s"', $destination));
		if (preg_match('#href="(.+)"#i', $rewrite, $match)) {
			$destination	= preg_match('#^http#i', $match[1]) ? $match[1] : $base.$match[1];
		}
		if(!CC_IN_ADMIN && !strstr($destination, '.') && substr($destination, 0, 1)!=='?') { // Add .html if for some reason its missing!!
			$destination = $destination.'.html';
		}
	}
	
	// Redirect - appending the last tab anchor for extra cleverness
	if (!empty($anchor)) {
		$destination .= '#'.$anchor;
	} else if (isset($_POST['previous-tab'])) {
		$destination	.= (preg_match('/^#/', $_POST['previous-tab'])) ? $_POST['previous-tab'] : '#'.$_POST['previous-tab'];
	}
	
	## Now we'll send the redirect header using one method or another
	$destination = filter_var($destination, FILTER_UNSAFE_RAW);
	## Nasty HTML meta refresh required to lose domain masking for certain payment modules
	if ($meta_refresh) {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Redirecting&hellip;</title>
<meta http-equiv="Refresh" content="0;URL='.$destination.'" />
</head>
<body>
</body>
</html>';
	} else {
		// Prefered PHP header redirect
		header('Location: '.$destination, true, $status);
	}
	exit;
}

/**
 * Recursively merges 2 arrays while keeping the structure
 *
 * @param array $first
 * @param array $second
 *
 * @return array
 */
function merge_array($first, $second) {
	//Quickly merger the array if nothing is in the first array
	if ((empty($first) || !is_array($first)) && !empty($second)) {
		return $second;
	}

	if (!empty($second) && is_array($second)) {
		//Used the key for loop since it is a tad faster than foreach
		$key = array_keys($second);
		$size = sizeOf($key);
		for ($i = 0; $i < $size; ++$i) {
			if (isset($first[$key[$i]])) {
				$first[(string)$key[$i]] = (is_array($second[$key[$i]])) ? array_merge($first[$key[$i]], $second[$key[$i]]) : $second[$key[$i]];
			} else {
				$first[(string)$key[$i]] = $second[$key[$i]];
			}
		}
		unset($second, $key);
	}

	return $first;
}

/**
 * Check that the Module/Skin/Plugin/etc is compatible with the current version
 *
 * @param string $min
 * @param string $max
 *
 * @return bool
 */
function moduleVersion($min = false, $max = false) {
	if (!empty($min) && !empty($max)) {
		$max = str_replace('*', 999, $max);	## We can safely assume we'll ever reach a minor/maintenance this high
		if (version_compare(CC_VERSION, $min, '>=') && version_compare(CC_VERSION, $max, '<=')) {
			return true;
		}
	}
	return false;
}

/**
 * Take the store offline?
 */
function offline() {
	## Check if store should be offline or not
	if ($GLOBALS['config']->get('config', 'offline')) {
		## Only show offline content if no admin session or admin is not allowed to view store front
		if (!Admin::getInstance()->is()) {
			$offlineContent = stripslashes($GLOBALS['config']->get('config', 'offline_content')); // No needs to base64_decode as the main config is already plain since 5.1.1
			$offlineFiles = glob('offline.{php,htm,html,txt}', GLOB_BRACE);
			if (!empty($offlineFiles) && is_array($offlineFiles)) {
				foreach ($offlineFiles as $file) {
					include $file;
					break;
				}
			} else {
				echo $offlineContent;
			}
			## Load 'offline' hooks
			foreach ($GLOBALS['hooks']->load('offline') as $hook) include $hook;
			exit;
		} else {
			$GLOBALS['smarty']->assign('STORE_OFFLINE',true); 
		}
	}
}

/**
 * Sort by prince
 *
 * @param array $x
 * @param array $y
 *
 * @return 0/1
 */
function price_sort($x, $y) {
	if ($x['value'] < $y['value']) {
		return -1;
	} else if ($x['value'] == $y['value']) {
		return 0;
	}
	return 1;
}

/**
 * Recursive delete
 *
 * @param string $path
 *
 * @return bool
 */

function recursiveDelete($path) {
	if (is_dir($path)) {
		$files	= glob($path.'/'.'*');
		foreach ($files as $file) {
			if (is_dir($file)) {
				recursiveDelete($file);
			} else if (is_file($file)) {
				unlink($file);
			}
		}
		return rmdir($path);
	} else if (is_file($path)) {
		return unlink($path);
	}
}

/**
 * Sanitize a variable
 *
 * @param string $text
 *
 * @return string
 */
function sanitizeVar($text) {
	## Sanitize GET variables to prevent XSS attacks
	return htmlspecialchars($text, ENT_COMPAT);
}

/**
 * Sanitize SEO allowed path
 *
 * @return string
 */
function sanitizeSEOPath($path) {
	## Remove extention
	$path = preg_replace("/\.\w{2,4}$/", '', $path);
	## Make path lowercase
	$path = strtolower($path);
	## Allow 0-9, a-z, -,_ and /
	$path = preg_replace('/[^a-z0-9-_\/]/', '-', $path);
	## Trim multiple dashes
	return trim(preg_replace('/-+/', '-', $path), '-');
}

/**
 *
 * @param float $value
 * @param int $figures
 */
function sigfig($value, $figures = 2) {
	$exponent		= floor(log10($value) + 1);
	$significant	= $value / pow(10, $exponent);
	$significant	= ceil($significant * pow(10, $figures)) / pow(10, $figures);
	return $significant * pow(10, $exponent);
}

/**
 * Create state json
 *
 * @return json
 */
function state_json() {

	## Generate a JSON string for state selector
	if (($json = $GLOBALS['cache']->read('json.states')) === false) {
		$counties = $GLOBALS['db']->query('SELECT gc.numcode, gz.id, gz.name FROM `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_geo_zone` AS `gz` LEFT JOIN `'.$GLOBALS['config']->get('config', 'dbprefix').'CubeCart_geo_country` AS `gc` ON gc.id=gz.country_id ORDER BY gc.name, gz.name ASC');
		$json_array = array();
		if ($counties) {
			$current = '';
			foreach ($counties as $state) {
				if ($current != $state['numcode']) {
					$json_array[$state['numcode']][] = array('id' => '0', 'name' => '-- '.$GLOBALS['language']->common['please_select'].' --');
					$current = $state['numcode'];
				}
//				$json_array[$state['numcode']][] = array('id' => $state['id'], 'name' => utf8_encode($state['name'])); // data already utf-8
				$json_array[$state['numcode']][] = array('id' => $state['id'], 'name' => $state['name']);
			}

			$json = json_encode($json_array);
			$GLOBALS['cache']->write($json, 'json.states');
		}
	}
	return $json;
}

/**
 * Recursive implode array
 * kromped at yahoo dot com @ php.net
 *
 * @param string $glue
 * @param array $pieces
 * @return string
 */
function recursive_implode($glue, $pieces) {
	if (is_array($pieces)) {
		foreach ($pieces as $r_pieces){
			if (is_array($r_pieces)) {
				$ret[] = recursive_implode($glue, $r_pieces);
			} else {
				$ret[] = $r_pieces;
			}
		}
	}
	return implode($glue, $ret);
}

/**
 * Get root path above public_html e.g. /home/user/public_html to /home/user
 *
 * @return string
 */
function rootHomePath() {
	return str_replace(array('public_html', 'htdocs'), '', str_replace(substr($GLOBALS['rootRel'],0,-1), '', CC_ROOT_DIR));
}

/**
 * Create a valid html string
 *
 * @param string $var
 *
 * @return string
 */
function validHTML($var) {
	## Create W3C compliant output
	$var = html_entity_decode($var, ENT_QUOTES, 'UTF-8');
	$var = htmlspecialchars($var);
	return str_ireplace("&amp;#39;", "&#39;", $var);
}

/**
 * Tidies up the messy ubuntu/debian/et al versioning (i.e. 5.2.4-2ubuntu5.3 becomes 5.2.4)
 *
 * @param string $version
 *
 * @return string
 */
function version_clean($version) {
	return substr($version, 0, strpos($version, '-'));
}

/**
 * URL safe base64 encoding
 *
 * @param string $data
 *
 * @return string
 */
function base64url_encode($data) {
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * URL safe base64 decoding
 *
 * @param string $data
 *
 * @return string
 */
function base64url_decode($data) {
  return base64_decode(strtr($data, '-_', '+/'));
  //return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}
/**
 * Custom product optionsarray sorting
 *
 * @param array $a
 * @param array $b
 *
 * @return integer
 */
function cmpmc($a, $b) {

	$b = $b['priority'];
	$a = $a['priority'];

	return $a<$b ? -1 : ($a>$b ? 1 : 0);
}
/**
 * Recursive Diff
 *
 * @param array $aArray1
 * @param array $aArray2
 *
 * @return diff array
 */
function arrayRecursiveDiff($aArray1, $aArray2) { 
    $aReturn = array(); 
   
    foreach ($aArray1 as $mKey => $mValue) { 
        if (array_key_exists($mKey, $aArray2)) { 
            if (is_array($mValue)) { 
                $aRecursiveDiff = arrayRecursiveDiff($mValue, $aArray2[$mKey]); 
                if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; } 
            } else { 
                if ($mValue != $aArray2[$mKey]) { 
                    $aReturn[$mKey] = $mValue; 
                } 
            } 
        } else { 
            $aReturn[$mKey] = $mValue; 
        } 
    } 
   
    return $aReturn; 
} 