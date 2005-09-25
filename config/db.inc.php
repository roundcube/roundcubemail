<?php

/*
 +-----------------------------------------------------------------------+
 | Configuration file for database access                                |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
 | All rights reserved.                                                  |
 |                                                                       |
 +-----------------------------------------------------------------------+

*/

$rcmail_config = array();

// database engine (currently supported: mysql)
$rcmail_config['db_type'] = 'mysql';

// database host
$rcmail_config['db_host'] = 'localhost';

// database user
$rcmail_config['db_user'] = 'roundcube';

// pwd
$rcmail_config['db_pass'] = 'pass';

// database name
$rcmail_config['db_name'] = 'roundcubemail';


// you can define specific table names used to store webmail data
$rcmail_config['db_table_users'] = 'users';

$rcmail_config['db_table_identities'] = 'identities';

$rcmail_config['db_table_contacts'] = 'contacts';

$rcmail_config['db_table_session'] = 'session';

$rcmail_config['db_table_cache'] = 'cache';


// end db config file
?>
