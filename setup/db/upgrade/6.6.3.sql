ALTER TABLE `CubeCart_order_inventory` DROP INDEX `quantity`; #EOQ
ALTER TABLE `CubeCart_order_inventory` ADD INDEX `cartorder_product_qty` (`cart_order_id`, `product_id`, `quantity`); #EOQ
ALTER TABLE `CubeCart_order_summary` DROP INDEX `status`; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD INDEX `status_cartorder` (`status`, `cart_order_id`); #EOQ
CREATE TABLE IF NOT EXISTS `CubeCart_email_log` (`id` int(11) NOT NULL AUTO_INCREMENT, `subject` varchar(255) NOT NULL, `content_html` mediumtext NOT NULL, `content_text` mediumtext NOT NULL, `to` varchar(255) NOT NULL, `from` varchar(255) NOT NULL, `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, `email_content_id` int(11) NOT NULL, `result` tinyint(1) NOT NULL, `email_method` varchar(20) NOT NULL DEFAULT '', `fail_reason` text, `attachment` text DEFAULT NULL, PRIMARY KEY (`id`), KEY `to` (`to`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD COLUMN `extensions_last_seen` INT UNSIGNED NOT NULL DEFAULT 0; #EOQ
