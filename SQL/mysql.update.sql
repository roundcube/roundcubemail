-- RoundCube Webmail update script for MySQL databases
-- Version 0.1-20051007


ALTER TABLE session ADD ip VARCHAR(15) NOT NULL AFTER changed;
ALTER TABLE users ADD alias VARCHAR(128) NOT NULL AFTER mail_host;



-- RoundCube Webmail update script for MySQL databases
-- Version 0.1-20051021

ALTER TABLE `session` CHANGE `sess_id` `sess_id` VARCHAR(40) NOT NULL;
ALTER TABLE `contacts` ADD `changed` DATETIME NOT NULL AFTER `user_id`;
