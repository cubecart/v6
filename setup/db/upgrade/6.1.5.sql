ALTER TABLE `CubeCart_order_notes` DROP INDEX `admin_id`; #EOQ
ALTER TABLE `CubeCart_order_notes` DROP INDEX `time`; #EOQ
ALTER TABLE `CubeCart_order_notes` DROP INDEX `cart_order_id`; #EOQ
ALTER TABLE `CubeCart_order_notes` ADD INDEX `admin_id` (`admin_id`, `cart_order_id`, `time`); #EOQ
ALTER TABLE `CubeCart_pricing_quantity` ADD INDEX (`group_id`); #EOQ
ALTER TABLE `CubeCart_pricing_quantity` ADD INDEX (`quantity`); #EOQ
ALTER TABLE `CubeCart_modules` ADD INDEX (`folder`); #EOQ
ALTER TABLE `CubeCart_modules` ADD INDEX (`status`); #EOQ
ALTER TABLE `CubeCart_modules` ADD INDEX (`module`); #EOQ
ALTER TABLE `CubeCart_access_log` ADD INDEX (`time`); #EOQ
ALTER TABLE `CubeCart_access_log` ADD INDEX (`type`); #EOQ
ALTER TABLE `CubeCart_addressbook` ADD INDEX (`billing`); #EOQ
ALTER TABLE `CubeCart_addressbook` ADD INDEX (`hash`); #EOQ
ALTER TABLE `CubeCart_addressbook` ADD INDEX (`default`); #EOQ
ALTER TABLE `CubeCart_admin_log` ADD INDEX (`time`); #EOQ