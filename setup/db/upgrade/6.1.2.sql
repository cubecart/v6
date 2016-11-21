ALTER TABLE `CubeCart_alt_shipping` DROP INDEX `id`, ADD PRIMARY KEY (`id`) USING BTREE; #EOQ
ALTER TABLE `CubeCart_alt_shipping_prices` DROP INDEX `id`, ADD PRIMARY KEY (`id`) USING BTREE; #EOQ
ALTER TABLE `CubeCart_category_language` DROP INDEX `id`, ADD PRIMARY KEY (`translation_id`) USING BTREE; #EOQ
ALTER TABLE `CubeCart_downloads` DROP INDEX `id`, ADD PRIMARY KEY (`digital_id`) USING BTREE;
ALTER TABLE `CubeCart_inventory_language` DROP INDEX `id`, ADD PRIMARY KEY (`translation_id`) USING BTREE; #EOQ
ALTER TABLE `CubeCart_modules` DROP INDEX `module_id`, ADD PRIMARY KEY (`module_id`) USING BTREE; #EOQ