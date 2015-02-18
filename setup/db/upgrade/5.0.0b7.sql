ALTER TABLE `CubeCart_seo_urls` CHANGE `type` `type` VARCHAR( 45 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL; #EOQ

ALTER TABLE `CubeCart_option_assign` ADD `option_negative` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0'; #EOQ

ALTER TABLE `CubeCart_seo_urls` CHANGE `type` `type` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL; #EOQ

ALTER TABLE `CubeCart_inventory` CHANGE COLUMN `date_added` `date_added` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE COLUMN `updated` `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP; #EOQ
  
ALTER TABLE  `CubeCart_inventory` ADD  `condition` VARCHAR( 25 ) NULL; #EOQ

ALTER TABLE  `CubeCart_inventory` CHANGE  `eanupc_code`  `upc` VARCHAR( 12 ) NULL; #EOQ

ALTER TABLE  `CubeCart_inventory` ADD  `ean` VARCHAR( 14 ) NULL AFTER  `upc`; #EOQ

ALTER TABLE  `CubeCart_inventory` ADD  `jan` VARCHAR( 13 ) NULL AFTER  `ean`; #EOQ

ALTER TABLE  `CubeCart_inventory` ADD  `isbn` VARCHAR( 13 ) NULL AFTER  `jan`; #EOQ

ALTER TABLE `CubeCart_seo_urls` CHANGE `type` `type` VARCHAR( 45 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL; #EOQ