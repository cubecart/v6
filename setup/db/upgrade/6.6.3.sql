ALTER TABLE `CubeCart_order_inventory` DROP INDEX `quantity`; #EOQ
ALTER TABLE `CubeCart_order_inventory` ADD INDEX `cartorder_product_qty` (`cart_order_id`, `product_id`, `quantity`); #EOQ
ALTER TABLE `CubeCart_order_summary` DROP INDEX `status`; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD INDEX `status_cartorder` (`status`, `cart_order_id`); #EOQ
