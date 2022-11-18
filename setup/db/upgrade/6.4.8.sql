ALTER TABLE `CubeCart_extension_info` ADD INDEX(`seller_id`); #EOQ
ALTER TABLE `CubeCart_extension_info` ADD `keep_current` BOOLEAN NOT NULL DEFAULT FALSE AFTER `modified`; #EOQ
ALTER TABLE `CubeCart_filemanager` ADD `alt` TEXT NOT NULL; #EOQ
ALTER TABLE `CubeCart_order_summary` CHANGE `ship_tracking` `ship_tracking` TEXT NULL DEFAULT NULL; #EOQ
UPDATE `CubeCart_email_content` SET `content_html` = CONCAT(`content_html`,'{if !empty($DATA.ship_tracking)}<p>Track your order:<br>{$DATA.ship_tracking}</p>{/if}') WHERE `content_type` = 'cart.order_complete' AND `content_html` NOT LIKE '%{$DATA.ship_tracking}%'; #EOQ
UPDATE `CubeCart_email_content` SET `content_text` = CONCAT(`content_text`,'{if !empty($DATA.ship_tracking)}\r\n\r\nTrack your order:\r\n{$DATA.ship_tracking}{/if}') WHERE `content_type` = 'cart.order_complete' AND `content_text` NOT LIKE '%{$DATA.ship_tracking}%'; #EOQ
ALTER TABLE `CubeCart_inventory_language` ADD INDEX(`language`); #EOQ