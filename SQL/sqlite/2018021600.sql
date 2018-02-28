CREATE TABLE filestore (
    file_id integer PRIMARY KEY,
    user_id integer NOT NULL,
    filename varchar(128) NOT NULL,
    mtime integer NOT NULL,
    data text NOT NULL
);

CREATE UNIQUE INDEX ix_filestore_user_id ON filestore(user_id, filename);
