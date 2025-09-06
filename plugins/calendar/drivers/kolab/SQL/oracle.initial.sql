/**
 * Roundcube Calendar Kolab backend
 *
 * @author Aleksander Machniak
 * @licence GNU AGPL
 **/

CREATE TABLE "kolab_alarms" (
    "alarm_id" varchar(255) NOT NULL PRIMARY KEY,
    "user_id" integer NOT NULL
        REFERENCES "users" ("user_id") ON DELETE CASCADE,
    "notifyat" timestamp DEFAULT NULL,
    "dismissed" smallint DEFAULT 0 NOT NULL
);

CREATE INDEX "kolab_alarms_user_id_idx" ON "kolab_alarms" ("user_id");


CREATE TABLE "itipinvitations" (
    "token" varchar(64) NOT NULL PRIMARY KEY,
    "event_uid" varchar(255) NOT NULL,
    "user_id" integer NOT NULL
        REFERENCES "users" ("user_id") ON DELETE CASCADE,
    "event" long NOT NULL,
    "expires" timestamp DEFAULT NULL,
    "cancelled" smallint DEFAULT 0 NOT NULL
);

CREATE INDEX "itipinvitations_user_id_idx" ON "itipinvitations" ("user_id", "event_uid");

INSERT INTO "system" ("name", "value") VALUES ('calendar-kolab-version', '2014041700');
