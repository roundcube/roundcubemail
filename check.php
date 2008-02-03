<?php
/**
 * Copyright (c) 2008, Till Klampaeckel
 * 
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 *  * Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer.
 *  * Redistributions in binary form must reproduce the above copyright notice, this
 *    list of conditions and the following disclaimer in the documentation and/or
 *    other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * PHP Version 5
 *
 * @category Config
 * @package  RoundCube
 * @author   Till Klampaeckel <till@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version  CVS: $Id$
 * @link     https://svn.roundcube.net/trunk
 * @todo     Check IMAP settings.
 * @todo     Check SMTP settings.
 * @todo     HTML/CSS to make it pretty.
 */

$include_path  = dirname(__FILE__) . '/program/lib/';
$include_path .= PATH_SEPARATOR . get_include_path();
set_include_path($include_path);

$writable_dirs = array('logs/', 'temp/');
$create_files  = array('config/db.inc.php', 'config/main.inc.php');

$path = dirname(__FILE__) . '/';

echo '<h3>Check if directories are writable</h3>';
echo '<p>RoundCube may need to write/save files into these directories.</p>';

foreach ($writable_dirs AS $dir) {
    echo "Directory $dir: ";
    if (!is_writable($path . $dir)) {
        echo 'NOT OK';
    } else {
        echo 'OK';
    }
    echo "<br />";
}

echo '<h3>Check if you setup config files</h3>';
echo '<p>Checks if the files exist and if they are readable.</p>';

foreach ($create_files AS $file) {
    echo "File $file: ";
    if (file_exists($path . $file) && is_readable($path . $file)) {
        echo 'OK';
    } else {
        echo 'NOT OK';
    }
    echo '<br />';
}

echo '<h3>Check supplied DB settings</h3>';
@include $path . 'config/db.inc.php';

$db_working = false;
if (isset($rcmail_config)) {
    echo 'DB settings: ';
    include_once 'MDB2.php';
    $db = MDB2::connect($rcmail_config['db_dsnw']);
    if (!MDB2::IsError($db)) {
        echo 'OK';
        $db->disconnect();
        $db_working = true;
    } else {
        echo 'NOT OK';
    }
    echo '<br />';
} else {
    echo 'Could not open db.inc.php config file, or file is empty.<br />';
}

echo '<h3>TimeZone</h3>';
echo 'Status: ';
if ($db_working === true) {
    require_once 'include/rcube_mdb2.inc';
    $DB = new $dbclass($rcmail_config['db_dsnw'], '', false);
    $DB->db_connect('w');
    
    $tz_db    = $DB->unixtimestamp();
    $tz_local = time();
    if ($tz_db != $tz_local) {
        echo 'NOT OK';
    } else {
        echo 'OK';
    }
} else {
    echo 'Could not test (fix DB first).';
}
echo '<br />';

echo '<h3>Checking .ini settings</h3>';

$auto_start   = ini_get('session.auto_start');
$file_uploads = ini_get('file_uploads');

echo '<h4>session.auto_start</h4>';
echo 'status: ';
if ($auto_start == 1) {
    echo 'NOT OK';
} else {
    echo 'OK';
}
echo '<br />';

echo '<h4>file_uploads</h4>';
echo 'status: ';
if ($file_uploads == 1) {
    echo 'OK';
} else {
    echo 'NOT OK';
}

/*
 * Probably not needed because we have a custom handler
echo '<h4>session.save_path</h4>';
echo 'status: ';
$save_path = ini_get('session.save_path');
if (empty($save_path)) {
    echo 'NOT OK';
} else {
    echo "OK: $save_path";
    if (!file_exists($save_path)) {
        echo ', but it does not exist';
    } else {
        if (!is_readable($save_path) || !is_writable($save_path)) {
            echo ', but permissions to read and/or write are missing';
        }
    }
}
echo '<br />';
 */
?>