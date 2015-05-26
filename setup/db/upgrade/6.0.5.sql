UPDATE `CubeCart_email_content` SET `doc_content` = replace(`content_html`, '{$DATA.ship_method|replace:&#39;_&#39;:&#39; &#39;}', '{$DATA.ship_method}'); #EOQ
UPDATE `CubeCart_email_content` SET `doc_content` = replace(`content_html`, '{$DATA.ship_method|replace:'_':' '}', '{$DATA.ship_method}'); #EOQ
ALTER TABLE `CubeCart_inventory` ADD `description_short` TEXT NULL COMMENT 'Short Description' AFTER `description`; #EOQ
ALTER TABLE `CubeCart_inventory_language` ADD `description_short` TEXT NULL AFTER `description`; #EOQ
ALTER TABLE `CubeCart_option_value` CHANGE `value_name` `value_name` varchar(100) NOT NULL DEFAULT ''; #EOQ