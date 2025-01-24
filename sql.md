ALTER TABLE `accounts` ADD `verification` VARCHAR(255);
ALTER TABLE `accounts` ADD `verified` BOOLEAN NOT NULL DEFAULT FALSE AFTER `verification`;
ALTER TABLE `accounts` ADD `banned` BOOLEAN NOT NULL DEFAULT FALSE AFTER `verified`;