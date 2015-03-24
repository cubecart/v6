ALTER TABLE `CubeCart_cats_lang` ADD PRIMARY KEY (`id`), ADD INDEX (`cat_master_id`); #EOQ

ALTER TABLE `CubeCart_transactions` ADD PRIMARY KEY (`id`), ADD INDEX (`customer_id`); #EOQ

ALTER TABLE `CubeCart_category` ADD PRIMARY KEY (`cat_id`), ADD INDEX (`cat_father_id`); #EOQ

ALTER TABLE `CubeCart_cats_idx` ADD PRIMARY KEY (`id`), ADD INDEX (`cat_id`); #EOQ

ALTER TABLE `CubeCart_docs_lang` ADD PRIMARY KEY (`id`); #EOQ

ALTER TABLE `CubeCart_img_idx` ADD PRIMARY KEY (`id`), ADD INDEX (`productId`); #EOQ

ALTER TABLE `CubeCart_inv_lang` ADD PRIMARY KEY (`id`), ADD INDEX (`prod_master_id`); #EOQ

ALTER TABLE `CubeCart_inventory` ADD INDEX (`cat_id`); #EOQ

ALTER TABLE `CubeCart_iso_counties` ADD PRIMARY KEY (`id`), ADD INDEX (`countryId`); #EOQ

ALTER TABLE `CubeCart_options_bot` ADD INDEX (`product`); #EOQ

ALTER TABLE `CubeCart_order_inv` ADD INDEX (`productId`), ADD INDEX (`cart_order_id`); #EOQ

INSERT INTO `CubeCart_iso_counties` (`id`,`countryId`,`abbrev`,`name`) VALUES (NULL,225,"JEY","Guernsey"); #EOQ
INSERT INTO `CubeCart_iso_counties` (`id`,`countryId`,`abbrev`,`name`) VALUES (NULL,225,"GGY","Channel Islands"); #EOQ