CREATE TABLE collected_addresses (
  address_id integer NOT NULL PRIMARY KEY,
  user_id integer NOT NULL,
  changed datetime NOT NULL default '0000-00-00 00:00:00',
  name varchar(255) NOT NULL default '',
  email varchar(255) NOT NULL,
  "type" integer NOT NULL
);

CREATE UNIQUE INDEX ix_collected_addresses_user_id ON collected_addresses(user_id, "type", email);
