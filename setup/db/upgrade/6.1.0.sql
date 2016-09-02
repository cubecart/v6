ALTER TABLE `CubeCart_order_history` ADD `initiator` VARCHAR(1) NOT NULL DEFAULT 'G'; #EOQ
UPDATE `CubeCart_order_history` SET `initiator` = 'U'; #EOQ