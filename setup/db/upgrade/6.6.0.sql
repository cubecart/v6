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

-- Session data column for database-backed session handler
ALTER TABLE `CubeCart_sessions` ADD `session_data` mediumblob DEFAULT NULL AFTER `useragent`; #EOQ

-- Config NVP migration: rename old blob table
ALTER TABLE `CubeCart_config` RENAME TO `CubeCart_config_legacy`; #EOQ

-- Config NVP migration: create new NVP table
CREATE TABLE IF NOT EXISTS `CubeCart_config` (
	`name` VARCHAR(100) NOT NULL,
	`config_key` VARCHAR(128) NOT NULL,
	`config_value` TEXT,
	UNIQUE KEY `name_key` (`name`, `config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

-- Category access control by customer group
CREATE TABLE IF NOT EXISTS `CubeCart_category_group` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cat_id` INT UNSIGNED NOT NULL,
  `group_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cat_group` (`cat_id`, `group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

ALTER TABLE `CubeCart_category` ADD `guest_access` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'; #EOQ

-- Timestamps for categories and documents
ALTER TABLE `CubeCart_category` ADD `date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'; #EOQ
ALTER TABLE `CubeCart_category` ADD `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'; #EOQ
ALTER TABLE `CubeCart_documents` ADD `date_added` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'; #EOQ
ALTER TABLE `CubeCart_documents` ADD `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'; #EOQ

-- Add JSON-LD schema markup placeholder to email templates
UPDATE `CubeCart_email_template` SET `content_html` = REPLACE(`content_html`, '</head>', '{$DATA.jsonLd}\n</head>') WHERE `content_html` NOT LIKE '%{$DATA.jsonLd}%'; #EOQ

-- Correct Swedish locale code from se-SE to sv-SE
UPDATE `CubeCart_config` SET `config_value` = 'sv-SE' WHERE `config_key` = 'default_language' AND `config_value` = 'se-SE'; #EOQ
UPDATE `CubeCart_config` SET `config_key` = 'sv-SE' WHERE `config_key` = 'se-SE' AND `name` = 'language'; #EOQ

-- Remove stored plain-text email content; plain text is now auto-generated from HTML
ALTER TABLE `CubeCart_email_content` DROP COLUMN `content_text`; #EOQ
ALTER TABLE `CubeCart_email_template` DROP COLUMN `content_text`; #EOQ
ALTER TABLE `CubeCart_newsletter` DROP COLUMN `content_text`; #EOQ
-- Two-factor authentication: add columns to admin users table
ALTER TABLE `CubeCart_admin_users` ADD COLUMN `twofa_enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD COLUMN `twofa_method` VARCHAR(10) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD COLUMN `twofa_secret` VARCHAR(64) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD COLUMN `twofa_backup_codes` TEXT NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD COLUMN `twofa_otp_hash` VARCHAR(255) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD COLUMN `twofa_otp_expires` INT UNSIGNED NOT NULL DEFAULT 0; #EOQ

-- Two-factor authentication: email templates imported from language XML files by 6.6.0.php

-- Remove customer title (Mr/Mrs/Dr) field from all tables
ALTER TABLE `CubeCart_addressbook` DROP COLUMN `title`; #EOQ
ALTER TABLE `CubeCart_customer` DROP COLUMN `title`; #EOQ
ALTER TABLE `CubeCart_order_summary` DROP COLUMN `title`; #EOQ
ALTER TABLE `CubeCart_order_summary` DROP COLUMN `title_d`; #EOQ
