ALTER TABLE `CubeCart_order_summary` CHANGE `custom_oid` `custom_oid` VARCHAR(50) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD UNIQUE(`custom_oid`); #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `dimension_unit` `dimension_unit` VARCHAR(2) NULL DEFAULT 'cm'; #EOQ
ALTER TABLE `CubeCart_order_inventory` ADD `cost_price` DECIMAL(16,2) NOT NULL AFTER `price`; #EOQ