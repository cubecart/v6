ALTER TABLE `CubeCart_inventory` CHANGE `minimum_quantity` `minimum_quantity` INT NOT NULL DEFAULT '1'; #EOQ
UPDATE `CubeCart_inventory` SET `minimum_quantity` = 1 WHERE `minimum_quantity` = 0; #EOQ