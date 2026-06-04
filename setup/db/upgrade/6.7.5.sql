ALTER TABLE `CubeCart_cron_tasks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; #EOQ
ALTER TABLE `CubeCart_coupons` ADD COLUMN `country_id` TEXT NOT NULL AFTER `shipping_id`; #EOQ
ALTER TABLE `CubeCart_filemanager` DROP INDEX `md5hash`; #EOQ
ALTER TABLE `CubeCart_filemanager` ADD INDEX `md5hash` (`md5hash`); #EOQ
