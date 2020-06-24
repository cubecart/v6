ALTER TABLE `CubeCart_tax_details` DROP `reg_number`; #EOQ
ALTER TABLE `CubeCart_documents` ADD  `doc_parse` TINYINT( 1 ) NOT NULL DEFAULT  '0'; #EOQ
ALTER TABLE `CubeCart_request_log` ADD `error` BLOB NOT NULL AFTER `result`; #EOQ
ALTER TABLE `CubeCart_modules` CHANGE  `module`  `module` VARCHAR( 60 ) NOT NULL; #EOQ
ALTER TABLE `CubeCart_modules` CHANGE  `folder`  `folder` VARCHAR( 60 ) NOT NULL; #EOQ
ALTER TABLE `CubeCart_addressbook` ADD  `hash` VARCHAR( 32 ) NOT NULL; #EOQ
ALTER TABLE `CubeCart_currency` CHANGE  `symbol_decimal`  `symbol_decimal` VARCHAR( 10 ) NOT NULL DEFAULT  '.'; #EOQ
ALTER TABLE `CubeCart_currency` ADD  `symbol_thousand` VARCHAR( 10 ) NOT NULL DEFAULT  ','; #EOQ
UPDATE `CubeCart_currency` SET `symbol_decimal` = ',' WHERE `symbol_decimal` = '1'; #EOQ
UPDATE `CubeCart_currency` SET `symbol_decimal` = '.' WHERE `symbol_decimal` = '0'; #EOQ
UPDATE `CubeCart_currency` SET `symbol_decimal` = '.' WHERE `symbol_decimal` = ''; #EOQ
UPDATE `CubeCart_currency` SET `symbol_decimal` = '.' WHERE `symbol_decimal` = null; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD  `tour_shown` ENUM('0','1') NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_geo_country` ADD  `status` ENUM('0','1') NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_geo_country` CHANGE  `eu`  `eu` ENUM('0','1') NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_seo_urls` ADD `custom` ENUM('0','1') NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_seo_urls` ADD INDEX (`custom`); #EOQ
ALTER TABLE `CubeCart_inventory` ADD `available` ENUM('0','1') NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD  `note_to_customer` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` DROP PRIMARY KEY, ADD INDEX (`cart_order_id`); #EOQ
ALTER TABLE `CubeCart_order_summary` ADD  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST; #EOQ
ALTER TABLE `CubeCart_order_inventory` ADD  `options_array` BLOB NULL AFTER  `product_options`; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD PRIMARY KEY(`admin_id`); #EOQ
ALTER TABLE `CubeCart_currency` CHANGE  `symbol_left`  `symbol_left` TINYBLOB; #EOQ
ALTER TABLE `CubeCart_currency` CHANGE  `symbol_right`  `symbol_right` TINYBLOB; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD  `ship_product` VARCHAR(100) NULL AFTER `ship_method`; #EOQ
UPDATE `CubeCart_geo_country` SET `eu` = '1' WHERE `iso` IN('BG','CZ','DK','DE','EE','IE','EL','ES','FR','HR','IT','CY','LV','LT','LU','HU','MT','NL','AT','PL','PT','RO','SI','SK','FI','SE','UK'); #EOQ
UPDATE `CubeCart_currency` SET `symbol_left` = '£' WHERE `symbol_left` = '&pound;'; #EOQ
UPDATE `CubeCart_currency` SET `symbol_left` = '$' WHERE `symbol_left` = '&dollar;'; #EOQ
UPDATE `CubeCart_currency` SET `symbol_left` = '¥' WHERE `symbol_left` = '&yen;'; #EOQ
UPDATE `CubeCart_currency` SET `symbol_left` = '€' WHERE `symbol_left` = '&euro;'; #EOQ