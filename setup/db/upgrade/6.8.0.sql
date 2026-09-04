ALTER TABLE `CubeCart_search` ADD INDEX `hits` (`hits`); #EOQ
CREATE TABLE IF NOT EXISTS `CubeCart_stock_log` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`product_id` INT UNSIGNED NOT NULL DEFAULT '0',
	`matrix_id` INT UNSIGNED NULL DEFAULT NULL,
	`change` INT NOT NULL DEFAULT '0',
	`stock_after` INT NULL DEFAULT NULL,
	`source` VARCHAR(32) NOT NULL DEFAULT '',
	`cart_order_id` VARCHAR(18) NULL DEFAULT NULL,
	`admin_id` INT UNSIGNED NULL DEFAULT NULL,
	`note` VARCHAR(255) NOT NULL DEFAULT '',
	`time` INT UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	KEY `product_time` (`product_id`, `time`),
	KEY `cart_order_id` (`cart_order_id`),
	KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ
DELETE FROM `CubeCart_filemanager` WHERE `type` = 1 AND `mimetype` IS NOT NULL AND `mimetype` <> '' AND `mimetype` NOT LIKE 'image/%'; #EOQ
ALTER TABLE `CubeCart_documents` ADD `doc_contact` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'; #EOQ
ALTER TABLE `CubeCart_documents` ADD `doc_departments` TEXT NULL; #EOQ
ALTER TABLE `CubeCart_documents` DROP INDEX `doc_privacy`; #EOQ
ALTER TABLE `CubeCart_documents` DROP `doc_privacy`; #EOQ
ALTER TABLE `CubeCart_admin_users` ADD `extensions_dismissed` TEXT NULL; #EOQ
ALTER TABLE `CubeCart_reviews` ADD `verified` TINYINT(1) NOT NULL DEFAULT '0'; #EOQ
INSERT INTO `CubeCart_config` (`name`, `config_key`, `config_value`) VALUES ('config', 'review_eligibility', '2') ON DUPLICATE KEY UPDATE `config_value` = `config_value`; #EOQ
INSERT INTO `CubeCart_config` (`name`, `config_key`, `config_value`) VALUES ('config', 'review_notify', '1') ON DUPLICATE KEY UPDATE `config_value` = `config_value`; #EOQ
ALTER TABLE `CubeCart_customer` ADD `activate` VARCHAR(64) NULL DEFAULT NULL; #EOQ
ALTER TABLE `CubeCart_customer` ADD `activate_expires` DATETIME NULL DEFAULT NULL; #EOQ
INSERT INTO `CubeCart_email_content` (`description`, `content_type`, `language`, `subject`, `content_html`, `enabled`) SELECT 'Account Activation', 'account.activate', 'en-GB', 'Confirm your email address', '<p>Hi {$DATA.first_name|capitalize},</p>\n<p>Someone asked to create an account with this email address, which we already have on file from a previous order. To finish setting the account up, please confirm the address by following the link below:</p>\n<p><a href=\"{$DATA.activate_link}\">{$DATA.activate_link}</a></p>\n<p>If the link above doesn\'t work, please copy and paste it into your browser address bar. The link expires in 1 hour.</p>\n<p>If this wasn\'t you, no action is needed and no account has been created. Your previous orders are not accessible until the address is confirmed.</p>', 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `CubeCart_email_content` WHERE `content_type` = 'account.activate' AND `language` = 'en-GB'); #EOQ
INSERT INTO `CubeCart_email_content` (`description`, `content_type`, `language`, `subject`, `content_html`, `enabled`) SELECT 'Account Activation', 'account.activate', 'en-US', 'Confirm your email address', '<p>Hi {$DATA.first_name|capitalize},</p>\n<p>Someone asked to create an account with this email address, which we already have on file from a previous order. To finish setting the account up, please confirm the address by following the link below:</p>\n<p><a href=\"{$DATA.activate_link}\">{$DATA.activate_link}</a></p>\n<p>If the link above doesn\'t work, please copy and paste it into your browser address bar. The link expires in 1 hour.</p>\n<p>If this wasn\'t you, no action is needed and no account has been created. Your previous orders are not accessible until the address is confirmed.</p>', 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `CubeCart_email_content` WHERE `content_type` = 'account.activate' AND `language` = 'en-US'); #EOQ
