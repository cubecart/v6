ALTER TABLE `CubeCart_taxes` RENAME TO `CubeCart_tax_class`, DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ
ALTER TABLE `CubeCart_tax_class` CHANGE `taxName` `tax_name` VARCHAR(50) NOT NULL; #EOQ
ALTER TABLE `CubeCart_tax_class` DROP `percent`; #EOQ
ALTER TABLE `CubeCart_tax_class` ADD PRIMARY KEY ( `id` ); #EOQ
ALTER TABLE `CubeCart_tax_class` DROP INDEX `id`; #EOQ