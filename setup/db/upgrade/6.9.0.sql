-- #4738 - The line an inbox shows after the subject (preview text), per email and language
ALTER TABLE `CubeCart_email_content` ADD COLUMN `preheader` VARCHAR(255) NOT NULL DEFAULT '' AFTER `subject`; #EOQ
-- #4738 - Nothing has sent the "tell a friend" email since the feature was removed, so its wording goes too
DELETE FROM `CubeCart_email_content` WHERE `content_type` = 'catalogue.tell_friend'; #EOQ
