-- #4738 - The line an inbox shows after the subject (preview text), per email and language
ALTER TABLE `CubeCart_email_content` ADD COLUMN `preheader` VARCHAR(255) NOT NULL DEFAULT '' AFTER `subject`; #EOQ
