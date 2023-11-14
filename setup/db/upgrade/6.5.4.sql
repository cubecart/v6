ALTER TABLE `CubeCart_filemanager` ADD INDEX(`type`); #EOQ
ALTER TABLE `CubeCart_filemanager` ADD UNIQUE(`md5hash`); #EOQ
ALTER TABLE `CubeCart_currency` ADD `adjustment` DECIMAL(5,3) NOT NULL DEFAULT '0.000'; #EOQ