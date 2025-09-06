/**
 * Roundcube Calendar
 *
 * Plugin to add a calendar to Roundcube.
 *
 * @author Lazlo Westerhof
 * @author Thomas Bruederli
 * @author Albert Lee
 * @licence GNU AGPL
 * @copyright (c) 2010 Lazlo Westerhof - Netherlands
 * @copyright (c) 2014 Kolab Systems AG
 *
 **/

CREATE TABLE calendars (
  calendar_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  color varchar(255) NOT NULL default '',
  showalarms tinyint(1) NOT NULL default '1',
  CONSTRAINT fk_calendars_user_id FOREIGN KEY (user_id)
    REFERENCES users(user_id)
);

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

CREATE TABLE attachments (
  attachment_id integer NOT NULL PRIMARY KEY,
  event_id integer NOT NULL default '0',
  filename varchar(255) NOT NULL default '',
  mimetype varchar(255) NOT NULL default '',
  size integer NOT NULL default '0',
  data text NOT NULL default '',
  CONSTRAINT fk_attachment_event_id FOREIGN KEY (event_id)
    REFERENCES events(event_id)
);

CREATE TABLE itipinvitations (
  token varchar(64) NOT NULL PRIMARY KEY,
  event_uid varchar(255) NOT NULL,
  user_id integer NOT NULL default '0',
  event text NOT NULL,
  expires datetime NOT NULL default '1000-01-01 00:00:00',
  cancelled tinyint(1) NOT NULL default '0',
  CONSTRAINT fk_itipinvitations_user_id FOREIGN KEY (user_id)
    REFERENCES users(user_id)
);

CREATE INDEX ix_itipinvitations_uid ON itipinvitations(user_id, event_uid);

INSERT INTO system (name, value) VALUES ('calendar-database-version', '2015022700');
