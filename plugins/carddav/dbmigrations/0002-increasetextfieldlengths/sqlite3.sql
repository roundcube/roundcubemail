PRAGMA foreign_keys=OFF;

CREATE TABLE IF NOT EXISTS TABLE_PREFIXcarddav_addressbooks_X (
	id           integer NOT NULL PRIMARY KEY,
	name         VARCHAR(64) NOT NULL,
	username     VARCHAR(255) NOT NULL,
	password     VARCHAR(255) NOT NULL,
	url          VARCHAR(255) NOT NULL,
	active       TINYINT UNSIGNED NOT NULL DEFAULT 1,
	user_id      integer NOT NULL,
	last_updated DATETIME NOT NULL DEFAULT 0,  -- time stamp of the last update of the local database
	refresh_time TIME NOT NULL DEFAULT '01:00:00', -- time span after that the local database will be refreshed
	sync_token   VARCHAR(255) NOT NULL DEFAULT '', -- sync-token the server sent us for the last sync
	authentication_scheme VARCHAR(64) NOT NULL DEFAULT "auto", -- the HTTP authentication scheme to use, auto will be overwritten

	presetname   VARCHAR(255),                  -- presetname

	use_categories TINYINT NOT NULL DEFAULT 0,

	-- not enforced by sqlite < 3.6.19
	FOREIGN KEY(user_id) REFERENCES TABLE_PREFIXusers(user_id) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO TABLE_PREFIXcarddav_addressbooks_X SELECT * FROM TABLE_PREFIXcarddav_addressbooks;

DROP TABLE TABLE_PREFIXcarddav_addressbooks;

ALTER TABLE TABLE_PREFIXcarddav_addressbooks_X RENAME TO TABLE_PREFIXcarddav_addressbooks;

PRAGMA foreign_key_check;

PRAGMA foreign_keys=ON;
