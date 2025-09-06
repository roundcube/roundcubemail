-- Postgres database updates since version 1.0

ALTER TABLE events ADD status character varying(32) NOT NULL;
