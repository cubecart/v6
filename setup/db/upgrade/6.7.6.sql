DELETE FROM `CubeCart_image_index` WHERE `product_id` = 0; #EOQ
ALTER TABLE `CubeCart_search` ADD INDEX `searchstr` (`searchstr`); #EOQ
ALTER TABLE `CubeCart_saved_cart` ADD COLUMN `updated` INT UNSIGNED NOT NULL DEFAULT 0; #EOQ
