-- RoundCube Webmail initial database structure
-- Version 0.1a
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `cache`
-- 

CREATE TABLE cache (
  cache_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL default 0,
  session_id varchar(32) default NULL,
  cache_key varchar(128) NOT NULL default '',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  data longtext NOT NULL
);

CREATE INDEX ix_cache_user_id ON cache(user_id);
CREATE INDEX ix_cache_cache_key ON cache(cache_key);
CREATE INDEX ix_cache_session_id ON cache(session_id);

-- --------------------------------------------------------

-- 
-- Table structure for table contacts
-- 

CREATE TABLE contacts (
  contact_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL default '0',
  del integer NOT NULL default '0',
  name varchar(128) NOT NULL default '',
  email varchar(128) NOT NULL default '',
  firstname varchar(128) NOT NULL default '',
  surname varchar(128) NOT NULL default '',
  vcard text NOT NULL default ''
);

CREATE INDEX ix_contacts_user_id ON contacts(user_id);

-- --------------------------------------------------------

-- 
-- Table structure for table identities
-- 

CREATE TABLE identities (
  identity_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL default '0',
  del integer NOT NULL default '0',
  "default" integer NOT NULL default '0',
  name varchar(128) NOT NULL default '',
  organization varchar(128) NOT NULL default '',
  email varchar(128) NOT NULL default '',
  "reply-to" varchar(128) NOT NULL default '',
  bcc varchar(128) NOT NULL default '',
  signature text NOT NULL default ''
);

CREATE INDEX ix_identities_user_id ON identities(user_id);


-- --------------------------------------------------------

-- 
-- Table structure for table users
-- 

CREATE TABLE users (
  user_id integer NOT NULL PRIMARY KEY,
  username varchar(128) NOT NULL default '',
  mail_host varchar(128) NOT NULL default '',
  alias varchar(128) NOT NULL default '',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  last_login datetime NOT NULL default '0000-00-00 00:00:00',
  language varchar(5) NOT NULL default 'en',
  preferences text NOT NULL default ''
);
