ALTER SEQUENCE calendar_ids RENAME TO calendars_seq;
ALTER TABLE calendars ALTER COLUMN calendar_id SET DEFAULT nextval('calendars_seq'::text);

ALTER SEQUENCE event_ids RENAME TO events_seq;
ALTER TABLE events ALTER COLUMN event_id SET DEFAULT nextval('events_seq'::text);

ALTER SEQUENCE attachment_ids RENAME TO attachments_seq;
ALTER TABLE attachments ALTER COLUMN attachment_id SET DEFAULT nextval('attachments_seq'::text);
