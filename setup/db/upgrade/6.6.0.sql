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

-- =============================================================
-- Geo data corrections: fix ISO codes, names, and add missing entries
-- =============================================================

-- Convert VARBINARY columns to VARCHAR for proper collation and sorting
ALTER TABLE `CubeCart_geo_country` MODIFY `name` VARCHAR(80) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_geo_zone` MODIFY `abbrev` VARCHAR(6) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_geo_zone` MODIFY `name` VARCHAR(60) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_currency` MODIFY `name` VARCHAR(255) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_search` MODIFY `searchstr` VARCHAR(255) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_sessions` MODIFY `location` VARCHAR(255) DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_tax_details` MODIFY `name` VARCHAR(150) NOT NULL DEFAULT ''; #EOQ
ALTER TABLE `CubeCart_tax_details` MODIFY `display` VARCHAR(150) NOT NULL DEFAULT ''; #EOQ

-- Fix wrong country codes
UPDATE `CubeCart_geo_country` SET `numcode` = 024 WHERE `iso` = 'AO'; #EOQ
UPDATE `CubeCart_geo_country` SET `iso3` = 'ROU' WHERE `iso` = 'RO'; #EOQ
UPDATE `CubeCart_geo_country` SET `numcode` = 729 WHERE `iso` = 'SD'; #EOQ

-- Fix outdated country names
UPDATE `CubeCart_geo_country` SET `name` = 'Cabo Verde' WHERE `iso` = 'CV'; #EOQ
UPDATE `CubeCart_geo_country` SET `name` = 'Czechia' WHERE `iso` = 'CZ'; #EOQ
UPDATE `CubeCart_geo_country` SET `name` = 'Libya' WHERE `iso` = 'LY'; #EOQ
UPDATE `CubeCart_geo_country` SET `name` = 'North Macedonia' WHERE `iso` = 'MK'; #EOQ
UPDATE `CubeCart_geo_country` SET `name` = 'Eswatini' WHERE `iso` = 'SZ'; #EOQ
UPDATE `CubeCart_geo_country` SET `name` = 'Turkiye' WHERE `iso` = 'TR'; #EOQ
UPDATE `CubeCart_geo_country` SET `name` = 'Virgin Islands, U.S.' WHERE `iso` = 'VI'; #EOQ

-- Disable dissolved Netherlands Antilles
UPDATE `CubeCart_geo_country` SET `status` = 2 WHERE `iso` = 'AN'; #EOQ

-- Add missing countries
INSERT IGNORE INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (245, 'AM', 'Armenia', 'ARM', 051); #EOQ
INSERT IGNORE INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (246, 'LR', 'Liberia', 'LBR', 430); #EOQ
INSERT IGNORE INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (247, 'SS', 'South Sudan', 'SSD', 728); #EOQ
INSERT IGNORE INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (248, 'CW', 'Curacao', 'CUW', 531); #EOQ
INSERT IGNORE INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (249, 'SX', 'Sint Maarten (Dutch part)', 'SXM', 534); #EOQ
INSERT IGNORE INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (250, 'BQ', 'Bonaire, Sint Eustatius and Saba', 'BES', 535); #EOQ
INSERT IGNORE INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (251, 'BL', 'Saint Barthelemy', 'BLM', 652); #EOQ
INSERT IGNORE INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (252, 'MF', 'Saint Martin (French part)', 'MAF', 663); #EOQ
INSERT IGNORE INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (253, 'AX', 'Aland Islands', 'ALA', 248); #EOQ
INSERT IGNORE INTO `CubeCart_geo_country` (`id`, `iso`, `name`, `iso3`, `numcode`) VALUES (254, 'XK', 'Kosovo', 'XKX', 000); #EOQ

-- Fix zone name typos
UPDATE `CubeCart_geo_zone` SET `name` = 'Pernambuco' WHERE `country_id` = 30 AND `abbrev` = 'PE'; #EOQ
UPDATE `CubeCart_geo_zone` SET `name` = 'Tocantins' WHERE `country_id` = 30 AND `abbrev` = 'TO'; #EOQ
UPDATE `CubeCart_geo_zone` SET `name` = 'Vorarlberg' WHERE `country_id` = 14 AND `abbrev` = 'VB'; #EOQ

-- Fix outdated zone names and abbreviations
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'NL', `name` = 'Newfoundland and Labrador' WHERE `country_id` = 38 AND `abbrev` = 'NF'; #EOQ
UPDATE `CubeCart_geo_zone` SET `name` = 'Yukon' WHERE `country_id` = 38 AND `abbrev` = 'YT' AND `name` = 'Yukon Territory'; #EOQ

-- Merge Indian UTs: Dadra & Nagar Haveli + Daman & Diu (merged 2020)
UPDATE `CubeCart_geo_zone` SET `name` = 'Dadra and Nagar Haveli and Daman and Diu' WHERE `country_id` = 99 AND `abbrev` = 'INDN'; #EOQ
DELETE FROM `CubeCart_geo_zone` WHERE `country_id` = 99 AND `abbrev` = 'INDD'; #EOQ

-- Fix Swiss canton abbreviations to ISO 3166-2
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'GR' WHERE `country_id` = 206 AND `abbrev` = 'JUB'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'AR' WHERE `country_id` = 206 AND `abbrev` = 'APP'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'BL' WHERE `country_id` = 206 AND `abbrev` = 'BLA'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'NE', `name` = 'Neuchatel' WHERE `country_id` = 206 AND `abbrev` = 'NEU'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'TI', `name` = 'Ticino' WHERE `country_id` = 206 AND `abbrev` = 'TE'; #EOQ

-- Fix German state abbreviations to ISO 3166-2
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'NI' WHERE `country_id` = 80 AND `abbrev` = 'NDS'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'BW' WHERE `country_id` = 80 AND `abbrev` = 'BAW'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'BY' WHERE `country_id` = 80 AND `abbrev` = 'BAY'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'BE' WHERE `country_id` = 80 AND `abbrev` = 'BER'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'BB' WHERE `country_id` = 80 AND `abbrev` = 'BRG'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'HB' WHERE `country_id` = 80 AND `abbrev` = 'BRE'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'HH' WHERE `country_id` = 80 AND `abbrev` = 'HAM'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'HE' WHERE `country_id` = 80 AND `abbrev` = 'HES'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'MV' WHERE `country_id` = 80 AND `abbrev` = 'MEC'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'NW' WHERE `country_id` = 80 AND `abbrev` = 'NRW'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'RP' WHERE `country_id` = 80 AND `abbrev` = 'RHE'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'SL' WHERE `country_id` = 80 AND `abbrev` = 'SAR'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'SN' WHERE `country_id` = 80 AND `abbrev` = 'SAS'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'ST' WHERE `country_id` = 80 AND `abbrev` = 'SAC'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'SH' WHERE `country_id` = 80 AND `abbrev` = 'SCN'; #EOQ
UPDATE `CubeCart_geo_zone` SET `abbrev` = 'TH' WHERE `country_id` = 80 AND `abbrev` = 'THE'; #EOQ

-- Add missing zones for existing countries
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (99, 'INUK', 'Uttarakhand'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (99, 'INLA', 'Ladakh'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (10, 'BA', 'Buenos Aires'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (107, 'FKS', 'Fukushima'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (107, 'KGS', 'Kagoshima'); #EOQ

-- Add Mexico states (country_id=138)
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'AG', 'Aguascalientes'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'BC', 'Baja California'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'BS', 'Baja California Sur'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'CM', 'Campeche'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'CS', 'Chiapas'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'CH', 'Chihuahua'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'CX', 'Ciudad de Mexico'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'CO', 'Coahuila'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'CL', 'Colima'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'DG', 'Durango'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'GT', 'Guanajuato'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'GR', 'Guerrero'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'HG', 'Hidalgo'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'JA', 'Jalisco'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'MX', 'Mexico'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'MI', 'Michoacan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'MO', 'Morelos'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'NA', 'Nayarit'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'NL', 'Nuevo Leon'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'OA', 'Oaxaca'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'PU', 'Puebla'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'QT', 'Queretaro'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'QR', 'Quintana Roo'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'SL', 'San Luis Potosi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'SI', 'Sinaloa'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'SO', 'Sonora'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'TB', 'Tabasco'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'TM', 'Tamaulipas'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'TL', 'Tlaxcala'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'VE', 'Veracruz'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'YU', 'Yucatan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (138, 'ZA', 'Zacatecas'); #EOQ

-- Add China provinces (country_id=44)
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'AH', 'Anhui'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'BJ', 'Beijing'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'CQ', 'Chongqing'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'FJ', 'Fujian'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'GS', 'Gansu'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'GD', 'Guangdong'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'GX', 'Guangxi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'GZ', 'Guizhou'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'HI', 'Hainan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'HE', 'Hebei'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'HL', 'Heilongjiang'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'HA', 'Henan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'HB', 'Hubei'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'HN', 'Hunan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'NM', 'Inner Mongolia'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'JS', 'Jiangsu'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'JX', 'Jiangxi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'JL', 'Jilin'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'LN', 'Liaoning'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'NX', 'Ningxia'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'QH', 'Qinghai'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'SN', 'Shaanxi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'SD', 'Shandong'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'SH', 'Shanghai'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'SX', 'Shanxi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'SC', 'Sichuan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'TJ', 'Tianjin'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'XZ', 'Tibet'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'XJ', 'Xinjiang'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'YN', 'Yunnan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'ZJ', 'Zhejiang'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'TW', 'Taiwan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'HK', 'Hong Kong'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (44, 'MO', 'Macau'); #EOQ

-- Add Italy regions (country_id=105)
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '65', 'Abruzzo'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '77', 'Basilicata'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '78', 'Calabria'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '72', 'Campania'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '45', 'Emilia-Romagna'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '36', 'Friuli Venezia Giulia'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '62', 'Lazio'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '42', 'Liguria'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '25', 'Lombardia'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '57', 'Marche'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '67', 'Molise'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '21', 'Piemonte'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '75', 'Puglia'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '88', 'Sardegna'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '82', 'Sicilia'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '52', 'Toscana'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '32', 'Trentino-Alto Adige'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '55', 'Umbria'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '23', 'Valle d\'Aosta'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (105, '34', 'Veneto'); #EOQ

-- Add France regions (country_id=73)
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'ARA', 'Auvergne-Rhone-Alpes'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'BFC', 'Bourgogne-Franche-Comte'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'BRE', 'Bretagne'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'CVL', 'Centre-Val de Loire'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'COR', 'Corse'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'GES', 'Grand Est'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'HDF', 'Hauts-de-France'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'IDF', 'Ile-de-France'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'NOR', 'Normandie'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'NAQ', 'Nouvelle-Aquitaine'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'OCC', 'Occitanie'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'PDL', 'Pays de la Loire'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'PAC', 'Provence-Alpes-Cote d\'Azur'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'GUA', 'Guadeloupe'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'GUF', 'Guyane'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'MTQ', 'Martinique'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'REU', 'La Reunion'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (73, 'MAY', 'Mayotte'); #EOQ

-- Add Indonesia provinces (country_id=100)
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'AC', 'Aceh'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'BA', 'Bali'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'BB', 'Bangka Belitung'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'BT', 'Banten'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'BE', 'Bengkulu'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'JT', 'Central Java'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'KT', 'Central Kalimantan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'ST', 'Central Sulawesi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'JI', 'East Java'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'KI', 'East Kalimantan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'NT', 'East Nusa Tenggara'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'GO', 'Gorontalo'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'JK', 'Jakarta'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'JA', 'Jambi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'LA', 'Lampung'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'MA', 'Maluku'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'KU', 'North Kalimantan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'MU', 'North Maluku'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'SA', 'North Sulawesi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'SU', 'North Sumatra'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'PA', 'Papua'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'PB', 'West Papua'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'RI', 'Riau'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'KR', 'Riau Islands'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'SS', 'South Sumatra'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'KS', 'South Kalimantan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'SN', 'South Sulawesi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'SR', 'Southeast Sulawesi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'NB', 'West Nusa Tenggara'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'JB', 'West Java'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'KB', 'West Kalimantan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'SB', 'West Sulawesi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'SM', 'West Sumatra'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'YO', 'Yogyakarta'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'PS', 'Central Papua'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'PT', 'Highland Papua'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'PD', 'South Papua'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (100, 'SW', 'Southwest Papua'); #EOQ

-- Add Thailand provinces (country_id=211)
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-10', 'Bangkok'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-11', 'Samut Prakan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-12', 'Nonthaburi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-13', 'Pathum Thani'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-14', 'Phra Nakhon Si Ayutthaya'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-15', 'Ang Thong'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-16', 'Lop Buri'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-17', 'Sing Buri'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-18', 'Chai Nat'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-19', 'Saraburi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-20', 'Chon Buri'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-21', 'Rayong'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-22', 'Chanthaburi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-23', 'Trat'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-24', 'Chachoengsao'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-25', 'Prachin Buri'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-26', 'Nakhon Nayok'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-27', 'Sa Kaeo'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-30', 'Nakhon Ratchasima'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-31', 'Buri Ram'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-32', 'Surin'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-33', 'Si Sa Ket'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-34', 'Ubon Ratchathani'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-35', 'Yasothon'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-36', 'Chaiyaphum'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-37', 'Amnat Charoen'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-38', 'Bueng Kan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-39', 'Nong Bua Lam Phu'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-40', 'Khon Kaen'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-41', 'Udon Thani'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-42', 'Loei'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-43', 'Nong Khai'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-44', 'Maha Sarakham'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-45', 'Roi Et'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-46', 'Kalasin'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-47', 'Sakon Nakhon'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-48', 'Nakhon Phanom'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-49', 'Mukdahan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-50', 'Chiang Mai'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-51', 'Lamphun'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-52', 'Lampang'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-53', 'Uttaradit'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-54', 'Phrae'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-55', 'Nan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-56', 'Phayao'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-57', 'Chiang Rai'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-58', 'Mae Hong Son'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-60', 'Nakhon Sawan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-61', 'Uthai Thani'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-62', 'Kamphaeng Phet'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-63', 'Tak'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-64', 'Sukhothai'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-65', 'Phitsanulok'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-66', 'Phichit'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-67', 'Phetchabun'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-70', 'Ratchaburi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-71', 'Kanchanaburi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-72', 'Suphan Buri'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-73', 'Nakhon Pathom'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-74', 'Samut Sakhon'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-75', 'Samut Songkhram'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-76', 'Phetchaburi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-77', 'Prachuap Khiri Khan'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-80', 'Nakhon Si Thammarat'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-81', 'Krabi'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-82', 'Phangnga'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-83', 'Phuket'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-84', 'Surat Thani'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-85', 'Ranong'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-86', 'Chumphon'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-90', 'Songkhla'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-91', 'Satun'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-92', 'Trang'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-93', 'Phatthalung'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-94', 'Pattani'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-95', 'Yala'); #EOQ
INSERT IGNORE INTO `CubeCart_geo_zone` (`country_id`, `abbrev`, `name`) VALUES (211, 'TH-96', 'Narathiwat'); #EOQ
