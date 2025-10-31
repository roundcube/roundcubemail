ALTER TABLE `session` RENAME COLUMN `changed` TO `expires_at`;
ALTER TABLE `session` RENAME INDEX `session_changed_idx` TO `session_expires_at_idx`;
