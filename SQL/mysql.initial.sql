-- RoundCube Webmail initial database structure
-- Version 0.1beta2
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `cache`
-- 

CREATE TABLE `cache` (
  `cache_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `session_id` varchar(40) default NULL,
  `cache_key` varchar(128) NOT NULL default '',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `data` longtext NOT NULL,
  PRIMARY KEY  (`cache_id`),
  KEY `user_id` (`user_id`),
  KEY `cache_key` (`cache_key`),
  KEY `session_id` (`session_id`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `contacts`
-- 

CREATE TABLE `contacts` (
  `contact_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `del` tinyint(1) NOT NULL default '0',
  `name` varchar(128) NOT NULL default '',
  `email` varchar(128) NOT NULL default '',
  `first_name` varchar(128) NOT NULL default '',
  `middle_name` varchar(128) NOT NULL default '',
  `last_name` varchar(128) NOT NULL default '',
  `edu_title` varchar(128) NOT NULL default '',
  `addon` varchar(128) NOT NULL default '',
  `nickname` varchar(128) NOT NULL default '',
  `company` varchar(128) NOT NULL default '',
  `organisation` varchar(128) NOT NULL default '',
  `department` varchar(128) NOT NULL default '',
  `job_title` varchar(128) NOT NULL default '',
  `note` varchar(128) NOT NULL default '',
  `tel_work1_voice` varchar(128) NOT NULL default '',
  `tel_work2_voice` varchar(128) NOT NULL default '',
  `tel_home1_voice` varchar(128) NOT NULL default '',
  `tel_home2_voice` varchar(128) NOT NULL default '',
  `tel_cell_voice` varchar(128) NOT NULL default '',
  `tel_car_voice` varchar(128) NOT NULL default '',
  `tel_pager_voice` varchar(128) NOT NULL default '',
  `tel_additional` varchar(128) NOT NULL default '',
  `tel_work_fax` varchar(128) NOT NULL default '',
  `tel_home_fax` varchar(128) NOT NULL default '',
  `tel_isdn` varchar(128) NOT NULL default '',
  `tel_preferred` varchar(128) NOT NULL default '',
  `tel_telex` varchar(128) NOT NULL default '',
  `work_street` varchar(128) NOT NULL default '',
  `work_zip` varchar(128) NOT NULL default '',
  `work_city` varchar(128) NOT NULL default '',
  `work_region` varchar(128) NOT NULL default '',
  `work_country` varchar(128) NOT NULL default '',
  `home_street` varchar(128) NOT NULL default '',
  `home_zip` varchar(128) NOT NULL default '',
  `home_city` varchar(128) NOT NULL default '',
  `home_region` varchar(128) NOT NULL default '',
  `home_country` varchar(128) NOT NULL default '',
  `postal_street` varchar(128) NOT NULL default '',
  `postal_zip` varchar(128) NOT NULL default '',
  `postal_city` varchar(128) NOT NULL default '',
  `postal_region` varchar(128) NOT NULL default '',
  `postal_country` varchar(128) NOT NULL default '',
  `url_work` varchar(128) NOT NULL default '',
  `role` varchar(128) NOT NULL default '',
  `birthday` varchar(128) NOT NULL default '',
  `rev` varchar(128) NOT NULL default '',
  `lang` varchar(128) NOT NULL default '',
  `vcard` text NOT NULL,
  PRIMARY KEY  (`contact_id`),
  KEY `user_id` (`user_id`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `identities`
-- 

CREATE TABLE `identities` (
  `identity_id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `del` tinyint(1) NOT NULL default '0',
  `standard` tinyint(1) NOT NULL default '0',
  `name` varchar(128) NOT NULL default '',
  `organization` varchar(128) NOT NULL default '',
  `email` varchar(128) NOT NULL default '',
  `reply-to` varchar(128) NOT NULL default '',
  `bcc` varchar(128) NOT NULL default '',
  `signature` text NOT NULL,
  PRIMARY KEY  (`identity_id`),
  KEY `user_id` (`user_id`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `session`
-- 

CREATE TABLE `session` (
  `sess_id` varchar(40) NOT NULL default '',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `changed` datetime NOT NULL default '0000-00-00 00:00:00',
  `ip` VARCHAR(15) NOT NULL default '',
  `vars` text NOT NULL,
  PRIMARY KEY  (`sess_id`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(128) NOT NULL default '',
  `mail_host` varchar(128) NOT NULL default '',
  `alias` varchar(128) NOT NULL default '',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_login` datetime NOT NULL default '0000-00-00 00:00:00',
  `language` varchar(5) NOT NULL default 'en',
  `preferences` text NOT NULL default '',
  PRIMARY KEY  (`user_id`)
);

-- --------------------------------------------------------

-- 
-- Table structure for table `messages`
-- 

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
);


