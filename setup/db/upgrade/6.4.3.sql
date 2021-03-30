UPDATE `CubeCart_geo_country` SET `eu` = '0' WHERE `iso` = 'GB'; #EOQ
ALTER TABLE `CubeCart_inventory` ADD COLUMN `maximum_quantity` INT(10) NULL DEFAULT NULL AFTER `minimum_quantity`; #EOQ
ALTER TABLE `CubeCart_newsletter_subscriber` ADD INDEX(`email`); #EOQ