ALTER TABLE `CubeCart_order_summary` ADD `custom_oid` VARCHAR(50) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_system_error_log` ADD UNIQUE (`custom_oid`); #EOQ
ALTER TABLE `CubeCart_geo_zone` ADD `status` TINYINT(1)  UNSIGNED  NULL  DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_geo_zone` ADD INDEX (`status`); #EOQ