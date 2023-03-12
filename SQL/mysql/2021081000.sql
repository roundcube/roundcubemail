CREATE TABLE `responses` (
 `response_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
 `user_id` int(10) UNSIGNED NOT NULL,
 `name` varchar(255) NOT NULL,
 `data` longtext NOT NULL,
 `is_html` tinyint(1) NOT NULL DEFAULT '0',
 `changed` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
 `del` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`response_id`),
 CONSTRAINT `user_id_fk_responses` FOREIGN KEY (`user_id`)
   REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 INDEX `user_responses_index` (`user_id`, `del`)
) ROW_FORMAT=DYNAMIC ENGINE=INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
