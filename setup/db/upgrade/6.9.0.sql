-- #4738 - The line an inbox shows after the subject (preview text), per email and language
ALTER TABLE `CubeCart_email_content` ADD COLUMN `preheader` VARCHAR(255) NOT NULL DEFAULT '' AFTER `subject`; #EOQ
-- #4738 - Nothing has sent the "tell a friend" email since the feature was removed, so its wording goes too
DELETE FROM `CubeCart_email_content` WHERE `content_type` = 'catalogue.tell_friend'; #EOQ
-- #4738 - The template a new newsletter starts in, chosen apart from the one the store's emails are sent in
ALTER TABLE `CubeCart_email_template` ADD COLUMN `newsletter_default` ENUM('0','1') NOT NULL DEFAULT '0' AFTER `template_default`; #EOQ
-- #4738 - An upgraded store starts with its newsletter template chosen where there is no doubt which it is: the only template with an unsubscribe link
UPDATE `CubeCart_email_template` SET `newsletter_default` = '1' WHERE `content_html` LIKE '%unsubscribeURL%' AND (SELECT `found` FROM (SELECT COUNT(*) AS `found` FROM `CubeCart_email_template` WHERE `content_html` LIKE '%unsubscribeURL%') AS `candidates`) = 1; #EOQ
-- #4738 - Newsletters: preview text, a name to greet a subscriber by when theirs is not known, a time to send at, and what came of it. The two counts are empty, not zero, for a newsletter sent before they were kept
ALTER TABLE `CubeCart_newsletter` ADD COLUMN `preheader` VARCHAR(255) NOT NULL DEFAULT '' AFTER `subject`; #EOQ
ALTER TABLE `CubeCart_newsletter` ADD COLUMN `name_fallback` VARCHAR(64) NOT NULL DEFAULT '' AFTER `content_html`; #EOQ
ALTER TABLE `CubeCart_newsletter` ADD COLUMN `send_at` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `dbl_opt`; #EOQ
ALTER TABLE `CubeCart_newsletter` ADD COLUMN `opened_count` INT UNSIGNED NULL DEFAULT NULL AFTER `total_subscribers`; #EOQ
ALTER TABLE `CubeCart_newsletter` ADD COLUMN `unsubscribed_count` INT UNSIGNED NULL DEFAULT NULL AFTER `opened_count`; #EOQ
-- #4738 - A store installed new on 6.7.0 or later was never given the columns that record an email being opened (an upgraded store was), so they are added where they are missing
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'CubeCart_email_log' AND COLUMN_NAME = 'tracking_token'); #EOQ
SET @s := IF(@c = 0, 'ALTER TABLE `CubeCart_email_log` ADD COLUMN `tracking_token` VARCHAR(32) NULL DEFAULT NULL AFTER `email_method`', 'SELECT 1'); #EOQ
PREPARE stmt FROM @s; #EOQ
EXECUTE stmt; #EOQ
DEALLOCATE PREPARE stmt; #EOQ
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'CubeCart_email_log' AND COLUMN_NAME = 'seen_at'); #EOQ
SET @s := IF(@c = 0, 'ALTER TABLE `CubeCart_email_log` ADD COLUMN `seen_at` TIMESTAMP NULL DEFAULT NULL AFTER `tracking_token`', 'SELECT 1'); #EOQ
PREPARE stmt FROM @s; #EOQ
EXECUTE stmt; #EOQ
DEALLOCATE PREPARE stmt; #EOQ
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'CubeCart_email_log' AND COLUMN_NAME = 'seen_count'); #EOQ
SET @s := IF(@c = 0, 'ALTER TABLE `CubeCart_email_log` ADD COLUMN `seen_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `seen_at`', 'SELECT 1'); #EOQ
PREPARE stmt FROM @s; #EOQ
EXECUTE stmt; #EOQ
DEALLOCATE PREPARE stmt; #EOQ
SET @c := (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'CubeCart_email_log' AND INDEX_NAME = 'tracking_token'); #EOQ
SET @s := IF(@c = 0, 'ALTER TABLE `CubeCart_email_log` ADD INDEX `tracking_token` (`tracking_token`)', 'SELECT 1'); #EOQ
PREPARE stmt FROM @s; #EOQ
EXECUTE stmt; #EOQ
DEALLOCATE PREPARE stmt; #EOQ
-- #4738 - Which newsletter an email in the log was, so that an open can be counted against it
ALTER TABLE `CubeCart_email_log` ADD COLUMN `newsletter_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `seen_count`; #EOQ
-- #2858 - The key in an order's payment link. It is empty until a link is first made for the order
ALTER TABLE `CubeCart_order_summary` ADD COLUMN `pay_key` VARCHAR(32) NULL DEFAULT NULL AFTER `coupon_data`; #EOQ
