ALTER TABLE  `CubeCart_inventory` ADD  `google_category` VARCHAR( 250 ) NULL; #EOQ
ALTER TABLE  `CubeCart_coupons` ADD `subtotal` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `shipping`; #EOQ