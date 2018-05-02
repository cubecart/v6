ALTER TABLE `CubeCart_order_summary` ADD `custom_oid` VARCHAR(50) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_geo_zone` ADD `status` enum('0','1') NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_geo_zone` ADD INDEX (`status`); #EOQ
ALTER TABLE `CubeCart_geo_country` CHANGE `status` `status` TINYINT(1)  NOT NULL  DEFAULT '1'; #EOQ
UPDATE `CubeCart_geo_country` SET `status` = `status` - 1; #EOQ
UPDATE `CubeCart_geo_country` SET `status` = 2 WHERE `iso` NOT IN('AR', 'BR', 'CA', 'CN', 'ID', 'IN', 'JP', 'MX', 'TH', 'US'); #EOQ
ALTER TABLE `CubeCart_inventory` ADD `product_width` DECIMAL(10,4) DEFAULT NULL COMMENT 'Product Width'; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `product_height` DECIMAL(10,4) DEFAULT NULL COMMENT 'Product Height'; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `product_depth` DECIMAL(10,4) DEFAULT NULL COMMENT 'Product Depth'; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `dimension_unit` VARCHAR(2) DEFAULT NULL COMMENT 'Product Dimension Unit'; #EOQ
ALTER TABLE `CubeCart_newsletter_subscriber` ADD `ip_address` VARCHAR(45) NOT NULL; #EOQ
ALTER TABLE `CubeCart_newsletter_subscriber` ADD `date` DATETIME DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_newsletter_subscriber` ADD `imported` TINYINT(1) DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_newsletter_subscriber` ADD `dbl_opt` enum('0','1') DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_newsletter_subscriber` CHANGE `customer_id` `customer_id` INT(10)  UNSIGNED  NULL  DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_newsletter_subscriber` ADD INDEX (`dbl_opt`); #EOQ
ALTER TABLE `CubeCart_newsletter_subscriber` ADD INDEX (`status`); #EOQ
ALTER TABLE `CubeCart_newsletter` ADD `dbl_opt` enum('0','1') NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_email_log` ADD `fail_reason` TEXT  NULL; #EOQ
CREATE TABLE `CubeCart_invoice_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8_unicode_ci,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `hash` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_documents` ADD `hide_title` ENUM('0', '1')  DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `currency` VARCHAR(3)  NULL  DEFAULT ''; #EOQ