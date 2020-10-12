#!/usr/bin/env php
<?php

/*
 +-----------------------------------------------------------------------+
 | bin/userimport.sh                                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2016, The Roundcube Dev Team                            |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Utility script to import data exported by userexport.sh.            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Lukas Erlacher <luke@lerlacher.de>                            |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/' );
ini_set('memory_limit', -1);

require_once INSTALL_PATH.'program/include/clisetup.php';

function print_usage()
{
	print "Usage:  userimport -f file -u username -h host -l limit -c -s -e\n";
	print "--file       Input file\n";
	print "--user       IMAP user name\n";
	print "--host       User IMAP host\n";
	print "--limit      Limit\n";
	print "--clobber    Overwrite existing records without asking\n";
	print "--safe       Skip existing records\n";
	print "--ephemeral  Include ephemeral data (such as last_login)\n";
}

// get arguments
$opts = array('u' => 'user', 'h' => 'host', 'l' => 'limit', 'f' => 'file', 'c' => 'clobber:', 's' => 'safe:', 'e': 'ephemeral:');
$args = rcube_utils::get_opt($opts);

if ($_SERVER['argv'][1] == 'help')
{
	print_usage();
	exit;
}

if (!empty($args['file']))
{
    $file = fopen($args['file']);
    if (!$file)
    {
        print "Could not open ${args['file']}.\n";
        exit;
    }
} else {
    if ($args['clobber'] !== true && $args['safe'] !== true)
    {
        print "Please specify clobber (-c) or safe (-s) mode to make this script noninteractive if you want to pass input via stdin.\n";
        exit;
    }
    $file = STDIN;
    print "Reading user records from stdin...\n";
}

$rcmail = rcube::get_instance();
$config = $rcmail->config;

// connect to DB
$db = $rcmail->get_dbh();
$db->db_connect('w');
$transaction = false;

if (!$db->is_connected() || $db->is_error()) {
    _die("No DB connection\n" . $db->is_error());
}

print ("Connected to DB\n");

while (($line = fgets($file)) !== false)
{
    // decode user
    $record = json_decode($line);
    $username = $record['user']['data']['username'];
    $userhost = $record['user']['data']['mail_host'];

    // check if user should be skipped
    if (!empty($args['host']) && $args['host'] != $userhost)
    {
        continue;
    } 
    else if (!empty($args['user']) && $args['user'] != $username)
    {
        continue;
    }

    // does user exist?
    $user = rcube_user::query($username, $userhost);

    if ($user === false)
    {
        $user = rcube_user::create($username, $userhost);
    }
    else
    {
        // safe mode means skip existing users
        if (!empty($args['safe']))
        {
            print "Skipping ${username}@${userhost}.\n";
            continue;
        }

        // !clobber means ask
        if (empty($args['clobber']))
        {
            print "WARNING: ${username}@${userhost} already exists in local roundcube DB.\n"
            echo "Replace existing user? [y/N] ";
            $clobber = strtolower(fgets(STDIN)[0]) == 'y';

            if ($clobber !== true) {
                print "Skipping ${username}@${userhost}.\n";
                continue;
            }
        }

        print "Clobbering existing user ${username}@${userhost}.\n";
    }

    // full steam ahead - will not check before clobbering past here

    // direct user data
    $userprefs = $record['user']['data']['preferences'];

    $user->save_prefs($userprefs, true);

    if ($args['ephemeral'])
    {
        $last_login = $record['user']['data']['last_login'];
        $created = $record['user']['data']['last_login'];

        $user->touch($last_login);

        $db->query(
            'UPDATE '.$db->table_name('users', true).
            ' SET `created` = ? WHERE `user_id` = ?',
            $created,
            $user->ID
        );

    }

    // identities
    $current_idents = $user->list_identities();
    $idents_map = array();

    // hash by 'name' and 'email', if they match an import entry they will get overwritten
    foreach ($current_idents as $ident)
    {
        $idents_map[$ident['name'] . '=!=' . $ident['email']] = $ident;
    }

    $import_idents = $record['identities'];

    foreach($import_idents as $ident)
    {
        $hash = $ident['name'] . '=!=' . $ident['email'];
        if (!empty($idents_map[$hash]))
        {
            $user->update_identity($idents_map[$hash]['identity_id'], $ident);
        }
        else
        {
            $user->insert_identity($ident);
        }

        // selecting the default identity works by inserting / updating a new identity
        // with the `standard` column set and then unsetting `standard` from all other
        // identities.
        if ($ident['standard'] == 1)
        {
            $user->set_default($ident['identity_id']);
        }
    }

    // contacts - import groups first
    $import_groups = $record['groups'];
    $contacts_manager = new rcube_contacts($db, $user->ID);
    $current_groups = $contacts_manager->list_groups();

    // groups do not need to be updated (they only have `name` column),
    // only new groups added

    // ID map that will be needed later for contact->group associations
    // - keys: import data IDs
    // - values: IDs in live database
    $groups_idmap = array();

    // hash on name only this time
    $groups_map = array();
    foreach($current_groups as $group)
    {
        $groups_map[$group['name']] = $group;
    }

    foreach($import_groups as $group)
    {
        $hash = $group['name'];
        if (empty($groups_map[$hash]]))
        {
            $res = $contacts->create_group($hash);
            $groups_idmap[$group['contactgroup_id']] = $res['id'];
        }
        else
        {
            $groups_idmap[$group['contactgroup_id']] = $groups_map[$hash]['contactgroup_id']
        }
    }

    // now do actual contacts and group memberships
    $import_contacts = $record['contacts'];
    $current_contacts = $contacts->list_rcords();

    // id map for contact->group associations
    $contacts_idmap = array();

    // hash on contact name - other attributes are optional
    $contacts_map = array();
    foreach ($current_contacts as $contact)
    {
        $contacts_map[$contact['name']] = $contact;
    }

    foreach($import_contacts as $contact_rec)
    {
        $contact = $contact_rec['contact'];
        $groups = $contact_rec['groups'];

        $hash = $contact['name'];

        // update or insert contact
        if (!empty($contacts_map[$hash]))
        {
            $contact_db_id = $contacts_map[$hash]['contact_id'];
            $contacts_manager->update($id, $contact);
        }
        else
        {
            $contact_db_id = $contacts_manager->insert($contact);
        }
        $contacts_idmap[$contact['id']] = $id;

        // now process contactgroup memberships
        foreach($groups as $import_id, $import_name) {
            $group_db_id = $groups_idmap[$import_id];

            // FIXME: Batch this? (though it will still be one insert query per contact)
            // FIXME: Also maybe manually calculate updates that need to be done so
            //        all the work done in add_to_group isn't necessary
            $contacts_manager->add_to_group($group_db_id, $contact_db_id);
        }
        // TODO: Removal?



    }
}

