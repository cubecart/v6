ALTER TABLE `CubeCart_admin_users` ADD PRIMARY KEY(`admin_id`); #EOQ
ALTER TABLE  `CubeCart_currency` CHANGE  `symbol_left`  `symbol_left` TINYBLOB; #EOQ
ALTER TABLE  `CubeCart_currency` CHANGE  `symbol_right`  `symbol_right` TINYBLOB; #EOQ