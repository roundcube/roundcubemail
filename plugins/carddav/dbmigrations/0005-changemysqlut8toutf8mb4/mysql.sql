-- table to store the configured address books
ALTER TABLE TABLE_PREFIXcarddav_addressbooks DROP INDEX `user_id`, ADD UNIQUE `user_id` (`user_id`, `presetname`(191)) USING BTREE;
ALTER TABLE TABLE_PREFIXcarddav_addressbooks CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_addressbooks CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_addressbooks CHANGE `username` `username` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_addressbooks CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_addressbooks CHANGE `url` `url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_addressbooks CHANGE `sync_token` `sync_token` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_addressbooks CHANGE `authentication_scheme` `authentication_scheme` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_addressbooks CHANGE `presetname` `presetname` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE TABLE_PREFIXcarddav_contacts DROP INDEX `uri`, ADD UNIQUE `uri` (`uri`(191), `abook_id`) USING BTREE;
ALTER TABLE TABLE_PREFIXcarddav_contacts DROP INDEX `cuid`, ADD UNIQUE `cuid` (`cuid`(191), `abook_id`) USING BTREE;
ALTER TABLE TABLE_PREFIXcarddav_contacts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_contacts CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_contacts CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_contacts CHANGE `firstname` `firstname` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_contacts CHANGE `surname` `surname` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_contacts CHANGE `organization` `organization` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_contacts CHANGE `showas` `showas` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_contacts CHANGE `vcard` `vcard` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_contacts CHANGE `etag` `etag` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_contacts CHANGE `uri` `uri` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_contacts CHANGE `cuid` `cuid` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE TABLE_PREFIXcarddav_groups DROP INDEX `uri`, ADD UNIQUE `uri` (`uri`(191), `abook_id`) USING BTREE;
ALTER TABLE TABLE_PREFIXcarddav_groups DROP INDEX `cuid`, ADD UNIQUE `cuid` (`cuid`(191), `abook_id`) USING BTREE;
ALTER TABLE TABLE_PREFIXcarddav_groups CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_groups CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_groups CHANGE `etag` `etag` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_groups CHANGE `uri` `uri` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_groups CHANGE `cuid` `cuid` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE TABLE_PREFIXcarddav_group_user CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE TABLE_PREFIXcarddav_migrations CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_migrations CHANGE `filename` `filename` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE TABLE_PREFIXcarddav_xsubtypes CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_xsubtypes CHANGE `typename` `typename` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE TABLE_PREFIXcarddav_xsubtypes CHANGE `subtype` `subtype` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


REPAIR TABLE TABLE_PREFIXcarddav_addressbooks;
REPAIR TABLE TABLE_PREFIXcarddav_contacts;
REPAIR TABLE TABLE_PREFIXcarddav_groups;
REPAIR TABLE TABLE_PREFIXcarddav_group_user;
REPAIR TABLE TABLE_PREFIXcarddav_migrations;
REPAIR TABLE TABLE_PREFIXcarddav_xsubtypes;

OPTIMIZE TABLE TABLE_PREFIXcarddav_addressbooks;
OPTIMIZE TABLE TABLE_PREFIXcarddav_contacts;
OPTIMIZE TABLE TABLE_PREFIXcarddav_groups;
OPTIMIZE TABLE TABLE_PREFIXcarddav_group_user;
OPTIMIZE TABLE TABLE_PREFIXcarddav_migrations;
OPTIMIZE TABLE TABLE_PREFIXcarddav_xsubtypes;
