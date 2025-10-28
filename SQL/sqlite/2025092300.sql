ALTER TABLE `session` RENAME COLUMN `changed` TO `expires_at`;
ALTER TABLE `session` RENAME INDEX `ix_session_changed` TO `ix_session_expires_at`;
