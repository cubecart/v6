ALTER TABLE `CubeCart_order_summary` ADD `custom_oid` VARCHAR(50) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_system_error_log` ADD UNIQUE (`custom_oid`); #EOQ
ALTER TABLE `CubeCart_geo_zone` ADD `status` enum('0','1') NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_geo_zone` ADD INDEX (`status`); #EOQ
ALTER TABLE `CubeCart_geo_country` CHANGE `status` `status` TINYINT(1)  NOT NULL  DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `product_width` DECIMAL(10,4) DEFAULT NULL COMMENT 'Product Width'; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `product_height` DECIMAL(10,4) DEFAULT NULL COMMENT 'Product Height'; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `product_depth` DECIMAL(10,4) DEFAULT NULL COMMENT 'Product Depth'; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `dimension_unit` VARCHAR(2) DEFAULT NULL COMMENT 'Product Dimension Unit'; #EOQ