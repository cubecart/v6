ALTER TABLE `CubeCart_order_summary` ADD `custom_oid` VARCHAR(50) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD UNIQUE (`custom_oid`); #EOQ
ALTER TABLE `CubeCart_geo_zone` ADD `status` enum('0','1') NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_geo_zone` ADD INDEX (`status`); #EOQ
ALTER TABLE `CubeCart_geo_country` CHANGE `status` `status` TINYINT(1)  NOT NULL  DEFAULT '1'; #EOQ
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
UPDATE `CubeCart_geo_country` SET `status` = 2 WHERE `iso` NOT IN('AR', 'BR', 'CA', 'CN', 'ID', 'IN', 'JP', 'MX', 'TH', 'US'); #EOQ
ALTER TABLE `CubeCart_newsletter` ADD `dbl_opt` enum('0','1') NOT NULL DEFAULT '0'; #EOQ