ALTER TABLE  `CubeCart_inventory` ADD `cost_price` DECIMAL( 16, 2 ) NOT NULL DEFAULT '0.00' AFTER `sale_price`; #EOQ

ALTER TABLE  `CubeCart_inventory` DROP `seo_custom_url`; #EOQ

ALTER TABLE  `CubeCart_category` DROP `seo_custom_url`; #EOQ

UPDATE `CubeCart_filemanager` SET `filepath` = NULL WHERE `filepath` = ''; #EOQ