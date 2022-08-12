ALTER TABLE "filestore" ADD COLUMN context varchar(32);
UPDATE "filestore" SET context = 'enigma';
ALTER TABLE "filestore" ALTER COLUMN context SET NOT NULL;
ALTER TABLE "filestore" DROP CONSTRAINT "filestore_user_id_filename";
ALTER TABLE "filestore" ADD CONSTRAINT "filestore_user_id_context_filename" UNIQUE (user_id, context, filename);
