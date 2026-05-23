ALTER TABLE `session` CHANGE COLUMN `changed` `expires_at` datetime NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE `session` DROP INDEX `changed_index`;
ALTER TABLE `session` ADD INDEX `expires_at_index` (`expires_at`);
UPDATE session SET expires_at = ADDTIME(expires_at, '00:10:00');
