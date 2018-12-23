CREATE TABLE tmp_filestore (
    file_id integer PRIMARY KEY,
    user_id integer NOT NULL,
    filename varchar(128) NOT NULL,
    mtime integer NOT NULL,
    data text NOT NULL
);

INSERT INTO tmp_filestore (file_id, user_id, filename, mtime, data)
    SELECT file_id, user_id, filename, mtime, data FROM filestore;

DROP TABLE filestore;

CREATE TABLE filestore (
    file_id integer NOT NULL PRIMARY KEY,
    user_id integer NOT NULL,
    context varchar(32) NOT NULL,
    filename varchar(128) NOT NULL,
    mtime integer NOT NULL,
    data text NOT NULL
);

INSERT INTO filestore (file_id, user_id, filename, mtime, data, context)
    SELECT file_id, user_id, filename, mtime, data, 'enigma' FROM tmp_filestore;

CREATE UNIQUE INDEX ix_filestore_user_id ON filestore(user_id, context, filename);

DROP TABLE tmp_filestore;

