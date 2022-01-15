CREATE TABLE responses (
  response_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL default '0',
  changed datetime NOT NULL default '0000-00-00 00:00:00',
  del tinyint NOT NULL default '0',
  name varchar(255) NOT NULL,
  data text NOT NULL,
  is_html tinyint NOT NULL default '0'
);

CREATE UNIQUE INDEX ix_responses_user_id ON responses(user_id, del);
