-- Postgres database updates since version 0.7/0.8

ALTER TABLE events ADD sequence integer NOT NULL DEFAULT 0;
