CREATE TABLE IF NOT EXISTS `CubeCart_tariff` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source` varchar(2) NOT NULL,
  `destination` varchar(2) NOT NULL,
  `tariff` enum('D','M') NOT NULL,
  `percent` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `combos` (`source`,`destination`,`tariff`),
  KEY `source` (`source`),
  KEY `destination` (`destination`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `manufacture_country` VARCHAR(2) NULL; #EOQ
ALTER TABLE `CubeCart_order_tax` CHANGE `tax_id` `tax_id` VARCHAR(10) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `line1` VARCHAR(200) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `line2` VARCHAR(200) NOT NULL; #EOQ 
ALTER TABLE `CubeCart_manufacturers` ADD `town` VARCHAR(100) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `state` VARCHAR(100) NOT NULL; #EOQ 
ALTER TABLE `CubeCart_manufacturers` ADD `postcode` VARCHAR(15) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `country` MEDIUMINT(3) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `email` VARCHAR(200) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `phone` VARCHAR(20) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `contact_url` VARCHAR(200) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `eu_name` VARCHAR(200) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `eu_line1` VARCHAR(200) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `eu_line2` VARCHAR(200) NOT NULL; #EOQ 
ALTER TABLE `CubeCart_manufacturers` ADD `eu_town` VARCHAR(100) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `eu_state` VARCHAR(100) NOT NULL; #EOQ 
ALTER TABLE `CubeCart_manufacturers` ADD `eu_postcode` VARCHAR(15) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `eu_country` MEDIUMINT(3) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `eu_email` VARCHAR(200) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `eu_phone` VARCHAR(20) NOT NULL; #EOQ
ALTER TABLE `CubeCart_manufacturers` ADD `eu_contact_url` VARCHAR(200) NOT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `spec_array` TEXT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `spec_copy` TEXT NULL; #EOQ