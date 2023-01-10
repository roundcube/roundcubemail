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
 |   User identity updating                                                |
 +-----------------------------------------------------------------------+
 | Author: Vladas K <info@vladasko.com>                                  |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/' );

require INSTALL_PATH.'program/include/clisetup.php';

$options = rcube_utils::get_opt([
    'u' => 'username',
    'e' => 'email',
    'n' => 'name',
    'o' => 'organization',
    's' => 'signature',
    'b' => 'bcc_email',
    'r' => 'reply_to_email',
    'H' => 'is_html_signature',
    'S' => 'is_default',
    'a' => 'attribute',
    'i' => 'identity_id'
]);

$subcommand_executables = [
    'add' => 'add_identity',
    'get-attr' => 'get_identity_attr',
    'update' => 'update_identity',
    'delete' => 'delete_identity',
    'list' => 'list_identities',
];

$programName = @$options[0];

if (isset($subcommand_executables[$programName])) {
    $program = $subcommand_executables[$programName];

    $program($options);
} else {
    echo "available sub-commands\n";
    echo "======================\n\n";
    echo "    add\n";
    echo "        create a new identity for a user\n\n";
    echo "    delete\n";
    echo "        delete an identity (sets del to 1 and it is not active, but it can still be found in the database)\n\n";
    echo "    list\n";
    echo "        list all identiteis of a user\n\n";
    echo "    get-attr\n";
    echo "        get some attribute of an identity\n\n";
    echo "    update\n";
    echo "        update identity\n\n";
}

function get_identity_attr($options) {
    if (count($options) === 1) {
        echo "get-attr\n";
        echo "========\n\n";
        echo "    Prints the specified attribute of a users identity.\n\n\n";
        echo "    Mandatory arguments:\n\n";
        echo "    -u\n";
        echo "        username - the identity holder e.g. -u mainmail@example.com\n\n";
        echo "    -i\n";
        echo "        identity id - the id of the identity being queried e.g. -i 70) use list sub-command to get identity id\n\n";
        echo "    -a\n";
        echo "        attributes name - e.g. -a email\n";
        echo "        available attributes: identity_id user_id changed del standard name organization email reply-to bcc signature html_signature\n\n";
  
        exit;
    }
  
    $username = getOptionValue($options, 'username', '', false, true, "Enter the username e.g. -u user@example.com");
    $identity_id = getOptionValue($options, 'identity_id', '', false, true, "Enter the identity id e.g. -i 70");
    $attribute = getOptionValue($options, 'attribute', '', false, true, "Enter the attribute key e.g. -a name");

    $host = rcube_utils::get_host($options);
    $user = get_user($username, $host);

    if (!$user) {
        exit;
    }

    $identity = @$user->get_identity($identity_id);

    if (!isset($identity)) {
        rcube::raise_error("Invalid identity ID.");
    
        exit;
    }

    if (isset($identity[$attribute])) {
        $attrValue = $identity[$attribute];

        echo "$attribute - $attrValue\n";
    } else {
        rcube::raise_error("Invalid attribute. Available attributes: identity_id user_id changed del standard name organization email reply-to bcc signature html_signature");
    
        exit;
    }
}

function list_identities($options) {
    if (count($options) === 1) {
        echo "list\n";
        echo "====\n\n";
        echo "    Prints the attributes of all identities of a user.\n\n\n";
        echo "    Mandatory arguments:\n\n";
        echo "    -u\n";
        echo "        username - the identity holder e.g. -u mainmail@example.com\n\n";
   
        exit;
    }
  
    $username = getOptionValue($options, 'username', '', false, true, "Enter the username e.g. -u user@example.com");

    $host = rcube_utils::get_host($options);
    $user = get_user($username, $host);

    if (!$user) {
      exit;
    }

    $identities = $user->list_identities(null, true);

    echoIdentities($identities);
}

function delete_identity($options) {
    if (count($options) === 1) {
        echo "delete\n";
        echo "======\n\n";
        echo "    Deletes an identity.\n";
        echo "    This sets 'del' value of an identity to 1 making it inactive, but it is not removed from the database. \n\n\n";
        echo "    Mandatory arguments:\n\n";
        echo "    -u\n";
        echo "        username - the identity holder e.g. -u mainmail@example.com\n\n";
        echo "    -i\n";
        echo "        identity id - the id of the identity being queried e.g. -i 70) use list sub-command to get identity id\n\n";
  
        exit;
    }  

    $username = getOptionValue($options, 'username', '', false, true, "Enter the username e.g. -u user@example.com");
    $identity_id = getOptionValue($options, 'identity_id', '', false, true, "Enter the identity id e.g. -i 70");

    $host = rcube_utils::get_host($options);
    $user = get_user($username, $host);

    if (!$user) {
        exit;
    }

    $identity = $user->delete_identity($identity_id);

    if (!$identity) {
        rcube::raise_error("Invalid identity ID.");
    
        exit;
    }

    echo "Identity deleted.\n";
}

function add_identity($options) {
    if (count($options) === 1) {
        echo "add\n";
        echo "===\n\n";
        echo "    Creates a new identity for a given user.\n\n\n";
        echo "    Mandatory arguments:\n\n";
        echo "    -u\n";
        echo "        username - the identity holder e.g. -u mainmail@example.com\n\n";
        echo "    -e\n";
        echo "        email - the email of the identity e.g. identityemail@example.com\n\n";
        echo "    -n\n";
        echo "        the name of the identity - e.g. -n 'John Smith'\n\n\n";
        echo "    Optional arguments:\n\n";
        echo "    -o\n";
        echo "        organization - e.g. -o 'Your Organization Name'\n\n";
        echo "    -r\n";
        echo "        reply-to email - e.g. -r replytothisemail@example.com\n\n";
        echo "    -b\n";
        echo "        bcc email - e.g. -b bcc@example.com\n\n";
        echo "    -s\n";
        echo "        signature - e.g. -s 'Sincerely, John Smith'\n\n";
        echo "    -H\n";
        echo "        is signature HTML (empty value or 1 for yes, 0 for no) e.g. -H\n\n";
        echo "    -S\n";
        echo "        should this be set as a default identity for the user (only 1 available so it disables all other. Empty value or 1 for yes, 0 for no) e.g. -S 1\n\n";;
  
        exit;
    }
  
    $username = getOptionValue($options, 'username', '', false, true, "Enter the username e.g. -u user@example.com");

    $new_identity = [];
    $setAsDefault = false;

    if (isset($options['email'])) {
        validateEmail($options['email'], 'email');
    }
    if (isset($options['is_html_signature'])) {
        validateBoolean($options['is_html_signature'], 'is signature HTML (H)'); 
    }
    if (isset($options['bcc_email'])) {
        validateEmail($options['bcc_email'], 'bcc email');
    }
    if (isset($options['reply_to_email'])) {
        validateEmail($options['reply_to_email'], 'reply-to email');
    }
    if (isset($options['is_default'])) {
        validateBoolean($options['is_default'], 'is default identity (S)');
        $setAsDefault = filter_var($options['is_default'], FILTER_VALIDATE_BOOLEAN);
    } 

    $new_identity['email'] = getOptionValue($options, 'email', '', false, true, "Enter the email e.g. -e somemail@example.com");
    $new_identity['name'] = getOptionValue($options, 'name', '', false, true, "Enter the name of an identity e.g. -n 'John Smith'");
    $new_identity['organization']  = getOptionValue($options, 'organization', '', false, false);
    $new_identity['signature'] = getOptionValue($options, 'signature', '', false, false);
    $new_identity['html_signature'] = getOptionValue($options, 'is_html_signature', 0, true, false);
    $new_identity['bcc'] = getOptionValue($options, 'bcc_email', '', false, false);
    $new_identity['reply-to'] = getOptionValue($options, 'reply_to_email', '', false, false);

    $host = rcube_utils::get_host($options);
    $user = get_user($username, $host);

    if (!$user) {
        exit;
    }

    $id = $user->insert_identity($new_identity);

    if ($setAsDefault) {
        $user->set_default($id);
    }

    echo "Identity created successfully.\n";
}

function update_identity($options) {
    if (count($options) === 1) {
        echo "update\n";
        echo "======\n\n";
        echo "    Update an existing identity with provided values.\n\n\n";
        echo "    Mandatory arguments:\n\n";
        echo "    -u\n";
        echo "        username - the identity holder e.g. -u mainmail@example.com\n\n";
        echo "    -i\n";
        echo "        identity id - the id of the identity being queried e.g. -i 70) use list sub-command to get identity id\n\n\n";
        echo "    Optional arguments (at least one must be specified):\n\n";
        echo "    -e\n";
        echo "        email - the email of the identity e.g. identityemail@example.com\n\n";
        echo "    -n\n";
        echo "        the name of the identity - e.g. -n 'John Smith'\n\n";
        echo "    -o\n";
        echo "        organization - e.g. -o 'Your Organization Name'\n\n";
        echo "    -r\n";
        echo "        reply-to email - e.g. -r replytothisemail@example.com\n\n";
        echo "    -b\n";
        echo "        bcc email - e.g. -b bcc@example.com\n\n";
        echo "    -s\n";
        echo "        signature - e.g. -s 'Sincerely, John Smith'\n\n";
        echo "    -H\n";
        echo "        is signature HTML (empty value or 1 for yes, 0 for no) e.g. -H\n\n";
        echo "    -S\n";
        echo "        should this be set as a default identity for the user (only 1 available so it disables all other. Empty value or 1 for yes, 0 for no) e.g. -S 1\n\n";;
  
        exit;
    }

    $username = getOptionValue($options, 'username', '', false, true, "Enter the username e.g. -u user@example.com");
    $identity_id = getOptionValue($options, 'identity_id', '', false, true, "Enter the identity id e.g. -i 70");
  
    $updated_identity = [];

    if (isset($options['email'])) {
        validateEmail($options['email'], 'email');
    } 
    if (isset($options['is_html_signature'])) {
        validateBoolean($options['is_html_signature'], 'is signature HTML (H)');
    }
    if (isset($options['bcc_email'])) {
        validateEmail($options['bcc_email'], 'bcc email');
    }
    if (isset($options['reply_to_email'])) {
        validateEmail($options['reply_to_email'], 'reply-to email');
    }

    $setAsDefault = false;
    if (isset($options['is_default'])) {
        validateBoolean($options['is_default'], 'is default identity (S)');

        $setAsDefault = filter_var($options['is_default'], FILTER_VALIDATE_BOOLEAN);
    }

    $email = getOptionValue($options, 'email', NULL, false, false);
    $name = getOptionValue($options, 'name', NULL, false, false);
    $organization = getOptionValue($options, 'organization', NULL, false, false);
    $signature = getOptionValue($options, 'signature', NULL, false, false);
    $html_signature = getOptionValue($options, 'is_html_signature', NULL, true, false);
    $bcc = getOptionValue($options, 'bcc_email', NULL, false, false);
    $reply_to = getOptionValue($options, 'reply_to_email', NULL, false, false);

    if ($email !== NULL) {
        $updated_identity['email'] = $email;
    }
    if ($name !== NULL) {
        $updated_identity['name'] = $name;
    }
    if ($organization !== NULL) {
        $updated_identity['organization'] = $organization;
    }
    if ($signature !== NULL) {
        $updated_identity['signature'] = $signature;
    }
    if ($html_signature !== NULL) {
        $updated_identity['html_signature'] = $html_signature;
    }
    if ($bcc !== NULL) {
        $updated_identity['bcc'] = $bcc;
    }
    if ($reply_to !== NULL) {
        $updated_identity['reply-to'] = $reply_to;
    }

    if (count($updated_identity) === 0) {
        rcube::raise_error("No attributes changed. Set some new values.");

        exit;
    }

    $host = rcube_utils::get_host($options);
    $user = get_user($username, $host);

    if (!$user) {
        exit;
    }

    $identity = $user->update_identity($identity_id, $updated_identity);

    if (!$identity) {
        rcube::raise_error("Identity not updated. Either the identity id is incorrect or provided values are invalid.");

        exit;
    }

    if ($setAsDefault) {
        $user->set_default($id);
    }

    echo "Identity updated successfully.\n";
}

// Helpers

function getOptionValue($options, $key, $fallback, $isBoolean, $isMandatory, $message = '') {
    $isValid = false;

    if (isset($options[$key])) {
        if ($isBoolean || !is_bool($options[$key])) {
            $isValid = true; 
        }
    }

    if ($isValid) {
        return $options[$key];
    } else {
        if ($isMandatory) {
            rcube::raise_error($message);

            exit;
        }
    }

    return $fallback;
}

function validateEmail($email, $fieldName) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        rcube::raise_error("invalid $fieldName format");

        exit;
    }
}

function validateBoolean($value, $fieldName) {
    if (!is_bool($value) && $value !== '0' && $value !== '1') {
        rcube::raise_error("$fieldName can either be set to 1 (true), 0 (false) or without a value (true)");

        exit;
    }
}

function echoIdentities($identities) {
    for ($i = 0; $i < count($identities); $i++) {
        foreach ($identities[$i] as $key => $val) {
            $diff = 17 - strlen($key);
            $separator = $diff > 0 ? str_repeat(' ', $diff) : '';

            echo "$key$separator- $val\n";
        }    

        if ($i < count($identities) - 1) {
            echo "\n-----\n\n";
        }
    }
}

function get_user($username, $host)
{
    $rcmail = rcube::get_instance();

    $db = $rcmail->get_dbh();

    // find user in local database
    $user = rcube_user::query($username, $host);

    if (empty($user)) {
        rcube::raise_error("User does not exist: $username");
    }

    return $user;
}
