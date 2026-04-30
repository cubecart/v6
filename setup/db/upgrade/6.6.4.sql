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

SET @c := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'CubeCart_inventory' AND INDEX_NAME = 'idx_latest_products'); #EOQ
SET @s := IF(@c = 0, 'ALTER TABLE `CubeCart_inventory` ADD INDEX `idx_latest_products` (`status`, `latest`, `date_added`)', 'SELECT 1'); #EOQ
PREPARE stmt FROM @s; #EOQ
EXECUTE stmt; #EOQ
DEALLOCATE PREPARE stmt; #EOQ