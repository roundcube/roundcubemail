<?php

$config = [];

// Database configuration
$config['db_dsnw'] = 'sqlite:////tmp/sqlite.db?mode=0646';

// Test user credentials
$config['tests_username'] = 'test';
$config['tests_password'] = 'test';

// GreenMail
$config['smtp_host'] = 'localhost:25';

// Settings required by the tests

$config['create_default_folders'] = true;
$config['skin'] = 'elastic';
$config['support_url'] = 'http://support.url';

// Plugins with tests

$config['plugins'] = [
    'archive',
    'attachment_reminder',
    'markasjunk',
    'zipdownload'
];

$config['archive_mbox'] = 'Archive';

$config['enable_spellcheck'] = true;
$config['spellcheck_engine'] = 'pspell';
