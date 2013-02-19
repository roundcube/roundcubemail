-- Roundcube Webmail initial database structure

-- 
-- Table structure for table cache
-- 

CREATE TABLE cache (
  user_id integer NOT NULL default 0,
  cache_key varchar(128) NOT NULL default '',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  data text NOT NULL
);

CREATE INDEX ix_cache_user_cache_key ON cache(user_id, cache_key);
CREATE INDEX ix_cache_created ON cache(created);


-- --------------------------------------------------------

-- 
-- Table structure for table contacts and related
-- 

CREATE TABLE contacts (
  contact_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL,
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

CREATE INDEX ix_contactgroupmembers_contact_id ON contactgroupmembers (contact_id);


-- --------------------------------------------------------

-- 
-- Table structure for table identities
-- 

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
CREATE INDEX ix_identities_email ON identities(email, del);


-- --------------------------------------------------------

-- 
-- Table structure for table users
-- 

CREATE TABLE users (
  user_id integer NOT NULL PRIMARY KEY,
  username varchar(128) NOT NULL default '',
  mail_host varchar(128) NOT NULL default '',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  last_login datetime DEFAULT NULL,
  language varchar(5),
  preferences text NOT NULL default ''
);

CREATE UNIQUE INDEX ix_users_username ON users(username, mail_host);

-- --------------------------------------------------------

-- 
-- Table structure for table session
-- 

CREATE TABLE session (
  sess_id varchar(128) NOT NULL PRIMARY KEY,
  created datetime NOT NULL default '0000-00-00 00:00:00',
  changed datetime NOT NULL default '0000-00-00 00:00:00',
  ip varchar(40) NOT NULL default '',
  vars text NOT NULL
);

CREATE INDEX ix_session_changed ON session (changed);

-- --------------------------------------------------------

--
-- Table structure for table dictionary
--

CREATE TABLE dictionary (
    user_id integer DEFAULT NULL,
   "language" varchar(5) NOT NULL,
    data text NOT NULL
);

CREATE UNIQUE INDEX ix_dictionary_user_language ON dictionary (user_id, "language");

-- --------------------------------------------------------

--
-- Table structure for table searches
--

CREATE TABLE searches (
  search_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL DEFAULT '0',
  "type" smallint NOT NULL DEFAULT '0',
  name varchar(128) NOT NULL,
  data text NOT NULL
);

CREATE UNIQUE INDEX ix_searches_user_type_name ON searches (user_id, type, name);

-- --------------------------------------------------------

--
-- Table structure for table cache_index
--

CREATE TABLE cache_index (
    user_id integer NOT NULL,
    mailbox varchar(255) NOT NULL,
    changed datetime NOT NULL default '0000-00-00 00:00:00',
    valid smallint NOT NULL DEFAULT '0',
    data text NOT NULL,
    PRIMARY KEY (user_id, mailbox)
);

CREATE INDEX ix_cache_index_changed ON cache_index (changed);

-- --------------------------------------------------------

--
-- Table structure for table cache_thread
--

CREATE TABLE cache_thread (
    user_id integer NOT NULL,
    mailbox varchar(255) NOT NULL,
    changed datetime NOT NULL default '0000-00-00 00:00:00',
    data text NOT NULL,
    PRIMARY KEY (user_id, mailbox)
);

CREATE INDEX ix_cache_thread_changed ON cache_thread (changed);

-- --------------------------------------------------------

--
-- Table structure for table cache_messages
--

CREATE TABLE cache_messages (
    user_id integer NOT NULL,
    mailbox varchar(255) NOT NULL,
    uid integer NOT NULL,
    changed datetime NOT NULL default '0000-00-00 00:00:00',
    data text NOT NULL,
    flags integer NOT NULL DEFAULT '0',
    PRIMARY KEY (user_id, mailbox, uid)
);

CREATE INDEX ix_cache_messages_changed ON cache_messages (changed);

-- --------------------------------------------------------

--
-- Table structure for table system
--

CREATE TABLE system (
  name varchar(64) NOT NULL PRIMARY KEY,
  value text NOT NULL
);

INSERT INTO system (name, value) VALUES ('roundcube-version', '2013011700');
