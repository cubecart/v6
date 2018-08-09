ALTER TABLE `CubeCart_order_summary` CHANGE `custom_oid` `custom_oid` VARCHAR(50) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD UNIQUE(`custom_oid`); #EOQ