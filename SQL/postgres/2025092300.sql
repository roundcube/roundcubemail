ALTER TABLE "session" RENAME COLUMN changed TO expires_at;
ALTER INDEX "session_changed_idx" RENAME TO "session_expires_at_idx";
UPDATE "session" SET expires_at = expires_at + INTERVAL '10 minutes';
