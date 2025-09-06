DROP TABLE IF EXISTS `kolab_alarms`;

CREATE TABLE `kolab_alarms` (
  `event_id` VARCHAR(255) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `notifyat` DATETIME DEFAULT NULL,
  `dismissed` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY(`event_id`),
  CONSTRAINT `fk_kolab_alarms_user_id` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) /*!40000 ENGINE=INNODB */;
