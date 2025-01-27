<?php
// Create tables etc if not created on last update
$table_exists = $db->misc("SHOW TABLE STATUS LIKE '".$glob['dbprefix']."CubeCart_cookie_consent_text'");
if(empty($table_exists)) {
    $db->parseSchema('CREATE TABLE `CubeCart_cookie_consent_text` (`id` int UNSIGNED NOT NULL,`hash` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,`log` text COLLATE utf8mb4_unicode_ci NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ');
    $db->parseSchema('ALTER TABLE `CubeCart_cookie_consent_text` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `hash` (`hash`); #EOQ');
    $db->parseSchema('ALTER TABLE `CubeCart_cookie_consent_text` MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1; #EOQ');
    $db->parseSchema('ALTER TABLE `CubeCart_cookie_consent` ADD `dialogue_id` INT UNSIGNED NOT NULL AFTER `customer_id`, ADD INDEX (`dialogue_id`); #EOQ');
    $db->parseSchema('ALTER TABLE `CubeCart_cookie_consent` DROP `log`; #EOQ');
    $db->parseSchema('ALTER TABLE `CubeCart_cookie_consent` DROP `log_hash`; #EOQ');
}