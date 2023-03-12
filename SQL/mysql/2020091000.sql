CREATE TABLE `collected_addresses` (
 `address_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
 `name` varchar(255) NOT NULL DEFAULT '',
 `email` varchar(255) NOT NULL,
 `user_id` int(10) UNSIGNED NOT NULL,
 `type` int(10) UNSIGNED NOT NULL,
 PRIMARY KEY(`address_id`),
 CONSTRAINT `user_id_fk_collected_addresses` FOREIGN KEY (`user_id`)
   REFERENCES `users`(`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 UNIQUE INDEX `user_email_collected_addresses_index` (`user_id`, `type`, `email`)
) ROW_FORMAT=DYNAMIC ENGINE=INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
