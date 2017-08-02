ALTER TABLE `CubeCart_inventory` CHANGE `product_weight` `product_weight` DECIMAL(10,4); #EOQ
ALTER TABLE `CubeCart_option_assign` CHANGE `option_weight` `option_weight` DECIMAL(10,4); #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `weight` `weight` DECIMAL(16,4); #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `basket` `basket` MEDIUMBLOB NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `sale_price` `sale_price` DECIMAL(16,2) NOT NULL DEFAULT '0.00' COMMENT 'Sale Price'; #EOQ