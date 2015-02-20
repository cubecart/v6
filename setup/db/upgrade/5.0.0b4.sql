ALTER TABLE `CubeCart_addressbook` DROP `phone`; #EOQ

ALTER TABLE `CubeCart_options_set_member` ADD `price` DECIMAL(16,2) NOT NULL DEFAULT '0.00'; #EOQ
ALTER TABLE `CubeCart_options_set_member` ADD `weight` DECIMAL(16,2) NOT NULL DEFAULT '0.00'; #EOQ