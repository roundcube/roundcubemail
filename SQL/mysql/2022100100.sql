CREATE TABLE `uploads` (
 `upload_id` varchar(64) NOT NULL,
 `session_id` varchar(128) NOT NULL,
 `group` varchar(128) NOT NULL,
 `metadata` mediumtext NOT NULL,
 `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
 PRIMARY KEY (`upload_id`),
 INDEX `uploads_session_group_index` (`session_id`, `group`, `created`)
) ROW_FORMAT=DYNAMIC ENGINE=INNODB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
