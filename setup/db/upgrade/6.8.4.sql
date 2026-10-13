ALTER TABLE `CubeCart_inventory` ADD `live_until` int(11) NOT NULL DEFAULT '0' AFTER `live_from`; #EOQ
ALTER TABLE `CubeCart_inventory` ADD INDEX (`live_until`); #EOQ
