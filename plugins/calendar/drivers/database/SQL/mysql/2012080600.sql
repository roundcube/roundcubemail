-- MySQL database updates since version 0.7/0.8

ALTER TABLE `events` ADD `sequence` int(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `changed`;
