-- RoundCube Webmail update script for Postres databases
-- Updates from version 0.1-beta and older

ALTER TABLE "messages" DROP body;
ALTER TABLE "messages" ADD structure TEXT;
ALTER TABLE "messages" ADD UNIQUE (cache_key, uid);

ALTER TABLE "identities" ADD html_signature integer DEFAULT 0 NOT NULL;

