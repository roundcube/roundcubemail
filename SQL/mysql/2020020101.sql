-- Dropping foreign keys and changing table format is needed for some versions of MySQL (#7277)
ALTER TABLE `cache` DROP FOREIGN KEY `user_id_fk_cache`;
ALTER TABLE `cache_index` DROP FOREIGN KEY `user_id_fk_cache_index`;
ALTER TABLE `cache_thread` DROP FOREIGN KEY `user_id_fk_cache_thread`;
ALTER TABLE `cache_messages` DROP FOREIGN KEY `user_id_fk_cache_messages`;
ALTER TABLE `contactgroups` DROP FOREIGN KEY `user_id_fk_contactgroups`;
ALTER TABLE `contacts` DROP FOREIGN KEY `user_id_fk_contacts`;
ALTER TABLE `dictionary` DROP FOREIGN KEY `user_id_fk_dictionary`;
ALTER TABLE `filestore` DROP FOREIGN KEY `user_id_fk_filestore`;
ALTER TABLE `identities` DROP FOREIGN KEY `user_id_fk_identities`;
ALTER TABLE `searches` DROP FOREIGN KEY `user_id_fk_searches`;

ALTER TABLE `session` ROW_FORMAT=DYNAMIC;
ALTER TABLE `users` ROW_FORMAT=DYNAMIC;
ALTER TABLE `cache` ROW_FORMAT=DYNAMIC;
ALTER TABLE `cache_shared` ROW_FORMAT=DYNAMIC;
ALTER TABLE `cache_index` ROW_FORMAT=DYNAMIC;
ALTER TABLE `cache_thread` ROW_FORMAT=DYNAMIC;
ALTER TABLE `cache_messages` ROW_FORMAT=DYNAMIC;
ALTER TABLE `contacts` ROW_FORMAT=DYNAMIC;
ALTER TABLE `contactgroups` ROW_FORMAT=DYNAMIC;
ALTER TABLE `contactgroupmembers` ROW_FORMAT=DYNAMIC;
ALTER TABLE `identities` ROW_FORMAT=DYNAMIC;
ALTER TABLE `dictionary` ROW_FORMAT=DYNAMIC;
ALTER TABLE `searches` ROW_FORMAT=DYNAMIC;
ALTER TABLE `filestore` ROW_FORMAT=DYNAMIC;
ALTER TABLE `system` ROW_FORMAT=DYNAMIC;

ALTER TABLE `session` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `cache` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `cache_shared` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `cache_index` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `cache_thread` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `cache_messages` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `contacts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `contactgroups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `identities` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `dictionary` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `searches` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `filestore` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `system` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `users` CHANGE `username` `username` varchar(128) BINARY NOT NULL;
ALTER TABLE `cache` CHANGE `cache_key` `cache_key` varchar(128) BINARY NOT NULL;
ALTER TABLE `cache_shared` CHANGE `cache_key` `cache_key` varchar(255) BINARY NOT NULL;
ALTER TABLE `cache_index` CHANGE `mailbox` `mailbox` varchar(255) BINARY NOT NULL;
ALTER TABLE `cache_thread` CHANGE `mailbox` `mailbox` varchar(255) BINARY NOT NULL;
ALTER TABLE `cache_messages` CHANGE `mailbox` `mailbox` varchar(255) BINARY NOT NULL;

ALTER TABLE `cache`
  ADD CONSTRAINT `user_id_fk_cache` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cache_index`
  ADD CONSTRAINT `user_id_fk_cache_index` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cache_thread`
  ADD CONSTRAINT `user_id_fk_cache_thread` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `cache_messages`
  ADD CONSTRAINT `user_id_fk_cache_messages` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `contactgroups`
  ADD CONSTRAINT `user_id_fk_contactgroups` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `contacts`
  ADD CONSTRAINT `user_id_fk_contacts` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `dictionary`
  ADD CONSTRAINT `user_id_fk_dictionary` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `filestore`
  ADD CONSTRAINT `user_id_fk_filestore` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `identities`
  ADD CONSTRAINT `user_id_fk_identities` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `searches`
  ADD CONSTRAINT `user_id_fk_searches` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
