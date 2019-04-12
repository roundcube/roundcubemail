-- add identifier for recurring instances and exceptions

ALTER TABLE events ADD instance character varying(16) NOT NULL;
ALTER TABLE events ADD isexception smallint NOT NULL DEFAULT '0';

-- extend alarms columns for multiple values

ALTER TABLE events ALTER COLUMN alarms TYPE text;

