ALTER TABLE `kolab_alarms` DROP PRIMARY KEY;
ALTER TABLE `kolab_alarms` ADD PRIMARY KEY (`alarm_id`, `user_id`);
