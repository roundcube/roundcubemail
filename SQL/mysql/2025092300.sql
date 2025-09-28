ALTER TABLE `session` RENAME COLUMN `changed` TO `expires_at`;
ALTER TABLE `session` RENAME INDEX `changed_index` TO `expires_at_index`;
UPDATE sessions SET expires_at = ADDTIME(expires_at, '00:10:00');
