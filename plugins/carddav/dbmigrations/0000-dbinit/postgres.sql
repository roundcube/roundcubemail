CREATE SEQUENCE TABLE_PREFIXcarddav_addressbooks_seq
    INCREMENT BY 1
    NO MAXVALUE
		MINVALUE 1
    CACHE 1;

-- table to store the configured address books

CREATE TABLE TABLE_PREFIXcarddav_addressbooks (
	id integer DEFAULT nextval('TABLE_PREFIXcarddav_addressbooks_seq'::text) PRIMARY KEY,
	name VARCHAR(64) NOT NULL,
	username VARCHAR(64) NOT NULL,
	password VARCHAR(255) NOT NULL,
	url VARCHAR(255) NOT NULL,
	active SMALLINT NOT NULL DEFAULT 1,
	user_id integer NOT NULL REFERENCES TABLE_PREFIXusers (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
	last_updated TIMESTAMP NOT NULL DEFAULT '-infinity', -- time stamp of the last update of the local database
	refresh_time INTERVAL NOT NULL DEFAULT '01:00:00', -- time span after that the local database will be refreshed, default 1h
	sync_token VARCHAR(255) NOT NULL DEFAULT '', -- sync-token the server sent us for the last sync
	authentication_scheme VARCHAR(64) NOT NULL DEFAULT 'auto', -- the HTTP authentication scheme to use, auto will be overwritten

	presetname VARCHAR(64) -- presetname
);

CREATE SEQUENCE TABLE_PREFIXcarddav_contacts_seq
    INCREMENT BY 1
    NO MAXVALUE
		MINVALUE 1
    CACHE 1;

CREATE TABLE TABLE_PREFIXcarddav_contacts (
	id integer DEFAULT nextval('TABLE_PREFIXcarddav_contacts_seq'::text) PRIMARY KEY,
	abook_id integer NOT NULL REFERENCES TABLE_PREFIXcarddav_addressbooks (id) ON DELETE CASCADE ON UPDATE CASCADE,
	name VARCHAR(255)     NOT NULL, -- display name
	email VARCHAR(255), -- ", " separated list of mail addresses
	firstname VARCHAR(255),
	surname VARCHAR(255),
	organization VARCHAR(255),
	showas VARCHAR(32) NOT NULL DEFAULT '', -- special display type (e.g., as a company)
	vcard text NOT NULL,        -- complete vcard
	etag VARCHAR(255) NOT NULL, -- entity tag, can be used to check if card changed on server
	uri  VARCHAR(255) NOT NULL,  -- path of the card on the server
	cuid VARCHAR(255) NOT NULL,  -- unique identifier of the card within the collection

	UNIQUE(uri,abook_id),
	UNIQUE(cuid,abook_id)
);

CREATE INDEX TABLE_PREFIXcarddav_contacts_abook_id_idx ON TABLE_PREFIXcarddav_contacts(abook_id);

CREATE SEQUENCE TABLE_PREFIXcarddav_xsubtypes_seq
    INCREMENT BY 1
    NO MAXVALUE
		MINVALUE 1
    CACHE 1;

CREATE TABLE TABLE_PREFIXcarddav_xsubtypes (
	id integer DEFAULT nextval('TABLE_PREFIXcarddav_xsubtypes_seq'::text) PRIMARY KEY,
	typename VARCHAR(128) NOT NULL,  -- name of the type
	subtype  VARCHAR(128) NOT NULL,  -- name of the subtype
	abook_id integer NOT NULL REFERENCES TABLE_PREFIXcarddav_addressbooks (id) ON DELETE CASCADE ON UPDATE CASCADE,
	UNIQUE (typename,subtype,abook_id) 
);

CREATE SEQUENCE TABLE_PREFIXcarddav_groups_seq
    INCREMENT BY 1
    NO MAXVALUE
		MINVALUE 1
    CACHE 1;

CREATE TABLE TABLE_PREFIXcarddav_groups (
	id integer DEFAULT nextval('TABLE_PREFIXcarddav_groups_seq'::text) PRIMARY KEY,
	abook_id integer NOT NULL REFERENCES TABLE_PREFIXcarddav_addressbooks (id) ON DELETE CASCADE ON UPDATE CASCADE,
	name VARCHAR(255) NOT NULL, -- display name
	vcard TEXT NOT NULL,        -- complete vcard
	etag VARCHAR(255) NOT NULL, -- entity tag, can be used to check if card changed on server
	uri  VARCHAR(255) NOT NULL, -- path of the card on the server
	cuid VARCHAR(255) NOT NULL, -- unique identifier of the card within the collection
	
	UNIQUE(uri,abook_id),
	UNIQUE(cuid,abook_id)
);

CREATE TABLE IF NOT EXISTS TABLE_PREFIXcarddav_group_user (
	group_id   integer NOT NULL,
	contact_id integer NOT NULL,

	PRIMARY KEY(group_id,contact_id),
	FOREIGN KEY(group_id) REFERENCES TABLE_PREFIXcarddav_groups(id) ON DELETE CASCADE ON UPDATE CASCADE,
	FOREIGN KEY(contact_id) REFERENCES TABLE_PREFIXcarddav_contacts(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE SEQUENCE TABLE_PREFIXcarddav_migrations_seq
	INCREMENT BY 1
	NO MAXVALUE
	MINVALUE 1
	CACHE 1;

CREATE TABLE TABLE_PREFIXcarddav_migrations (
	ID integer DEFAULT nextval('TABLE_PREFIXcarddav_migrations_seq'::text) PRIMARY KEY,
	filename VARCHAR(64) NOT NULL,
	processed_at TIMESTAMP NOT NULL DEFAULT now(),

	UNIQUE(filename)
);
