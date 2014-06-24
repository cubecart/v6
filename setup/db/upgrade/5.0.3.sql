CREATE TABLE IF NOT EXISTS `CubeCart_saved_cart` (
  `customer_id` INT UNSIGNED NOT NULL,
  `basket` mediumblob NOT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ