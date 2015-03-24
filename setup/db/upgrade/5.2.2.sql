ALTER TABLE  `CubeCart_option_group` ADD  `priority` INT(10) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE  `CubeCart_option_value` ADD  `priority` INT(10) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
UPDATE `CubeCart_geo_zone` SET `country_id` = 225 WHERE `country_id` = 255; #EOQ
DELETE FROM `CubeCart_geo_zone` WHERE `country_id` = 255; #EOQ