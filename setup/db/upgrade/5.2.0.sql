ALTER TABLE `CubeCart_option_assign` DROP `option_use_stock`, DROP `option_stock`; #EOQ

ALTER TABLE  `CubeCart_option_assign` ADD  `matrix_include` TINYINT( 1 ) NOT NULL DEFAULT  '0'; #EOQ

ALTER TABLE  `CubeCart_order_inventory` ADD  `options_identifier` VARCHAR( 32 ) NULL; #EOQ

ALTER TABLE  `CubeCart_order_inventory` ADD INDEX  `options_identifier` (`options_identifier`); #EOQ

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
  PRIMARY KEY (`matrix_id`),
  KEY `product_id` (`product_id`,`options_identifier`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_shipping_rates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` int(11) NOT NULL DEFAULT '0',
  `method_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `min_weight` decimal(10,3) NOT NULL DEFAULT '0.000',
  `max_weight` decimal(10,3) NOT NULL DEFAULT '0.000',
  `min_value` decimal(16,2) NOT NULL DEFAULT '0.00',
  `max_value` decimal(16,2) NOT NULL DEFAULT '0.00',
  `min_items` int(11) NOT NULL DEFAULT '0',
  `max_items` int(11) NOT NULL DEFAULT '0',
  `flat_rate` decimal(12,2) NOT NULL DEFAULT '0.00',
  `weight_rate` decimal(12,2) NOT NULL DEFAULT '0.00',
  `percent_rate` decimal(12,2) NOT NULL DEFAULT '0.00',
  `item_rate` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_shipping_zones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `zone_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `countries` text COLLATE utf8_unicode_ci NOT NULL,
  `states` text COLLATE utf8_unicode_ci NOT NULL,
  `postcodes` text COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ

ALTER TABLE  `CubeCart_modules` ADD  `position` INT NOT NULL DEFAULT '1'; #EOQ

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

ALTER TABLE `CubeCart_inventory` CHANGE `product_id` `product_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Product ID'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `status` `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Status'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `product_code` `product_code` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'Product Code'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `quantity` `quantity` INT(11) NOT NULL DEFAULT '1' COMMENT 'Quantity'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'Description'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `price` `price` DECIMAL(16,2) NOT NULL DEFAULT '0.00' COMMENT 'Retail Price'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `sale_price` `sale_price` DECIMAL(16,2) NULL DEFAULT '0.00' COMMENT 'Sale Price'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `cost_price` `cost_price` DECIMAL(16,2) NOT NULL DEFAULT '0.00' COMMENT 'Cost Price'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `name` `name` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'Product Name'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `cat_id` `cat_id` INT(10) UNSIGNED NULL DEFAULT '0' COMMENT 'Main Category ID'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `popularity` `popularity` INT(10) UNSIGNED NULL DEFAULT '0' COMMENT 'Popularity'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `stock_level` `stock_level` INT(11) NULL DEFAULT '0' COMMENT 'Main Stock Level'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `stock_warning` `stock_warning` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Main Stock Warning level'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `use_stock_level` `use_stock_level` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Use Stock Control'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `digital` `digital` INT(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is Digital?'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `digital_path` `digital_path` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'Digital Path'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `product_weight` `product_weight` DECIMAL(10,3) NULL DEFAULT NULL COMMENT 'Product Weight'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `tax_type` `tax_type` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Tax Type'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `tax_inclusive` `tax_inclusive` TINYINT(1) UNSIGNED NULL DEFAULT '0' COMMENT 'Price inclusive of tax'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `featured` `featured` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Included on Homepage'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `seo_meta_title` `seo_meta_title` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'SEO Meta Title'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `seo_meta_description` `seo_meta_description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'SEO Meta Description'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `seo_meta_keywords` `seo_meta_keywords` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'SEO Meta Keywords'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `upc` `upc` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'UPC Code'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `ean` `ean` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'EAN Code'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `jan` `jan` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'JAN Code'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `isbn` `isbn` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'ISBN Code'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `date_added` `date_added` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date Added'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `updated` `updated` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Last Updated'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `manufacturer` `manufacturer` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Manufacturer ID'; #EOQ 
ALTER TABLE `CubeCart_inventory` CHANGE `condition` `condition` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'Condition'; #EOQ

DELETE FROM `CubeCart_request_log` WHERE `request_url` LIKE '%cubecart.com%'; #EOQ

ALTER TABLE  `CubeCart_option_matrix` ADD  `restock_note` VARCHAR( 255 ) NOT NULL; #EOQ