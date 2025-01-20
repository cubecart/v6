<?php
// Get distinct logs
if($logs = $db->misc("SELECT DISTINCT `log_hash`, `log` FROM `".$glob['dbprefix']."CubeCart_cookie_consent` GROUP BY `log_hash`;")) {
    $map = array();
    // Create new table for log
    $db->parseSchema('CREATE TABLE `CubeCart_cookie_consent_text` (`id` int UNSIGNED NOT NULL,`hash` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,`log` text COLLATE utf8mb4_unicode_ci NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ');
    // Add keys for table
    $db->parseSchema('ALTER TABLE `CubeCart_cookie_consent_text` ADD PRIMARY KEY (`id`), ADD KEY `hash` (`hash`); #EOQ');
    $db->parseSchema('ALTER TABLE `CubeCart_cookie_consent_text` MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1; #EOQ');
    // Insert logs
    foreach($logs as $log) {
        $id = $db->insert('CubeCart_cookie_consent_text', array('log' => $log['log'], 'hash' => $log['log_hash']));
        $map[$log['log_hash']] = $id;
    }
    // Add ID to replace log and hash
    $db->parseSchema('ALTER TABLE `CubeCart_cookie_consent` ADD `dialogue_id` INT UNSIGNED NOT NULL AFTER `customer_id`, ADD INDEX (`dialogue_id`); #EOQ');
    // Replace log and hash with ID
    foreach($map as $hash => $id) {
        $db->misc("UPDATE `".$glob['dbprefix']."CubeCart_cookie_consent` SET `dialogue_id` = $id WHERE `log_hash` = '".$hash."'");
    }
    // Drop the bloat
    $db->parseSchema('ALTER TABLE `CubeCart_cookie_consent` DROP `log`; #EOQ');
    $db->parseSchema('ALTER TABLE `CubeCart_cookie_consent` DROP `log_hash`; #EOQ');
    $db->update('CubeCart_cookie_consent',"`url_shown` = REPLACE(`url_shown`,'".CC_STORE_URL."/','')");
}