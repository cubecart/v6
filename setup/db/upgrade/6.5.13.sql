-- High priority: CubeCart_downloads had no indexes beyond PRIMARY KEY
ALTER TABLE `CubeCart_downloads` ADD KEY `customer_id` (`customer_id`); #EOQ
ALTER TABLE `CubeCart_downloads` ADD KEY `cart_order_id` (`cart_order_id`); #EOQ
ALTER TABLE `CubeCart_downloads` ADD KEY `accesskey` (`accesskey`); #EOQ
ALTER TABLE `CubeCart_downloads` ADD KEY `order_inv_id` (`order_inv_id`); #EOQ

-- High priority: inventory language fetched by product_id+language but only language was indexed
ALTER TABLE `CubeCart_inventory_language` ADD KEY `product_lang` (`product_id`, `language`); #EOQ

-- High priority: admin login and password recovery look up by username and email
ALTER TABLE `CubeCart_admin_users` ADD KEY `username` (`username`); #EOQ
ALTER TABLE `CubeCart_admin_users` ADD KEY `email` (`email`); #EOQ

-- Medium priority: category queries always filter status+hide alongside cat_parent_id
ALTER TABLE `CubeCart_category` DROP KEY `cat_parent_id`; #EOQ
ALTER TABLE `CubeCart_category` ADD KEY `cat_parent_status_hide` (`cat_parent_id`, `status`, `hide`); #EOQ

-- Medium priority: hooks are looked up and deleted by plugin name
ALTER TABLE `CubeCart_hooks` ADD KEY `plugin` (`plugin`); #EOQ

-- Medium priority: blocker records are deleted by username on successful login
ALTER TABLE `CubeCart_blocker` ADD KEY `username` (`username`(150)); #EOQ

-- Medium priority: products filtered by manufacturer on catalogue pages
ALTER TABLE `CubeCart_inventory` ADD KEY `manufacturer` (`manufacturer`); #EOQ

-- Medium priority: SEO URL lookups almost always combine type+item_id
ALTER TABLE `CubeCart_seo_urls` DROP KEY `type`; #EOQ
ALTER TABLE `CubeCart_seo_urls` DROP KEY `item_id`; #EOQ
ALTER TABLE `CubeCart_seo_urls` ADD KEY `type_item` (`type`, `item_id`); #EOQ