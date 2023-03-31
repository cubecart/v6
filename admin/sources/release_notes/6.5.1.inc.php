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
$GLOBALS['main']->addTabControl($lang['settings']['release_notes'], 'general');
$GLOBALS['gui']->addBreadcrumb($lang['settings']['release_notes'], currentPage(array('node')), true);

$elastic = <<<END
    <p><img src="./{$GLOBALS['config']->get('config', 'adminFolder')}/skins/{$GLOBALS['config']->get('config', 'admin_skin')}/images/logo.elasticsearch.png" alt="Elasticsearch" /></p>
	<p>6.5.0 - Getting your products in front of your customers is critical. Elasticsearch brings lightening fast, search-as-you-type functionality to your store. This is included as standard with official <a href="https://hosted.cubecart.com/" target="_blank">CubeCart Hosting</a>.<br>Alternatively please contact your hosting company to check for availability. To configure and enable Elasticsearch please update your store <a href="?_g=settings#Advanced_Settings">settings</a>.</p>
	<p>For more information talk to us at <a href="mailto:hello@cubecart.com">hello@cubecart.com</a>.</p>
	<h4>Example:</h4>
	<video width="750" loop="true" autoplay="autoplay" controls muted>
		<source src="./{$GLOBALS['config']->get('config', 'adminFolder')}/skins/{$GLOBALS['config']->get('config', 'admin_skin')}/media/movie.elasticsearch.mp4" type="video/mp4">
	</video>
END;

$features = array( 
	'2600' => $elastic,
	'3218' => '6.5.0 - Release notes added to CubeCart to showcase new features',
	'3213' => '6.5.0 - Tumblr & Reddit socials icons added',
	'3105' => '6.5.0 - Debug output to modal window to prevent page output interruption',
	'3186' => '6.5.0 - Large page breaks added to product, customer and order pages to compensate for removed &quot;View All&quot;',
	'3220' => '6.5.1 - Escape key to close and clear search results',
);
$notes = '<p>This release patches a critical bug in 6.5.0 see Github <a href="https://github.com/cubecart/v6/issues/3219" target="_blank">#3219</a>. Some of the features below have been available since the 6.5.0 milestone.</p>';
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 3, $notes);
?>