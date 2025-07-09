ALTER TABLE `CubeCart_filemanager` CHANGE `md5hash` `md5hash` VARCHAR(32) default NULL; #EOQ
ALTER TABLE `CubeCart_email_log` ADD COLUMN `attachment` text NULL; #EOQ
ALTER TABLE `CubeCart_sessions` DROP PRIMARY KEY; #EOQ
ALTER TABLE `CubeCart_sessions` ADD KEY (`session_id`); #EOQ
ALTER TABLE `CubeCart_sessions` ADD `id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`); #EOQ
ALTER TABLE `CubeCart_seo_urls` DROP PRIMARY KEY; #EOQ
ALTER TABLE `CubeCart_seo_urls` DROP KEY (`id`); #EOQ
ALTER TABLE `CubeCart_seo_urls` ADD PRIMARY KEY(`id`); #EOQ
ALTER TABLE `CubeCart_seo_urls`  ADD UNIQUE (`path`); #EOQ