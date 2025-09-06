-- MySQL database updates since version 1.0

ALTER TABLE `events` ADD `status` VARCHAR(32) NOT NULL AFTER `sensitivity`;
