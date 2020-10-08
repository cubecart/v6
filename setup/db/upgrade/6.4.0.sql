DELETE FROM `CubeCart_cookie_consent` WHERE `log_hash` = 'd41d8cd98f00b204e9800998ecf8427e'; #EOQ
ALTER TABLE `CubeCart_coupons` ADD `coupon_per_customer` INT(10) UNSIGNED NULL DEFAULT NULL; #EOQ
CREATE TABLE `CubeCart_customer_coupon` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `coupon` varchar(255) NOT NULL,
  `used` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `email` (`email`),
  KEY `coupon` (`coupon`)
) ENGINE=InnoDB; #EOQ
ALTER TABLE `CubeCart_option_matrix` ADD INDEX(`status`); #EOQ
DELETE FROM `CubeCart_option_matrix` WHERE `status` = 0; #EOQ
ALTER TABLE `CubeCart_option_matrix` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP; #EOQ
ALTER TABLE `CubeCart_option_matrix` ADD INDEX(`timestamp`); #EOQ
ALTER TABLE `CubeCart_option_matrix` ADD `gtin` VARCHAR(20) NULL AFTER `isbn`; #EOQ
ALTER TABLE `CubeCart_inventory` CHANGE `tax_type` `tax_type` INT(10) NOT NULL; #EOQ
ALTER TABLE `CubeCart_coupons` ADD `manufacturer_id` TEXT NOT NULL AFTER `product_id`; #EOQ
ALTER TABLE `CubeCart_system_error_log` ADD `url` VARCHAR(255) NOT NULL AFTER `message`; #EOQ
ALTER TABLE `CubeCart_system_error_log` ADD `backtrace` TEXT NOT NULL AFTER `url`; #EOQ
ALTER TABLE `CubeCart_addressbook` ADD `w3w` VARCHAR(255) NOT NULL AFTER `country`; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `w3w_d` VARCHAR(255) NOT NULL AFTER `country_d`; #EOQ
ALTER TABLE `CubeCart_order_summary` ADD `w3w` VARCHAR(255) NOT NULL AFTER `country`; #EOQ
UPDATE `CubeCart_email_content` SET `content_html` = REPLACE(`content_html`,'{$BILLING.country}','{$BILLING.country}{if !empty($BILLING.w3w)}<div class="w3w">///<a href="https://what3words.com/{$BILLING.w3w}">{$BILLING.w3w}</a></div>{/if}'); #EOQ
UPDATE `CubeCart_email_content` SET `content_html` = REPLACE(`content_html`,'{$SHIPPING.country}','{$SHIPPING.country}{if !empty($SHIPPING.w3w)}<div class="w3w">///<a href="https://what3words.com/{$SHIPPING.w3w}">{$SHIPPING.w3w}</a></div>{/if}'); #EOQ
UPDATE `CubeCart_email_content` SET `content_text` = REPLACE(`content_text`,'{$BILLING.country}','{$BILLING.country}{if !empty($BILLING.w3w)}\n///{$BILLING.w3w}\n{/if}'); #EOQ
UPDATE `CubeCart_email_content` SET `content_text` = REPLACE(`content_text`,'{$SHIPPING.country}','{$SHIPPING.country}{if !empty($SHIPPING.w3w)}\n///{$SHIPPING.w3w}\n{/if}'); #EOQ
UPDATE `CubeCart_email_template` SET `content_html` = REPLACE(`content_html`,'</style>','.w3w{\r\ncolor: #E11F26;\r\ndisplay: block\r\n}.w3w a{\r\ncolor: #333333;\r\ntext-decoration: none\r\n}\r\n</style>'); #EOQ
UPDATE `CubeCart_invoice_template` SET `content` = REPLACE(`content`,'</style>','.w3w{\r\ncolor: #E11F26;\r\ndisplay: block\r\n}.w3w a{\r\ncolor: #333333;\r\ntext-decoration: none\r\n}\r\n</style>'); #EOQ
UPDATE `CubeCart_invoice_template` SET `content` = REPLACE(`content`,'{$order.country_d}{/if}','{$order.country_d}{/if}{if !empty($order.w3w_d)}<br><div class="w3w">///<a href="https://what3words.com/{$order.w3w_d}">{$order.w3w_d}</a></div>{/if}'); #EOQ
ALTER TABLE `CubeCart_seo_urls` ADD `redirect` enum('0','301','302') NOT NULL DEFAULT '0' AFTER `custom`; #EOQ
ALTER TABLE `CubeCart_seo_urls` ADD INDEX(`redirect`); #EOQ
UPDATE `CubeCart_seo_urls` SET `path` = CONCAT(`path`,'.html'); #EOQ
ALTER TABLE `CubeCart_filemanager` ADD `title` VARCHAR(255) NOT NULL AFTER `md5hash`; #EOQ
ALTER TABLE `CubeCart_filemanager` ADD `stream` ENUM('0','1') NOT NULL DEFAULT '0' AFTER `description`; #EOQ
INSERT INTO `CubeCart_seo_urls` (`path`, `type`,`item_id`) VALUES ('login', 'login', 0); #EOQ
INSERT INTO `CubeCart_seo_urls` (`path`, `type`,`ietm_id`) VALUES ('register', 'register', 0); #EOQ
INSERT INTO `CubeCart_seo_urls` (`path`, `type`,`ietm_id`) VALUES ('search', 'search', 0); #EOQ