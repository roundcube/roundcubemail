-- RoundCube Webmail update script for MySQL databases

-- Updates from version 0.1-stable

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

-- Updates from version 0.2-beta (InnoDB required)

ALTER TABLE `cache`
    DROP `session_id`;

ALTER TABLE `session`
    ADD INDEX `changed_index` (`changed`);

ALTER TABLE `cache`
    ADD INDEX `created_index` (`created`);

ALTER TABLE `users`
    CHANGE `language` `language` varchar(5);

ALTER TABLE `cache` ENGINE=InnoDB;
ALTER TABLE `session` ENGINE=InnoDB;
ALTER TABLE `messages` ENGINE=InnoDB;
ALTER TABLE `users` ENGINE=InnoDB;
ALTER TABLE `contacts` ENGINE=InnoDB;
ALTER TABLE `identities` ENGINE=InnoDB;

-- Updates from version 0.3-stable

TRUNCATE `messages`;

ALTER TABLE `messages`
    ADD INDEX `index_index` (`user_id`, `cache_key`, `idx`);

ALTER TABLE `session` 
    CHANGE `vars` `vars` MEDIUMTEXT NOT NULL;

ALTER TABLE `contacts`
    ADD INDEX `user_contacts_index` (`user_id`,`email`);

-- Updates from version 0.3.1
-- WARNING: Make sure that all tables are using InnoDB engine!!!
--          If not, use: ALTER TABLE xxx ENGINE=InnoDB;

/* MySQL bug workaround: http://bugs.mysql.com/bug.php?id=46293 */
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

ALTER TABLE `messages` DROP FOREIGN KEY `user_id_fk_messages`;
ALTER TABLE `cache` DROP FOREIGN KEY `user_id_fk_cache`;
ALTER TABLE `contacts` DROP FOREIGN KEY `user_id_fk_contacts`;
ALTER TABLE `identities` DROP FOREIGN KEY `user_id_fk_identities`;

ALTER TABLE `messages` ADD CONSTRAINT `user_id_fk_messages` FOREIGN KEY (`user_id`)
 REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cache` ADD CONSTRAINT `user_id_fk_cache` FOREIGN KEY (`user_id`)
 REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `contacts` ADD CONSTRAINT `user_id_fk_contacts` FOREIGN KEY (`user_id`)
 REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `identities` ADD CONSTRAINT `user_id_fk_identities` FOREIGN KEY (`user_id`)
 REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `contacts` ALTER `name` SET DEFAULT '';
ALTER TABLE `contacts` ALTER `firstname` SET DEFAULT '';
ALTER TABLE `contacts` ALTER `surname` SET DEFAULT '';

ALTER TABLE `identities` ADD INDEX `user_identities_index` (`user_id`, `del`);
ALTER TABLE `identities` ADD `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' AFTER `user_id`;

CREATE TABLE `contactgroups` (
  `contactgroup_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  `del` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY(`contactgroup_id`),
  CONSTRAINT `user_id_fk_contactgroups` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `contactgroups_user_index` (`user_id`,`del`)
) /*!40000 ENGINE=INNODB */ /*!40101 CHARACTER SET utf8 COLLATE utf8_general_ci */;

CREATE TABLE `contactgroupmembers` (
  `contactgroup_id` int(10) UNSIGNED NOT NULL,
  `contact_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  PRIMARY KEY (`contactgroup_id`, `contact_id`),
  CONSTRAINT `contactgroup_id_fk_contactgroups` FOREIGN KEY (`contactgroup_id`)
    REFERENCES `contactgroups`(`contactgroup_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `contact_id_fk_contacts` FOREIGN KEY (`contact_id`)
    REFERENCES `contacts`(`contact_id`) ON DELETE CASCADE ON UPDATE CASCADE
) /*!40000 ENGINE=INNODB */;

/*!40014 SET FOREIGN_KEY_CHECKS=1 */;

-- Updates from version 0.4-beta

ALTER TABLE `users` CHANGE `last_login` `last_login` datetime DEFAULT NULL;
UPDATE `users` SET `last_login` = NULL WHERE `last_login` = '1000-01-01 00:00:00';
