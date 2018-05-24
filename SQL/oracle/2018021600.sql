CREATE TABLE "filestore" (
    "file_id" integer PRIMARY KEY,
    "user_id" integer NOT NULL
        REFERENCES "users" ("user_id") ON DELETE CASCADE ON UPDATE CASCADE,
    "filename" varchar(128) NOT NULL,
    "mtime" integer NOT NULL,
    "data" long,
    CONSTRAINT "filestore_user_id_key" UNIQUE ("user_id", "filename")
);

CREATE SEQUENCE "filestore_seq"
    START WITH 1 INCREMENT BY 1 NOMAXVALUE;

CREATE TRIGGER "filestore_seq_trig"
BEFORE INSERT ON "filestore" FOR EACH ROW
BEGIN
    :NEW."user_id" := "filestore_seq".nextval;
END;
/
