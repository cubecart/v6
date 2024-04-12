<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
$GLOBALS['main']->addTabControl($lang['settings']['release_notes'], 'general');
$GLOBALS['gui']->addBreadcrumb($lang['settings']['release_notes'], currentPage(array('node')), true);

$list_view = <<<END
    <div>List view aded to filemanager.</div>
    <div><img src="./{$GLOBALS['config']->get('config', 'adminFolder')}/skins/{$GLOBALS['config']->get('config', 'admin_skin')}/media/issue.3543.png" style="width: 60%" alt="List view" /></div>
END;
$er_buffer = <<<END
<div>Exchange rate &quot;buffer&quot; with percentage adjustment.</div>
<div><img src="./{$GLOBALS['config']->get('config', 'adminFolder')}/skins/{$GLOBALS['config']->get('config', 'admin_skin')}/media/issue.3424.png" style="width: 60%" alt="Exchange rate buffer" /></div>
END;

$product_dates = <<<END
<div>Adjust product sales report by date.</div>
    <div><img src="./{$GLOBALS['config']->get('config', 'adminFolder')}/skins/{$GLOBALS['config']->get('config', 'admin_skin')}/media/issue.3392.png" style="width: 60%" alt="Exchange rate buffer" /></div>

END;

$features = array( 
	'3543' => $list_view,
	'3544' => 'Sorter added to filemanager for name, date added and filesize (see screenshot above).',
    '3536' => 'reCaptcha added to password recovery tool.',
    '3532' => 'Customer commets icon with link added to dashboard orders (unsettled orders) list.',
    '3525' => 'Bulk action to add/remove orders from dasboard (unsettled orders).',
    '3488' => 'Use of hooks to manipulate dashboard  (unsettled orders) bulk actions.',
    '3487' => 'Order list to have new &quot;Last Updated&quot; column with sorter.',
    '3447' => 'Preview icon on category and document list to view on front end.',
    '3427' => 'Switch to allow for product and category descriptions to be parsed via Smarty (for dynamic contnt).',
    '3425' => 'Improved character set support utf8mb3 to utf8mb4',
    '3424' => $er_buffer,
    '3418' => 'Order summary to show both custom order ID (if avaialable) and traditional oreder ID.',
    '3413' => 'Filemanager last location memory for product option images',
    '3392' => $product_dates,
    '3385' => 'Switch off order email whilst in PayPal Sandbox mode (PayPal Commerce 1.9.5+ required).'
);
$notes = '';
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 94, $notes);
?>