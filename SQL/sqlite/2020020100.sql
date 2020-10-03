CREATE TABLE tmp_users (
  user_id integer NOT NULL PRIMARY KEY,
  username varchar(128) NOT NULL default '',
  mail_host varchar(128) NOT NULL default '',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  last_login datetime DEFAULT NULL,
  failed_login datetime DEFAULT NULL,
  failed_login_counter integer DEFAULT NULL,
  language varchar(16),
  preferences text NOT NULL default ''
);

INSERT INTO tmp_users (user_id, username, mail_host, created, last_login, failed_login, failed_login_counter, language, preferences)
    SELECT user_id, username, mail_host, created, last_login, failed_login, failed_login_counter, language, preferences FROM users;

DROP TABLE users;

CREATE TABLE users (
  user_id integer NOT NULL PRIMARY KEY,
  username varchar(128) NOT NULL default '',
  mail_host varchar(128) NOT NULL default '',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  last_login datetime DEFAULT NULL,
  failed_login datetime DEFAULT NULL,
  failed_login_counter integer DEFAULT NULL,
  language varchar(16),
  preferences text NOT NULL default ''
);

INSERT INTO users (user_id, username, mail_host, created, last_login, failed_login, failed_login_counter, language, preferences)
    SELECT user_id, username, mail_host, created, last_login, failed_login, failed_login_counter, language, preferences FROM tmp_users;

CREATE UNIQUE INDEX ix_users_username ON users(username, mail_host);

DROP TABLE tmp_users;

CREATE TABLE tmp_dictionary (
    user_id integer DEFAULT NULL,
   language varchar(16) NOT NULL,
    data text NOT NULL
);

INSERT INTO tmp_dictionary (user_id, language, data) SELECT user_id, language, data FROM dictionary;

DROP TABLE dictionary;

CREATE TABLE dictionary (
    user_id integer DEFAULT NULL,
   language varchar(16) NOT NULL,
    data text NOT NULL
);

INSERT INTO dictionary (user_id, language, data) SELECT user_id, language, data FROM tmp_dictionary;

CREATE UNIQUE INDEX ix_dictionary_user_language ON dictionary (user_id, language);

DROP TABLE tmp_dictionary;
