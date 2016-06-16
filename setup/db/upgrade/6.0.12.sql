ALTER TABLE `CubeCart_category` CHANGE `cat_image` `cat_image` INT(10) NOT NULL; #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'East Yorkshire', `abbrev` = 'ERY'; #EOQ
ALTER TABLE `CubeCart_option_group` CHANGE `option_name` `option_name` VARCHAR(50) NOT NULL DEFAULT ''; #EOQ