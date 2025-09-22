<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2025. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
$GLOBALS['main']->addTabControl($lang['settings']['release_notes'], 'general');
$GLOBALS['gui']->addBreadcrumb($lang['settings']['release_notes'], currentPage(array('node')), true);
$features = array( 
	'GHSA-qfrx-vvvp-h5m2' => 'HTML Injection in Product Reviews Allows Malicious Links and Defacement',
    'GHSA-5hg3-m3q3-v2p4' => 'Stored/Reflected HTML Injection in Contact Enquiry — Admin Receives Raw HTML',
    'GHSA-869v-gjv8-9m7f' => 'Unauthorized Victim Newsletter Unsubscription via force_unsubscribe Parameter',
    'GHSA-4vwh-x8m2-fmvv' => 'Session Not Invalidated After Password Change'
);
$security = array();
$page_content = $GLOBALS['main']->newFeatures($_GET['node'], $features, 12, '', $security);