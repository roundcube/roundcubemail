/**
 * Roundcube Calendar Kolab backend
 *
 * @version @package_version@
 * @author Thomas Bruederli
 * @licence GNU AGPL
 **/

CREATE TABLE IF NOT EXISTS `kolab_alarms` (
  `alarm_id` VARCHAR(255) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `notifyat` DATETIME DEFAULT NULL,
  `dismissed` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY(`alarm_id`,`user_id`),
  CONSTRAINT `fk_kolab_alarms_user_id` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) /*!40000 ENGINE=INNODB */;

CREATE TABLE IF NOT EXISTS `itipinvitations` (
  `token` VARCHAR(64) NOT NULL,
  `event_uid` VARCHAR(255) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `event` TEXT NOT NULL,
  `expires` DATETIME DEFAULT NULL,
  `cancelled` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY(`token`),
  INDEX `uid_idx` (`event_uid`,`user_id`),
  CONSTRAINT `fk_itipinvitations_user_id` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) /*!40000 ENGINE=INNODB */ /*!40101 CHARACTER SET utf8 COLLATE utf8_general_ci */;

REPLACE INTO system (name, value) VALUES ('calendar-kolab-version', '2014041700');
