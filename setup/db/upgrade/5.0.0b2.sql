UPDATE `CubeCart_image_index` INNER JOIN `CubeCart_filemanager` ON `CubeCart_image_index`.`img` = CONCAT(`CubeCart_filemanager`.`filepath`,`CubeCart_filemanager`.`filename`) SET `CubeCart_image_index`.`file_id` = `CubeCart_filemanager`.`file_id` WHERE `CubeCart_filemanager`.`filepath` IS NOT NULL AND `CubeCart_image_index`.`file_id` = 0; #EOQ
UPDATE `CubeCart_image_index` INNER JOIN `CubeCart_filemanager` ON `CubeCart_image_index`.`img` = `CubeCart_filemanager`.`filename` SET `CubeCart_image_index`.`file_id` = `CubeCart_filemanager`.`file_id` WHERE `CubeCart_filemanager`.`filepath` IS NULL AND `CubeCart_image_index`.`file_id` = 0; #EOQ

ALTER TABLE `CubeCart_addressbook` ADD `phone` VARCHAR(20) NULL DEFAULT NULL; #EOQ

ALTER TABLE `CubeCart_admin_users` ADD `order_notify` TINYINT(1) UNSIGNED DEFAULT '0'; #EOQ

UPDATE `CubeCart_geo_country` SET `iso3` = 'AQA', `numcode` = '010' WHERE `iso` = 'AQ'; #EOQ
UPDATE `CubeCart_geo_country` SET `iso3` = 'BVT', `numcode` = '074' WHERE `iso` = 'BV'; #EOQ
UPDATE `CubeCart_geo_country` SET `iso3` = 'IOT', `numcode` = '086' WHERE `iso` = 'IO'; #EOQ
UPDATE `CubeCart_geo_country` SET `iso3` = 'CXR', `numcode` = '162' WHERE `iso` = 'CX'; #EOQ
UPDATE `CubeCart_geo_country` SET `iso3` = 'CCK', `numcode` = '166' WHERE `iso` = 'CC'; #EOQ
UPDATE `CubeCart_geo_country` SET `iso3` = 'ATF', `numcode` = '260' WHERE `iso` = 'TF'; #EOQ
UPDATE `CubeCart_geo_country` SET `iso3` = 'HMD', `numcode` = '334' WHERE `iso` = 'HM'; #EOQ
UPDATE `CubeCart_geo_country` SET `iso3` = 'MYT', `numcode` = '175' WHERE `iso` = 'YT'; #EOQ
UPDATE `CubeCart_geo_country` SET `iso3` = 'PSE', `numcode` = '275' WHERE `iso` = 'PS'; #EOQ
UPDATE `CubeCart_geo_country` SET `iso3` = 'SGS', `numcode` = '239' WHERE `iso` = 'GS'; #EOQ
UPDATE `CubeCart_geo_country` SET `iso3` = 'TLS', `numcode` = '626' WHERE `iso` = 'TL'; #EOQ
UPDATE `CubeCart_geo_country` SET `iso3` = 'UMI', `numcode` = '581' WHERE `iso` = 'UM'; #EOQ

DELETE FROM `CubeCart_geo_country` WHERE `iso` IN ('CS'); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `printable_name`, `iso3`, `numcode`) VALUES (244, 'RS', 'Serbia', 'SRB', 688); #EOQ
INSERT INTO `CubeCart_geo_country` (`id`, `iso`, `printable_name`, `iso3`, `numcode`) VALUES (245, 'ME', 'Montenegro', 'MNE', 499); #EOQ

ALTER TABLE `CubeCart_inventory` DROP `image`; #EOQ

DROP TABLE `CubeCart_lang`; #EOQ