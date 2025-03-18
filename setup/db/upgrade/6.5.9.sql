ALTER TABLE `CubeCart_customer` ADD `currency` VARCHAR(3) NULL AFTER `language`; #EOQ
ALTER TABLE `CubeCart_customer` ENGINE=InnoDB; #EOQ
ALTER TABLE `CubeCart_inventory` ENGINE=InnoDB; #EOQ
ALTER TABLE `CubeCart_inventory_language` ENGINE=InnoDB; #EOQ
ALTER TABLE `CubeCart_order_notes` ENGINE=InnoDB; #EOQ
ALTER TABLE `CubeCart_reviews` ENGINE=InnoDB; #EOQ