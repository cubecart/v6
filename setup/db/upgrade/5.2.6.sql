ALTER TABLE `CubeCart_inventory` ADD `minimum_quantity` INT( 10) NOT NULL DEFAULT '0' ; #EOQ
UPDATE `CubeCart_email_content` SET `content_html` = REPLACE(`content_html`, '<!--{', '{'); #EOQ
UPDATE `CubeCart_email_content` SET `content_html` = REPLACE(`content_html`, '}-->', '}'); #EOQ
UPDATE `CubeCart_email_content` SET `content_text` = REPLACE(`content_text`, '<!--{', '{'); #EOQ
UPDATE `CubeCart_email_content` SET `content_text` = REPLACE(`content_text`, '}-->', '}'); #EOQ