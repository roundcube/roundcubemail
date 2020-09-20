CREATE TABLE "collected_addresses" (
    "address_id" integer PRIMARY KEY,
    "user_id" integer NOT NULL
        REFERENCES "users" ("user_id") ON DELETE CASCADE,
    "changed" timestamp with time zone DEFAULT current_timestamp NOT NULL,
    "name" varchar(255) DEFAULT NULL,
    "email" varchar(255) DEFAULT NULL,
    "type" integer NOT NULL
);

CREATE UNIQUE INDEX "collected_addresses_user_id_idx" ON "collected_addresses" ("user_id", "type", "email");

CREATE SEQUENCE "collected_addresses_seq"
    START WITH 1 INCREMENT BY 1 NOMAXVALUE;

CREATE TRIGGER "collected_addresses_seq_trig"
BEFORE INSERT ON "collected_addresses" FOR EACH ROW
BEGIN
    :NEW."address_id" := "collected_addresses_seq".nextval;
END;
/
