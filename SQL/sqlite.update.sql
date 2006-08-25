-- RoundCube Webmail update script for SQLite databases
-- Updates from version 0.1-beta2 and older

DROP TABLE messages;

CREATE TABLE messages (
  message_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL default '0',
  del tinyint NOT NULL default '0',
  cache_key varchar(128) NOT NULL default '',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  idx integer NOT NULL default '0',
  uid integer NOT NULL default '0',
  subject varchar(255) NOT NULL default '',
  "from" varchar(255) NOT NULL default '',
  "to" varchar(255) NOT NULL default '',
  cc varchar(255) NOT NULL default '',
  date datetime NOT NULL default '0000-00-00 00:00:00',
  size integer NOT NULL default '0',
  headers text NOT NULL,
  structure text
);

CREATE INDEX ix_messages_user_id ON messages(user_id);
CREATE INDEX ix_messages_cache_key ON messages(cache_key);
CREATE INDEX ix_messages_idx ON messages(idx);
CREATE INDEX ix_messages_uid ON messages(uid);
