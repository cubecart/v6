ALTER TABLE `CubeCart_admin_log` ADD `item_id` INT UNSIGNED NULL DEFAULT NULL AFTER `description`; #EOQ
ALTER TABLE `CubeCart_admin_log` ADD `item_type` VARCHAR(4) NULL DEFAULT NULL AFTER `item_id`; #EOQ
ALTER TABLE `CubeCart_request_log` ADD `request_headers` BLOB NULL AFTER `time`; #EOQ
ALTER TABLE `CubeCart_request_log` ADD `response_headers` BLOB NULL AFTER `request_headers`; #EOQ
ALTER TABLE `CubeCart_inventory_language` ADD `description_short` TEXT NOT NULL AFTER `description`; #EOQ
CREATE TABLE IF NOT EXISTS `CubeCart_404_log` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `uri` varchar(510) COLLATE utf8mb3_unicode_ci NOT NULL,
  `hits` int UNSIGNED NOT NULL DEFAULT '1',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `uri` (`uri`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ