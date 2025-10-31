ALTER TABLE `session` RENAME COLUMN `changed` TO `expires_at`;
ALTER TABLE `session` RENAME INDEX `changed_index` TO `expires_at_index`;
