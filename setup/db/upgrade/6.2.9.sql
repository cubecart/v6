ALTER TABLE `CubeCart_cookie_consent` ADD `log_hash` VARCHAR(32) NOT NULL; #EOQ
ALTER TABLE `CubeCart_cookie_consent` ADD `url_shown` VARCHAR(255) NOT NULL; #EOQ
UPDATE `CubeCart_cookie_consent` SET `log_hash` = MD5(`log`); #EOQ
ALTER TABLE `CubeCart_cookie_consent` ADD INDEX(`log_hash`); #EOQ
CREATE TEMPORARY TABLE `tmp_cookie_table` (SELECT * FROM `CubeCart_cookie_consent` GROUP BY `log_hash`,`session_id`); #EOQ
TRUNCATE `CubeCart_cookie_consent`; #EOQ
INSERT INTO `CubeCart_cookie_consent` (SELECT * FROM `tmp_cookie_table`); #EOQ
DROP TEMPORARY TABLE `tmp_cookie_table`; #EOQ