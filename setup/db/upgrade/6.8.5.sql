ALTER TABLE `CubeCart_admin_users` MODIFY `session_id` VARCHAR(64) DEFAULT NULL; #EOQ
UPDATE `CubeCart_admin_users` SET `session_id` = ''; #EOQ
