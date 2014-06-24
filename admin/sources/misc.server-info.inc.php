<?php
if (!defined('CC_INI_SET')) die('Access Denied');

Admin::getInstance()->permissions('settings', CC_PERM_EDIT, true);
$GLOBALS['main']->addTabControl('PHP Info', 'php_info');

ob_start();
phpinfo();
preg_match('%<style type="text/css">(.*?)</style>.*?<body>(.*?)</body>%s', ob_get_clean(), $matches);
$page_content = "<div class='phpinfodisplay tab_content' id='php_info'><style type='text/css'>\n";
$page_content .= join("\n",
	array_map(
		create_function('$i', 'return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );'),
		preg_split('/\n/', trim(
				preg_replace("/\nbody/", "\n", $matches[1])
			)
		)
	)
);
$page_content .= "</style>\n".$matches[2]."\n</div>\n";