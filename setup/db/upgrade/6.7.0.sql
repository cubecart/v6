ALTER TABLE `CubeCart_cron_tasks` ADD COLUMN `started_at` DATETIME DEFAULT NULL AFTER `last_run`; #EOQ
ALTER TABLE `CubeCart_cron_tasks` ADD COLUMN `last_completed` DATETIME DEFAULT NULL AFTER `started_at`; #EOQ

ALTER TABLE `CubeCart_newsletter` ADD COLUMN `last_subscriber_id` INT UNSIGNED NOT NULL DEFAULT 0; #EOQ
ALTER TABLE `CubeCart_newsletter` ADD COLUMN `sent_count` INT UNSIGNED NOT NULL DEFAULT 0; #EOQ
ALTER TABLE `CubeCart_newsletter` ADD COLUMN `total_subscribers` INT UNSIGNED NOT NULL DEFAULT 0; #EOQ
ALTER TABLE `CubeCart_newsletter` ADD COLUMN `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP; #EOQ
UPDATE `CubeCart_newsletter` SET `date_created` = `date_saved` WHERE `date_created` >= `date_saved`; #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_newsletter_send_log` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `newsletter_id` INT UNSIGNED NOT NULL, `sent_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `sent_at` (`sent_at`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ

INSERT INTO `CubeCart_cron_tasks` (`method`, `label`, `enabled`, `frequency`) VALUES ('processNewsletters', 'Process Newsletter Queue', 1, 600) ON DUPLICATE KEY UPDATE `method`=`method`; #EOQ
UPDATE `CubeCart_inventory` SET `minimum_quantity` = 1 WHERE `minimum_quantity` IS NULL OR `minimum_quantity` = 0 OR `minimum_quantity` = ''; #EOQ

ALTER TABLE `CubeCart_seo_urls` ADD COLUMN `hit_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `redirect`; #EOQ
ALTER TABLE `CubeCart_seo_urls` ADD COLUMN `last_hit` TIMESTAMP NULL DEFAULT NULL AFTER `hit_count`; #EOQ

DELETE FROM `CubeCart_404_log` WHERE `uri` REGEXP '\\.(png|jpe?g|gif|webp|svg|bmp|ico|avif|css|js|map|woff2?|ttf|eot|otf|mp3|mp4|webm|pdf|xml|json)$'; #EOQ
DELETE FROM `CubeCart_404_log` WHERE `uri` REGEXP '/\\.[^/]+(/|$)'; #EOQ

DROP TABLE IF EXISTS `CubeCart_cookie_consent`; #EOQ
DROP TABLE IF EXISTS `CubeCart_cookie_consent_text`; #EOQ

SET @c := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'CubeCart_inventory' AND INDEX_NAME = 'idx_latest_products'); #EOQ
SET @s := IF(@c = 0, 'ALTER TABLE `CubeCart_inventory` ADD INDEX `idx_latest_products` (`status`, `latest`, `date_added`)', 'SELECT 1'); #EOQ
PREPARE stmt FROM @s; #EOQ
EXECUTE stmt; #EOQ
DEALLOCATE PREPARE stmt; #EOQ

INSERT INTO `CubeCart_config` (`name`, `config_key`, `config_value`) VALUES ('config', 'catalogue_related_products', '1') ON DUPLICATE KEY UPDATE `config_key`=`config_key`; #EOQ
INSERT INTO `CubeCart_config` (`name`, `config_key`, `config_value`) VALUES ('config', 'catalogue_related_products_count', '5') ON DUPLICATE KEY UPDATE `config_key`=`config_key`; #EOQ

ALTER TABLE `CubeCart_email_log` ADD COLUMN `tracking_token` VARCHAR(32) NULL DEFAULT NULL AFTER `email_method`; #EOQ
ALTER TABLE `CubeCart_email_log` ADD COLUMN `seen_at` TIMESTAMP NULL DEFAULT NULL AFTER `tracking_token`; #EOQ
ALTER TABLE `CubeCart_email_log` ADD COLUMN `seen_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `seen_at`; #EOQ

SET @c := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'CubeCart_email_log' AND INDEX_NAME = 'tracking_token'); #EOQ
SET @s := IF(@c = 0, 'ALTER TABLE `CubeCart_email_log` ADD INDEX `tracking_token` (`tracking_token`)', 'SELECT 1'); #EOQ
PREPARE stmt FROM @s; #EOQ
EXECUTE stmt; #EOQ
DEALLOCATE PREPARE stmt; #EOQ

INSERT INTO `CubeCart_config` (`name`, `config_key`, `config_value`) VALUES ('config', 'email_track_opens', '1') ON DUPLICATE KEY UPDATE `config_key`=`config_key`; #EOQ

SET @c := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'CubeCart_order_summary' AND INDEX_NAME = 'status_orderdate_total'); #EOQ
SET @s := IF(@c = 0, 'ALTER TABLE `CubeCart_order_summary` ADD INDEX `status_orderdate_total` (`status`, `order_date`, `total`)', 'SELECT 1'); #EOQ
PREPARE stmt FROM @s; #EOQ
EXECUTE stmt; #EOQ
DEALLOCATE PREPARE stmt; #EOQ

# Error log: switch to hash-deduped rows so identical errors update a counter
# instead of stacking duplicate rows. Existing rows are truncated — the data was
# noisy and the merchant can re-accumulate cleanly with the new schema.
TRUNCATE TABLE `CubeCart_admin_error_log`; #EOQ
TRUNCATE TABLE `CubeCart_system_error_log`; #EOQ

ALTER TABLE `CubeCart_admin_error_log` ADD COLUMN `message_hash` CHAR(40) NOT NULL DEFAULT '' AFTER `admin_id`; #EOQ
ALTER TABLE `CubeCart_admin_error_log` ADD COLUMN `first_time` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `time`; #EOQ
ALTER TABLE `CubeCart_admin_error_log` ADD COLUMN `occurrences` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `first_time`; #EOQ
ALTER TABLE `CubeCart_admin_error_log` ADD UNIQUE KEY `admin_message_hash` (`admin_id`, `message_hash`); #EOQ

ALTER TABLE `CubeCart_system_error_log` ADD COLUMN `message_hash` CHAR(40) NOT NULL DEFAULT '' AFTER `log_id`; #EOQ
ALTER TABLE `CubeCart_system_error_log` ADD COLUMN `first_time` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `time`; #EOQ
ALTER TABLE `CubeCart_system_error_log` ADD COLUMN `occurrences` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `first_time`; #EOQ
ALTER TABLE `CubeCart_system_error_log` ADD UNIQUE KEY `message_hash` (`message_hash`); #EOQ
# Request log: hash-deduped rows so identical outbound HTTP calls update a counter
# instead of stacking duplicates. Existing rows are truncated.
TRUNCATE TABLE `CubeCart_request_log`; #EOQ
ALTER TABLE `CubeCart_request_log` ADD COLUMN `request_hash` CHAR(40) NOT NULL DEFAULT '' AFTER `request_id`; #EOQ
ALTER TABLE `CubeCart_request_log` ADD COLUMN `first_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `time`; #EOQ
ALTER TABLE `CubeCart_request_log` ADD COLUMN `occurrences` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `first_time`; #EOQ
ALTER TABLE `CubeCart_request_log` ADD UNIQUE KEY `request_hash` (`request_hash`); #EOQ
ALTER TABLE `CubeCart_image_index` ADD COLUMN `position` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `main_img`; #EOQ
UPDATE `CubeCart_image_index` SET `position` = `id` WHERE `position` = 0; #EOQ
ALTER TABLE `CubeCart_image_index` ADD INDEX `product_position` (`product_id`, `position`); #EOQ
