ALTER TABLE `CubeCart_order_summary` CHANGE `custom_oid` `custom_oid` VARCHAR(50) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD UNIQUE(`custom_oid`); #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `dimension_unit` `dimension_unit` VARCHAR(2) NULL DEFAULT 'cm'; #EOQ
ALTER TABLE `CubeCart_order_inventory` ADD `cost_price` DECIMAL(16,2) NOT NULL AFTER `price`; #EOQ
ALTER TABLE `CubeCart_order_inventory` ADD `tax_percent` DECIMAL(7,4 ) NOT NULL DEFAULT '0.0000' AFTER `tax`; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `shipping_tax` DECIMAL(16,2) NOT NULL DEFAULT '0.00' AFTER `shipping`; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `shipping_tax_rate` DECIMAL(7,4) NOT NULL DEFAULT '0.0000' AFTER `shipping_tax`; #EOQ
ALTER TABLE `CubeCart_email_content` ADD `description` VARCHAR(255) NOT NULL DEFAULT '' AFTER `content_id`; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `live_from` int(11) NOT NULL DEFAULT '0' AFTER `status`
ALTER TABLE `CubeCart_inventory` ADD INDEX (`live_from`); #EOQ