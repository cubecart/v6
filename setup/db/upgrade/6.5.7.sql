CREATE TABLE IF NOT EXISTS `CubeCart_category_discount` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cat_id` int NOT NULL,
  `group_id` int NOT NULL,
  `percent` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cat_id` (`cat_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `coupon_data` TEXT NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_order_notes` ADD `print` ENUM('0','1') NOT NULL DEFAULT '1' AFTER `content`; #EOQ
ALTER TABLE `CubeCart_order_notes` ADD INDEX(`print`); #EOQ
ALTER TABLE `CubeCart_coupons` ADD `shipping_id` TEXT NOT NULL AFTER `category_id`; #EOQ