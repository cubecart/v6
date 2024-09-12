CREATE TABLE IF NOT EXISTS `CubeCart_category_discount` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cat_id` int NOT NULL,
  `group_id` int NOT NULL,
  `percent` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cat_id` (`cat_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `coupon_data` TEXT NULL DEFAULT NULL; #EOQ