ALTER TABLE `CubeCart_order_summary` ADD `lang` VARCHAR( 5 ) NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `lang` `lang` VARCHAR( 5 ) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_option_value` CHANGE `value_name` `value_name` VARBINARY( 50 ) NOT NULL DEFAULT ''; #EOQ