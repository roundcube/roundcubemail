<?php

$config = [];

// Database configuration
$config['db_dsnw'] = 'sqlite:///' . sys_get_temp_dir() . '/roundcube-test-sqlite.db?mode=0646';

// Test user credentials
$config['tests_username'] = 'admin@example.net';
$config['tests_password'] = 'admin';

$config['imap_host'] = getenv('RC_CONFIG_IMAP_HOST') ?: 'localhost:143';
$config['imap_auth_type'] = 'IMAP';
$config['imap_conn_options'] = [
   'ssl' => [
       'verify_peer' => false,
       'verify_peer_name' => false,
    ],
];
// $config['managesieve_host'] = 'localhost:4190';

// GreenMail
$config['smtp_host'] = getenv('RC_CONFIG_SMTP_HOST') ?: 'localhost:25';

// Settings required by the tests

$config['create_default_folders'] = true;
$config['skin'] = 'elastic';
$config['support_url'] = 'http://support.url';

// Plugins with tests

$config['plugins'] = [
    'archive',
    'attachment_reminder',
    'markasjunk',
    'markdown_editor',
    'zipdownload',
];

$config['archive_mbox'] = 'Archive';

$config['enable_spellcheck'] = true;
$config['spellcheck_engine'] = 'pspell';
