CREATE SEQUENCE "filestore_seq"
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

CREATE TABLE "filestore" (
    file_id integer DEFAULT nextval('filestore_seq'::text) PRIMARY KEY,
    user_id integer NOT NULL
        REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
    filename varchar(128) NOT NULL,
    mtime integer NOT NULL,
    data text NOT NULL,
    CONSTRAINT filestore_user_id_filename UNIQUE (user_id, filename)
);
