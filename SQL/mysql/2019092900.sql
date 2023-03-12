ALTER TABLE `cache` CHANGE `cache_key` `cache_key` varchar(128) BINARY NOT NULL;
ALTER TABLE `cache_shared` CHANGE `cache_key` `cache_key` varchar(255) BINARY NOT NULL;
