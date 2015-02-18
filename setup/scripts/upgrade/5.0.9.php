<?php
## fix any broken SEO paths
if ($seo_paths = $db->select('CubeCart_seo_urls', array('id', 'path'))) {
	foreach ($seo_paths as $seo_path) {
		$db->update('CubeCart_seo_urls', array('path' => sanitizeSEOPath($seo_path['path'])), array('id' => $seo_path['id']));
	}
}