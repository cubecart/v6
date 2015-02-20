ALTER TABLE  `CubeCart_sessions` CHANGE  `useragent`  `useragent` TEXT NULL; #EOQ

ALTER TABLE  `CubeCart_access_log` CHANGE  `user_id`  `user_id` INT( 10 ) UNSIGNED NULL; #EOQ

ALTER TABLE `CubeCart_inventory` ADD `updated` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'; #EOQ

ALTER TABLE  `CubeCart_modules` CHANGE  `default`  `default` TINYINT( 1 ) UNSIGNED NULL DEFAULT  '0'; #EOQ

ALTER TABLE  `CubeCart_order_summary` CHANGE  `phone`  `phone` VARCHAR( 50 ) NULL; #EOQ

ALTER TABLE  `CubeCart_order_summary` CHANGE  `phone`  `phone` VARCHAR( 50 ) NULL; #EOQ

ALTER TABLE  `CubeCart_order_summary` CHANGE  `email`  `email` VARCHAR( 254 ) NULL; #EOQ

ALTER TABLE  `CubeCart_order_summary` CHANGE  `mobile`  `mobile` VARCHAR( 50 ) NULL; #EOQ

ALTER TABLE  `CubeCart_order_summary` CHANGE  `title`  `title` VARCHAR( 10 ) NULL; #EOQ

ALTER TABLE  `CubeCart_coupons` CHANGE  `product_id`  `product_id` TEXT; #EOQ

ALTER TABLE  `CubeCart_inventory` CHANGE  `upc`  `upc` VARCHAR( 20 ) NULL DEFAULT NULL; #EOQ

ALTER TABLE  `CubeCart_inventory` CHANGE  `ean`  `ean` VARCHAR( 20 ) NULL DEFAULT NULL; #EOQ

ALTER TABLE  `CubeCart_inventory` CHANGE  `jan`  `jan` VARCHAR( 20 ) NULL DEFAULT NULL; #EOQ

ALTER TABLE  `CubeCart_inventory` CHANGE  `isbn`  `isbn` VARCHAR( 20 ) NULL DEFAULT NULL; #EOQ

ALTER TABLE  `CubeCart_customer` CHANGE  `type`  `type` TINYINT( 1 ) UNSIGNED NULL DEFAULT  '1'; #EOQ

UPDATE `CubeCart_customer` SET  `type` = 1 WHERE  `type` = 0; #EOQ