CREATE TABLE "uploads" (
    upload_id varchar(64) PRIMARY KEY,
    session_id varchar(128) NOT NULL,
    "group" varchar(128) NOT NULL,
    metadata text NOT NULL,
    created timestamp with time zone DEFAULT now() NOT NULL
);

CREATE INDEX uploads_session_id_idx ON uploads (session_id, "group", created);
