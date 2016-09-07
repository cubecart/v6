<?php
header('Content-Type: text/css');
## Condense all plugin stylesheets into one file for faster transfer (and a better YSlow score)
$search		= dirname(__FILE__).DIRECTORY_SEPARATOR.'jquery.*.css';
$plugins	= glob($search, GLOB_BRACE);
if ($plugins && is_array($plugins)) {
	foreach ($plugins as $plugin) {
		echo file_get_contents($plugin);
	}
}