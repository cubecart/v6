ALTER TABLE  `CubeCart_modules` CHANGE  `module`  `module` VARCHAR( 60 ) NOT NULL; #EOQ
ALTER TABLE  `CubeCart_modules` CHANGE  `folder`  `folder` VARCHAR( 60 ) NOT NULL; #EOQ
ALTER TABLE  `CubeCart_addressbook` ADD  `hash` VARCHAR( 32 ) NOT NULL; #EOQ
ALTER TABLE  `CubeCart_currency` CHANGE  `symbol_decimal`  `symbol_decimal` VARCHAR( 10 ) NOT NULL DEFAULT  '.'; #EOQ
ALTER TABLE  `CubeCart_currency` ADD  `symbol_thousand` VARCHAR( 10 ) NOT NULL DEFAULT  ','; #EOQ
UPDATE `CubeCart_currency` SET `symbol_decimal` = ',' WHERE `symbol_decimal` = '1'; #EOQ
UPDATE `CubeCart_currency` SET `symbol_decimal` = '.' WHERE `symbol_decimal` = '0'; #EOQ
UPDATE `CubeCart_currency` SET `symbol_decimal` = '.' WHERE `symbol_decimal` = ''; #EOQ
UPDATE `CubeCart_currency` SET `symbol_decimal` = '.' WHERE `symbol_decimal` = null; #EOQ