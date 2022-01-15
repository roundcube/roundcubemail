#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Import keys from Enigma's homedir into database for multihost       |
 |   support.                                                            |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/../../../') . '/');

require INSTALL_PATH . 'program/include/clisetup.php';

$rcmail = rcube::get_instance();

// get arguments
$args = rcube_utils::get_opt([
        'u' => 'user',
        'h' => 'host',
        'd' => 'dir',
        'x' => 'dry-run',
]);

if (!empty($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'help') {
    print_usage();
    exit;
}

if (empty($args['dir'])) {
    rcube::raise_error("--dir argument is required", true);
}

$host = get_host($args);
$dirs = [];

// Read the homedir and iterate over all subfolders (as users)
if (empty($args['user'])) {
    if ($dh = opendir($args['dir'])) {
        while (($dir = readdir($dh)) !== false) {
            if ($dir != '.' && $dir != '..') {
                $dirs[$args['dir'] . '/' . $dir] = $dir;
            }
        }
        closedir($dh);
    }
}
// a single user
else {
    $dirs = [$args['dir'] => $args['user']];
}

foreach ($dirs as $dir => $user) {
    echo "Importing keys from $dir\n";

    if ($user_id = get_user_id($user, $host)) {
        reset_state($user_id, !empty($args['dry-run']));
        import_dir($user_id, $dir, !empty($args['dry-run']));
    }
}


function print_usage()
{
    print "Usage: import.sh [options]\n";
    print "Options:\n";
    print "    --user=username User, if not set --dir subfolders will be iterated\n";
    print "    --host=host     The IMAP hostname or IP the given user is related to\n";
    print "    --dir=path      Location of the gpg homedir\n";
    print "    --dry-run       Do nothing, just list found user/files\n";
}

function get_host($args)
{
    global $rcmail;

    if (empty($args['host'])) {
        $hosts = $rcmail->config->get('imap_host', '');
        if (is_string($hosts)) {
            $args['host'] = $hosts;
        }
        else if (is_array($hosts) && count($hosts) == 1) {
            $args['host'] = reset($hosts);
        }
        else {
            rcube::raise_error("Specify a host name", true);
        }

        // host can be a URL like tls://192.168.12.44
        $host_url = parse_url($args['host']);
        if (!empty($host_url['host'])) {
            $args['host'] = $host_url['host'];
        }
    }

    return $args['host'];
}

function get_user_id($username, $host)
{
    global $rcmail;

    $db = $rcmail->get_dbh();

    // find user in local database
    $user = rcube_user::query($username, $host);

    if (empty($user)) {
        rcube::raise_error("User does not exist: $username");
    }

    return $user->ID;
}

function reset_state($user_id, $dry_run = false)
{
    global $rcmail;

    if ($dry_run) {
        return;
    }

    $db = $rcmail->get_dbh();

    $db->query("DELETE FROM " . $db->table_name('filestore', true)
        . " WHERE `user_id` = ? AND `context` = ?",
        $user_id, 'enigma');
}

function import_dir($user_id, $dir, $dry_run = false)
{
    global $rcmail;

    $db       = $rcmail->get_dbh();
    $table    = $db->table_name('filestore', true);
    $db_files = ['pubring.gpg', 'secring.gpg', 'pubring.kbx'];
    $maxsize  = min($db->get_variable('max_allowed_packet', 1048500), 4*1024*1024) - 2000;

    foreach (glob("$dir/private-keys-v1.d/*.key") as $file) {
        $db_files[] = substr($file, strlen($dir) + 1);
    }

    foreach ($db_files as $file) {
        if ($mtime = @filemtime("$dir/$file")) {
            $data     = file_get_contents("$dir/$file");
            $data     = base64_encode($data);
            $datasize = strlen($data);

            if ($datasize > $maxsize) {
                rcube::raise_error([
                        'code' => 605, 'line' => __LINE__, 'file' => __FILE__,
                        'message' => "Enigma: Failed to save $file. Size exceeds max_allowed_packet."
                    ], true, false);

                continue;
            }

            echo "* $file\n";

            if ($dry_run) {
                continue;
            }

            $result = $db->query(
                "INSERT INTO $table (`user_id`, `context`, `filename`, `mtime`, `data`)"
                . " VALUES(?, 'enigma', ?, ?, ?)",
                $user_id, $file, $mtime, $data);

            if ($db->is_error($result)) {
                rcube::raise_error([
                        'code' => 605, 'line' => __LINE__, 'file' => __FILE__,
                        'message' => "Enigma: Failed to save $file into database."
                    ], true, false);
            }
        }
    }
}
