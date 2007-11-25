-- RoundCube Webmail update script for Postres databases
-- Updates from version 0.1-beta and older

ALTER TABLE "messages" DROP body;
ALTER TABLE "messages" ADD structure TEXT;
ALTER TABLE "messages" ADD UNIQUE (user_id, cache_key, uid);

ALTER TABLE "identities" ADD html_signature INTEGER;
ALTER TABLE "identities" ALTER html_signature SET DEFAULT 0;
UPDATE identities SET html_signature = 0;
ALTER TABLE "identities" ALTER html_signature SET NOT NULL;

