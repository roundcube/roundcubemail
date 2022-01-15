CREATE TABLE "responses" (
    "response_id" integer PRIMARY KEY,
    "user_id" integer NOT NULL
        REFERENCES "users" ("user_id") ON DELETE CASCADE,
    "changed" timestamp with time zone DEFAULT current_timestamp NOT NULL,
    "del" smallint DEFAULT 0 NOT NULL,
    "name" varchar(128) NOT NULL,
    "data" long NOT NULL,
    "is_html" smallint DEFAULT 0 NOT NULL
);

CREATE INDEX "responses_user_id_idx" ON "responses" ("user_id", "del");

CREATE SEQUENCE "responses_seq"
    START WITH 1 INCREMENT BY 1 NOMAXVALUE;

CREATE TRIGGER "responses_seq_trig"
BEFORE INSERT ON "responses" FOR EACH ROW
BEGIN
    :NEW."response_id" := "response_seq".nextval;
END;
/
