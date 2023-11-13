CREATE TABLE IF NOT EXISTS `CubeCart_saved_cart` (
  `customer_id` INT UNSIGNED NOT NULL,
  `basket` mediumblob NOT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ