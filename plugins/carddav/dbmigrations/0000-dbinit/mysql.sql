-- table to store the configured address books
CREATE TABLE IF NOT EXISTS TABLE_PREFIXcarddav_addressbooks (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(64) NOT NULL,
	username VARCHAR(64) NOT NULL,
	password VARCHAR(255) NOT NULL,
	url VARCHAR(255) NOT NULL,
	active TINYINT UNSIGNED NOT NULL DEFAULT 1,
	user_id INT(10) UNSIGNED NOT NULL,
	last_updated TIMESTAMP NOT NULL DEFAULT '2000-01-01 00:00:01', -- time stamp of the last update of the local database
	refresh_time TIME NOT NULL DEFAULT '01:00:00', -- time span after that the local database will be refreshed, default 1h
	sync_token VARCHAR(255) NOT NULL DEFAULT '', -- sync-token the server sent us for the last sync
	authentication_scheme VARCHAR(64) NOT NULL DEFAULT "auto", -- the HTTP authentication scheme to use, auto will be overwritten

	presetname   VARCHAR(64), -- presetname

	PRIMARY KEY(id),
	FOREIGN KEY (user_id) REFERENCES TABLE_PREFIXusers(user_id) ON DELETE CASCADE ON UPDATE CASCADE
) CHARACTER SET utf8 COLLATE utf8_unicode_ci /*!40000 ENGINE=INNODB */;

CREATE TABLE IF NOT EXISTS TABLE_PREFIXcarddav_contacts (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	abook_id INT UNSIGNED NOT NULL,
	name VARCHAR(255)     NOT NULL, -- display name
	email VARCHAR(255), -- ", " separated list of mail addresses
	firstname VARCHAR(255),
	surname VARCHAR(255),
	organization VARCHAR(255),
	showas VARCHAR(32) NOT NULL DEFAULT '', -- special display type (e.g., as a company)
	vcard LONGTEXT NOT NULL, -- complete vcard
	etag VARCHAR(255) NOT NULL, -- entity tag, can be used to check if card changed on server
	uri  VARCHAR(255) NOT NULL, -- path of the card on the server
	cuid VARCHAR(255) NOT NULL, -- unique identifier of the card within the collection

	PRIMARY KEY(id),
	INDEX (abook_id),
	UNIQUE INDEX(uri,abook_id),
	UNIQUE INDEX(cuid,abook_id),
	FOREIGN KEY (abook_id) REFERENCES TABLE_PREFIXcarddav_addressbooks(id) ON DELETE CASCADE ON UPDATE CASCADE
) CHARACTER SET utf8 COLLATE utf8_unicode_ci /*!40000 ENGINE=INNODB */;

CREATE TABLE IF NOT EXISTS TABLE_PREFIXcarddav_xsubtypes (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	typename VARCHAR(128) NOT NULL,  -- name of the type
	subtype  VARCHAR(128) NOT NULL,  -- name of the subtype
	abook_id INT UNSIGNED NOT NULL,
	PRIMARY KEY(id),
	UNIQUE INDEX(typename,subtype,abook_id),
	FOREIGN KEY (abook_id) REFERENCES TABLE_PREFIXcarddav_addressbooks(id) ON DELETE CASCADE ON UPDATE CASCADE
) CHARACTER SET utf8 COLLATE utf8_unicode_ci /*!40000 ENGINE=INNODB */;

CREATE TABLE IF NOT EXISTS TABLE_PREFIXcarddav_groups (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	abook_id INT UNSIGNED NOT NULL,
	name VARCHAR(255) NOT NULL, -- display name
	vcard TEXT NOT NULL,        -- complete vcard
	etag VARCHAR(255) NOT NULL, -- entity tag, can be used to check if card changed on server
	uri  VARCHAR(255) NOT NULL, -- path of the card on the server
	cuid VARCHAR(255) NOT NULL, -- unique identifier of the card within the collection
	
	PRIMARY KEY(id),
	UNIQUE(uri,abook_id),
	UNIQUE(cuid,abook_id),

	FOREIGN KEY (abook_id) REFERENCES TABLE_PREFIXcarddav_addressbooks(id) ON DELETE CASCADE ON UPDATE CASCADE
) CHARACTER SET utf8 COLLATE utf8_unicode_ci /*!40000 ENGINE=INNODB */;

CREATE TABLE IF NOT EXISTS TABLE_PREFIXcarddav_group_user (
	group_id   INT UNSIGNED NOT NULL,
	contact_id INT UNSIGNED NOT NULL,

	PRIMARY KEY(group_id,contact_id),
	FOREIGN KEY(group_id) REFERENCES TABLE_PREFIXcarddav_groups(id) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY(contact_id) REFERENCES TABLE_PREFIXcarddav_contacts(id) ON DELETE CASCADE ON UPDATE CASCADE
) CHARACTER SET utf8 COLLATE utf8_unicode_ci /*!40000 ENGINE=INNODB */;

CREATE TABLE TABLE_PREFIXcarddav_migrations (
	`ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`filename` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`processed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	UNIQUE (
		`filename`
	)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;
