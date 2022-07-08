ALTER TABLE `CubeCart_request_log` ADD INDEX(`time`); #EOQ
ALTER TABLE `CubeCart_geo_country` ADD INDEX(`numcode`); #EOQ
ALTER TABLE `CubeCart_order_summary` ADD INDEX(`dashboard`); #EOQ
ALTER TABLE `CubeCart_coupons` ADD `starts` DATE NOT NULL DEFAULT '0000-00-00' AFTER `discount_price`; #EOQ
ALTER TABLE `CubeCart_category` ADD `cat_hier_position` INT NOT NULL DEFAULT '0' AFTER `status`; #EOQ