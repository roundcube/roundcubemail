<?php

// Password Plugin options
// -----------------------
// A driver to use for password change. Default: "sql".
$rcmail_config['password_driver'] = 'poppassd';

// Determine whether current password is required to change password.
// Default: false.
$rcmail_config['password_confirm_current'] = true;


// SQL Driver options
// ------------------
// PEAR database DSN for performing the query. By default
// Roundcube DB settings are used.
$rcmail_config['password_db_dsn'] = '';

// The SQL query used to change the password.
// The query can contain the following macros that will be expanded as follows:
//	%p is replaced with the plaintext new password
//      %c is replaced with the crypt version of the new password, MD5 if available
//    	   otherwise DES.
//      %u is replaced with the username (from the session info)
//      %o is replaced with the password before the change
//      %h is replaced with the imap host (from the session info)
// Escaping of macros is handled by this module.
// Default: "SELECT update_passwd(%c, %u)"
$rcmail_config['password_query'] = 'SELECT update_passwd(%c, %u)';


// Poppassd Driver options
// -----------------------
// The host which changes the password
$rcmail_config['password_pop_host'] = 'localhost';

// TCP port used for poppassd connections
$rcmail_config['password_pop_port'] = 106;

?>
