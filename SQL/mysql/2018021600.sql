CREATE TABLE `filestore` (
    `file_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` int(10) UNSIGNED NOT NULL,
    `filename` varchar(128) NOT NULL,
    `mtime` int(10) NOT NULL,
    `data` longtext NOT NULL,
    PRIMARY KEY (`file_id`),
    CONSTRAINT `user_id_fk_filestore` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE `uniqueness` (`user_id`, `filename`)
);
