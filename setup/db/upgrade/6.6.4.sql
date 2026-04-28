ALTER TABLE `CubeCart_cron_tasks` ADD COLUMN `started_at` DATETIME DEFAULT NULL AFTER `last_run`; #EOQ
ALTER TABLE `CubeCart_cron_tasks` ADD COLUMN `last_completed` DATETIME DEFAULT NULL AFTER `started_at`; #EOQ
