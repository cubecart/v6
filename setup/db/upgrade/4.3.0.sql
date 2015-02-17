ALTER TABLE `CubeCart_admin_users` ADD `salt` VARCHAR(6) NOT NULL DEFAULT ''; #EOQ

ALTER TABLE `CubeCart_customer` ADD `salt` VARCHAR(6) NOT NULL DEFAULT ''; #EOQ

ALTER TABLE `CubeCart_Modules` ADD UNIQUE KEY (`folder`); #EOQ