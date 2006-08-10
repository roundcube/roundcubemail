-- RoundCube Webmail update script for MySQL databases
-- Updates from version 0.1-20051007

 ALTER TABLE `contacts` ADD `name` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `email` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `first_name` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `middle_name` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `last_name` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `edu_title` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `addon` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `nickname` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `company` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `organisation` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `department` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `job_title` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `note` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_work1_voice` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_work2_voice` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_home1_voice` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_home2_voice` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_cell_voice` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_car_voice` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_pager_voice` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_additional` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_work_fax` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_home_fax` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_isdn` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_preferred` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `tel_telex` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `work_street` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `work_zip` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `work_city` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `work_region` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `work_country` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `home_street` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `home_zip` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `home_city` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `home_region` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `home_country` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `postal_street` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `postal_zip` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `postal_city` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `postal_region` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `postal_country` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `url_work` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `role` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `birthday` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `rev` varchar(128) NOT NULL default '';
 ALTER TABLE `contacts` ADD `lang` varchar(128) NOT NULL default '';

-- RoundCube Webmail update script for MySQL databases
-- Updates from version 0.1-20051007

ALTER TABLE `session` ADD `ip` VARCHAR(15) NOT NULL AFTER changed;
ALTER TABLE `users` ADD `alias` VARCHAR(128) NOT NULL AFTER mail_host;



-- RoundCube Webmail update script for MySQL databases
-- Updates from version 0.1-20051021

ALTER TABLE `session` CHANGE `sess_id` `sess_id` VARCHAR(40) NOT NULL;

ALTER TABLE `contacts` CHANGE `del` `del` TINYINT(1) NOT NULL;
ALTER TABLE `contacts` ADD `changed` DATETIME NOT NULL AFTER `user_id`;

UPDATE `contacts`  SET `del`=0 WHERE `del`=1;
UPDATE `contacts`  SET `del`=1 WHERE `del`=2;

ALTER TABLE `identities` CHANGE `default` `standard` TINYINT(1) NOT NULL;
ALTER TABLE `identities` CHANGE `del` `del` TINYINT(1) NOT NULL;

UPDATE `identities`  SET `del`=0 WHERE `del`=1;
UPDATE `identities`  SET `del`=1 WHERE `del`=2;
UPDATE `identities`  SET `standard`=0 WHERE `standard`=1;
UPDATE `identities`  SET `standard`=1 WHERE `standard`=2;

CREATE TABLE `messages` (
  `message_id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL default '0',
  `del` tinyint(1) NOT NULL default '0',
  `cache_key` varchar(128) NOT NULL default '',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `idx` int(11) unsigned NOT NULL default '0',
  `uid` int(11) unsigned NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '',
  `from` varchar(255) NOT NULL default '',
  `to` varchar(255) NOT NULL default '',
  `cc` varchar(255) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `size` int(11) unsigned NOT NULL default '0',
  `headers` text NOT NULL,
  `body` longtext,
  PRIMARY KEY  (`message_id`),
  KEY `user_id` (`user_id`),
  KEY `cache_key` (`cache_key`),
  KEY `idx` (`idx`),
  KEY `uid` (`uid`)
) TYPE=MyISAM;



-- RoundCube Webmail update script for MySQL databases
-- Updates from version 0.1-20051216

ALTER TABLE `messages` ADD `created` DATETIME NOT NULL AFTER `cache_key` ;
