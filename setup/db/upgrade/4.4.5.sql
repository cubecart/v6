ALTER TABLE  `CubeCart_inventory` CHANGE  `eanupcCode`  `upc` VARCHAR( 12 ) NULL; #EOQ
ALTER TABLE  `CubeCart_inventory` ADD  `ean` VARCHAR( 14 ) NULL; #EOQ
ALTER TABLE  `CubeCart_inventory` ADD  `jan` VARCHAR( 13 ) NULL; #EOQ
ALTER TABLE  `CubeCart_inventory` ADD  `isbn` VARCHAR( 13 ) NULL; #EOQ