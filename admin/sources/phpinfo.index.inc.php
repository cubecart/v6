<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}

Admin::getInstance()->permissions('settings', CC_PERM_EDIT, true);
$GLOBALS['main']->addTabControl('PHP Info', 'php_info');
$GLOBALS['gui']->addBreadcrumb('PHP Info', '?_g=phpinfo', true);

ob_start();
phpinfo();
preg_match('%<style type="text/css">(.*?)</style>.*?<body>(.*?)</body>%s', ob_get_clean(), $matches);
$page_content = "<div class='phpinfodisplay tab_content' id='php_info'><style type='text/css'>\n";
$page_content .= join(
    "\n",
    array_map(
        function ($i) {
            return ".phpinfodisplay " . preg_replace("/,/", ",.phpinfodisplay ", $i);
        },
        preg_split(
            '/\n/',
            trim(
                preg_replace("/\nbody/", "\n", $matches[1])
            )
        )
    )
);
$page_content .= "</style>\n".$matches[2]."\n</div>\n";
