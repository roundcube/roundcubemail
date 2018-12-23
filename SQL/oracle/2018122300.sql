ALTER TABLE "filestore" ADD COLUMN "context" varchar(32) NOT NULL;
UPDATE "filestore" SET "context" = 'enigma';
ALTER TABLE "filestore" DROP CONSTRAINT "filestore_user_id_key";
ALTER TABLE "filestore" ADD CONSTRAINT "filestore_user_id_key" UNIQUE ("user_id", "context", "filename");
