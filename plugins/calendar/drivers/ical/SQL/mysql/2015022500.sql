/**
 * iCAL Client
 *
 * @version @package_version@
 * @author Daniel Morlock <daniel.morlock@awesome-it.de>
 *
 * Copyright (C) Awesome IT GbR <info@awesome-it.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/* Create new tables */
CREATE TABLE IF NOT EXISTS `ical_calendars` (
  `calendar_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `color` varchar(8) NOT NULL,
  `showalarms` tinyint(1) NOT NULL DEFAULT '1',

  `ical_url` varchar(255) NOT NULL,
  `ical_user` varchar(255) DEFAULT NULL,
  `ical_pass` varchar(1024) DEFAULT NULL,
  `ical_last_change` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY(`calendar_id`),
  INDEX `ical_user_name_idx` (`user_id`, `name`),
  CONSTRAINT `fk_ical_calendars_user_id` FOREIGN KEY (`user_id`)
  REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) /*!40000 ENGINE=INNODB */ /*!40101 CHARACTER SET utf8 COLLATE utf8_general_ci */;

CREATE TABLE IF NOT EXISTS `ical_events` (
  `event_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `calendar_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `recurrence_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `uid` varchar(255) NOT NULL DEFAULT '',
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `sequence` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `start` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `end` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `recurrence` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) NOT NULL DEFAULT '',
  `categories` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `all_day` tinyint(1) NOT NULL DEFAULT '0',
  `free_busy` tinyint(1) NOT NULL DEFAULT '0',
  `priority` tinyint(1) NOT NULL DEFAULT '0',
  `sensitivity` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(32) NOT NULL DEFAULT '',
  `alarms` varchar(255) DEFAULT NULL,
  `attendees` text DEFAULT NULL,
  `notifyat` datetime DEFAULT NULL,

  PRIMARY KEY(`event_id`),
  INDEX `ical_uid_idx` (`uid`),
  INDEX `ical_recurrence_idx` (`recurrence_id`),
  INDEX `ical_calendar_notify_idx` (`calendar_id`,`notifyat`),
  CONSTRAINT `fk_ical_events_calendar_id` FOREIGN KEY (`calendar_id`)
  REFERENCES `calendars`(`calendar_id`) ON DELETE CASCADE ON UPDATE CASCADE
) /*!40000 ENGINE=INNODB */ /*!40101 CHARACTER SET utf8 COLLATE utf8_general_ci */;

CREATE TABLE IF NOT EXISTS `ical_attachments` (
  `attachment_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `mimetype` varchar(255) NOT NULL DEFAULT '',
  `size` int(11) NOT NULL DEFAULT '0',
  `data` longtext NOT NULL,
  PRIMARY KEY(`attachment_id`),
  CONSTRAINT `fk_ical_attachments_event_id` FOREIGN KEY (`event_id`)
  REFERENCES `events`(`event_id`) ON DELETE CASCADE ON UPDATE CASCADE
) /*!40000 ENGINE=INNODB */ /*!40101 CHARACTER SET utf8 COLLATE utf8_general_ci */;

/* Migrate Data */
INSERT INTO ical_calendars
  SELECT calendar_id, user_id, `name`, color, showalarms,
    url as ical_url, NULL as ical_user, NULL as ical_pass, last_change as ical_last_change
  FROM calendars cal, ical_props dav
  WHERE dav.obj_id = cal.calendar_id
        AND dav.obj_type = 'ical';

INSERT INTO ical_events SELECT e.* FROM `events` e
  WHERE e.calendar_id IN (
    SELECT obj_id FROM ical_props
    WHERE obj_type = 'ical'
  );

INSERT INTO ical_attachments SELECT * FROM attachments a
WHERE a.event_id IN (
  SELECT e.event_id FROM `events` e
  WHERE e.calendar_id IN (
    SELECT obj_id FROM ical_props
    WHERE obj_type = 'ical'
  )
);

/* Drop deprecated data */
DELETE FROM `events` WHERE event_id IN (
    SELECT obj_id FROM ical_props dav
    WHERE dav.obj_type = 'vevent'
);
DELETE FROM calendars WHERE calendar_id IN (
  SELECT obj_id FROM ical_props dav
  WHERE dav.obj_type = 'ical'
);
DELETE FROM attachments WHERE event_id IN (
  SELECT obj_id FROM ical_props dav
  WHERE dav.obj_type = 'vevent'
);
DROP TABLE ical_props;

