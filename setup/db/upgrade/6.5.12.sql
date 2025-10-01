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