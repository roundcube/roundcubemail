-- RoundCube Webmail update script for MySQL databases
-- Updates from version 0.1-beta and 0.1-beta2

ALTER TABLE `messages`
  DROP `body`,
  DROP INDEX `cache_key`,
  ADD `structure` TEXT,
  ADD UNIQUE `uniqueness` (`user_id`, `cache_key`, `uid`);

ALTER TABLE `identities`
  ADD `html_signature` tinyint(1) default 0 NOT NULL;

-- Uncomment these lines if you're using MySQL 4.1 or higher
-- ALTER TABLE `users`
--  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci,
--  CHANGE `username` `username` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
--  CHANGE `alias` `alias` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
