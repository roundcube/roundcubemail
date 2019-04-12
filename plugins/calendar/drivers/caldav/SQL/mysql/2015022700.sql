-- add identifier for recurring instances and exceptions

ALTER TABLE `caldav_events` ADD `instance` varchar(16) NOT NULL DEFAULT '' AFTER `uid`;
ALTER TABLE `caldav_events` ADD `isexception` tinyint(1) NOT NULL DEFAULT '0' AFTER `instance`;

UPDATE `caldav_events` SET `instance` = DATE_FORMAT(`start`, '%Y%m%d')
  WHERE `recurrence_id` != 0 AND `instance` = '' AND `all_day` = 1;

UPDATE `caldav_events` SET `instance` = DATE_FORMAT(`start`, '%Y%m%dT%k%i%s')
  WHERE `recurrence_id` != 0 AND `instance` = '' AND `all_day` = 0;

-- extend alarms columns for multiple values

ALTER TABLE `caldav_events` CHANGE `alarms` `alarms` TEXT NULL DEFAULT NULL;