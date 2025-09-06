-- MySQL database updates since version 0.9-beta

ALTER TABLE `events` ADD `url` VARCHAR(255) NOT NULL AFTER `categories`;