ALTER TABLE `CubeCart_order_notes` DROP INDEX `admin_id`; #EOQ
ALTER TABLE `CubeCart_order_notes` DROP INDEX `time`; #EOQ
ALTER TABLE `CubeCart_order_notes` DROP INDEX `cart_order_id`; #EOQ
ALTER TABLE `CubeCart_order_notes` ADD INDEX `admin_id` (`admin_id`, `cart_order_id`, `time`); #EOQ
ALTER TABLE `CubeCart_pricing_quantity` ADD INDEX (`group_id`); #EOQ
ALTER TABLE `CubeCart_pricing_quantity` ADD INDEX (`quantity`); #EOQ