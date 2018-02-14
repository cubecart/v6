ALTER TABLE `CubeCart_order_summary` ADD `custom_oid` VARCHAR(50) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_system_error_log` ADD INDEX (`custom_oid`); #EOQ