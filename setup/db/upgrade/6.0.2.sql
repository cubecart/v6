UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'example-orbit', 'bxslider'); #EOQ
UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'data-orbit=""', ''); #EOQ
UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'orbit-caption', 'hide'); #EOQ
UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'slide1.jpg"', 'slide1.jpg" title="Promotional Message One"'); #EOQ
UPDATE `CubeCart_documents` SET `doc_content` = replace(`doc_content`, 'slide3.jpg"', 'slide3.jpg" title="Promotional Message Two"'); #EOQ
ALTER TABLE  `CubeCart_order_summary` ADD UNIQUE (`cart_order_id`); #EOQ

CREATE TABLE IF NOT EXISTS `CubeCart_order_notes` (
	`note_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`admin_id` INT UNSIGNED NOT NULL,
	`cart_order_id` VARCHAR(18) NOT NULL,
	`time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`content` TEXT NOT NULL,
	PRIMARY KEY (`note_id`),
	KEY `admin_id` (`admin_id`),
	KEY `cart_order_id` (`cart_order_id`),
	KEY `time` (`time`),
	FULLTEXT KEY `content` (`content`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; #EOQ