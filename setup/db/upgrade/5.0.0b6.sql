DELETE FROM `CubeCart_hooks` WHERE `plugin` IN ('Google_Checkout','PayPal_Pro'); #EOQ

ALTER TABLE `CubeCart_sessions` DROP `language`; #EOQ
ALTER TABLE `CubeCart_sessions` DROP `currency`; #EOQ
ALTER TABLE `CubeCart_sessions` DROP `skin`; #EOQ
ALTER TABLE `CubeCart_sessions` DROP `basket`; #EOQ
ALTER TABLE `CubeCart_sessions` DROP `data`; #EOQ
  
ALTER TABLE `CubeCart_customer` ADD `new_password` TINYINT( 1 ) NOT NULL DEFAULT '1'; #EOQ

UPDATE `CubeCart_customer` SET `new_password` = 0; #EOQ

ALTER TABLE `CubeCart_customer` CHANGE `password` `password` VARCHAR( 128 ) NULL DEFAULT NULL; #EOQ

ALTER TABLE `CubeCart_admin_users` ADD `new_password` TINYINT( 1 ) NOT NULL DEFAULT '1'; #EOQ

UPDATE `CubeCart_admin_users` SET `new_password` = 0; #EOQ

ALTER TABLE `CubeCart_admin_users` CHANGE `password` `password` VARCHAR( 128 ) NULL DEFAULT NULL; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_admin_error_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `read` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

UPDATE  `CubeCart_modules` SET  `folder` =  'SagePay' WHERE  `folder` = 'Protx'; #EOQ
UPDATE  `CubeCart_config` SET  `name` =  'SagePay' WHERE  `name` = 'Protx'; #EOQ

UPDATE  `CubeCart_modules` SET  `folder` =  'OptimalPayments' WHERE  `folder` = 'optimal'; #EOQ
UPDATE  `CubeCart_config` SET  `name` =  'OptimalPayments' WHERE  `name` = 'optimal'; #EOQ

UPDATE  `CubeCart_modules` SET  `folder` =  'BarclayCard' WHERE  `folder` = 'ePDQ'; #EOQ
UPDATE  `CubeCart_config` SET  `name` =  'BarclayCard' WHERE  `name` = 'ePDQ'; #EOQ

ALTER TABLE `CubeCart_order_summary` ADD `discount_type` CHAR( 1 ) NOT NULL DEFAULT 'f'; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_system_error_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) unsigned NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `read` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_request_log` (
  `request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `request` blob NOT NULL,
  `result` blob NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE  `CubeCart_geo_country` CHANGE  `printable_name`  `name` VARCHAR( 80 ) NOT NULL DEFAULT  ''; #EOQ

CREATE TABLE `CubeCart_seo_urls` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`type` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
	`item_id` int(25) unsigned DEFAULT NULL,
  PRIMARY KEY (`path`),
  KEY `id` (`id`),
  KEY `type` (`type`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; #EOQ