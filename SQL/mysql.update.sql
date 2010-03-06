-- RoundCube Webmail update script for MySQL databases
-- Updates from version 0.1-stable to 0.3.1

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

-- Updates from version 0.3-stable

TRUNCATE `messages`;

ALTER TABLE `messages`
    ADD INDEX `index_index` (`user_id`, `cache_key`, `idx`);

ALTER TABLE `session` 
    CHANGE `vars` `vars` MEDIUMTEXT NOT NULL;

ALTER TABLE `contacts`
    ADD INDEX `user_contacts_index` (`user_id`,`email`);

-- Updates from version 0.3.1

/* MySQL bug workaround: http://bugs.mysql.com/bug.php?id=46293 */
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

ALTER TABLE `messages` DROP FOREIGN KEY `user_id_fk_messages`;
ALTER TABLE `cache` DROP FOREIGN KEY `user_id_fk_cache`;
ALTER TABLE `contacts` DROP FOREIGN KEY `user_id_fk_contacts`;
ALTER TABLE `identities` DROP FOREIGN KEY `user_id_fk_identities`;

ALTER TABLE `messages` ADD CONSTRAINT `user_id_fk_messages` FOREIGN KEY (`user_id`)
 REFERENCES `users`(`user_id`);
ALTER TABLE `cache` ADD CONSTRAINT `user_id_fk_cache` FOREIGN KEY (`user_id`)
 REFERENCES `users`(`user_id`);
ALTER TABLE `contacts` ADD CONSTRAINT `user_id_fk_contacts` FOREIGN KEY (`user_id`)
 REFERENCES `users`(`user_id`);
ALTER TABLE `identities` ADD CONSTRAINT `user_id_fk_identities` FOREIGN KEY (`user_id`)
 REFERENCES `users`(`user_id`);

ALTER TABLE `contacts` ALTER `name` SET DEFAULT '';
ALTER TABLE `contacts` ALTER `firstname` SET DEFAULT '';
ALTER TABLE `contacts` ALTER `surname` SET DEFAULT '';

ALTER TABLE `identities` INDEX `user_identities_index` (`user_id`, `del`);

/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
