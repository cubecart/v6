UPDATE `CubeCart_currency` SET `symbol_decimal` = '.' WHERE `symbol_decimal` = '0'; #EOQ
UPDATE `CubeCart_currency` SET `symbol_decimal` = ',' WHERE `symbol_decimal` = '1'; #EOQ