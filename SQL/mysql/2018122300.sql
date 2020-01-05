ALTER TABLE `filestore` ADD COLUMN `context` varchar(32) NOT NULL;
UPDATE `filestore` SET `context` = 'enigma';
ALTER TABLE `filestore` DROP FOREIGN KEY `user_id_fk_filestore`;
ALTER TABLE `filestore` DROP INDEX `uniqueness`;
ALTER TABLE `filestore` ADD UNIQUE INDEX `uniqueness` (`user_id`, `context`, `filename`);
ALTER TABLE `filestore` ADD CONSTRAINT `user_id_fk_filestore` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
