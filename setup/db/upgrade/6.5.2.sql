ALTER TABLE `CubeCart_admin_log` ADD `item_id` INT UNSIGNED NULL DEFAULT NULL AFTER `description`; #EOQ
ALTER TABLE `CubeCart_admin_log` ADD `item_type` VARCHAR(4) NULL DEFAULT NULL AFTER `item_id`; #EOQ
ALTER TABLE `CubeCart_request_log` ADD `request_headers` BLOB NULL AFTER `time`; #EOQ
ALTER TABLE `CubeCart_request_log` ADD `response_headers` BLOB NULL AFTER `request_headers`; #EOQ
ALTER TABLE `CubeCart_inventory_language` ADD `description_short` TEXT NOT NULL AFTER `description`; #EOQ