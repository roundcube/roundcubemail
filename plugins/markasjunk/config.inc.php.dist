<?php

// Learning driver
// Use an external process such as sa-learn to learn from spam/ham messages. Default: null.
// Please see the README for more information
$config['markasjunk_learning_driver'] = null;

// Ham mailbox
// Mailbox messages should be moved to when they are marked as ham. null = INBOX
// set to FALSE to disable message moving
$config['markasjunk_ham_mbox'] = null;

// Spam mailbox
// Mailbox messages should be moved to when they are marked as spam.
// null = the mailbox assigned as the spam folder in Roundcube settings
// set to FALSE to disable message moving
$config['markasjunk_spam_mbox'] = null;

// Mark messages as read when reporting them as spam
$config['markasjunk_read_spam'] = false;

// Mark messages as unread when reporting them as ham
$config['markasjunk_unread_ham'] = false;

// Add flag to messages marked as spam (flag will be removed when marking as ham)
// If you do not want to use message flags set this to false
$config['markasjunk_spam_flag'] = 'Junk';

// Add flag to messages marked as ham (flag will be removed when marking as spam)
// If you do not want to use message flags set this to false
$config['markasjunk_ham_flag'] = 'NonJunk';

// Write output from spam/ham commands to the log for debug
$config['markasjunk_debug'] = false;

// The mark as spam/ham icon can either be displayed on the toolbar or as part of the mark messages menu.
// Set to False to use Mark menu instead of the toolbar. Default: true.
$config['markasjunk_toolbar'] = true;

// Learn any message moved to the spam mailbox as spam (not just when the button is pressed)
$config['markasjunk_move_spam'] = false;

// Learn any message moved from the spam mailbox to the ham mailbox as ham (not just when the button is pressed)
$config['markasjunk_move_ham'] = false;

// Some drivers create new copies of the target message(s), in this case the original message(s) will be deleted
// Rather than deleting the message(s) (moving to Trash) setting this option true will cause the original message(s) to be permanently removed
$config['markasjunk_permanently_remove'] = false;

// Display only a mark as spam button
$config['markasjunk_spam_only'] = false;

// Activate markasjunk for selected mail hosts only. If this is not set all mail hosts are allowed.
// Example: $config['markasjunk_allowed_hosts'] = ['mail1.domain.tld', 'mail2.domain.tld'];
$config['markasjunk_allowed_hosts'] = null;

// Load specific config for different mail hosts
// Example: $config['markasjunk_host_config'] = [
//    'mail1.domain.tld' => 'mail1_config.inc.php',
//    'mail2.domain.tld' => 'mail2_config.inc.php',
// ];
$config['markasjunk_host_config'] = null;

// cmd_learn Driver options
// ------------------------
// The command used to learn that a message is spam
// The command can contain the following macros that will be expanded as follows:
//      %u is replaced with the username (from the session info)
//      %l is replaced with the local part of the username (if the username is an email address)
//      %d is replaced with the domain part of the username (if the username is an email address or default mail domain if not)
//      %i is replaced with the email address from the user's default identity
//      %s is replaced with the email address the message is from
//      %f is replaced with the path to the message file
//      %h:<header name> is replaced with the content of that header from the message (lower case) eg: %h:x-dspam-signature
// If you do not want to run the command set this to null
$config['markasjunk_spam_cmd'] = null;

// The command used to learn that a message is ham
// The command can contain the following macros that will be expanded as follows:
//      %u is replaced with the username (from the session info)
//      %l is replaced with the local part of the username (if the username is an email address)
//      %d is replaced with the domain part of the username (if the username is an email address or default mail domain if not)
//      %i is replaced with the email address from the user's default identity
//      %s is replaced with the email address the message is from
//      %f is replaced with the path to the message file
//      %h:<header name> is replaced with the content of that header from the message (lower case) eg: %h:x-dspam-signature
// If you do not want to run the command set this to null
$config['markasjunk_ham_cmd'] = null;

// dir_learn Driver options
// ------------------------
// The full path of the directory used to store spam (must be writable by webserver)
$config['markasjunk_spam_dir'] = null;

// The full path of the directory used to store ham (must be writable by webserver)
$config['markasjunk_ham_dir'] = null;

// The filename prefix
// The filename can contain the following macros that will be expanded as follows:
//      %u is replaced with the username (from the session info)
//      %l is replaced with the local part of the username (if the username is an email address)
//      %d is replaced with the domain part of the username (if the username is an email address or default mail domain if not)
//      %t is replaced with the type of message (spam/ham)
$config['markasjunk_filename'] = null;

// email_learn Driver options
// --------------------------
// The email address that spam messages will be sent to
// The address can contain the following macros that will be expanded as follows:
//      %u is replaced with the username (from the session info)
//      %l is replaced with the local part of the username (if the username is an email address)
//      %d is replaced with the domain part of the username (if the username is an email address or default mail domain if not)
//      %i is replaced with the email address from the user's default identity
// If you do not want to send an email set this to null
$config['markasjunk_email_spam'] = null;

// The email address that ham messages will be sent to
// The address can contain the following macros that will be expanded as follows:
//      %u is replaced with the username (from the session info)
//      %l is replaced with the local part of the username (if the username is an email address)
//      %d is replaced with the domain part of the username (if the username is an email address or default mail domain if not)
//      %i is replaced with the email address from the user's default identity
// If you do not want to send an email set this to null
$config['markasjunk_email_ham'] = null;

// Should the spam/ham message be sent as an attachment
$config['markasjunk_email_attach'] = true;

// The email subject (when sending as attachment)
// The subject can contain the following macros that will be expanded as follows:
//      %u is replaced with the username (from the session info)
//      %l is replaced with the local part of the username (if the username is an email address)
//      %d is replaced with the domain part of the username (if the username is an email address or default mail domain if not)
//      %t is replaced with the type of message (spam/ham)
$config['markasjunk_email_subject'] = 'learn this message as %t';

// sa_blacklist Driver options
// ---------------------------
// Path to SAUserPrefs config file
$config['markasjunk_sauserprefs_config'] = '../sauserprefs/config.inc.php';

// amavis_blacklist Driver options
// ---------------------------
// Path to amacube config file
$config['markasjunk_amacube_config'] = '../amacube/config.inc.php';

// edit_headers Driver options
// ---------------------------
// Patterns to match and replace headers for spam messages
// Replacement method uses preg_replace - http://www.php.net/manual/function.preg-replace.php
// WARNING: Be sure to match the entire header line, including the name of the header, also use ^ and $ and the 'm' flag
// see the README for an example
// TEST CAREFULLY BEFORE USE ON REAL MESSAGES
$config['markasjunk_spam_patterns'] = [
    'patterns'     => [],
    'replacements' => []
];

// Patterns to match and replace headers for spam messages
// Replacement method uses preg_replace - http://www.php.net/manual/function.preg-replace.php
// WARNING: Be sure to match the entire header line, including the name of the header, also use ^ and $ and the 'm' flag
// see the README for an example
// TEST CAREFULLY BEFORE USE ON REAL MESSAGES
$config['markasjunk_ham_patterns'] = [
    'patterns'     => [],
    'replacements' => []
];
