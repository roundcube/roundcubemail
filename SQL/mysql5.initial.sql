-- RoundCube Webmail initial database structure
-- Version 0.1-beta2
-- 

-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS=0;


-- Table structure for table `session`

CREATE TABLE `session` (
 `sess_id` varchar(40) NOT NULL,
 `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `changed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `ip` varchar(15) NOT NULL,
 `vars` text NOT NULL,
 PRIMARY KEY(`sess_id`)
) TYPE=MYISAM CHARACTER SET ascii COLLATE ascii_general_ci;


-- Table structure for table `users`

CREATE TABLE `users` (
 `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 `username` varchar(128) NOT NULL,
 `mail_host` varchar(128) NOT NULL,
 `alias` varchar(128) NOT NULL,
 `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `language` varchar(5) NOT NULL DEFAULT 'en',
 `preferences` text NOT NULL,
 PRIMARY KEY(`user_id`)
) TYPE=MYISAM CHARACTER SET ascii COLLATE ascii_general_ci;


-- Table structure for table `messages`

CREATE TABLE `messages` (
 `message_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
 `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
 `del` tinyint(1) NOT NULL DEFAULT '0',
 `cache_key` varchar(128) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
 `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `idx` int(11) UNSIGNED NOT NULL DEFAULT '0',
 `uid` int(11) UNSIGNED NOT NULL DEFAULT '0',
 `subject` varchar(255) NOT NULL,
 `from` varchar(255) NOT NULL,
 `to` varchar(255) NOT NULL,
 `cc` varchar(255) NOT NULL,
 `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `size` int(11) UNSIGNED NOT NULL DEFAULT '0',
 `headers` text NOT NULL,
 `structure` text,
 PRIMARY KEY(`message_id`),
 INDEX `idx`(`idx`),
 INDEX `uid`(`uid`),
 UNIQUE `uniqueness` (`user_id`, `cache_key`, `uid`),
 CONSTRAINT `User_ID_FK_messages` FOREIGN KEY (`user_id`)
   REFERENCES `users`(`user_id`)
     ON DELETE CASCADE
     ON UPDATE CASCADE
) TYPE=MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;


-- Table structure for table `cache`

CREATE TABLE `cache` (
 `cache_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 `session_id` varchar(40) CHARACTER SET ascii COLLATE ascii_general_ci,
 `cache_key` varchar(128) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
 `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `data` longtext NOT NULL,
 `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
 PRIMARY KEY(`cache_id`),
 INDEX `cache_key`(`cache_key`),
 INDEX `session_id`(`session_id`),
 CONSTRAINT `User_ID_FK_cache` FOREIGN KEY (`user_id`)
   REFERENCES `users`(`user_id`)
     ON DELETE CASCADE
     ON UPDATE CASCADE
) TYPE=MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;


-- Table structure for table `contacts`

CREATE TABLE `contacts` (
 `contact_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 `changed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `del` tinyint(1) NOT NULL DEFAULT '0',
 `name` varchar(128) NOT NULL,
 `email` varchar(128) NOT NULL,
 `firstname` varchar(128) NOT NULL,
 `surname` varchar(128) NOT NULL,
 `vcard` text NOT NULL,
 `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
 PRIMARY KEY(`contact_id`),
 CONSTRAINT `User_ID_FK_contacts` FOREIGN KEY (`user_id`)
   REFERENCES `users`(`user_id`)
     ON DELETE CASCADE
     ON UPDATE CASCADE
) TYPE=MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;


-- Table structure for table `identities`

CREATE TABLE `identities` (
 `identity_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 `del` tinyint(1) NOT NULL DEFAULT '0',
 `standard` tinyint(1) NOT NULL DEFAULT '0',
 `name` varchar(128) NOT NULL,
 `organization` varchar(128) NOT NULL,
 `email` varchar(128) NOT NULL,
 `reply-to` varchar(128) NOT NULL,
 `bcc` varchar(128) NOT NULL,
 `signature` text NOT NULL,
 `html_signature` tinyint(1) NOT NULL DEFAULT '0',
 `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
 PRIMARY KEY(`identity_id`),
 CONSTRAINT `User_ID_FK_identities` FOREIGN KEY (`user_id`)
   REFERENCES `users`(`user_id`)
     ON DELETE CASCADE
     ON UPDATE CASCADE
) TYPE=MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;


SET FOREIGN_KEY_CHECKS=1;
