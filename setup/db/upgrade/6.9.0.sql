-- #4738 - The line an inbox shows after the subject (preview text), per email and language
ALTER TABLE `CubeCart_email_content` ADD COLUMN `preheader` VARCHAR(255) NOT NULL DEFAULT '' AFTER `subject`; #EOQ
-- #4738 - Nothing has sent the "tell a friend" email since the feature was removed, so its wording goes too
DELETE FROM `CubeCart_email_content` WHERE `content_type` = 'catalogue.tell_friend'; #EOQ
-- #4738 - The template a new newsletter starts in, chosen apart from the one the store's emails are sent in
ALTER TABLE `CubeCart_email_template` ADD COLUMN `newsletter_default` ENUM('0','1') NOT NULL DEFAULT '0' AFTER `template_default`; #EOQ
-- #4738 - An upgraded store starts with its newsletter template chosen where there is no doubt which it is: the only template with an unsubscribe link
UPDATE `CubeCart_email_template` SET `newsletter_default` = '1' WHERE `content_html` LIKE '%unsubscribeURL%' AND (SELECT `found` FROM (SELECT COUNT(*) AS `found` FROM `CubeCart_email_template` WHERE `content_html` LIKE '%unsubscribeURL%') AS `candidates`) = 1; #EOQ
