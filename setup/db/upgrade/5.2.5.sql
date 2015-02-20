ALTER TABLE `CubeCart_order_summary` CHANGE `discount_type` `discount_type` CHAR( 2 ); #EOQ
ALTER TABLE `CubeCart_image_index` DROP `img`; #EOQ
UPDATE `CubeCart_email_template` SET `content_html` = REPLACE(`content_html`, '</head>', '<base href="{$DATA.storeURL}" />\r\n</head>'); #EOQ