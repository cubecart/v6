INSERT INTO `CubeCart_config` (`name`, `config_key`, `config_value`, `array_serial`) VALUES ('config', 'abandoned_cart_enabled', '0', '') ON DUPLICATE KEY UPDATE `name`=`name`; #EOQ
INSERT INTO `CubeCart_config` (`name`, `config_key`, `config_value`, `array_serial`) VALUES ('config', 'abandoned_cart_delay', '86400', '') ON DUPLICATE KEY UPDATE `name`=`name`; #EOQ
UPDATE `CubeCart_config` SET `config_value` = '604800' WHERE `name` = 'config' AND `config_key` = 'abandoned_cart_notify_cooldown' AND `config_value` = '259200'; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `verify` `verify` VARCHAR(64) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `verify` `verify` VARCHAR(64) NULL DEFAULT NULL; #EOQ