ALTER TABLE `CubeCart_alt_shipping` DROP INDEX `id`, ADD PRIMARY KEY (`id`) USING BTREE; #EOQ
ALTER TABLE `CubeCart_alt_shipping_prices` DROP INDEX `id`, ADD PRIMARY KEY (`id`) USING BTREE; #EOQ
ALTER TABLE `CubeCart_category_language` DROP INDEX `id`, ADD PRIMARY KEY (`translation_id`) USING BTREE; #EOQ
ALTER TABLE `CubeCart_downloads` DROP INDEX `id`, ADD PRIMARY KEY (`digital_id`) USING BTREE;
ALTER TABLE `CubeCart_inventory_language` DROP INDEX `id`, ADD PRIMARY KEY (`translation_id`) USING BTREE; #EOQ
ALTER TABLE `CubeCart_modules` DROP INDEX `module_id`, ADD PRIMARY KEY (`module_id`) USING BTREE; #EOQ
ALTER TABLE `CubeCart_filemanager` ADD INDEX(`filepath`); #EOQ
ALTER TABLE `CubeCart_filemanager` ADD INDEX(`filename`); #EOQ
ALTER TABLE `CubeCart_order_notes` ADD `time_tmp` INT(11) NOT NULL AFTER `time`; #EOQ
UPDATE `CubeCart_order_notes` SET `time_tmp` = UNIX_TIMESTAMP(`time`); #EOQ
ALTER TABLE `CubeCart_order_notes` DROP `time`; #EOQ
ALTER TABLE `CubeCart_order_notes` CHANGE `time_tmp` `time` INT(11) UNSIGNED NOT NULL; #EOQ
ALTER TABLE `CubeCart_order_notes` ADD INDEX( `time`); #EOQ