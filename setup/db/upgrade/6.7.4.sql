ALTER TABLE `CubeCart_order_summary` MODIFY `basket` MEDIUMBLOB NULL DEFAULT NULL; #EOQ
DELETE FROM `CubeCart_config` WHERE `name` = 'config' AND `config_key` = 'abandoned_cart_order_window'; #EOQ
DELETE FROM `CubeCart_config` WHERE `name` = 'config' AND `config_key` = 'admin_skin'; #EOQ
