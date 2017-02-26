ALTER TABLE `CubeCart_coupons` ADD COLUMN `per_cust` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `allowed_uses`; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD COLUMN `coupon_code` VARCHAR(25) DEFAULT NULL AFTER `dashboard` COMMENT 'Records what coupon was used.'; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD INDEX (`coupon_code`); #EOQ
