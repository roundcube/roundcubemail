CREATE TABLE tmp_users (
  user_id integer NOT NULL PRIMARY KEY,
  username varchar(128) NOT NULL default '',
  mail_host varchar(128) NOT NULL default '',
  created datetime NOT NULL default '0000-00-00 00:00:00',
  last_login datetime DEFAULT NULL,
  failed_login datetime DEFAULT NULL,
  failed_login_counter integer DEFAULT NULL,
  language varchar(16),
  preferences text DEFAULT NULL
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
  preferences text DEFAULT NULL
);

INSERT INTO users (user_id, username, mail_host, created, last_login, failed_login, failed_login_counter, language, preferences)
    SELECT user_id, username, mail_host, created, last_login, failed_login, failed_login_counter, language, preferences FROM tmp_users;

CREATE UNIQUE INDEX ix_users_username ON users(username, mail_host);

DROP TABLE tmp_users;
