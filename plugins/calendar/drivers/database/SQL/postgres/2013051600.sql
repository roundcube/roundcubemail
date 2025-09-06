-- Postgres database updates since version 0.9-beta

ALTER TABLE events ADD url character varying(255) NOT NULL;
