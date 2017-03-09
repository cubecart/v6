DELETE FROM `CubeCart_filemanager` WHERE `filename` NOT REGEXP '(jpeg|jpg|gif|png)$' AND `type` = 1; #EOQ
ALTER TABLE `CubeCart_option_assign` ADD COLUMN `option_default` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 AFTER `set_enabled`; #EOQ
ALTER TABLE `CubeCart_option_assign` CHANGE `option_weight` `option_weight` DECIMAL(10,3)  NOT NULL  DEFAULT '0.00'; #EOQ