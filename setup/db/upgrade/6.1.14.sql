ALTER TABLE `CubeCart_shipping_rates` ADD INDEX(`zone_id`); #EOQ
ALTER TABLE `CubeCart_shipping_rates` ADD INDEX(`method_name`); #EOQ
ALTER TABLE `CubeCart_shipping_rates` ADD INDEX(`min_weight`); #EOQ
ALTER TABLE `CubeCart_shipping_rates` ADD INDEX(`max_weight`); #EOQ
ALTER TABLE `CubeCart_shipping_rates` ADD INDEX(`min_value`); #EOQ
ALTER TABLE `CubeCart_shipping_zones` ADD INDEX(`zone_name`); #EOQ
ALTER TABLE `CubeCart_customer_group` ADD INDEX(`group_name`); #EOQ
ALTER TABLE `CubeCart_pricing_group` ADD INDEX(`tax_type`); #EOQ
ALTER TABLE `CubeCart_tax_rates` ADD INDEX(`active`); #EOQ
ALTER TABLE `CubeCart_order_inventory` ADD INDEX(`quantity`); #EOQ
ALTER TABLE `CubeCart_code_snippet` ADD INDEX(`enabled`); #EOQ
ALTER TABLE `CubeCart_hooks` ADD INDEX(`enabled`); #EOQ
ALTER TABLE `CubeCart_documents` ADD INDEX(`doc_status`); #EOQ
ALTER TABLE `CubeCart_documents` ADD INDEX(`doc_home`); #EOQ
INSERT INTO `CubeCart_geo_zone` SET `country_id` = 225, `name` = 'London', `abbrev` = 'LND'; #EOQ
ALTER TABLE `CubeCart_order_inventory` ADD `tax` DECIMAL(16,2)  NOT NULL  DEFAULT '0.00'  AFTER `price`; #EOQ
