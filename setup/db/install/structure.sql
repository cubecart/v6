SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"; #EOQ

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

CREATE TABLE IF NOT EXISTS `CubeCart_admin_log` (
	`log_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`admin_id` INT UNSIGNED NOT NULL,
	`time` INT UNSIGNED NOT NULL,
	`ip_address` VARCHAR(45) NOT NULL,
	`description` TEXT NOT NULL,
	PRIMARY KEY (`log_id`),
	KEY `admin_id` (`admin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_admin_error_log` (
	`log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`admin_id` int(10) unsigned NOT NULL,
	`time` int(10) unsigned NOT NULL,
	`message` text COLLATE utf8_unicode_ci NOT NULL,
	`read` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_admin_users` (
	`admin_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`customer_id` INT UNSIGNED DEFAULT NULL,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`name` VARCHAR(150) NOT NULL,
	`username` VARCHAR(150) NOT NULL,
	`password` VARCHAR(128) NOT NULL,
	`salt` VARCHAR(32) DEFAULT NULL,
	`new_password` tinyint(1) NOT NULL DEFAULT '1',
	`email` VARCHAR(254) NOT NULL,
	`verify` VARCHAR(32) DEFAULT NULL,
	`logins` INT UNSIGNED NOT NULL DEFAULT '0',
	`super_user` TINYINT(1) NOT NULL DEFAULT '0',
	`notes` TEXT NULL,
	`failLevel` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`blockTime` INT UNSIGNED NOT NULL DEFAULT '0',
	`lastTime` INT UNSIGNED NOT NULL DEFAULT '0',
	`session_id` VARCHAR(32) DEFAULT NULL,
	`browser` TEXT NULL,
	`ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Supports IPv6 addresses',
	`language` VARCHAR(5) NOT NULL DEFAULT 'en-US',
	`dashboard_notes` TEXT NULL,
	`order_notify` TINYINT(1) UNSIGNED DEFAULT '0',
	KEY `admin_id` (`admin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_alt_shipping` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`status` SMALLINT(1) NOT NULL DEFAULT '0',
	`byprice` SMALLINT(1) NOT NULL,
	`global` SMALLINT(1) NOT NULL,
	`notes` VARCHAR(255) DEFAULT NULL,
	`order` INT UNSIGNED DEFAULT '0',
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
	`block_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`level` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
	`last_attempt` INT UNSIGNED NOT NULL DEFAULT '0',
	`ban_expires` INT UNSIGNED NOT NULL DEFAULT '0',
	`username` TEXT NOT NULL,
	`location` CHAR(1) NOT NULL,
	`user_agent` TEXT NOT NULL,
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'Supports IPv6 addresses',
	PRIMARY KEY (`block_id`),
	KEY `location` (`location`),
	KEY `last_attempt` (`last_attempt`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_category` (
	`cat_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`cat_name` VARCHAR(100) NOT NULL,
	`cat_desc` TEXT NULL,
	`cat_parent_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`cat_image` VARBINARY(250) NOT NULL DEFAULT '',
	`per_ship` DECIMAL(20,2) NOT NULL DEFAULT '0.00',
	`item_ship` DECIMAL(20,2) NOT NULL DEFAULT '0.00',
	`item_int_ship` DECIMAL(20,2) NOT NULL DEFAULT '0.00',
	`per_int_ship` DECIMAL(20,2) NOT NULL DEFAULT '0.00',
	`hide` SMALLINT(1) NOT NULL DEFAULT '0',
	`seo_meta_title` TEXT NOT NULL,
	`seo_meta_description` TEXT NOT NULL,
	`seo_meta_keywords` TEXT NOT NULL,
	`priority` SMALLINT(6) UNSIGNED NOT NULL DEFAULT '0',
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (`cat_id`),
	KEY `cat_parent_id` (`cat_parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_category_index` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`cat_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`product_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`primary` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `cat_id` (`cat_id`),
	KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_category_language` (
	`translation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`cat_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`language` VARCHAR(5) NULL DEFAULT NULL,
	`cat_name` VARCHAR(255) NULL DEFAULT NULL,
	`cat_desc` TEXT NOT NULL,
	`seo_meta_title` TEXT NULL,
	`seo_meta_description` TEXT NULL,
	`seo_meta_keywords` TEXT NULL,
	KEY `id` (`translation_id`),
	KEY `cat_master_id` (`cat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_code_snippet` (
  `snippet_id` int(11) NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `unique_id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hook_trigger` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `php_code` blob NOT NULL,
  `version` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`snippet_id`),
  UNIQUE KEY `unique_id` (`unique_id`),
  KEY `hook_trigger` (`hook_trigger`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_config` (
	`name` VARCHAR(100) NOT NULL,
	`array` text NOT NULL,
	UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_coupons` (
	`coupon_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`archived` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`code` VARCHAR(25) NOT NULL,
	`product_id` TEXT NOT NULL,
	`discount_percent` DECIMAL(5,2) NOT NULL DEFAULT '0.00',
	`discount_price` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`expires` DATE NOT NULL,
	`allowed_uses` INT UNSIGNED NOT NULL DEFAULT '0',
	`min_subtotal` DECIMAL(16,2) UNSIGNED NOT NULL DEFAULT '0.00',
	`count` INT UNSIGNED NOT NULL DEFAULT '0',
	`shipping` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`subtotal` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`description` text NOT NULL,
	`cart_order_id` VARCHAR(18) DEFAULT NULL,
	PRIMARY KEY (`coupon_id`),
	UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_currency` (
	`currency_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARBINARY(255) NOT NULL DEFAULT '',
	`code` VARCHAR(7) NOT NULL,
	`iso` INT(3) UNSIGNED ZEROFILL DEFAULT NULL,
	`symbol_left` VARCHAR(10) DEFAULT NULL,
	`symbol_right` VARCHAR(10) DEFAULT NULL,
	`value` DECIMAL(10,5) NOT NULL DEFAULT '0.00000',
	`decimal_places` TINYINT(2) UNSIGNED DEFAULT '2',
	`updated` INT UNSIGNED NOT NULL DEFAULT '0',
	`active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`symbol_decimal` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`currency_id`),
	UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_customer` (
	`customer_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(254) NOT NULL,
	`password` VARCHAR(128) DEFAULT NULL,
	`salt` VARCHAR(32) DEFAULT NULL,
	`new_password` tinyint(1) NOT NULL DEFAULT '1',
	`verify` VARCHAR(32) DEFAULT NULL,
	`title` VARCHAR(10) DEFAULT NULL,
	`first_name` VARCHAR(150) NOT NULL,
	`last_name` VARCHAR(150) NOT NULL,
	`country` SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
	`phone` VARCHAR(20) NOT NULL,
	`mobile` VARCHAR(20) DEFAULT NULL,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`registered` INT UNSIGNED NOT NULL DEFAULT '0',
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'Supports IPv6 addresses',
	`order_count` INT UNSIGNED DEFAULT '0',
	`type` TINYINT(1) UNSIGNED DEFAULT '1',
	`language` VARCHAR(5) NOT NULL DEFAULT 'en-US',
	PRIMARY KEY (`customer_id`),
	UNIQUE KEY `email` (`email`),
	FULLTEXT KEY `fulltext` (`first_name`,`last_name`,`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

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

CREATE TABLE IF NOT EXISTS `CubeCart_documents` (
	`doc_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`doc_parent_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`doc_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`doc_order` INT UNSIGNED NOT NULL DEFAULT '0',
	`doc_terms` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`doc_home` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`doc_lang` VARCHAR(5) NOT NULL,
	`doc_name` VARCHAR(200) NOT NULL,
	`doc_content` TEXT NOT NULL,
	`doc_url` VARCHAR(200) DEFAULT NULL,
	`doc_url_openin` TINYINT(1) UNSIGNED DEFAULT NULL,
	`seo_meta_title` TEXT NOT NULL,
	`seo_meta_description` TEXT NOT NULL,
	`seo_meta_keywords` TEXT NOT NULL,
	`navigation_link` tinyint(1) unsigned NOT NULL DEFAULT '1',
	`doc_parse` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY (`doc_id`),
	KEY `doc_parent_id` (`doc_parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_downloads` (
	`digital_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_inv_id` INT UNSIGNED NOT NULL,
	`customer_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`cart_order_id` VARCHAR(18) NOT NULL,
	`downloads` INT UNSIGNED NOT NULL DEFAULT '0',
	`expire` INT UNSIGNED NOT NULL DEFAULT '0',
	`product_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`accesskey` VARCHAR(32) NOT NULL,
	KEY `id` (`digital_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

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
	`title` VARCHAR(100) NOT NULL,
	`content_html` TEXT NOT NULL,
	`content_text` TEXT NOT NULL,
	PRIMARY KEY (`template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_filemanager` (
	`file_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`disabled` TINYINT(1) NOT NULL DEFAULT '0',
	`filepath` varchar(255) default NULL,
	`filename` VARCHAR(255) NOT NULL,
	`filesize` INT UNSIGNED NOT NULL,
	`mimetype` VARCHAR(50) NOT NULL,
	`md5hash` VARCHAR(32) NOT NULL,
	`description` TEXT NOT NULL,
	PRIMARY KEY (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_geo_country` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`iso` CHAR(2) NOT NULL,
	`name` VARBINARY(80) NOT NULL DEFAULT '',
	`iso3` CHAR(3) DEFAULT NULL,
	`numcode` SMALLINT(3) UNSIGNED ZEROFILL DEFAULT NULL,
	PRIMARY KEY (`iso`),
	KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_geo_zone` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`country_id` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0',
	`abbrev` VARBINARY(4) NOT NULL DEFAULT '',
	`name` VARBINARY(40) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_history` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`version` VARCHAR(50) NOT NULL,
	`time` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_hooks` (
	`hook_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`plugin` VARCHAR(100) NOT NULL,
	`hook_name` VARCHAR(255) NOT NULL COMMENT 'A descriptive name for the hook',
	`enabled` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'All hooks should be disabled by DEFAULT',
	`trigger` VARCHAR(255) NOT NULL COMMENT 'The trigger used to call the hook',
	`filepath` TEXT NOT NULL,
	`priority` INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`hook_id`),
	KEY `trigger` (`trigger`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_image_index` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL,
	`file_id` INT UNSIGNED NOT NULL,
	`main_img` ENUM('0','1') NOT NULL DEFAULT '0',
	PRIMARY KEY `id` (`id`),
	KEY `file_id` (`file_id`),
	KEY `productId` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_inventory` (
  `product_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Product ID',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Status',
  `product_code` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Product Code',
  `quantity` int(11) NOT NULL DEFAULT '1' COMMENT 'Quantity',
  `description` text COLLATE utf8_unicode_ci COMMENT 'Description',
  `price` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT 'Retail Price',
  `sale_price` decimal(16,2) DEFAULT '0.00' COMMENT 'Sale Price',
  `cost_price` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT 'Cost Price',
  `name` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Product Name',
  `cat_id` int(10) unsigned DEFAULT '0' COMMENT 'Main Category ID',
  `popularity` int(10) unsigned DEFAULT '0' COMMENT 'Popularity',
  `stock_level` int(11) DEFAULT '0' COMMENT 'Main Stock Level',
  `stock_warning` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Main Stock Warning level',
  `use_stock_level` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Use Stock Control',
  `digital` int(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Is Digital?',
  `digital_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Digital Path',
  `product_weight` decimal(10,3) DEFAULT NULL COMMENT 'Product Weight',
  `tax_type` int(10) unsigned DEFAULT NULL COMMENT 'Tax Type',
  `tax_inclusive` tinyint(1) unsigned DEFAULT '0' COMMENT 'Price inclusive of tax',
  `featured` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Included on Homepage',
  `seo_meta_title` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'SEO Meta Title',
  `seo_meta_description` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'SEO Meta Description',
  `seo_meta_keywords` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'SEO Meta Keywords',
  `upc` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'UPC Code',
  `ean` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'EAN Code',
  `jan` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'JAN Code',
  `isbn` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'ISBN Code',
  `brand` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Brand',
  `google_category` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Google Cat',
  `gtin` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'GTIN Code',
  `mpn` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'MPN Code',
  `date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date Added',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Last Updated',
  `manufacturer` int(10) unsigned DEFAULT NULL COMMENT 'Manufacturer ID',
  `condition` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Condition',
  PRIMARY KEY (`product_id`),
  KEY `status` (`status`),
  KEY `popularity` (`popularity`),
  FULLTEXT KEY `fulltext` (`product_code`,`description`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `CubeCart_inventory_language` (
	`translation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL,
	`language` VARCHAR(5) NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`description` TEXT NOT NULL,
	`seo_meta_title` TEXT NOT NULL,
	`seo_meta_description` TEXT NOT NULL,
	`seo_meta_keywords` TEXT NOT NULL,
	KEY `id` (`translation_id`),
	FULLTEXT KEY `fulltext` (`name`,`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ 

CREATE TABLE IF NOT EXISTS `CubeCart_modules` (
	`module_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`module` VARCHAR(25) NOT NULL,
	`folder` VARBINARY(30) NOT NULL DEFAULT '',
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`default` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`countries` TINYTEXT DEFAULT NULL,
	`position` int(11) NOT NULL DEFAULT '1',
	KEY `module_id` (`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_newsletter` (
	`newsletter_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`template_id` INT UNSIGNED NOT NULL,
	`date_saved` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`date_sent` TIMESTAMP,
	`subject` VARCHAR(250) NOT NULL,
	`sender_email` VARCHAR(254) NOT NULL,
	`sender_name` VARCHAR(255) NOT NULL,
	`content_html` TEXT NOT NULL,
	`content_text` TEXT NOT NULL,
	PRIMARY KEY (`newsletter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_newsletter_subscriber` (
	`subscriber_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`customer_id` INT UNSIGNED DEFAULT NULL,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`email` VARCHAR(254) NOT NULL,
	`validation` VARCHAR(50) DEFAULT NULL,
	PRIMARY KEY (`subscriber_id`),
	KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_options_set` (
	`set_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`set_name` TEXT NOT NULL,
	`set_description` TEXT NOT NULL,
	PRIMARY KEY (`set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_options_set_member` (
	`set_member_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`set_id` INT UNSIGNED NOT NULL,
	`option_id` INT UNSIGNED NOT NULL,
	`value_id` INT UNSIGNED NOT NULL,
	`priority` INT NOT NULL,
	PRIMARY KEY (`set_member_id`),
	KEY `set_id` (`set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_options_set_product` (
	`set_product_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`set_id` INT UNSIGNED NOT NULL,
	`product_id` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`set_product_id`),
	KEY `set_id` (`set_id`),
	KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_option_assign` (
	`assign_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product` INT UNSIGNED NOT NULL DEFAULT '0',
	`option_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`value_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`set_member_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`set_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`option_negative` tinyint(1) unsigned NOT NULL DEFAULT '0',
	`option_price` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`option_weight` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
	`matrix_include` TINYINT(1) NOT NULL DEFAULT  '0',
	PRIMARY KEY (`assign_id`),
	KEY `member_id` (`set_member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_option_group` (
	`option_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`option_name` VARBINARY(30) NOT NULL DEFAULT '',
	`option_description` TEXT NOT NULL,
	`option_type` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
	`option_required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`priority` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_option_matrix` (
  `matrix_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `options_identifier` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `cached_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stock_level` int(11) NOT NULL,
  `use_stock` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `product_code` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `upc` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ean` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `jan` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isbn` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `restock_note` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`matrix_id`),
  KEY `product_id` (`product_id`,`options_identifier`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ


CREATE TABLE IF NOT EXISTS `CubeCart_option_value` (
	`value_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`value_name` VARBINARY(30) NOT NULL DEFAULT '',
	`option_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`priority` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`value_id`),
	KEY `option_id` (`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_history` (
	`history_id` int(10) unsigned NOT NULL auto_increment,
	`cart_order_id` varchar(18) collate utf8_unicode_ci NOT NULL,
	`status` tinyint(2) unsigned NOT NULL default '0',
	`updated` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`history_id`),
  KEY `cart_order_id` (`cart_order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_inventory` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`product_code` VARCHAR(255) NOT NULL,
	`name` VARCHAR(225) NOT NULL,
	`quantity` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	`price` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`cart_order_id` VARCHAR(18) NOT NULL,
	`product_options` BLOB NULL,
	`digital` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`stock_updated` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`custom` BLOB NULL,
	`coupon_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
	`options_identifier` VARCHAR(32) NULL,
	PRIMARY KEY (`id`),
	KEY `product_id` (`product_id`),
	KEY `cart_order_id` (`cart_order_id`),
	KEY `options_identifier` (`options_identifier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_notes` (
	`note_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`admin_id` INT UNSIGNED NOT NULL,
	`cart_order_id` VARCHAR(18) NOT NULL,
	`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`content` TEXT NOT NULL,
	PRIMARY KEY (`note_id`),
	KEY `admin_id` (`admin_id`,`cart_order_id`,`time`),
	FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_summary` (
	`cart_order_id` VARCHAR(18) NOT NULL,
	`order_date` INT UNSIGNED NOT NULL DEFAULT '0',
	`customer_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`subtotal` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`discount` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`shipping` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`total_tax` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`total` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`offline_capture` BLOB NULL,
	`ship_method` VARCHAR(100) DEFAULT NULL,
	`ship_date` date DEFAULT NULL,
	`ship_tracking` VARCHAR(100) DEFAULT NULL,
	`gateway` VARCHAR(100) NOT NULL,
	`title` VARCHAR(100) NULL,
	`first_name` VARCHAR(100) NOT NULL,
	`last_name` VARCHAR(100) NOT NULL,
	`company_name` VARCHAR(200) DEFAULT NULL,
	`line1` VARCHAR(100) NOT NULL,
	`line2` VARCHAR(100) DEFAULT NULL,
	`town` VARCHAR(120) NOT NULL,
	`state` VARCHAR(100) NOT NULL,
	`postcode` VARCHAR(50) NOT NULL,
	`country` SMALLINT(3) UNSIGNED NOT NULL,
	`title_d` VARCHAR(100) NOT NULL,
	`first_name_d` VARCHAR(100) NOT NULL,
	`last_name_d` VARCHAR(100) NOT NULL,
	`company_name_d` VARCHAR(200) DEFAULT NULL,
	`line1_d` VARCHAR(100) NOT NULL,
	`line2_d` VARCHAR(100) DEFAULT NULL,
	`town_d` VARCHAR(120) NOT NULL,
	`state_d` VARCHAR(100) NOT NULL,
	`postcode_d` VARCHAR(50) NOT NULL,
	`country_d` SMALLINT(3) UNSIGNED NOT NULL,
	`phone` VARCHAR(50) NULL,
	`mobile` VARCHAR(50) NULL,
	`email` VARCHAR(254) NULL,
	`customer_comments` TEXT,
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'Supports IPv6 addresses',
	`dashboard` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`discount_type` char(1) NOT NULL DEFAULT 'f',
	`basket` BLOB NULL DEFAULT NULL,
	`lang` varchar(5) DEFAULT NULL,
	PRIMARY KEY (`cart_order_id`),
	KEY `customer_id` (`customer_id`),
	KEY `status` (`status`),
	KEY `email` (`email`),
	KEY `order_date` (`order_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_tax` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`cart_order_id` VARCHAR(18) NOT NULL,
	`tax_id` INT UNSIGNED NOT NULL,
	`amount` DECIMAL(16,2) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	KEY `cart_order_id` (`cart_order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_permissions` (
	`permission_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`admin_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`section_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`level` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`permission_id`),
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
	PRIMARY KEY (`price_id`),
	KEY `group_id` (`group_id`),
	KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_pricing_quantity` (
	`discount_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL,
	`group_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`quantity` INT UNSIGNED NOT NULL,
	`price` DECIMAL(16,2) NOT NULL,
	PRIMARY KEY (`discount_id`),
	KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_reviews` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`approved` SMALLINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`product_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`customer_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`rating` DECIMAL(2,1) UNSIGNED NOT NULL DEFAULT '0.0',
	`vote_up` INT NOT NULL DEFAULT '0',
	`vote_down` INT NOT NULL DEFAULT '0',
	`anon` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`name` VARCHAR(255) NOT NULL,
	`email` VARCHAR(254) NOT NULL,
	`title` VARCHAR(255) NOT NULL,
	`review` TEXT NOT NULL,
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'Supports IPv6 addresses',
	`time` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	KEY `product_id` (`product_id`),
	KEY `votes` (`vote_up`,`vote_down`),
	KEY `approved` (`approved`),
	FULLTEXT KEY `fulltext` (`name`,`email`,`title`,`review`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_saved_cart` (
  `customer_id` INT UNSIGNED NOT NULL,
  `basket` mediumblob NOT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_search` (
	`id` INT(64) UNSIGNED NOT NULL AUTO_INCREMENT,
	`hits` INT(64) NOT NULL DEFAULT '1',
	`searchstr` VARBINARY(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_sessions` (
	`session_id` VARCHAR(32) NOT NULL,
	`session_start` INT UNSIGNED NOT NULL DEFAULT '0',
	`session_last` INT UNSIGNED NOT NULL DEFAULT '0',
	`admin_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`customer_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`location` VARBINARY(255) DEFAULT NULL,
	`ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Supports IPv6 addresses',
	`useragent` TEXT NULL,
	PRIMARY KEY (`session_id`),
	KEY `customer_id` (`customer_id`),
	KEY `session_last` (`session_last`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_shipping_rates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `zone_id` INT NOT NULL default '0',
  `method_name` VARCHAR(255) NOT NULL DEFAULT '',
  `min_weight` DECIMAL(10,3) NOT NULL DEFAULT '0.000',
  `max_weight` DECIMAL(10,3) NOT NULL DEFAULT '0.000',
  `min_value` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
  `max_value` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
  `min_items` INT NOT NULL DEFAULT '0',
  `max_items` INT NOT NULL DEFAULT '0',
  `flat_rate` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `weight_rate` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `percent_rate` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `item_rate` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ


CREATE TABLE IF NOT EXISTS `CubeCart_shipping_zones` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `zone_name` VARCHAR(255) NOT NULL DEFAULT '',
  `countries` TEXT NOT NULL DEFAULT '',
  `states` TEXT NOT NULL DEFAULT '',
  `postcodes` TEXT NOT NULL DEFAULT '',
  `sort_order` INT NOT NULL DEFAULT '1',
  PRIMARY KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ


CREATE TABLE IF NOT EXISTS `CubeCart_system_error_log` (
	`log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`time` int(10) unsigned NOT NULL,
	`message` text COLLATE utf8_unicode_ci NOT NULL,
	`read` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_tax_class` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`tax_name` VARCHAR(50) NOT NULL,
	PRIMARY KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_tax_details` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARBINARY(150) NOT NULL DEFAULT '',
	`display` VARBINARY(150) NOT NULL DEFAULT '',
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_tax_rates` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`type_id` INT UNSIGNED NOT NULL DEFAULT '1',
	`details_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`country_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`county_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`tax_percent` DECIMAL(7,4) NOT NULL DEFAULT '0.0000',
	`goods` INT UNSIGNED NOT NULL DEFAULT '0',
	`shipping` INT UNSIGNED NOT NULL DEFAULT '0',
	`active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE KEY `type_id` (`type_id`,`details_id`,`country_id`,`county_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_trackback` (
	`trackback_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL,
	`url` VARCHAR(250) NOT NULL,
	`title` TEXT NULL,
	`excerpt` TINYTEXT NULL,
	`blog_name` TEXT NULL,
	PRIMARY KEY (`trackback_id`),
	UNIQUE KEY `url` (`url`),
	KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_transactions` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`gateway` VARCHAR(100) DEFAULT NULL,
	`extra` VARCHAR(255) DEFAULT NULL,
	`status` VARCHAR(50) DEFAULT NULL,
	`customer_id` INT UNSIGNED DEFAULT NULL,
	`order_id` VARCHAR(18) DEFAULT NULL,
	`trans_id` VARCHAR(50) DEFAULT NULL,
	`time` INT UNSIGNED DEFAULT NULL,
	`amount` DECIMAL(16,2) DEFAULT NULL,
	`captured` DECIMAL(16,2) DEFAULT NULL,
	`notes` TEXT NULL,
	PRIMARY KEY `id` (`id`),
	KEY `order_id` (`order_id`),
	KEY `customer_id` (`customer_id`),
	KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_request_log` (
	`request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`request_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`request` blob NOT NULL,
	`result` blob NOT NULL,
	`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ; #EOQ

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