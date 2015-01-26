<?php
header('Content-Type: text/javascript');
## Condense all plugins into one file for faster transfer (and a better YSlow score)
$search		= dirname(__FILE__).DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'*.js';
$plugins	= glob($search, GLOB_BRACE);
if ($plugins && is_array($plugins)) {
	foreach ($plugins as $plugin) {
		echo "\n\n/* ".basename($plugin)." */\n\n";
		echo file_get_contents($plugin);
	}
}