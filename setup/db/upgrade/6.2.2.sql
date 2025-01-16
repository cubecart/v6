ALTER TABLE `CubeCart_tax_rates` CHANGE `goods` `goods` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_tax_rates` CHANGE `shipping` `shipping` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_sessions` ADD `acp` TINYINT(1)  UNSIGNED  NULL  DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_sessions` ADD INDEX (`acp`); #EOQ