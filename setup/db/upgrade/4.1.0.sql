ALTER TABLE `CubeCart_admin_log` CHANGE `ipAddress` `ipAddress` VARCHAR(45) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_reviews` CHANGE `ip` `ip` VARCHAR (45) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_blocker` CHANGE `ip` `ip` VARCHAR (45) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_SpamBot` CHANGE `userIp` `userIp` VARCHAR (45) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_admin_sessions` CHANGE `ipAddress` `ipAddress` VARCHAR (45) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_admin_users` CHANGE `sessIp` `sessIp` VARCHAR (45) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_customer` CHANGE `ipAddress` `ipAddress` VARCHAR (45) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_order_sum` CHANGE `ip` `ip` VARCHAR (45) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_sessions` CHANGE `ip` `ip` VARCHAR (45) NOT NULL DEFAULT ''; #EOQ

ALTER TABLE `CubeCart_docs` ADD `doc_url` TEXT NULL, ADD `doc_url_openin` TINYINT(1) UNSIGNED DEFAULT '0'; #EOQ

ALTER TABLE `CubeCart_inventory` ADD `disabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `image` `image` VARBINARY(250) NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `popularity` `popularity` BIGINT(64) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `stock_level` `stock_level` INT(11) NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `stockWarn` `stockWarn` TINYINT(1) NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `useStockLevel` `useStockLevel` INT NOT NULL DEFAULT '1'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `digitalDir` `digitalDir` VARCHAR(255) NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `prodWeight` `prodWeight` DECIMAL(10,3) NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `taxType` `taxType` INT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `tax_inclusive` `tax_inclusive` TINYINT(1) NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `prod_metatitle` `prod_metatitle` TEXT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `prod_metadesc` `prod_metadesc` TEXT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `prod_metakeywords` `prod_metakeywords` TEXT NULL; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `eanupcCode` `eanupcCode` BIGINT(17) UNSIGNED NULL; #EOQ
ALTER TABLE `CubeCart_inventory` ADD `date_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP; #EOQ