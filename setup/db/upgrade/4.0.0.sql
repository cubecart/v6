CREATE TABLE IF NOT EXISTS `CubeCart_admin_log` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`user` VARCHAR(255) NOT NULL DEFAULT '',
	`desc` TEXT NOT NULL,
	`time` INT UNSIGNED NOT NULL DEFAULT '0',
	`ipAddress` VARCHAR(16) NOT NULL DEFAULT '',
	PRIMARY KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_admin_permissions` CHANGE `permissionId` `permissionId` SMALLINT NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_admin_permissions` CHANGE `sectId` `sectId` SMALLINT NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_admin_permissions` CHANGE `adminId` `adminId` INT(11) NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_admin_permissions` CHANGE `read` `read` SMALLINT NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_admin_permissions` CHANGE `write` `write` SMALLINT NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_admin_permissions` CHANGE `edit` `edit` SMALLINT NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_admin_permissions` CHANGE `delete` `delete` SMALLINT NOT NULL DEFAULT '0'; #EOQ

ALTER TABLE `CubeCart_admin_sections` CHANGE `sectId` `sectId` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_admin_sections` CHANGE `name` `name` VARCHAR(50) NOT NULL; #EOQ
 
ALTER TABLE `CubeCart_admin_sessions` CHANGE `username` `username` VARCHAR(255) NOT NULL; #EOQ
ALTER TABLE `CubeCart_admin_sessions` CHANGE `success` `success` smallint(1) NOT NULL DEFAULT '0'; #EOQ

ALTER TABLE `CubeCart_admin_users` CHANGE `notes` `notes` TEXT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `isSuper` `isSuper` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD `sessId` VARCHAR(32) DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD `browser` TEXT; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD `sessIp` VARCHAR(15) DEFAULT NULL; #EOQ

ALTER TABLE `CubeCart_admin_users` ADD `failLevel` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD `blockTime` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD `lastTime` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_alt_shipping` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` smallint(1) NOT NULL DEFAULT '0',
  `byprice` smallint(1) NOT NULL,
  `global` smallint(1) NOT NULL,
  `notes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order` int(10) unsigned DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_alt_shipping_prices` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`alt_ship_id` INT UNSIGNED NOT NULL,
	`low` DECIMAL(16,3) NOT NULL DEFAULT '0.000',
	`high` DECIMAL(16,3) NOT NULL DEFAULT '0.000',
	`price` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_blocker` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`browser` TEXT NOT NULL,
	`ip` VARCHAR(15) NOT NULL,
	`username` VARCHAR(50) NOT NULL,
	`blockTime` INT UNSIGNED NOT NULL DEFAULT '0',
	`blockLevel` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`loc` CHAR(1) NOT NULL,
	`lastTime` INT UNSIGNED NOT NULL,
	KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_category` CHANGE `cat_name` `cat_name` VARCHAR(100) NOT NULL; #EOQ
ALTER TABLE `CubeCart_category` ADD `cat_desc` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_category` CHANGE `cat_image` `cat_image` VARCHAR(250) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_category` ADD `hide` SMALLINT(1) NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_category` ADD `cat_metatitle` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_category` ADD `cat_metadesc` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_category` ADD `cat_metakeywords` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_category` ADD `priority` SMALLINT(6) DEFAULT '0'; #EOQ

ALTER TABLE `CubeCart_cats_lang` ADD `cat_desc` TEXT NOT NULL DEFAULT ''; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_cats_lang` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`cat_master_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`cat_lang` VARCHAR(20) NOT NULL DEFAULT '',
	`cat_name` VARCHAR(255) NOT NULL DEFAULT '',
	`cat_desc` TEXT NOT NULL DEFAULT '',
	KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_Coupons` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`code` VARCHAR(25) NOT NULL DEFAULT '',
	`product_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`discount_percent` FLOAT NOT NULL DEFAULT '0',
	`discount_price` FLOAT NOT NULL DEFAULT '0',
	`expires` VARCHAR(10) NOT NULL DEFAULT '',
	`allowed_uses` INT UNSIGNED NOT NULL DEFAULT '0',
	`count` INT UNSIGNED NOT NULL DEFAULT '0',
	`desc` TEXT NOT NULL,
	`cart_order_id` VARCHAR(30) DEFAULT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_currencies` ADD `DECIMALSymbol` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_currencies` CHANGE `symbolLeft` `symbolLeft` VARBINARY(10) DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_currencies` CHANGE `symbolRight` `symbolRight` VARBINARY(10) DEFAULT NULL; #EOQ

ALTER TABLE `CubeCart_customer` ADD `companyName` VARCHAR(150) NOT NULL; #EOQ
ALTER TABLE `CubeCart_customer` DROP COLUMN `zoneId`; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `country` `country` SMALLINT(3) NOT NULL; #EOQ

ALTER TABLE `CubeCart_docs` CHANGE `doc_content` `doc_content` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_docs` ADD `doc_metatitle` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_docs` ADD `doc_metadesc` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_docs` ADD `doc_metakeywords` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_docs` ADD `doc_order` INT UNSIGNED DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_docs` ADD `doc_terms` TINYINT(1) UNSIGNED DEFAULT '0'; #EOQ

UPDATE `CubeCart_docs` SET doc_order = doc_id WHERE 1; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_history` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`version` VARCHAR(50) NOT NULL,
	`time` INT UNSIGNED NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_inventory` ADD `stockWarn` INT NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `prodWeight` `prodWeight` DECIMAL(10,3) DEFAULT NULL; #EOQ,
ALTER TABLE `CubeCart_inventory` CHANGE `showFeatured` `showFeatured` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `prod_metatitle` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `prod_metadesc` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `prod_metakeywords` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `eanupcCode` BIGINT(17) UNSIGNED NULL; #EOQ
ALTER TABLE `CubeCart_inventory` ADD FULLTEXT `fulltext` (`productCode`,`description`,`name`); #EOQ
ALTER TABLE  `CubeCart_inventory` ADD INDEX (`popularity`); #EOQ

ALTER TABLE `CubeCart_inv_lang` CHANGE `id` `id` INT UNSIGNED NOT NULL AUTO_INCREMENT; #EOQ
ALTER TABLE `CubeCart_inv_lang` ADD FULLTEXT `fulltext` (`name`,`description`); #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_lang` (
	`identifier` VARCHAR(50) NOT NULL DEFAULT '', 
	`langArray` LONGTEXT NOT NULL, 
	UNIQUE KEY `identifier` (`identifier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_order_inv` ADD `custom` TEXT NULL; #EOQ
ALTER TABLE `CubeCart_order_inv` ADD `couponId` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ

ALTER TABLE `CubeCart_order_inv` ADD `stockUpdated` INT UNSIGNED NOT NULL DEFAULT '0'; #EOQ

DROP TABLE IF EXISTS `CubeCart_order_state`; #EOQ

ALTER TABLE `CubeCart_order_sum` ADD `discount` DECIMAL(30,2) DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_order_sum` ADD `tax1_disp` VARCHAR(128) NOT NULL; #EOQ
ALTER TABLE `CubeCart_order_sum` ADD `tax1_amt` DECIMAL(30,2) NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_order_sum` ADD `tax2_disp` VARCHAR(128) NOT NULL; #EOQ
ALTER TABLE `CubeCart_order_sum` ADD `tax2_amt` DECIMAL(30,2) NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_order_sum` ADD `tax3_disp` VARCHAR(128) NOT NULL; #EOQ
ALTER TABLE `CubeCart_order_sum` ADD `tax3_amt` DECIMAL(30,2) NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_order_sum` ADD `offline_capture` BLOB NULL; #EOQ
ALTER TABLE `CubeCart_order_sum` ADD `courier_tracking` TEXT NULL; #EOQ
ALTER TABLE `CubeCart_order_sum` ADD `companyName` VARCHAR(150) NOT NULL; #EOQ
ALTER TABLE `CubeCart_order_sum` ADD `companyName_d` VARCHAR(150) NOT NULL; #EOQ
ALTER TABLE `CubeCart_order_sum` ADD `basket` TEXT DEFAULT NULL; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_reviews` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`approved` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`productId` INT UNSIGNED NOT NULL,
	`type` TINYINT(1) UNSIGNED NOT NULL,
	`rating` SMALLINT(1) UNSIGNED NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	`title` VARCHAR(255) NOT NULL,
	`review` TEXT NOT NULL,
	`ip` VARCHAR(15) NOT NULL,
	`time` INT UNSIGNED NOT NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_sessions` ADD `skin` VARCHAR(25) DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_sessions` ADD `ip` VARCHAR(15) DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_sessions` ADD `browser` TEXT NOT NULL; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_SpamBot` (
	`uniqueId` VARCHAR(32) NOT NULL DEFAULT '',
	`spamCode` VARCHAR(5) NOT NULL DEFAULT '',
	`userIp` VARCHAR(15) NOT NULL DEFAULT '',
	`time` INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY  (`uniqueId`),
	UNIQUE KEY `uniqueId` (`uniqueId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_taxes` CHANGE `percent` `percent` DECIMAL(7,4) NOT NULL DEFAULT '0.0000'; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_tax_details` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(128) NOT NULL DEFAULT '',
	`display` VARCHAR(128) NOT NULL DEFAULT '',
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY  (`id`),
	UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ


CREATE TABLE IF NOT EXISTS `CubeCart_tax_rates` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`type_id` INT UNSIGNED NOT NULL DEFAULT '1',
	`details_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`country_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`county_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`tax_percent` DECIMAL(7,4) NOT NULL DEFAULT '0.0000',
	`goods` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`shipping` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY  (`id`),
	UNIQUE KEY `type_id` (`type_id`,`details_id`,`country_id`,`county_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ


CREATE TABLE IF NOT EXISTS `CubeCart_transactions` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`gateway` VARCHAR(255) DEFAULT NULL,
	`status` VARCHAR(50) DEFAULT NULL,
	`customer_id` INT UNSIGNED DEFAULT NULL,
	`order_id` VARCHAR(255) DEFAULT NULL,
	`trans_id` VARCHAR(50) DEFAULT NULL,
	`time` INT UNSIGNED DEFAULT NULL,
	`amount` DECIMAL(30,2) DEFAULT NULL,
	`notes` TEXT NULL,
	KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE `CubeCart_Coupons` CHANGE `discount_percent` `discount_percent` DECIMAL(16,2) NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_Coupons` CHANGE `discount_price` `discount_price` DECIMAL(16,2) NOT NULL DEFAULT '0'; #EOQ

ALTER TABLE `CubeCart_Modules` CHANGE `module` `module` VARCHAR(25) NOT NULL; #EOQ

DELETE FROM `CubeCart_Modules` WHERE `folder` = 'DirectPayment'; #EOQ
DELETE FROM `CubeCart_config` WHERE `name` = 'DirectPayment'; #EOQ

ALTER TABLE `CubeCart_transactions` ADD `remainder` DECIMAL(16,2) NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_transactions` ADD `extra` VARCHAR(255) NULL; #EOQ