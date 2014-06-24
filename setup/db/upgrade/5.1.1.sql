ALTER TABLE  `CubeCart_option_assign` CHANGE  `set_enabled`  `set_enabled` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '1'; #EOQ

ALTER TABLE  `CubeCart_customer` CHANGE  `language`  `language` VARCHAR( 5 ) NOT NULL DEFAULT  'en-US'; #EOQ

ALTER TABLE  `CubeCart_config` CHANGE `array` `array` MEDIUMTEXT NOT NULL; #EOQ