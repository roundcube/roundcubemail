<?php

// Password Plugin options
// -----------------------
// A driver to use for password change. Default: "sql".
$rcmail_config['password_driver'] = 'sasl';

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
//      %p is replaced with the plaintext new password
//      %c is replaced with the crypt version of the new password, MD5 if available
//         otherwise DES.
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


// SASL Driver options
// -------------------
// Additional arguments for the saslpasswd2 call
$rcmail_config['password_saslpasswd_args'] = '-u rolig';


// LDAP Driver options
// -------------------
// LDAP server name to connect to. 
// You can provide one or several hosts in an array in which case the hosts are tried from left to right.
// Exemple: array('ldap1.exemple.com', 'ldap2.exemple.com');
// Default: 'localhost'
$rcmail_config['password_ldap_host'] = 'localhost';

// LDAP server port to connect to
// Default: '389'
$rcmail_config['password_ldap_port'] = '389';

// TLS is started after connecting
// Using TLS for password modification is recommanded.
// Default: false
$rcmail_config['password_ldap_starttls'] = false;

// LDAP version
// Default: '3'
$rcmail_config['password_ldap_version'] = '3';

// LDAP base name (root directory)
// Exemple: 'dc=exemple,dc=com'
$rcmail_config['password_ldap_basedn'] = 'dc=exemple,dc=com';

// LDAP connection method
// There is two connection method for changing a user's LDAP password.
// 'user': use user credential (recommanded, require password_confirm_current=true)
// 'admin': use admin credential (this mode require password_ldap_adminDN and password_ldap_adminPW)
// Default: 'user'
$rcmail_config['password_ldap_method'] = 'user';

// LDAP Admin DN
// Used only in admin connection mode
// Default: null
$rcmail_config['password_ldap_adminDN'] = null;

// LDAP Admin Password
// Used only in admin connection mode
// Default: null
$rcmail_config['password_ldap_adminPW'] = null;

// LDAP user DN mask
// The user's DN is mandatory and as we only have his login, we need to re-create his DN using a mask
// '%login' will be replace by the current roundcube user's login
// Exemple: 'uid=%login,ou=people,dc=exemple,dc=com'
$rcmail_config['password_ldap_userDN_mask'] = 'uid=%login,ou=people,dc=exemple,dc=com';

// LDAP password hash type
// Standard LDAP encryption type which must be one of: crypt,
// ext_des, md5crypt, blowfish, md5, sha, smd5, ssha, or clear.
// Please note that most encodage types require external libraries
// to be included in your PHP installation, see function hashPassword in drivers/ldap.php for more info.
// Default: 'crypt'
$rcmail_config['password_ldap_encodage'] = 'crypt';

// LDAP password attribute
// Name of the ldap's attribute used for storing user password
// Default: 'userPassword'
$rcmail_config['password_ldap_pwattr'] = 'userPassword';

?>
