CREATE TABLE `CubeCart_newsletter_subscriber_log` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`log` text COLLATE utf8_unicode_ci,
	`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`ip_address` varchar(45) COLLATE utf8_unicode_ci DEFAULT '',
	PRIMARY KEY (`id`),
	KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; #EOQ