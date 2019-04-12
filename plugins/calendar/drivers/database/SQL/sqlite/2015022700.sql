-- ALTER TABLE `events` ADD `instance` varchar(16) NOT NULL DEFAULT '' AFTER `uid`;
-- ALTER TABLE `events` ADD `isexception` tinyint(3) NOT NULL DEFAULT '0' AFTER `instance`;
-- ALTER TABLE `events` CHANGE `alarms` `alarms` TEXT NULL DEFAULT NULL;

CREATE TABLE temp_events (
  event_id integer NOT NULL PRIMARY KEY,
  calendar_id integer NOT NULL default '0',
  recurrence_id integer NOT NULL default '0',
  uid varchar(255) NOT NULL default '',
  created datetime NOT NULL default '1000-01-01 00:00:00',
  changed datetime NOT NULL default '1000-01-01 00:00:00',
  sequence integer NOT NULL default '0',
  start datetime NOT NULL default '1000-01-01 00:00:00',
  end datetime NOT NULL default '1000-01-01 00:00:00',
  recurrence varchar(255) default NULL,
  title varchar(255) NOT NULL,
  description text NOT NULL,
  location varchar(255) NOT NULL default '',
  categories varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  all_day tinyint(1) NOT NULL default '0',
  free_busy tinyint(1) NOT NULL default '0',
  priority tinyint(1) NOT NULL default '0',
  sensitivity tinyint(1) NOT NULL default '0',
  status varchar(32) NOT NULL default '',
  alarms varchar(255) default NULL,
  attendees text default NULL,
  notifyat datetime default NULL
);

INSERT INTO temp_events (event_id, calendar_id, recurrence_id, uid, created, changed, sequence, start, end, recurrence, title, description, location, categories, url, all_day, free_busy, priority, sensitivity, alarms, attendees, notifyat)
                  SELECT event_id, calendar_id, recurrence_id, uid, created, changed, sequence, start, end, recurrence, title, description, location, categories, url, all_day, free_busy, priority, sensitivity, alarms, attendees, notifyat
                  FROM events;

DROP TABLE events;

CREATE TABLE events (
  event_id integer NOT NULL PRIMARY KEY,
  calendar_id integer NOT NULL default '0',
  recurrence_id integer NOT NULL default '0',
  uid varchar(255) NOT NULL default '',
  instance varchar(16) NOT NULL default '',
  isexception tinyint(1) NOT NULL default '0',
  created datetime NOT NULL default '1000-01-01 00:00:00',
  changed datetime NOT NULL default '1000-01-01 00:00:00',
  sequence integer NOT NULL default '0',
  start datetime NOT NULL default '1000-01-01 00:00:00',
  end datetime NOT NULL default '1000-01-01 00:00:00',
  recurrence varchar(255) default NULL,
  title varchar(255) NOT NULL,
  description text NOT NULL,
  location varchar(255) NOT NULL default '',
  categories varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default '',
  all_day tinyint(1) NOT NULL default '0',
  free_busy tinyint(1) NOT NULL default '0',
  priority tinyint(1) NOT NULL default '0',
  sensitivity tinyint(1) NOT NULL default '0',
  status varchar(32) NOT NULL default '',
  alarms text default NULL,
  attendees text default NULL,
  notifyat datetime default NULL,
  CONSTRAINT fk_events_calendar_id FOREIGN KEY (calendar_id)
    REFERENCES calendars(calendar_id)
);

INSERT INTO events (event_id, calendar_id, recurrence_id, uid, created, changed, sequence, start, end, recurrence, title, description, location, categories, url, all_day, free_busy, priority, sensitivity, alarms, attendees, notifyat)
             SELECT event_id, calendar_id, recurrence_id, uid, created, changed, sequence, start, end, recurrence, title, description, location, categories, url, all_day, free_busy, priority, sensitivity, alarms, attendees, notifyat
             FROM temp_events;

DROP TABLE temp_events;

-- Derrive instance columns from start date/time

UPDATE events SET instance = strftime('%Y%m%d', start)
 WHERE recurrence_id != 0 AND instance = '' AND all_day = 1;

UPDATE events SET instance = strftime('%Y%m%dT%H%M%S', start)
 WHERE recurrence_id != 0 AND instance = '' AND all_day = 0;
