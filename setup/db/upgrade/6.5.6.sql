ALTER TABLE `CubeCart_filemanager` CHANGE `title` `title` VARCHAR(255) NOT NULL; #EOQ
ALTER TABLE `CubeCart_coupons` ADD `category_id` TEXT NOT NULL AFTER `manufacturer_id`; #EOQ
ALTER TABLE `CubeCart_customer` ADD `credit` DECIMAL(8,2) NOT NULL DEFAULT '0.00'; #EOQ