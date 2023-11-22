SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_404_log` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `uri` varchar(510) NOT NULL,
  `hits` int UNSIGNED NOT NULL DEFAULT '1',
  `done` tinyint(1) NOT NULL DEFAULT '0',
  `warn` tinyint(1) NOT NULL DEFAULT '0',
  `ignore` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`uri`),
  KEY `ignore` (`ignore`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_access_log` (
	`log_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`type` CHAR(1) NOT NULL,
	`time` INT UNSIGNED NOT NULL,
	`username` VARCHAR(100) NOT NULL,
	`user_id` INT UNSIGNED NOT NULL,
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'Supports IPv6 addresses',
	`useragent` TEXT NOT NULL,
	`success` ENUM('Y','N') NOT NULL,
	PRIMARY KEY (`log_id`),
	KEY `type` (`type`),
	KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_addressbook` (
	`address_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`customer_id` INT UNSIGNED NOT NULL,
	`billing` ENUM('0','1') NOT NULL DEFAULT '0',
	`default` ENUM('0','1') NOT NULL DEFAULT '0',
	`description` VARCHAR(250) NOT NULL,
	`title` VARCHAR(16) NOT NULL,
	`first_name` VARCHAR(32) NOT NULL,
	`last_name` VARCHAR(32) NOT NULL,
	`company_name` VARCHAR(200) NOT NULL,
	`line1` VARCHAR(200) NOT NULL,
	`line2` VARCHAR(200) NOT NULL,
	`town` VARCHAR(100) NOT NULL,
	`state` VARCHAR(100) NOT NULL,
	`postcode` VARCHAR(15) NOT NULL,
	`country` SMALLINT(3) UNSIGNED NOT NULL,
	`w3w` VARCHAR(255) NOT NULL,
	`hash` varchar(32) NOT NULL,
	PRIMARY KEY (`address_id`),
	KEY `customer_id` (`customer_id`),
	KEY `billing` (`billing`),
	KEY `default` (`default`),
	KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_admin_log` (
	`log_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`admin_id` INT UNSIGNED NOT NULL,
	`time` INT UNSIGNED NOT NULL,
	`ip_address` VARCHAR(45) NOT NULL,
	`description` TEXT NOT NULL,
	`item_id` INT UNSIGNED NULL,
	`item_type` VARCHAR(4) NULL,
	PRIMARY KEY (`log_id`),
	KEY `admin_id` (`admin_id`),
	KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_admin_error_log` (
	`log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`admin_id` int(10) unsigned NOT NULL,
	`time` int(10) unsigned NOT NULL,
	`message` text NOT NULL,
	`read` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

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
	`language` VARCHAR(5) NOT NULL DEFAULT 'en-GB',
	`dashboard_notes` TEXT NULL,
	`order_notify` TINYINT(1) UNSIGNED DEFAULT '0',
	`tour_shown` ENUM('0','1') NOT NULL DEFAULT '0',
	PRIMARY KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_alt_shipping` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`status` SMALLINT(1) NOT NULL DEFAULT '0',
	`byprice` SMALLINT(1) NOT NULL,
	`global` SMALLINT(1) NOT NULL,
	`notes` VARCHAR(255) DEFAULT NULL,
	`order` INT UNSIGNED DEFAULT '0',
	PRIMARY KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_alt_shipping_prices` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`alt_ship_id` INT UNSIGNED NOT NULL,
	`low` DECIMAL(16,3) NOT NULL DEFAULT '0.000',
	`high` DECIMAL(16,3) NOT NULL DEFAULT '0.000',
	`price` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	PRIMARY KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_category` (
	`cat_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`cat_name` VARCHAR(100) NOT NULL,
	`cat_desc` TEXT NULL,
	`cat_parent_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`cat_image` int(10) NOT NULL,
	`per_ship` DECIMAL(20,2) NOT NULL DEFAULT '0.00',
	`item_ship` DECIMAL(20,2) NOT NULL DEFAULT '0.00',
	`item_int_ship` DECIMAL(20,2) NOT NULL DEFAULT '0.00',
	`per_int_ship` DECIMAL(20,2) NOT NULL DEFAULT '0.00',
	`hide` SMALLINT(1) NOT NULL DEFAULT '0',
	`seo_meta_title` TEXT NULL,
	`seo_meta_description` TEXT NULL,
	`seo_meta_keywords` TEXT NULL,
	`priority` SMALLINT(6) UNSIGNED NOT NULL DEFAULT '0',
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`cat_hier_position` int NOT NULL DEFAULT '0',
	PRIMARY KEY (`cat_id`),
	KEY `cat_parent_id` (`cat_parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_category_index` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`cat_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`product_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`primary` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `cat_id` (`cat_id`),
	KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_category_language` (
	`translation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`cat_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`language` VARCHAR(5) NULL DEFAULT NULL,
	`cat_name` VARCHAR(255) NULL DEFAULT NULL,
	`cat_desc` TEXT NOT NULL,
	`seo_meta_title` TEXT NULL,
	`seo_meta_description` TEXT NULL,
	`seo_meta_keywords` TEXT NULL,
	PRIMARY KEY `translation_id` (`translation_id`),
	KEY `cat_id` (`cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_code_snippet` (
  `snippet_id` int(11) NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `unique_id` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `hook_trigger` varchar(255) NOT NULL,
  `php_code` blob NOT NULL,
  `version` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`snippet_id`),
  UNIQUE KEY `unique_id` (`unique_id`),
  KEY `hook_trigger` (`hook_trigger`),
  KEY `enabled` (`enabled`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_config` (
	`name` VARCHAR(100) NOT NULL,
	`array` text NOT NULL,
	UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_coupons` (
	`coupon_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`archived` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`code` VARCHAR(25) NOT NULL,
	`product_id` TEXT NOT NULL,
	`manufacturer_id` TEXT NOT NULL,
	`discount_percent` DECIMAL(5,2) NOT NULL DEFAULT '0.00',
	`discount_price` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`starts` DATE NOT NULL DEFAULT '0000-00-00',
	`expires` DATE NOT NULL DEFAULT '0000-00-00',
	`allowed_uses` INT UNSIGNED NOT NULL DEFAULT '0',
	`min_subtotal` DECIMAL(16,2) UNSIGNED NOT NULL DEFAULT '0.00',
	`count` INT UNSIGNED NOT NULL DEFAULT '0',
	`shipping` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`free_shipping` ENUM('0','1') NOT NULL DEFAULT '0',
	`subtotal` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`description` text NOT NULL,
	`cart_order_id` VARCHAR(18) DEFAULT NULL,
	`email_sent` enum('0','1') NOT NULL DEFAULT '0',
	`coupon_per_customer` INT(10) UNSIGNED NULL DEFAULT NULL,
	`exclude_sale_items` ENUM('0','1') NOT NULL DEFAULT '0',
	PRIMARY KEY (`coupon_id`),
	UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_currency` (
  `currency_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varbinary(255) NOT NULL DEFAULT '',
  `code` varchar(7) NOT NULL,
  `iso` int(3) unsigned zerofill DEFAULT NULL,
  `symbol_left` tinyblob,
  `symbol_right` tinyblob,
  `value` decimal(10,5) NOT NULL DEFAULT '0.00000',
  `decimal_places` tinyint(2) unsigned DEFAULT '2',
  `updated` int(10) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `symbol_decimal` varchar(10) NOT NULL DEFAULT '.',
  `symbol_thousand` varchar(10) NOT NULL DEFAULT ',',
  `adjustment` decimal(5,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`currency_id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=32 ; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_customer` (
	`customer_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`email` VARCHAR(96) NOT NULL,
	`password` VARCHAR(128) DEFAULT NULL,
	`salt` VARCHAR(32) DEFAULT NULL,
	`new_password` tinyint(1) NOT NULL DEFAULT '1',
	`verify` VARCHAR(32) DEFAULT NULL,
	`title` VARCHAR(16) DEFAULT NULL,
	`first_name` VARCHAR(32) NOT NULL,
	`last_name` VARCHAR(32) NOT NULL,
	`country` SMALLINT(3) UNSIGNED NOT NULL DEFAULT '0',
	`phone` VARCHAR(20) NOT NULL,
	`mobile` VARCHAR(20) DEFAULT NULL,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`registered` INT UNSIGNED NOT NULL DEFAULT '0',
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'Supports IPv6 addresses',
	`order_count` INT UNSIGNED DEFAULT '0',
	`type` TINYINT(1) UNSIGNED DEFAULT '1',
	`language` VARCHAR(5) NOT NULL DEFAULT 'en-GB',
	`notes` text,
	PRIMARY KEY (`customer_id`),
	UNIQUE KEY `email` (`email`),
	FULLTEXT KEY `fulltext` (`first_name`,`last_name`,`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_customer_group` (
	`group_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`group_name` VARCHAR(150) NOT NULL,
	`group_description` TEXT NOT NULL,
	PRIMARY KEY (`group_id`),
	KEY (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_customer_membership` (
	`membership_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`group_id` INT UNSIGNED NOT NULL,
	`customer_id` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`membership_id`),
	KEY `group_id` (`group_id`),
	KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_documents` (
	`doc_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`doc_parent_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`doc_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`doc_order` INT UNSIGNED NOT NULL DEFAULT '0',
	`doc_terms` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`doc_home` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`doc_privacy` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`doc_lang` VARCHAR(5) NOT NULL,
	`doc_name` VARCHAR(200) NOT NULL,
	`doc_content` MEDIUMTEXT NOT NULL,
	`doc_url` VARCHAR(200) DEFAULT NULL,
	`doc_url_openin` TINYINT(1) UNSIGNED DEFAULT NULL,
	`seo_meta_title` TEXT NULL,
	`seo_meta_description` TEXT NULL,
	`seo_meta_keywords` TEXT NULL,
	`navigation_link` tinyint(1) unsigned NOT NULL DEFAULT '1',
	`doc_parse` tinyint(1) NOT NULL DEFAULT '0',
	`hide_title` enum('0','1') DEFAULT '0',
	PRIMARY KEY (`doc_id`),
	KEY `doc_parent_id` (`doc_parent_id`),
	KEY `doc_status` (`doc_status`),
	KEY `doc_home` (`doc_home`),
	KEY `doc_privacy` (`doc_privacy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_domains` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`language` varchar(5) NOT NULL,
	`domain` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_downloads` (
	`digital_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_inv_id` INT UNSIGNED NOT NULL,
	`customer_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`cart_order_id` VARCHAR(18) NOT NULL,
	`downloads` INT UNSIGNED NOT NULL DEFAULT '0',
	`expire` INT UNSIGNED NOT NULL DEFAULT '0',
	`product_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`accesskey` VARCHAR(32) NOT NULL,
	PRIMARY KEY `digital_id` (`digital_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_email_content` (
	`content_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`description` VARCHAR(255) NOT NULL DEFAULT '',
	`content_type` VARCHAR(70) NOT NULL,
	`language` VARCHAR(5) NOT NULL,
	`subject` VARCHAR(250) NOT NULL,
	`content_html` TEXT NOT NULL,
	`content_text` TEXT NOT NULL,
	PRIMARY KEY (`content_id`),
	KEY `content_type` (`content_type`),
	KEY `language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_email_template` (
	`template_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`template_default` ENUM('0','1') NOT NULL DEFAULT '0',
	`title` VARCHAR(100) NOT NULL,
	`content_html` TEXT NOT NULL,
	`content_text` TEXT NOT NULL,
	PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_extension_info` (
  `file_id` int(10) unsigned NOT NULL,
  `seller_id` int(10) unsigned NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `dir` varchar(255) NOT NULL,
  `modified` int(11) NOT NULL,
  `keep_current` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_id`),
  KEY (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_filemanager` (
	`file_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`type` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`disabled` TINYINT(1) NOT NULL DEFAULT '0',
	`filepath` varchar(255) COLLATE utf8_bin default NULL,
	`filename` VARCHAR(255) COLLATE utf8_bin NOT NULL,
	`filesize` BIGINT UNSIGNED NOT NULL,
	`mimetype` VARCHAR(50) NOT NULL,
	`md5hash` VARCHAR(32) NOT NULL,
	`title` varchar(16) NOT NULL,
	`description` TEXT NOT NULL,
	`stream` enum('0','1') NOT NULL DEFAULT '0',
	`alt` TEXT NOT NULL,
	PRIMARY KEY (`file_id`),
	KEY (`type`),
	KEY (`filepath`),
	KEY (`filename`),
	UNIQUE KEY (`md5hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_geo_country` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `iso` char(2) NOT NULL,
  `name` varbinary(80) NOT NULL DEFAULT '',
  `iso3` char(3) DEFAULT NULL,
  `numcode` smallint(3) unsigned zerofill DEFAULT NULL,
  `eu` enum('0','1') NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`iso`),
  KEY `id` (`id`),
  KEY `eu` (`eu`),
  KEY `numcode` (`numcode`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_geo_zone` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`country_id` SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0',
	`abbrev` VARBINARY(4) NOT NULL DEFAULT '',
	`name` VARBINARY(40) NOT NULL DEFAULT '',
	`status` enum('0','1') NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`),
	KEY (`status`),
	UNIQUE(`country_id`, `abbrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_history` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`version` VARCHAR(50) NOT NULL,
	`time` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_hooks` (
	`hook_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`plugin` VARCHAR(100) NOT NULL,
	`hook_name` VARCHAR(255) NOT NULL COMMENT 'A descriptive name for the hook',
	`enabled` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'All hooks should be disabled by DEFAULT',
	`trigger` VARCHAR(255) NOT NULL COMMENT 'The trigger used to call the hook',
	`filepath` TEXT NOT NULL,
	`priority` INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`hook_id`),
	KEY `trigger` (`trigger`),
	KEY `enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_image_index` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL,
	`file_id` INT UNSIGNED NOT NULL,
	`main_img` ENUM('0','1') NOT NULL DEFAULT '0',
	PRIMARY KEY `id` (`id`),
	KEY `file_id` (`file_id`),
	KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_inventory` (
  `product_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Product ID',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Status',
  `live_from` int(11) NOT NULL DEFAULT '0',
  `product_code` varchar(60) DEFAULT NULL COMMENT 'Product Code',
  `quantity` int(11) NOT NULL DEFAULT '1' COMMENT 'Quantity',
  `description` text COMMENT 'Description',
  `description_short` text NULL COMMENT 'Short Description',
  `price` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT 'Retail Price',
  `sale_price` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT 'Sale Price',
  `cost_price` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT 'Cost Price',
  `name` varchar(250) DEFAULT NULL COMMENT 'Product Name',
  `cat_id` int(10) unsigned DEFAULT '0' COMMENT 'Main Category ID',
  `popularity` int(10) unsigned DEFAULT '0' COMMENT 'Popularity',
  `stock_level` int(11) DEFAULT '0' COMMENT 'Main Stock Level',
  `stock_warning` int(10) NOT NULL DEFAULT '0' COMMENT 'Main Stock Warning level',
  `use_stock_level` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Use Stock Control',
  `digital` int(4) unsigned NOT NULL DEFAULT '0' COMMENT 'Is Digital?',
  `digital_path` varchar(255) DEFAULT NULL COMMENT 'Digital Path',
  `product_weight` decimal(10,4) DEFAULT NULL COMMENT 'Product Weight',
  `product_width` decimal(10,4) DEFAULT NULL COMMENT 'Product Width',
  `product_height` decimal(10,4) DEFAULT NULL COMMENT 'Product Height',
  `product_depth` decimal(10,4) DEFAULT NULL COMMENT 'Product Depth',
  `dimension_unit` VARCHAR(2) DEFAULT 'cm' COMMENT 'Product Dimension Unit',
  `tax_type` int(10) unsigned DEFAULT '0' NOT NULL COMMENT 'Tax Type',
  `tax_inclusive` tinyint(1) unsigned DEFAULT '0' COMMENT 'Price inclusive of tax',
  `featured` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Featured product',
  `latest` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Included on Homepage',
  `seo_meta_title` text NULL COMMENT 'SEO Meta Title',
  `seo_meta_description` text NULL COMMENT 'SEO Meta Description',
  `seo_meta_keywords` text NULL COMMENT 'SEO Meta Keywords',
  `upc` varchar(20) DEFAULT NULL COMMENT 'UPC Code',
  `ean` varchar(20) DEFAULT NULL COMMENT 'EAN Code',
  `jan` varchar(20) DEFAULT NULL COMMENT 'JAN Code',
  `isbn` varchar(20) DEFAULT NULL COMMENT 'ISBN Code',
  `brand` varchar(20) DEFAULT NULL COMMENT 'Brand',
  `google_category` varchar(250) DEFAULT NULL COMMENT 'Google Cat',
  `gtin` varchar(20) DEFAULT NULL COMMENT 'GTIN Code',
  `mpn` varchar(70) DEFAULT NULL COMMENT 'MPN Code',
  `date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date Added',
  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Last Updated',
  `manufacturer` int(10) unsigned DEFAULT NULL COMMENT 'Manufacturer ID',
  `condition` varchar(25) DEFAULT NULL COMMENT 'Condition',
  `available` enum('0','1') NOT NULL DEFAULT '1',
  `minimum_quantity` INT(10) NOT NULL DEFAULT '1',
  `maximum_quantity` INT(10) NULL DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `status` (`status`),
  KEY `live_from` (`live_from`),
  KEY `popularity` (`popularity`),
  KEY `featured` (`featured`),
  FULLTEXT KEY `fulltext` (`product_code`,`description`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_inventory_language` (
	`translation_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL,
	`language` VARCHAR(5) NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`description` TEXT NOT NULL,
	`description_short` TEXT NOT NULL,
	`seo_meta_title` TEXT NULL,
	`seo_meta_description` TEXT NULL,
	`seo_meta_keywords` TEXT NULL,
	PRIMARY KEY `translation_id` (`translation_id`),
	FULLTEXT KEY `fulltext` (`name`,`description`),
	KEY `language` (`language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_manufacturers` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(200) NOT NULL,
	`URL` VARCHAR(250) NULL,
	`image` INT(10) UNSIGNED NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ 

CREATE TABLE IF NOT EXISTS `CubeCart_modules` (
	`module_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`module` VARCHAR(60) NOT NULL,
	`folder` VARCHAR(60) NOT NULL,
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`default` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`countries` TINYTEXT DEFAULT NULL,
	`position` int(11) NOT NULL DEFAULT '1',
	PRIMARY KEY `module_id` (`module_id`),
	KEY `folder` (`folder`),
	KEY `status` (`status`),
	KEY `module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_newsletter_subscriber` (
	`subscriber_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`customer_id` int(10) unsigned DEFAULT '0',
	`status` tinyint(1) unsigned NOT NULL DEFAULT '0',
	`email` varchar(96) NOT NULL,
	`validation` varchar(50) DEFAULT NULL,
	`ip_address` varchar(45) NOT NULL,
	`date` datetime DEFAULT NULL,
	`imported` tinyint(1) DEFAULT '0',
	`dbl_opt` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`subscriber_id`),
	KEY `customer_id` (`customer_id`),
	KEY `email` (`email`),
	KEY `dbl_opt` (`dbl_opt`),
	KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_newsletter_subscriber_log` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`email` varchar(96) DEFAULT NULL,
	`log` text COLLATE utf8mb4_unicode_ci,
	`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`ip_address` varchar(45) DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_options_set` (
	`set_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`set_name` TEXT NOT NULL,
	`set_description` TEXT NOT NULL,
	PRIMARY KEY (`set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_options_set_member` (
	`set_member_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`set_id` INT UNSIGNED NOT NULL,
	`option_id` INT UNSIGNED NOT NULL,
	`value_id` INT UNSIGNED NOT NULL,
	`priority` INT NOT NULL,
	PRIMARY KEY (`set_member_id`),
	KEY `set_id` (`set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_options_set_product` (
	`set_product_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`set_id` INT UNSIGNED NOT NULL,
	`product_id` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`set_product_id`),
	KEY `set_id` (`set_id`),
	KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_option_assign` (
	`assign_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product` INT UNSIGNED NOT NULL DEFAULT '0',
	`option_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`value_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`set_member_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`set_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`option_default` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`option_negative` tinyint(1) unsigned NOT NULL DEFAULT '0',
	`option_price` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`option_weight` DECIMAL(10,4) NOT NULL DEFAULT '0.0000',
	`matrix_include` TINYINT(1) NOT NULL DEFAULT  '0',
	`absolute_price` enum('0','1') NOT NULL DEFAULT '0',
	`image_id` int DEFAULT NULL,
	PRIMARY KEY (`assign_id`),
	KEY `set_member_id` (`set_member_id`),
	KEY `product` (`product`),
	KEY `set_enabled` (`set_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_option_group` (
	`option_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`option_name` VARCHAR(50) NOT NULL DEFAULT '',
	`option_description` TEXT NOT NULL,
	`option_type` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
	`option_required` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`priority` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`option_id`),
	UNIQUE KEY `option_name` (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_option_matrix` (
  `matrix_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL,
  `options_identifier` varchar(32) NOT NULL,
  `cached_name` varchar(255) DEFAULT NULL,
  `cached_array` TEXT DEFAULT NULL,
  `stock_level` int(11) NOT NULL,
  `use_stock` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `product_code` varchar(60) DEFAULT NULL,
  `upc` varchar(20) DEFAULT NULL,
  `ean` varchar(20) DEFAULT NULL,
  `jan` varchar(20) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `gtin` varchar(20) DEFAULT NULL,
  `image` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `restock_note` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`matrix_id`),
  KEY `product_id` (`product_id`),
  KEY `options_identifier` (`options_identifier`),
  KEY `status` (`status`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ


CREATE TABLE IF NOT EXISTS `CubeCart_option_value` (
	`value_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`value_name` varchar(100) NOT NULL,
	`option_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`priority` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`value_id`),
	KEY `option_id` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_history` (
	`history_id` int(10) unsigned NOT NULL auto_increment,
	`cart_order_id` varchar(18) NOT NULL,
	`status` tinyint(2) unsigned NOT NULL default '0',
	`updated` int(10) unsigned NOT NULL default '0',
	`initiator` char(1) NOT NULL DEFAULT 'G',
  PRIMARY KEY  (`history_id`),
  KEY `cart_order_id` (`cart_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_inventory` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`product_code` VARCHAR(255) NOT NULL,
	`name` VARCHAR(225) NOT NULL,
	`quantity` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	`price` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`cost_price` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`tax` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`tax_percent` decimal(7,4) NOT NULL DEFAULT '0.0000',
	`cart_order_id` VARCHAR(18) NOT NULL,
	`product_options` BLOB NULL,
	`options_array` BLOB NULL,
	`digital` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`stock_updated` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`custom` BLOB NULL,
	`coupon_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`hash` varchar(32) DEFAULT NULL,
	`options_identifier` VARCHAR(32) NULL,
	PRIMARY KEY (`id`),
	KEY `product_id` (`product_id`),
	KEY `cart_order_id` (`cart_order_id`),
	KEY `options_identifier` (`options_identifier`),
	KEY `quantity` (`quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_notes` (
	`note_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`admin_id` int(10) UNSIGNED NOT NULL,
	`cart_order_id` varchar(18) NOT NULL,
	`time` int(11) UNSIGNED NOT NULL,
	`content` text NOT NULL,
	PRIMARY KEY (`note_id`),
	KEY `admin_id` (`admin_id`,`cart_order_id`,`time`),
	FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_summary` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`cart_order_id` VARCHAR(18) NOT NULL,
	`order_date` INT UNSIGNED NOT NULL DEFAULT '0',
	`customer_id` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	`subtotal` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`discount` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`shipping` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`shipping_tax` decimal(16,2) NOT NULL DEFAULT '0.00',
  	`shipping_tax_rate` decimal(7,4) NOT NULL DEFAULT '0.0000',
	`total_tax` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`total` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
	`offline_capture` BLOB NULL,
	`ship_method` VARCHAR(100) DEFAULT NULL,
	`weight` DECIMAL(16,4) NOT NULL DEFAULT '0.0000',
	`ship_product` VARCHAR(100) DEFAULT NULL,
	`ship_date` date DEFAULT NULL,
	`ship_tracking` TEXT DEFAULT NULL,
	`gateway` VARCHAR(100) NOT NULL,
	`title` VARCHAR(16) NULL,
	`first_name` VARCHAR(32) NOT NULL,
	`last_name` VARCHAR(32) NOT NULL,
	`company_name` VARCHAR(200) DEFAULT NULL,
	`line1` VARCHAR(100) NOT NULL,
	`line2` VARCHAR(100) DEFAULT NULL,
	`town` VARCHAR(120) NOT NULL,
	`state` VARCHAR(100) NOT NULL,
	`postcode` VARCHAR(50) NOT NULL,
	`country` SMALLINT(3) UNSIGNED NOT NULL,
	`w3w` varchar(255) NOT NULL,
	`title_d` VARCHAR(100) NOT NULL,
	`first_name_d` VARCHAR(32) NOT NULL,
	`last_name_d` VARCHAR(32) NOT NULL,
	`company_name_d` VARCHAR(200) DEFAULT NULL,
	`line1_d` VARCHAR(100) NOT NULL,
	`line2_d` VARCHAR(100) DEFAULT NULL,
	`town_d` VARCHAR(120) NOT NULL,
	`state_d` VARCHAR(100) NOT NULL,
	`postcode_d` VARCHAR(50) NOT NULL,
	`country_d` SMALLINT(3) UNSIGNED NOT NULL,
	`w3w_d` varchar(255) NOT NULL,
	`phone` VARCHAR(50) NULL,
	`mobile` VARCHAR(50) NULL,
	`email` VARCHAR(96) NULL,
	`customer_comments` TEXT,
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'Supports IPv6 addresses',
	`dashboard` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`discount_type` char(1) NOT NULL DEFAULT 'f',
	`basket` MEDIUMBLOB NULL DEFAULT NULL,
	`lang` varchar(5) DEFAULT NULL,
	`note_to_customer` TEXT,
	`custom_oid` varchar(50) DEFAULT NULL,
	`currency` varchar(3) DEFAULT '',
	PRIMARY KEY (`id`),
	UNIQUE KEY `cart_order_id` (`cart_order_id`),
	UNIQUE KEY `custom_oid` (`custom_oid`),
	KEY `customer_id` (`customer_id`),
	KEY `status` (`status`),
	KEY `email` (`email`),
	KEY `order_date` (`order_date`),
	KEY `dashboard` (`dashboard`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_tax` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`cart_order_id` VARCHAR(18) NOT NULL,
	`tax_id` INT UNSIGNED NOT NULL,
	`amount` DECIMAL(16,2) UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	KEY `cart_order_id` (`cart_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_permissions` (
	`permission_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`admin_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`section_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`level` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`permission_id`),
	KEY `admin_id` (`admin_id`),
	KEY `section_id` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

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
	KEY `product_id` (`product_id`),
	KEY `tax_type` (`tax_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_pricing_quantity` (
	`discount_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL,
	`group_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`quantity` INT UNSIGNED NOT NULL,
	`price` DECIMAL(16,2) NOT NULL,
	PRIMARY KEY (`discount_id`),
	KEY `product_id` (`product_id`),
	KEY `group_id` (`group_id`),
	KEY `quantity` (`quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

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
	`email` VARCHAR(96) NOT NULL,
	`title` VARCHAR(255) NOT NULL,
	`review` TEXT NOT NULL,
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'Supports IPv6 addresses',
	`time` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	KEY `product_id` (`product_id`),
	KEY `vote_up` (`vote_up`),
	KEY `vote_down` (`vote_down`),
	KEY `approved` (`approved`),
	FULLTEXT KEY `fulltext` (`name`,`email`,`title`,`review`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_saved_cart` (
  `customer_id` INT UNSIGNED NOT NULL,
  `basket` mediumblob NOT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_search` (
	`id` INT(64) UNSIGNED NOT NULL AUTO_INCREMENT,
	`hits` INT(64) NOT NULL DEFAULT '1',
	`searchstr` VARBINARY(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_sessions` (
	`session_id` VARCHAR(32) NOT NULL,
	`session_start` INT UNSIGNED NOT NULL DEFAULT '0',
	`session_last` INT UNSIGNED NOT NULL DEFAULT '0',
	`admin_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`customer_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`location` VARBINARY(255) DEFAULT NULL,
	`ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Supports IPv6 addresses',
	`useragent` TEXT NULL,
	`acp` TINYINT(1) DEFAULT 0,
	PRIMARY KEY (`session_id`),
	KEY `customer_id` (`customer_id`),
	KEY `session_last` (`session_last`),
	KEY `acp` (`acp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_shipping_rates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `zone_id` INT NOT NULL default '0',
  `method_name` VARCHAR(255) NOT NULL DEFAULT '',
  `min_weight` DECIMAL(10,4) NOT NULL DEFAULT '0.0000',
  `max_weight` DECIMAL(10,4) NOT NULL DEFAULT '0.0000',
  `min_value` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
  `max_value` DECIMAL(16,2) NOT NULL DEFAULT '0.00',
  `min_items` INT NOT NULL DEFAULT '0',
  `max_items` INT NOT NULL DEFAULT '0',
  `flat_rate` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `weight_rate` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `percent_rate` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `item_rate` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY `id` (`id`),
  KEY `zone_id` (`zone_id`),
  KEY `method_name` (`method_name`),
  KEY `min_weight` (`min_weight`),
  KEY `max_weight` (`max_weight`),
  KEY `min_value` (`min_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ


CREATE TABLE IF NOT EXISTS `CubeCart_shipping_zones` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `zone_name` VARCHAR(255) NOT NULL DEFAULT '',
  `countries` TEXT NOT NULL DEFAULT '',
  `states` TEXT NOT NULL DEFAULT '',
  `postcodes` TEXT NOT NULL DEFAULT '',
  `sort_order` INT NOT NULL DEFAULT '1',
  PRIMARY KEY `id` (`id`),
  KEY `zone_name` (`zone_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ


CREATE TABLE IF NOT EXISTS `CubeCart_system_error_log` (
	`log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`time` int(10) unsigned NOT NULL,
	`message` text NOT NULL,
	`url` varchar(255) NOT NULL,
	`backtrace` TEXT NOT NULL,
	`read` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `time` (`time`),
  KEY `read` (`read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_tax_class` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`tax_name` VARCHAR(50) NOT NULL,
	PRIMARY KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_tax_details` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARBINARY(150) NOT NULL DEFAULT '',
	`display` VARBINARY(150) NOT NULL DEFAULT '',
	`status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

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
	PRIMARY KEY (`id`),
	UNIQUE KEY `type_id` (`type_id`,`details_id`,`country_id`,`county_id`),
	KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_request_log` (
	`request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`request_url` varchar(255) DEFAULT NULL,
	`request` blob NOT NULL,
	`result` blob NOT NULL,
	`response_code` varchar(3) DEFAULT NULL,
  	`is_curl` enum('1','0') DEFAULT NULL,
	`error` blob NOT NULL,
	`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`request_headers` blob NULL,
  	`response_headers` blob NULL,
  PRIMARY KEY (`request_id`),
  KEY `time` (`time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_seo_urls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL,
  `type` varchar(45) NOT NULL,
  `item_id` int(25) unsigned DEFAULT NULL,
  `custom` enum('0','1') NOT NULL DEFAULT '0',
  `redirect` enum('0','301','302') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`path`),
  KEY `id` (`id`),
  KEY `type` (`type`),
  KEY `item_id` (`item_id`),
  KEY `custom` (`custom`),
  KEY `redirect` (`redirect`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_email_log` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content_html` text NOT NULL,
  `content_text` text NOT NULL,
  `to` varchar(255) NOT NULL,
  `from` varchar(255) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email_content_id` int(11) NOT NULL,
  `result` tinyint(1) NOT NULL,
  `fail_reason` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ
ALTER TABLE `CubeCart_email_log` ADD PRIMARY KEY (`id`); #EOQ
ALTER TABLE `CubeCart_email_log` ADD INDEX(`to`); #EOQ
ALTER TABLE `CubeCart_email_log` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_invoice_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8mb4_unicode_ci,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `hash` varchar(35) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_cookie_consent` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) DEFAULT NULL,
  `session_id` varchar(32) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `log` text DEFAULT NULL,
  `time` INT UNSIGNED NOT NULL DEFAULT '0',
  `log_hash` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url_shown` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip_address` (`ip_address`),
  KEY `session_id` (`session_id`),
  KEY `customer_id` (`customer_id`),
  KEY `log_hash` (`log_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_customer_coupon` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(96) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coupon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `used` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `email` (`email`),
  KEY `coupon` (`coupon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ