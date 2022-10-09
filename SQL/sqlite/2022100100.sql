CREATE TABLE uploads (
    upload_id varchar(64) NOT NULL PRIMARY KEY,
    session_id varchar(128) NOT NULL,
    "group" varchar(128) NOT NULL,
    metadata text NOT NULL,
    created datetime NOT NULL default '0000-00-00 00:00:00'
);

CREATE INDEX ix_uploads_session_id ON uploads(session_id, "group", created);
