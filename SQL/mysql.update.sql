-- RoundCube Webmail update script for MySQL databases
-- Updates from version 0.1-beta and 0.1-beta2

ALTER TABLE `messages`
  DROP `body`,
  DROP INDEX `cache_key`,
  ADD `structure` TEXT,
  ADD UNIQUE `uniqueness` (`cache_key`, `uid`);

ALTER TABLE `identities`
  ADD `html_signature` tinyint(1) default 0 NOT NULL;
