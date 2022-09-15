ALTER TABLE `CubeCart_extension_info` ADD INDEX(`seller_id`); #EOQ
ALTER TABLE `CubeCart_extension_info` ADD `keep_current` BOOLEAN NOT NULL DEFAULT FALSE AFTER `modified`; #EOQ