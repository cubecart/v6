INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'East Sussex', `abbrev` = 'SXE'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'West Sussex', `abbrev` = 'SXW'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'North Yorkshire', `abbrev` = 'YSN'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'South Yorkshire', `abbrev` = 'YSS'; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'West Yorkshire', `abbrev` = 'YSW'; #EOQ
DELETE FROM `CubeCart_geo_zone` WHERE `country_id` = 225 AND `name` = 'Yorkshire'; #EOQ
DELETE FROM `CubeCart_geo_zone` WHERE `country_id` = 225 AND `name` = 'Sussex'; #EOQ
ALTER TABLE  `CubeCart_inventory` ADD  `brand` VARCHAR( 20 ) NULL; #EOQ
ALTER TABLE  `CubeCart_inventory` ADD  `gtin` VARCHAR( 20 ) NULL; #EOQ
ALTER TABLE  `CubeCart_inventory` ADD  `mpn` VARCHAR( 20 ) NULL; #EOQ