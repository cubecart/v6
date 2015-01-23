ALTER TABLE  `CubeCart_admin_users` ADD  `tour_shown` ENUM('0','1') NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE  `CubeCart_geo_country` ADD  `status` ENUM('0','1') NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE  `CubeCart_geo_country` CHANGE  `eu`  `eu` ENUM('0','1') NOT NULL DEFAULT '0'; #EOQ