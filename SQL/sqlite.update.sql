-- Roundcube Webmail update script for SQLite databases
-- Updates from version 0.1-stable to 0.1.1

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
  "cc" varchar(255) NOT NULL default '',
  "date" datetime NOT NULL default '0000-00-00 00:00:00',
  size integer NOT NULL default '0',
  headers text NOT NULL,
  structure text
);

CREATE INDEX ix_messages_user_cache_uid ON messages(user_id,cache_key,uid);
CREATE INDEX ix_users_username ON users(username);
CREATE INDEX ix_users_alias ON users(alias);

-- Updates from version 0.2-alpha

CREATE INDEX ix_messages_created ON messages (created);

-- Updates from version 0.2-beta

CREATE INDEX ix_session_changed ON session (changed);
CREATE INDEX ix_cache_created ON cache (created);

-- Updates from version 0.3-stable

DELETE FROM messages;
DROP INDEX ix_messages_user_cache_uid;
CREATE UNIQUE INDEX ix_messages_user_cache_uid ON messages (user_id,cache_key,uid);
CREATE INDEX ix_messages_index ON messages (user_id,cache_key,idx);
DROP INDEX ix_contacts_user_id;
CREATE INDEX ix_contacts_user_id ON contacts(user_id, email);

-- Updates from version 0.3.1

-- ALTER TABLE identities ADD COLUMN changed datetime NOT NULL default '0000-00-00 00:00:00'; --

CREATE TABLE temp_identities (
  identity_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL default '0',
  standard tinyint NOT NULL default '0',
  name varchar(128) NOT NULL default '',
  organization varchar(128) default '',
  email varchar(128) NOT NULL default '',
  "reply-to" varchar(128) NOT NULL default '',
  bcc varchar(128) NOT NULL default '',
  signature text NOT NULL default '',
  html_signature tinyint NOT NULL default '0'
);
INSERT INTO temp_identities (identity_id, user_id, standard, name, organization, email, "reply-to", bcc, signature, html_signature)
  SELECT identity_id, user_id, standard, name, organization, email, "reply-to", bcc, signature, html_signature
  FROM identities WHERE del=0;

DROP INDEX ix_identities_user_id;
DROP TABLE identities;

CREATE TABLE identities (
  identity_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL default '0',
  changed datetime NOT NULL default '0000-00-00 00:00:00',
  del tinyint NOT NULL default '0',
  standard tinyint NOT NULL default '0',
  name varchar(128) NOT NULL default '',
  organization varchar(128) default '',
  email varchar(128) NOT NULL default '',
  "reply-to" varchar(128) NOT NULL default '',
  bcc varchar(128) NOT NULL default '',
  signature text NOT NULL default '',
  html_signature tinyint NOT NULL default '0'
);
CREATE INDEX ix_identities_user_id ON identities(user_id, del);

INSERT INTO identities (identity_id, user_id, standard, name, organization, email, "reply-to", bcc, signature, html_signature)
  SELECT identity_id, user_id, standard, name, organization, email, "reply-to", bcc, signature, html_signature
  FROM temp_identities;

DROP TABLE temp_identities;

CREATE TABLE contactgroups (
  contactgroup_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL default '0',
  changed datetime NOT NULL default '0000-00-00 00:00:00',
  del tinyint NOT NULL default '0',
  name varchar(128) NOT NULL default ''
);

CREATE INDEX ix_contactgroups_user_id ON contactgroups(user_id, del);

CREATE TABLE contactgroupmembers (
  contactgroup_id integer NOT NULL,
  contact_id integer NOT NULL default '0',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (contactgroup_id, contact_id)
);

-- Updates from version 0.3.1

CREATE TABLE tmp_users (
  user_id integer NOT NULL PRIMARY KEY,
  username varchar(128) NOT NULL default '',
  mail_host varchar(128) NOT NULL default '',
  alias varchar(128) NOT NULL default '',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  last_login datetime NOT NULL default '0000-00-00 00:00:00',
  language varchar(5),
  preferences text NOT NULL default ''
);

INSERT INTO tmp_users (user_id, username, mail_host, alias, created, last_login, language, preferences)
    SELECT user_id, username, mail_host, alias, created, last_login, language, preferences FROM users;

DROP TABLE users;

CREATE TABLE users (
  user_id integer NOT NULL PRIMARY KEY,
  username varchar(128) NOT NULL default '',
  mail_host varchar(128) NOT NULL default '',
  alias varchar(128) NOT NULL default '',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  last_login datetime DEFAULT NULL,
  language varchar(5),
  preferences text NOT NULL default ''
);

INSERT INTO users (user_id, username, mail_host, alias, created, last_login, language, preferences)
    SELECT user_id, username, mail_host, alias, created, last_login, language, preferences FROM tmp_users;

CREATE INDEX ix_users_username ON users(username);
CREATE INDEX ix_users_alias ON users(alias);
DROP TABLE tmp_users;

-- Updates from version 0.4.2

DROP INDEX ix_users_username;
CREATE UNIQUE INDEX ix_users_username ON users(username, mail_host);

CREATE TABLE contacts_tmp (
    contact_id integer NOT NULL PRIMARY KEY,
    user_id integer NOT NULL default '0',
    changed datetime NOT NULL default '0000-00-00 00:00:00',
    del tinyint NOT NULL default '0',
    name varchar(128) NOT NULL default '',
    email varchar(255) NOT NULL default '',
    firstname varchar(128) NOT NULL default '',
    surname varchar(128) NOT NULL default '',
    vcard text NOT NULL default ''
);

INSERT INTO contacts_tmp (contact_id, user_id, changed, del, name, email, firstname, surname, vcard)
    SELECT contact_id, user_id, changed, del, name, email, firstname, surname, vcard FROM contacts;

DROP TABLE contacts;
CREATE TABLE contacts (
    contact_id integer NOT NULL PRIMARY KEY,
    user_id integer NOT NULL default '0',
    changed datetime NOT NULL default '0000-00-00 00:00:00',
    del tinyint NOT NULL default '0',
    name varchar(128) NOT NULL default '',
    email varchar(255) NOT NULL default '',
    firstname varchar(128) NOT NULL default '',
    surname varchar(128) NOT NULL default '',
    vcard text NOT NULL default ''
);

INSERT INTO contacts (contact_id, user_id, changed, del, name, email, firstname, surname, vcard)
    SELECT contact_id, user_id, changed, del, name, email, firstname, surname, vcard FROM contacts_tmp;

CREATE INDEX ix_contacts_user_id ON contacts(user_id, email);
DROP TABLE contacts_tmp;

DELETE FROM messages;


-- Updates from version 0.5.x

CREATE TABLE contacts_tmp (
    contact_id integer NOT NULL PRIMARY KEY,
    user_id integer NOT NULL default '0',
    changed datetime NOT NULL default '0000-00-00 00:00:00',
    del tinyint NOT NULL default '0',
    name varchar(128) NOT NULL default '',
    email varchar(255) NOT NULL default '',
    firstname varchar(128) NOT NULL default '',
    surname varchar(128) NOT NULL default '',
    vcard text NOT NULL default ''
);

INSERT INTO contacts_tmp (contact_id, user_id, changed, del, name, email, firstname, surname, vcard)
    SELECT contact_id, user_id, changed, del, name, email, firstname, surname, vcard FROM contacts;

DROP TABLE contacts;
CREATE TABLE contacts (
    contact_id integer NOT NULL PRIMARY KEY,
    user_id integer NOT NULL default '0',
    changed datetime NOT NULL default '0000-00-00 00:00:00',
    del tinyint NOT NULL default '0',
    name varchar(128) NOT NULL default '',
    email varchar(255) NOT NULL default '',
    firstname varchar(128) NOT NULL default '',
    surname varchar(128) NOT NULL default '',
    vcard text NOT NULL default '',
    words text NOT NULL default ''
);

INSERT INTO contacts (contact_id, user_id, changed, del, name, email, firstname, surname, vcard)
    SELECT contact_id, user_id, changed, del, name, email, firstname, surname, vcard FROM contacts_tmp;

CREATE INDEX ix_contacts_user_id ON contacts(user_id, email);
DROP TABLE contacts_tmp;

DELETE FROM messages;
