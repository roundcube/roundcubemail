-- Add foreign keys

DELETE FROM contacts WHERE user_id NOT IN (SELECT user_id FROM users);
ALTER TABLE contacts RENAME TO old_contacts;
DROP INDEX ix_contacts_user_id;
CREATE TABLE contacts (
  contact_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL
    REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  changed datetime NOT NULL default '0000-00-00 00:00:00',
  del tinyint NOT NULL default '0',
  name varchar(128) NOT NULL default '',
  email text NOT NULL default '',
  firstname varchar(128) NOT NULL default '',
  surname varchar(128) NOT NULL default '',
  vcard text NOT NULL default '',
  words text NOT NULL default ''
);
CREATE INDEX ix_contacts_user_id ON contacts(user_id, del);
INSERT INTO contacts (contact_id, user_id, changed, del, name, email, firstname, surname, vcard, words)
    SELECT contact_id, user_id, changed, del, name, email, firstname, surname, vcard, words FROM old_contacts;
DROP TABLE old_contacts;

DELETE FROM contactgroups WHERE user_id NOT IN (SELECT user_id FROM users);
ALTER TABLE contactgroups RENAME TO old_contactgroups;
DROP INDEX ix_contactgroups_user_id;
CREATE TABLE contactgroups (
  contactgroup_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL
    REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  changed datetime NOT NULL default '0000-00-00 00:00:00',
  del tinyint NOT NULL default '0',
  name varchar(128) NOT NULL default ''
);
CREATE INDEX ix_contactgroups_user_id ON contactgroups(user_id, del);
INSERT INTO contactgroups (contactgroup_id, user_id, changed, del, name)
    SELECT contactgroup_id, user_id, changed, del, name FROM old_contactgroups;
DROP TABLE old_contactgroups;

DELETE FROM contactgroupmembers WHERE contact_id NOT IN (SELECT contact_id FROM contacts);
DELETE FROM contactgroupmembers WHERE contactgroup_id NOT IN (SELECT contactgroup_id FROM contactgroups);
ALTER TABLE contactgroupmembers RENAME TO old_contactgroupmembers;
DROP INDEX ix_contactgroupmembers_contact_id;
CREATE TABLE contactgroupmembers (
  contactgroup_id integer NOT NULL
    REFERENCES contactgroups (contactgroup_id) ON DELETE CASCADE ON UPDATE CASCADE,
  contact_id integer NOT NULL
    REFERENCES contacts (contact_id) ON DELETE CASCADE ON UPDATE CASCADE,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY (contactgroup_id, contact_id)
);
INSERT INTO contactgroupmembers (contactgroup_id, contact_id, created)
    SELECT contactgroup_id, contact_id, created FROM old_contactgroupmembers;
CREATE INDEX ix_contactgroupmembers_contact_id ON contactgroupmembers (contact_id);
DROP TABLE old_contactgroupmembers;

DELETE FROM collected_addresses WHERE user_id NOT IN (SELECT user_id FROM users);
ALTER TABLE collected_addresses RENAME TO old_collected_addresses;
DROP  INDEX ix_collected_addresses_user_id;
CREATE TABLE collected_addresses (
  address_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL
    REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  changed datetime NOT NULL default '0000-00-00 00:00:00',
  name varchar(255) NOT NULL default '',
  email varchar(255) NOT NULL,
  "type" integer NOT NULL
);
CREATE UNIQUE INDEX ix_collected_addresses_user_id ON collected_addresses(user_id, "type", email);
INSERT INTO collected_addresses (address_id, user_id, changed, name, email, "type")
    SELECT address_id, user_id, changed, name, email, "type" FROM old_collected_addresses;
DROP TABLE old_collected_addresses;

DELETE FROM identities WHERE user_id NOT IN (SELECT user_id FROM users);
ALTER TABLE identities RENAME TO old_identities;
DROP INDEX ix_identities_user_id;
DROP INDEX ix_identities_email;
CREATE TABLE identities (
  identity_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL
    REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
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
CREATE INDEX ix_identities_email ON identities(email, del);
INSERT INTO identities (identity_id, user_id, changed, del, standard, name, organization, email, "reply-to", bcc, signature, html_signature)
    SELECT identity_id, user_id, changed, del, standard, name, organization, email, "reply-to", bcc, signature, html_signature FROM old_identities;
DROP TABLE old_identities;

DELETE FROM responses WHERE user_id NOT IN (SELECT user_id FROM users);
ALTER TABLE responses RENAME TO old_responses;
DROP INDEX ix_responses_user_id;
CREATE TABLE responses (
  response_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL
    REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  changed datetime NOT NULL default '0000-00-00 00:00:00',
  del tinyint NOT NULL default '0',
  name varchar(255) NOT NULL,
  data text NOT NULL,
  is_html tinyint NOT NULL default '0'
);
CREATE INDEX ix_responses_user_id ON responses(user_id, del);
INSERT INTO responses (response_id, user_id, changed, del, name, data, is_html)
    SELECT response_id, user_id, changed, del, name, data, is_html FROM old_responses;
DROP TABLE old_responses;

DELETE FROM dictionary WHERE user_id IS NOT NULL AND user_id NOT IN (SELECT user_id FROM users);
ALTER TABLE dictionary RENAME TO old_dictionary;
DROP INDEX ix_dictionary_user_language;
CREATE TABLE dictionary (
  user_id integer DEFAULT NULL
    REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  language varchar(16) NOT NULL,
  data text NOT NULL
);
CREATE UNIQUE INDEX ix_dictionary_user_language ON dictionary (user_id, language);
INSERT INTO dictionary (user_id, language, data)
    SELECT user_id, language, data FROM old_dictionary;
DROP TABLE old_dictionary;

DELETE FROM searches WHERE user_id NOT IN (SELECT user_id FROM users);
ALTER TABLE searches RENAME TO old_searches;
DROP INDEX ix_searches_user_type_name;
CREATE TABLE searches (
  search_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL
    REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  "type" smallint NOT NULL DEFAULT '0',
  name varchar(128) NOT NULL,
  data text NOT NULL
);
CREATE UNIQUE INDEX ix_searches_user_type_name ON searches (user_id, type, name);
INSERT INTO searches (search_id, user_id, "type", name, data)
    SELECT search_id, user_id, "type", name, data FROM old_searches;
DROP TABLE old_searches;

DELETE FROM filestore WHERE user_id NOT IN (SELECT user_id FROM users);
ALTER TABLE filestore RENAME TO old_filestore;
DROP INDEX ix_filestore_user_id;
CREATE TABLE filestore (
    file_id integer NOT NULL PRIMARY KEY,
    user_id integer NOT NULL
        REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    context varchar(32) NOT NULL,
    filename varchar(128) NOT NULL,
    mtime integer NOT NULL,
    data text NOT NULL
);
CREATE UNIQUE INDEX ix_filestore_user_id ON filestore(user_id, context, filename);
INSERT INTO filestore (file_id, user_id, context, filename, mtime, data)
    SELECT file_id, user_id, context, filename, mtime, data FROM old_filestore;
DROP TABLE old_filestore;


DROP TABLE cache;
CREATE TABLE cache (
  user_id integer NOT NULL
    REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  cache_key varchar(128) NOT NULL default '',
  expires datetime DEFAULT NULL,
  data text NOT NULL,
  PRIMARY KEY (user_id, cache_key)
);
CREATE INDEX ix_cache_expires ON cache(expires);

DROP TABLE cache_index;
CREATE TABLE cache_index (
    user_id integer NOT NULL
        REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    mailbox varchar(255) NOT NULL,
    expires datetime DEFAULT NULL,
    valid smallint NOT NULL DEFAULT '0',
    data text NOT NULL,
    PRIMARY KEY (user_id, mailbox)
);
CREATE INDEX ix_cache_index_expires ON cache_index (expires);

DROP TABLE cache_thread;
CREATE TABLE cache_thread (
    user_id integer NOT NULL
        REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    mailbox varchar(255) NOT NULL,
    expires datetime DEFAULT NULL,
    data text NOT NULL,
    PRIMARY KEY (user_id, mailbox)
);
CREATE INDEX ix_cache_thread_expires ON cache_thread (expires);

DROP TABLE cache_messages;
CREATE TABLE cache_messages (
    user_id integer NOT NULL
        REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    mailbox varchar(255) NOT NULL,
    uid integer NOT NULL,
    expires datetime DEFAULT NULL,
    data text NOT NULL,
    flags integer NOT NULL DEFAULT '0',
    PRIMARY KEY (user_id, mailbox, uid)
);

CREATE INDEX ix_cache_messages_expires ON cache_messages (expires);
