CREATE TABLE `CubeCart_newsletter_subscriber_log` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`log` text COLLATE utf8_unicode_ci,
	`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`ip_address` varchar(45) COLLATE utf8_unicode_ci DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD KEY (`custom_oid`); #EOQ
UPDATE `CubeCart_geo_country` SET `status` = 1 WHERE `iso` IN('AR', 'BR', 'CA', 'CN', 'ID', 'IN', 'JP', 'MX', 'TH', 'US') AND `status` = 2; #EOQ
ALTER TABLE `CubeCart_order_summary` DROP INDEX `custom_oid`; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD INDEX (`custom_oid`); #EOQ
ALTER TABLE `CubeCart_documents` ADD `doc_privacy` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_documents` ADD INDEX (`doc_privacy`); #EOQ
CREATE TABLE `CubeCart_cookie_consent` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `session_id` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `log` text DEFAULT NULL,
  `time` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ip_address` (`ip_address`),
  KEY `session_id` (`session_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ