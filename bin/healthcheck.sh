#!/usr/bin/env php
<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Roundcube health checker                                            |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <machniak@apheleia-it.ch>                 |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/');

require INSTALL_PATH . 'program/include/clisetup.php';

$args = rcube_utils::get_opt([
    'h' => 'host',
    'u' => 'user',
    'p' => 'pass',
]);

define('ROUNDCUBE_STDERR_DISABLE', true); // Disable STDERR output

$healthchecker = new rcmail_healthchecker($args);

exit($healthchecker->run());
