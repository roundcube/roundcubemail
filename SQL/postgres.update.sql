-- RoundCube Webmail update script for Postgres databases
-- Updates from version 0.1-stable to 0.1.1

ALTER TABLE "cache" ADD INDEX (user_id, cache_key);
