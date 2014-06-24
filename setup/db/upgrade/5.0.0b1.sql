-- Upgrade database structure from 4.3.x to 5.0.0

DROP TABLE IF EXISTS `CubeCart_admin_permissions`, `CubeCart_admin_sessions`, `CubeCart_blocker`, `CubeCart_SpamBot`; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_access_log` (
	`log_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`type` CHAR(1) NOT NULL,
	`time` INT UNSIGNED NOT NULL,
	`username` VARCHAR(100) NOT NULL,
	`user_id` INT UNSIGNED NOT NULL,
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'Supports IPv6 addresses',
	`useragent` TEXT NOT NULL,
	`success` ENUM('Y','N') NOT NULL,
	PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_addressbook` (
	`address_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`customer_id` INT UNSIGNED NOT NULL,
	`billing` ENUM('0','1') NOT NULL DEFAULT '0',
	`default` ENUM('0','1') NOT NULL DEFAULT '0',
	`description` VARCHAR(250) NOT NULL,
	`addressee` VARCHAR(100) NOT NULL,
	`title` VARCHAR(20) NOT NULL,
	`first_name` VARCHAR(250) NOT NULL,
	`last_name` VARCHAR(250) NOT NULL,
	`company_name` VARCHAR(200) NOT NULL,
	`line1` VARCHAR(200) NOT NULL,
	`line2` VARCHAR(200) NOT NULL,
	`town` VARCHAR(100) NOT NULL,
	`state` VARCHAR(100) NOT NULL,
	`postcode` VARCHAR(15) NOT NULL,
	`country` SMALLINT(3) UNSIGNED NOT NULL,
	PRIMARY KEY (`address_id`),
	KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

INSERT INTO `CubeCart_addressbook` (`customer_id`, `title`, `first_name`, `last_name`, `company_name`, `line1`, `line2`, `town`, `state`, `postcode`, `country`) (SELECT `customer_id`, `title`, `firstName`, `lastName`, `companyName`, `add_1`, `add_2`, `town`, `county`, `postcode`, `country` FROM `CubeCart_customer` WHERE `add_1` <> ''); #EOQ
UPDATE `CubeCart_addressbook` SET `billing` = '1', `default` = '1' WHERE 1; #EOQ

UPDATE `CubeCart_addressbook` AS `A`, `CubeCart_iso_countries` AS `C` SET `A`.`country` = `C`.`numcode` WHERE `A`.`country` = `C`.`id`; #EOQ

ALTER TABLE `CubeCart_admin_log` DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_admin_log` CHANGE `id` `log_id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_admin_log` ADD `admin_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_admin_log` CHANGE `time` `time` INT UNSIGNED NOT NULL; #EOQ
ALTER TABLE `CubeCart_admin_log` CHANGE `ipAddress` `ip_address` VARCHAR(45) NOT NULL; #EOQ
ALTER TABLE `CubeCart_admin_log` CHANGE `desc` `description` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_admin_log` DROP `user`; #EOQ
ALTER TABLE `CubeCart_admin_log` ADD PRIMARY KEY ( `log_id` ) ; #EOQ 
ALTER TABLE `CubeCart_admin_log` ADD INDEX `admin_id` ( `admin_id` ); #EOQ
ALTER TABLE `CubeCart_admin_log` DROP INDEX `id` ; #EOQ

DROP TABLE IF EXISTS `CubeCart_admin_sections`; #EOQ

ALTER TABLE `CubeCart_admin_users` DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `adminId` `admin_id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD `customer_id` INT UNSIGNED NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `password` `password` VARCHAR(40) NOT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `salt` `salt` VARCHAR(32) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `email` `email` VARCHAR(254) NOT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD `verify` VARCHAR(32) DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `noLogins` `logins` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `isSuper` `super_user` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `sessId` `session_id` VARCHAR(32) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `sessIp` `ip_address` VARCHAR(45) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD `language` VARCHAR(5) NOT NULL DEFAULT 'en-US'; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD `dashboard_notes` TEXT NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD `order_notify` TINYINT( 1 ) UNSIGNED NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `name` `name` VARCHAR( 150 ) NOT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `username` `username` VARCHAR( 150 ) NOT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` DROP INDEX `adminId` , ADD INDEX `admin_id` ( `admin_id` ); #EOQ 

UPDATE `CubeCart_admin_users` SET `status` = '1' WHERE 1; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_blocker` (
	`block_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`level` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
	`last_attempt` INT UNSIGNED NOT NULL DEFAULT '0',
	`ban_expires` INT UNSIGNED NOT NULL DEFAULT '0',
	`username` TEXT NOT NULL,
	`location` CHAR(1) NOT NULL,
	`user_agent` TEXT NOT NULL,
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'Supports IPv6 addresses',
	PRIMARY KEY  (`block_id`),
	KEY `location` (`location`),
	KEY `last_attempt` (`last_attempt`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_category` DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_category` CHANGE `cat_id` `cat_id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST; #EOQ
ALTER TABLE `CubeCart_category` CHANGE `cat_father_id` `cat_parent_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_category` CHANGE `cat_image` `cat_image` VARBINARY(250) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_category` CHANGE `priority` `priority` SMALLINT(6) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_category` ADD `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ
UPDATE `CubeCart_category` SET `status` = 0 WHERE `hide` = 1; #EOQ
ALTER TABLE `CubeCart_category` CHANGE `cat_metatitle` `seo_meta_title` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_category` CHANGE `cat_metadesc` `seo_meta_description` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_category` CHANGE `cat_metakeywords` `seo_meta_keywords` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_category` DROP `noProducts`; #EOQ
ALTER TABLE `CubeCart_category` DROP INDEX `cat_father_id` , ADD INDEX `cat_parent_id` ( `cat_parent_id` ); #EOQ 

ALTER TABLE `CubeCart_cats_idx` RENAME TO `CubeCart_category_index`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_category_index` CHANGE `productId` `product_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_category_index` ADD `primary` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ

UPDATE `CubeCart_category_index` SET `primary` = 1 WHERE `cat_id` = (SELECT `cat_id` FROM `CubeCart_category`); #EOQ

ALTER TABLE `CubeCart_inventory` CHANGE `productId` `product_id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
UPDATE `CubeCart_category_index` AS I INNER JOIN `CubeCart_inventory` AS C ON I.`cat_id` = C.`cat_id` SET I.`primary` = true WHERE I.`product_id` = C.`product_id` AND I.`cat_id` = C.`cat_id`; #EOQ

DELETE FROM `CubeCart_category_index` WHERE `cat_id` IN (SELECT `cat_id` FROM `CubeCart_category` WHERE `cat_desc` = '##HIDDEN##'); #EOQ
DELETE FROM `CubeCart_category` WHERE `cat_desc` = '##HIDDEN##'; #EOQ

ALTER TABLE `CubeCart_cats_lang` RENAME TO `CubeCart_category_language`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_category_language` CHANGE `id` `translation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST; #EOQ
ALTER TABLE `CubeCart_category_language` CHANGE `cat_master_id` `cat_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_category_language` CHANGE `cat_lang` `language` VARCHAR(5); #EOQ
ALTER TABLE `CubeCart_category_language` ADD `seo_meta_title` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_category_language` ADD `seo_meta_description` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_category_language` ADD `seo_meta_keywords` TEXT NOT NULL; #EOQ

ALTER TABLE `CubeCart_Coupons` RENAME TO `CubeCart_coupons_temp`; #EOQ
ALTER TABLE `CubeCart_coupons_temp` RENAME TO `CubeCart_coupons`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_coupons` CHANGE `id` `coupon_id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST; #EOQ
ALTER TABLE `CubeCart_coupons` ADD `archived` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_coupons` CHANGE `code` `code` VARCHAR(25) NOT NULL; #EOQ
ALTER TABLE `CubeCart_coupons` CHANGE `discount_percent` `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_coupons` CHANGE `product_id`  `product_id` TINYTEXT NULL; #EOQ
ALTER TABLE `CubeCart_coupons` CHANGE `discount_price` `discount_price` DECIMAL(16,2) NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_coupons` CHANGE `expires` `expires` DATE NOT NULL; #EOQ
ALTER TABLE `CubeCart_coupons` CHANGE `allowed_uses` `allowed_uses` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_coupons` ADD `min_subtotal` DECIMAL(16,2) UNSIGNED NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_coupons` CHANGE `count` `count` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_coupons` ADD `shipping` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_coupons` CHANGE `desc` `description` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_coupons` CHANGE `cart_order_id` `cart_order_id` VARCHAR(18) NULL DEFAULT NULL; #EOQ

ALTER TABLE `CubeCart_currencies` RENAME TO `CubeCart_currency`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_currency` CHANGE `currencyId` `currency_id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST; #EOQ
ALTER TABLE `CubeCart_currency` CHANGE `name` `name` VARCHAR(150) NOT NULL; #EOQ
ALTER TABLE `CubeCart_currency` CHANGE `code` `code` VARCHAR(3) NOT NULL; #EOQ
ALTER TABLE `CubeCart_currency` ADD `iso` INT(3) UNSIGNED ZEROFILL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_currency` CHANGE `symbolLeft` `symbol_left` VARCHAR(10) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_currency` CHANGE `symbolRight` `symbol_right` VARCHAR(10) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_currency` CHANGE `decimalPlaces` `decimal_places` TINYINT(2) UNSIGNED NULL DEFAULT '2'; #EOQ
ALTER TABLE `CubeCart_currency` CHANGE `lastUpdated` `updated` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_currency` CHANGE `active` `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_currency` CHANGE `decimalSymbol` `symbol_decimal` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ

UPDATE `CubeCart_currency` SET `iso` = '840' WHERE `code` = 'USD'; #EOQ
UPDATE `CubeCart_currency` SET `iso` = '826' WHERE `code` = 'GBP'; #EOQ
UPDATE `CubeCart_currency` SET `iso` = '978' WHERE `code` = 'EUR'; #EOQ
UPDATE `CubeCart_currency` SET `iso` = '392' WHERE `code` = 'JPY'; #EOQ
UPDATE `CubeCart_currency` SET `iso` = '124' WHERE `code` = 'CAD'; #EOQ
UPDATE `CubeCart_currency` SET `iso` = '036' WHERE `code` = 'AUD'; #EOQ
UPDATE `CubeCart_currency` SET `iso` = '756' WHERE `code` = 'CHF'; #EOQ
UPDATE `CubeCart_currency` SET `iso` = '643' WHERE `code` = 'RUB'; #EOQ
UPDATE `CubeCart_currency` SET `iso` = '156' WHERE `code` = 'CNY'; #EOQ
UPDATE `CubeCart_currency` SET `iso` = '710' WHERE `code` = 'ZAR'; #EOQ
UPDATE `CubeCart_currency` SET `iso` = '484' WHERE `code` = 'MXN'; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_newsletter_subscriber` (
	`subscriber_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`customer_id` INT UNSIGNED DEFAULT NULL,
	`status` TINYINT(1) NOT NULL DEFAULT '0',
	`email` VARCHAR(254) NOT NULL,
	`validation` VARCHAR(50) DEFAULT NULL,
	PRIMARY KEY  (`subscriber_id`),
	KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

INSERT INTO `CubeCart_newsletter_subscriber` (`email`, `status`)
SELECT DISTINCT (`email`), `optIn1st` AS `status` FROM `CubeCart_customer` WHERE `optIn1st` = 1; #EOQ

DELETE FROM `CubeCart_customer` WHERE `type` = 0; #EOQ

ALTER TABLE `CubeCart_customer` DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `customer_id` `customer_id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `email` `email` VARCHAR(254) NOT NULL; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `password` `password` VARCHAR(40) NOT NULL; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `salt` `salt` VARCHAR(32) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `firstName` `first_name` VARCHAR(150) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `lastName` `last_name` VARCHAR(150) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_customer` DROP `companyName`; #EOQ
ALTER TABLE `CubeCart_customer` DROP `add_1`; #EOQ
ALTER TABLE `CubeCart_customer` DROP `add_2`; #EOQ
ALTER TABLE `CubeCart_customer` DROP `town`; #EOQ
ALTER TABLE `CubeCart_customer` DROP `county`; #EOQ
ALTER TABLE `CubeCart_customer` DROP `postcode`; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `country` `country` SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `phone` `phone` VARCHAR(50) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `mobile` `mobile` VARCHAR(50) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_customer` ADD `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `regTime` `registered` INT UNSIGNED NOT NULL; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `ipAddress` `ip_address` VARCHAR(45) NOT NULL; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `noOrders` `order_count` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `type` `type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_customer` ADD `language` VARCHAR(5) NOT NULL DEFAULT 'en-US'; #EOQ
ALTER TABLE `CubeCart_customer` DROP `optIn1st`; #EOQ
ALTER TABLE `CubeCart_customer` DROP `htmlEmail`; #EOQ
ALTER TABLE `CubeCart_customer` ADD `verify` VARCHAR( 32 ) NULL; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_customer_group` (
	`group_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`group_name` VARCHAR(150) NOT NULL,
	`group_description` TEXT NOT NULL,
	PRIMARY KEY (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_customer_membership` (
	`membership_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`group_id` INT UNSIGNED NOT NULL,
	`customer_id` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`membership_id`),
	KEY `group_id` (`group_id`),
	KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_docs` RENAME TO `CubeCart_documents`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_documents` CHANGE `doc_id` `doc_id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST; #EOQ
ALTER TABLE `CubeCart_documents` ADD `doc_parent_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_documents` ADD `doc_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_documents` CHANGE `doc_order` `doc_order` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_documents` CHANGE `doc_terms` `doc_terms` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_documents` ADD `doc_home` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_documents` ADD `doc_lang` VARCHAR(5) NOT NULL; #EOQ
ALTER TABLE `CubeCart_documents` CHANGE `doc_name` `doc_name` VARCHAR(200) NOT NULL; #EOQ
ALTER TABLE `CubeCart_documents` CHANGE `doc_content` `doc_content` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_documents` CHANGE `doc_url` `doc_url` VARCHAR(200) DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_documents` CHANGE `doc_url_openin` `doc_url_openin` TINYINT(1) UNSIGNED DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_documents` CHANGE `doc_metatitle` `seo_meta_title` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_documents` CHANGE `doc_metadesc` `seo_meta_description` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_documents` CHANGE `doc_metakeywords` `seo_meta_keywords` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_documents` ADD KEY `doc_parent_id` (`doc_parent_id`); #EOQ

INSERT INTO `CubeCart_documents` (`doc_parent_id`, `doc_lang`, `doc_name`, `doc_content`)
SELECT `doc_master_id` AS `doc_parent_id`, `doc_lang`, `doc_name`, `doc_content` FROM `CubeCart_docs_lang` WHERE 1; #EOQ

DROP TABLE `CubeCart_docs_lang`; #EOQ

ALTER TABLE `CubeCart_Downloads` RENAME TO `CubeCart_downloads_temp`; #EOQ
ALTER TABLE `CubeCart_downloads_temp` RENAME TO `CubeCart_downloads`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_downloads` CHANGE `id` `digital_id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_downloads` ADD `order_inv_id` INT UNSIGNED NOT NULL; #EOQ
ALTER TABLE `CubeCart_downloads` CHANGE `customerId` `customer_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_downloads` CHANGE `cart_order_id` `cart_order_id` VARCHAR(18) NOT NULL; #EOQ
ALTER TABLE `CubeCart_downloads` CHANGE `noDownloads` `downloads` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_downloads` CHANGE `expire` `expire` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_downloads` CHANGE `productId` `product_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_downloads` CHANGE `accessKey` `accesskey` VARCHAR(32) NOT NULL; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_email_content` (
	`content_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`content_type` VARCHAR(70) NOT NULL,
	`language` VARCHAR(5) NOT NULL,
	`subject` VARCHAR(250) NOT NULL,
	`content_html` TEXT NOT NULL,
	`content_text` TEXT NOT NULL,
	PRIMARY KEY (`content_id`),
	KEY `content_type` (`content_type`),
	KEY `language` (`language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_email_template` (
	`template_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`template_default` ENUM('0','1') NOT NULL DEFAULT '0',
	`title` varchar(100) NOT NULL,
	`content_html` TEXT NOT NULL,
	`content_text` TEXT NOT NULL,
	PRIMARY KEY (`template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

UPDATE `CubeCart_filemanager` SET `filepath` = REPLACE(`filepath`, 'images/uploads/', ''); #EOQ
UPDATE `CubeCart_filemanager` SET `filepath` = REPLACE(`filepath`, 'images\uploads\\', ''); #EOQ
UPDATE `CubeCart_filemanager` SET `filepath` = REPLACE(`filepath`, `filename`, ''); #EOQ

ALTER TABLE `CubeCart_filemanager` CHANGE `filepath` `filepath` VARCHAR(255) default NULL; #EOQ

UPDATE `CubeCart_filemanager` SET `filepath` = NULL WHERE `filepath` = ''; #EOQ

ALTER TABLE `CubeCart_iso_countries` RENAME TO `CubeCart_geo_country`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_geo_country` CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_geo_country` CHANGE  `numcode`  `numcode` SMALLINT( 3 ) NULL DEFAULT NULL; #EOQ

ALTER TABLE `CubeCart_iso_counties` RENAME TO `CubeCart_geo_zone`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_geo_zone` CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_geo_zone` CHANGE `countryId` `country_id` INT(4) UNSIGNED NOT NULL DEFAULT '0'; #EOQ

ALTER TABLE `CubeCart_history` DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci, CHANGE `time` `time` INT UNSIGNED NOT NULL; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_hooks` (
	`hook_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`plugin` VARCHAR(100) NOT NULL,
	`hook_name` VARCHAR(255) NOT NULL COMMENT 'A descriptive name for the hook',
	`enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'All hooks should be disabled by default',
	`trigger` VARCHAR(255) NOT NULL COMMENT 'The trigger used to call the hook',
	`filepath` TEXT NOT NULL,
	`priority` INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`hook_id`),
	KEY `trigger` (`trigger`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_img_idx` RENAME TO `CubeCart_image_index`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_image_index` CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST; #EOQ
ALTER TABLE `CubeCart_image_index` CHANGE `productId` `product_id` INT UNSIGNED NOT NULL; #EOQ
ALTER TABLE `CubeCart_image_index` ADD `file_id` INT UNSIGNED NOT NULL; #EOQ
ALTER TABLE `CubeCart_image_index` ADD `main_img` ENUM('0','1') NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_image_index` ADD INDEX (`file_id`); #EOQ

UPDATE `CubeCart_image_index` INNER JOIN `CubeCart_filemanager` ON `CubeCart_image_index`.`img` = CONCAT(`CubeCart_filemanager`.`filepath`,`CubeCart_filemanager`.`filename`) SET `CubeCart_image_index`.`file_id` = `CubeCart_filemanager`.`file_id` WHERE `CubeCart_image_index`.`img` != ''; #EOQ

DROP INDEX `fulltext` ON `CubeCart_inventory`; #EOQ

ALTER TABLE `CubeCart_inventory` DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `productCode` `product_code` VARCHAR(60) NULL DEFAULT NULL COLLATE utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `description` `description` TEXT  COLLATE utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_inventory` DROP `noImages`; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `price` `price` DECIMAL(16,2) NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `sale_price` `sale_price` DECIMAL(16,2) NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `name` `name` VARCHAR(250) DEFAULT NULL COLLATE utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `stockWarn` `stock_warning` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `useStockLevel` `use_stock_level` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `digital` `digital` INT(4) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `digitalDir` `digital_path` VARCHAR(255) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `prodWeight` `product_weight` DECIMAL(10,2) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `taxType` `tax_type` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `eanupcCode` `eanupc_code` BIGINT(17) UNSIGNED NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `showFeatured` `featured` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `prod_metatitle` `seo_meta_title` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `prod_metadesc` `seo_meta_description` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `prod_metakeywords` `seo_meta_keywords` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `manufacturer` INT(10) UNSIGNED NULL; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP; #EOQ

UPDATE `CubeCart_inventory` SET `status` = '0' WHERE `disabled` = '1'; #EOQ
ALTER TABLE `CubeCart_inventory` DROP `disabled`; #EOQ

CREATE FULLTEXT INDEX `fulltext` ON `CubeCart_inventory` (`product_code`, `name`, `description`); #EOQ

ALTER TABLE `CubeCart_inv_lang` RENAME TO `CubeCart_inventory_language`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_inventory_language` CHANGE `id` `translation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_inventory_language` CHANGE `prod_master_id` `product_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_inventory_language` CHANGE `prod_lang` `language` VARCHAR(5); #EOQ
ALTER TABLE `CubeCart_inventory_language` ADD `seo_meta_title` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_inventory_language` ADD `seo_meta_description` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_inventory_language` ADD `seo_meta_keywords` TEXT NOT NULL; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_lang_strings` (
	`string_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`language` VARCHAR(5) NOT NULL,
	`type` VARCHAR(50) NOT NULL,
	`name` VARCHAR(100) NOT NULL,
	`value` TEXT NOT NULL,
	PRIMARY KEY (`string_id`),
	KEY `language` (`language`),
	KEY `type` (`type`),
	KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_logo` (
	`logo_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL,
	`filename` VARCHAR(150) NOT NULL,
	`mimetype` VARCHAR(100) NOT NULL,
	`width` INT UNSIGNED NOT NULL,
	`height` INT UNSIGNED NOT NULL,
	`skin` VARCHAR(100) NOT NULL,
	`style` VARCHAR(100) NOT NULL,
	PRIMARY KEY (`logo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_manufacturers` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `URL` VARCHAR(250) NULL,
  `image` INT(10) UNSIGNED NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_Modules` RENAME TO `CubeCart_modules_temp`; #EOQ
ALTER TABLE `CubeCart_modules_temp` RENAME TO `CubeCart_modules`; #EOQ
ALTER TABLE `CubeCart_modules` DROP INDEX `folder`; #EOQ 
ALTER TABLE `CubeCart_modules` CHANGE `moduleId` `module_id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ

ALTER TABLE `CubeCart_modules` ADD `countries` TINYTEXT NULL DEFAULT NULL; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_newsletter` (
	`newsletter_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`template_id` INT UNSIGNED NOT NULL,
	`date_saved` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`date_sent` TIMESTAMP NULL DEFAULT NULL,
	`subject` VARCHAR(250) NOT NULL,
	`sender_email` VARCHAR(254) NOT NULL,
	`sender_name` VARCHAR(255) NOT NULL,
	`content_html` TEXT NOT NULL,
	`content_text` TEXT NOT NULL,
	PRIMARY KEY (`newsletter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_options_set` (
	`set_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`set_name` TEXT NOT NULL,
	`set_description` TEXT NOT NULL,
	PRIMARY KEY  (`set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_options_set_member` (
	`set_member_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`set_id` INT UNSIGNED NOT NULL,
	`option_id` INT UNSIGNED NOT NULL,
	`value_id` INT UNSIGNED NOT NULL,
	`priority` INT NOT NULL,
	PRIMARY KEY  (`set_member_id`),
	KEY `set_id` (`set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_options_set_product` (
	`set_product_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`set_id` INT UNSIGNED NOT NULL,
	`product_id` INT UNSIGNED NOT NULL,
	PRIMARY KEY  (`set_product_id`),
	KEY `set_id` (`set_id`),
	KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_options_bot` RENAME TO `CubeCart_option_assign`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_option_assign` CHANGE `assign_id` `assign_id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_option_assign` CHANGE `product` `product` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_option_assign` CHANGE `option_id` `option_id` INT UNSIGNED NOT NULL; #EOQ
ALTER TABLE `CubeCart_option_assign` CHANGE `value_id` `value_id` INT UNSIGNED NOT NULL; #EOQ
ALTER TABLE `CubeCart_option_assign` ADD `set_member_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_option_assign` ADD `set_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_option_assign` CHANGE `option_price` `option_price` DECIMAL (16,2) NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_option_assign` ADD `option_weight` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
UPDATE `CubeCart_option_assign` SET `set_enabled` = 1; #EOQ

UPDATE `CubeCart_option_assign` SET `option_price` = (0-`option_price`) WHERE `option_symbol` = '-'; #EOQ
ALTER TABLE `CubeCart_option_assign` DROP `option_symbol`; #EOQ

ALTER TABLE `CubeCart_options_mid` RENAME TO `CubeCart_option_value`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_option_value` CHANGE `value_id` `value_id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_option_value` CHANGE `value_name` `value_name` VARCHAR(50) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_option_value` CHANGE `father_id` `option_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ

ALTER TABLE `CubeCart_options_top` RENAME TO `CubeCart_option_group`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_option_group` CHANGE `option_id` `option_id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_option_group` CHANGE `option_name` `option_name` VARCHAR(50) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_option_group` ADD `option_description` TEXT; #EOQ
ALTER TABLE `CubeCart_option_group` CHANGE `option_type` `option_type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_option_group` ADD `option_required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ

ALTER TABLE `CubeCart_order_inv` RENAME TO `CubeCart_order_inventory`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_order_inventory` CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST; #EOQ
ALTER TABLE `CubeCart_order_inventory` CHANGE `productId` `product_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_order_inventory` CHANGE `productCode` `product_code` VARCHAR(255) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_inventory` CHANGE `quantity` `quantity` INT(6) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_order_inventory` CHANGE `price` `price` DECIMAL(16,2) NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_order_inventory` CHANGE `cart_order_id` `cart_order_id` VARCHAR(18) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_inventory` CHANGE `product_options` `product_options` BLOB NULL; #EOQ
ALTER TABLE `CubeCart_order_inventory` CHANGE `digital` `digital` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_order_inventory` CHANGE `stockUpdated` `stock_updated` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_order_inventory` CHANGE `custom` `custom` BLOB NULL; #EOQ
ALTER TABLE `CubeCart_order_inventory` CHANGE `couponId` `coupon_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_notes` (
	`note_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`admin_id` INT UNSIGNED NOT NULL,
	`cart_order_id` VARCHAR(18) NOT NULL,
	`time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`content` TEXT NOT NULL,
	PRIMARY KEY (`note_id`),
	KEY `admin_id` (`admin_id`),
	KEY `cart_order_id` (`cart_order_id`),
	KEY `time` (`time`),
	FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_order_sum` RENAME TO `CubeCart_order_summary`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `cart_order_id` `cart_order_id` VARCHAR(18) NOT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `time` `order_date` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `customer_id` `customer_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `status` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `subtotal` `subtotal` DECIMAL(16,2) UNSIGNED NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `discount` `discount` DECIMAL(16,2) UNSIGNED NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `total_ship` `shipping` DECIMAL(16,2) UNSIGNED NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `total_tax` `total_tax` DECIMAL(16,2) UNSIGNED NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `prod_total` `total` DECIMAL(16,2) UNSIGNED NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `offline_capture` `offline_capture` BLOB; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `shipMethod` `ship_method` VARCHAR(100) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `ship_date` `ship_date` DATE NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `courier_tracking` `ship_tracking` VARCHAR(100) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `gateway` `gateway` VARCHAR(100) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `name` `name` VARCHAR(255) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `companyName` `company_name` VARCHAR(200) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `add_1` `line1` VARCHAR(150) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `add_2` `line2` VARCHAR(150) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `town` `town` VARCHAR(150) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `county` `state` VARCHAR(150) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `postcode` `postcode` VARCHAR(50) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `country` `country` VARCHAR(200); #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `name_d` `name_d` VARCHAR(255) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `companyName_d` `company_name_d` VARCHAR(200) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `add_1_d` `line1_d` VARCHAR(150) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `add_2_d` `line2_d` VARCHAR(150) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `town_d` `town_d` VARCHAR(150) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `county_d` `state_d` VARCHAR(150) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `postcode_d` `postcode_d` VARCHAR(50) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `country_d` `country_d` VARCHAR(200); #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `phone` `phone` VARCHAR(50) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `mobile` `mobile` VARCHAR(50) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `email` `email` VARCHAR(254) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `customer_comments` `customer_comments` TEXT NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `ip` `ip_address` VARCHAR(45) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `dashboard` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `title` VARCHAR(10) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `first_name` VARCHAR(100) NOT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `last_name` VARCHAR(100) NOT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `title_d` VARCHAR(10) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `first_name_d` VARCHAR(100) NOT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `last_name_d` VARCHAR(100) NOT NULL; #EOQ

UPDATE `CubeCart_order_summary` SET `subtotal` = `subtotal`+`discount`; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_history` (
  `history_id` int(10) unsigned NOT NULL auto_increment,
  `cart_order_id` varchar(18) collate utf8_unicode_ci NOT NULL,
  `status` tinyint(2) unsigned NOT NULL default '0',
  `updated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`history_id`),
  KEY `cart_order_id` (`cart_order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_tax` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`cart_order_id` VARCHAR(18) NOT NULL,
	`tax_id` INT UNSIGNED NOT NULL,
	`amount` DECIMAL(10,2) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	KEY `cart_order_id` (`cart_order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_permissions` (
	`permission_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`admin_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`section_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`level` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY  (`permission_id`),
	KEY `admin_id` (`admin_id`),
	KEY `section_id` (`section_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_pricing_group` (
	`price_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`group_id` INT UNSIGNED NOT NULL,
	`product_id` INT UNSIGNED NOT NULL,
	`price` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`sale_price` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`tax_type` INT UNSIGNED NOT NULL,
	`tax_inclusive` TINYINT(1) UNSIGNED NOT NULL,
	PRIMARY KEY  (`price_id`),
	KEY `group_id` (`group_id`),
	KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_pricing_quantity` (
	`discount_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL,
	`group_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`quantity` INT UNSIGNED NOT NULL,
	`price` DECIMAL(16,2) NOT NULL,
	PRIMARY KEY  (`discount_id`),
	KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_reviews` DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_reviews` CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_reviews` CHANGE `productId` `product_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_reviews` CHANGE `rating` `rating` DECIMAL(3,1) UNSIGNED DEFAULT '0.0'; #EOQ
ALTER TABLE `CubeCart_reviews` CHANGE `ip` `ip_address` VARCHAR(45) NOT NULL COMMENT 'Supports IPv6 addresses'; #EOQ
ALTER TABLE `CubeCart_reviews` CHANGE `time` `time` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_reviews` ADD `customer_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_reviews` ADD `vote_up` INT NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_reviews` ADD `vote_down` INT NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_reviews` ADD `anon` TINYINT(1) UNSIGNED DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_reviews` ADD INDEX (`approved`); #EOQ

ALTER TABLE `CubeCart_sessions` DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_sessions` CHANGE `sessId` `session_id` VARCHAR(32) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_sessions` CHANGE `timeStart` `session_start` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_sessions` CHANGE `timeLast` `session_last` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_sessions` CHANGE `customer_id` `customer_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_sessions` CHANGE `lang` `language` VARCHAR(5) DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_sessions` CHANGE `currency` `currency` CHAR(3) DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_sessions` CHANGE `skin` `skin` TEXT NULL; #EOQ
ALTER TABLE `CubeCart_sessions` CHANGE `ip` `ip_address` VARCHAR(45) DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_sessions` CHANGE `browser` `useragent` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_sessions` CHANGE `basket` `basket` BLOB NULL; #EOQ
ALTER TABLE `CubeCart_sessions` ADD `admin_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ

ALTER TABLE `CubeCart_tax_details` DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_tax_details` CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_tax_details` CHANGE `name` `name` VARBINARY(150) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_tax_details` CHANGE `display` `display` VARBINARY(150) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_tax_details` CHANGE `status` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ

ALTER TABLE `CubeCart_tax_rates` DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_tax_rates` CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_tax_rates` CHANGE `type_id` `type_id` INT UNSIGNED NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_tax_rates` CHANGE `details_id` `details_id` INT UNSIGNED NOT NULL; #EOQ
ALTER TABLE `CubeCart_tax_rates` CHANGE `country_id` `country_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_tax_rates` CHANGE `county_id` `county_id` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_tax_rates` CHANGE `goods` `goods` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_tax_rates` CHANGE `shipping` `shipping` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_tax_rates` CHANGE `active` `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_trackback` (
	`trackback_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL,
	`url` VARCHAR(250) NOT NULL,
	`title` TEXT NULL,
	`excerpt` TINYTEXT NULL,
	`blog_name` TEXT  NULL,
	PRIMARY KEY  (`trackback_id`),
	UNIQUE KEY `url` (`url`),
	KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_transactions` DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_transactions` CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_transactions` CHANGE `customer_id` `customer_id` INT UNSIGNED NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_transactions` CHANGE `order_id` `order_id` VARCHAR(18) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_transactions` CHANGE `time` `time` INT UNSIGNED NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_transactions` CHANGE `amount` `amount` DECIMAL(16,2) NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_transactions` CHANGE `remainder` `captured` DECIMAL(16,2) NULL DEFAULT NULL; #EOQ

ALTER TABLE `CubeCart_config` DROP INDEX `name` ; #EOQ
ALTER TABLE `CubeCart_config` ADD UNIQUE KEY (`name`); #EOQ

ALTER TABLE `CubeCart_customer` ADD UNIQUE KEY `email` (`email`); #EOQ
ALTER TABLE `CubeCart_customer` ADD FULLTEXT KEY `fulltext` (`first_name`,`last_name`,`email`); #EOQ

ALTER TABLE `CubeCart_category_index` ADD INDEX ( `product_id` ); #EOQ 

ALTER TABLE `CubeCart_alt_shipping` CHANGE `id` `id` INT( 10 ) NOT NULL AUTO_INCREMENT; #EOQ

ALTER TABLE `CubeCart_alt_shipping` CHANGE `order` `order` INT( 10 ) NULL DEFAULT '0'; #EOQ

ALTER TABLE `CubeCart_alt_shipping_prices` CHANGE `id` `id` INT( 10 ) NOT NULL AUTO_INCREMENT; #EOQ

ALTER TABLE `CubeCart_alt_shipping_prices` CHANGE `alt_ship_id` `alt_ship_id` INT( 10 ) NOT NULL; #EOQ

ALTER TABLE `CubeCart_alt_shipping_prices` CHANGE `low` `low` DECIMAL( 16, 3 ) NOT NULL DEFAULT '0.000'; #EOQ
ALTER TABLE `CubeCart_alt_shipping_prices` CHANGE `high` `high` DECIMAL( 16, 3 ) NOT NULL DEFAULT '0.000'; #EOQ
ALTER TABLE `CubeCart_alt_shipping_prices` CHANGE `price` `price` DECIMAL( 16, 2 ) NOT NULL DEFAULT '0.00'; #EOQ 


ALTER TABLE `CubeCart_category_index` CHANGE `id` `id` INT( 10 ) NOT NULL AUTO_INCREMENT; #EOQ 
ALTER TABLE `CubeCart_category_index` CHANGE `cat_id` `cat_id` INT( 10 ) NOT NULL DEFAULT '0'; #EOQ 
 
ALTER TABLE `CubeCart_category_language` ADD `seo_meta_keywords` TEXT NOT NULL; #EOQ 

ALTER TABLE `CubeCart_category_language` ADD INDEX ( `translation_id` ) ; #EOQ  
ALTER TABLE `CubeCart_category_language` DROP PRIMARY KEY ; #EOQ 

ALTER TABLE `CubeCart_coupons` ADD UNIQUE KEY (`code`) ; #EOQ
ALTER TABLE `CubeCart_currency` ADD UNIQUE KEY (`code`) ; #EOQ

ALTER TABLE `CubeCart_documents` ADD PRIMARY KEY ( `doc_id` ) ; #EOQ

ALTER TABLE `CubeCart_documents` DROP KEY `doc_id` ; #EOQ

ALTER TABLE `CubeCart_geo_zone` ADD KEY ( `country_id` ) ; #EOQ

ALTER TABLE `CubeCart_inventory` DROP INDEX `popularity`; #EOQ
ALTER TABLE `CubeCart_inventory` DROP INDEX `cat_id`; #EOQ
ALTER TABLE `CubeCart_inventory_language` ADD KEY ( `translation_id` ); #EOQ
ALTER TABLE `CubeCart_inventory_language` DROP PRIMARY KEY; #EOQ
ALTER TABLE `CubeCart_inventory_language` DROP INDEX `prod_master_id`; #EOQ
ALTER TABLE `CubeCart_options_set_member` DROP `price`; #EOQ
ALTER TABLE `CubeCart_options_set_member` DROP `weight`; #EOQ 
ALTER TABLE `CubeCart_option_value` ADD KEY ( `option_id` ); #EOQ
ALTER TABLE `CubeCart_order_summary` ADD KEY ( `customer_id` ); #EOQ 
ALTER TABLE `CubeCart_order_summary` ADD KEY ( `status` ); #EOQ
ALTER TABLE `CubeCart_order_summary` ADD KEY ( `email` ); #EOQ
ALTER TABLE `CubeCart_order_summary` ADD KEY ( `order_date` ); #EOQ
ALTER TABLE `CubeCart_reviews` ADD KEY `product_id` (`product_id`); #EOQ
ALTER TABLE `CubeCart_reviews` ADD KEY `votes` (`vote_up`,`vote_down`); #EOQ
ALTER TABLE `CubeCart_reviews` ADD FULLTEXT KEY `fulltext` (`name`,`email`,`title`,`review`); #EOQ
ALTER TABLE `CubeCart_sessions` KEY `customer_id` (`customer_id`); #EOQ
ALTER TABLE `CubeCart_sessions` KEY `session_last` (`session_last`); #EOQ
ALTER TABLE `CubeCart_transactions` ADD KEY `order_id` (`order_id`); #EOQ
ALTER TABLE `CubeCart_transactions` ADD KEY `time` (`time`); #EOQ
ALTER TABLE `CubeCart_option_assign` DROP INDEX `product`; #EOQ
ALTER TABLE `CubeCart_option_assign` ADD key ( `set_member_id` ); #EOQ  

UPDATE `CubeCart_documents` SET  `doc_content` = REPLACE(`doc_content`, 'images/uploads','images/source'); #EOQ
UPDATE `CubeCart_inventory` SET  `description` = REPLACE(`description`,'images/uploads','images/source'); #EOQ
UPDATE `CubeCart_category` SET  `cat_desc` = REPLACE(`cat_desc`,'images/uploads','images/source'); #EOQ

ALTER TABLE  `CubeCart_reviews` CHANGE  `rating`  `rating` DECIMAL( 2, 1 ) UNSIGNED NULL DEFAULT  '0.0'; #EOQ

UPDATE `CubeCart_order_summary` AS `S`, `CubeCart_geo_country` AS `C` SET `S`.`country` = `C`.`numcode`, `S`.`country_d` = `C`.`numcode` WHERE `S`.`country` = `C`.`printable_name`; #EOQ