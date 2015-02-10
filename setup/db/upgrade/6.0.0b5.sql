ALTER TABLE  `CubeCart_order_summary` ADD  `note_to_customer` TEXT NOT NULL; #EOQ
ALTER TABLE  `CubeCart_order_summary` DROP PRIMARY KEY, ADD INDEX (`cart_order_id`); #EOQ
ALTER TABLE  `CubeCart_order_summary` ADD  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST; #EOQ
ALTER TABLE  `CubeCart_order_inventory` ADD  `options_array` BLOB NULL AFTER  `product_options`; #EOQ