-- RoundCube Webmail update script for MySQL databases
-- Updates from version 0.1-stable to 0.1.1

TRUNCATE TABLE `messages`;

ALTER TABLE `messages`
  DROP INDEX `idx`,
  DROP INDEX `uid`;

ALTER TABLE `cache`
  DROP INDEX `cache_key`,
  DROP INDEX `session_id`,
  ADD INDEX `user_cache_index` (`user_id`,`cache_key`);

ALTER TABLE `users`
    ADD INDEX `username_index` (`username`),
    ADD INDEX `alias_index` (`alias`);

-- Updates from version 0.1.1

ALTER TABLE `identities`
    MODIFY `signature` text, 
    MODIFY `bcc` varchar(128) NOT NULL DEFAULT '', 
    MODIFY `reply-to` varchar(128) NOT NULL DEFAULT '', 
    MODIFY `organization` varchar(128) NOT NULL DEFAULT '',
    MODIFY `name` varchar(128) NOT NULL, 
    MODIFY `email` varchar(128) NOT NULL; 

-- Updates from version 0.2-alpha

ALTER TABLE `messages`
    ADD INDEX `created_index` (`created`);

-- Updates from version 0.2-beta (InnoDB only)

ALTER TABLE `cache`
    DROP `session_id`;
    
ALTER TABLE `session`
    ADD INDEX `changed_index` (`changed`);

ALTER TABLE `cache`
    ADD INDEX `created_index` (`created`);

ALTER TABLE `users`
    CHANGE `language` `language` varchar(5);
