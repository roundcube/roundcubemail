ALTER TABLE `session` RENAME COLUMN `changed` TO `expires_at`;
DROP INDEX ix_session_changed;
CREATE INDEX ix_session_expires_at ON session ("expires_at");
UPDATE session SET expires_at = DATETIME(expires_at, '+10 minutes');
