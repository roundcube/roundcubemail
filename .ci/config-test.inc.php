<?php

$config = array();

// Database configuration
$config['db_dsnw'] = 'sqlite:////tmp/sqlite.db?mode=0646';

// Test user credentials
$config['tests_username'] = 'test';
$config['tests_password'] = 'test';

// GreenMail
$config['smtp_port'] = 25;

// Settings required by the tests

$config['devel_mode'] = true;
$config['skin'] = 'elastic';
$config['support_url'] = 'http://support.url';

