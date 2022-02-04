ALTER TABLE `CubeCart_filemanager` CHANGE `filesize` `filesize` BIGINT UNSIGNED NOT NULL; #EOQ
UPDATE `CubeCart_geo_zone` SET `name` = 'Laois' WHERE `name` LIKE 'Laoighis'; #EOQ
ALTER TABLE `CubeCart_coupons` ADD `free_shipping_excluded` ENUM('0','1') NOT NULL; #EOQ