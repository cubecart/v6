ALTER TABLE `CubeCart_filemanager` CHANGE `md5hash` `md5hash` VARCHAR(32) default NULL; #EOQ
ALTER TABLE `CubeCart_email_log` ADD COLUMN `attachment` text NULL; #EOQ