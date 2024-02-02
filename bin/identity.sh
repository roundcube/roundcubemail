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
 |   Identities management                                               |
 +-----------------------------------------------------------------------+
 | Author: Vladas K <info@vladasko.com>                                  |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/');

require INSTALL_PATH . 'program/include/clisetup.php';

$options = rcube_utils::get_opt([
    'u' => 'username',
    'e' => 'email',
    'n' => 'name',
    'o' => 'organization',
    's' => 'plain_text_signature',
    'b' => 'bcc_email',
    'r' => 'reply_to_email',
    'h' => 'html_signature',
    'S' => 'is_default',
    'a' => 'attribute',
    'i' => 'identity_id',
]);

$subcommand_executables = [
    'add' => 'add_identity',
    'update' => 'update_identity',
    'delete' => 'delete_identity',
    'list' => 'list_identities',
    'get-attr' => 'get_identity_attr',
];

$program_name = $options[0] ?? '';

if (isset($subcommand_executables[$program_name])) {
    $program = $subcommand_executables[$program_name];

    $program($options);
} else {
    echo "Available sub-commands:\n";
    echo "add       - create a new identity for a user\n";
    echo "delete    - delete an identity (mark as deleted)\n";
    echo "list      - list all identities of a user\n";
    echo "get-attr  - get some attribute of an identity\n";
    echo "update    - update identity\n\n";
}

function get_identity_attr($options)
{
    if (count($options) === 1) {
        echo "get-attr: Prints attribute value of the specified identity.\n\n";
        echo "Mandatory arguments:\n";
        echo "-u <username> - the identity holder e.g. -u mainmail@example.com\n";
        echo "-i <id> - the id of the identity being queried e.g. -i 70 (use list sub-command to get identity id)\n";
        echo "-a <attr-name> - e.g. -a email\n";
        echo "   available attributes: identity_id, user_id, changed, del, standard, name,\n";
        echo "                         organization, email, reply-to, bcc, signature, html_signature.\n\n";
        exit;
    }

    $identity_id = get_option_value($options, 'identity_id', '', false, true, 'Enter the identity id e.g. -i 70');
    $attribute = get_option_value($options, 'attribute', '', false, true, 'Enter the attribute name e.g. -a name');

    $user = get_user($options);

    $identity = $user->get_identity($identity_id);

    if (empty($identity)) {
        rcube::raise_error('Invalid identity ID.', false, true);
    }

    if (isset($identity[$attribute])) {
        $attrValue = $identity[$attribute];

        echo "{$attrValue}\n";
    } else {
        rcube::raise_error('Invalid attribute name. Available attributes: identity_id, user_id, changed, del, standard, name, '
            . 'organization, email, reply-to, bcc, signature, html_signature.', false, true);
    }
}

function list_identities($options)
{
    if (count($options) === 1) {
        echo "list: Prints the attributes of all identities of a user.\n\n";
        echo "Mandatory arguments:\n";
        echo "-u <username> - the identity holder e.g. -u mainmail@example.com\n\n";
        exit;
    }

    $user = get_user($options);

    $identities = $user->list_identities(null, true);

    echo_identities($identities);
}

function delete_identity($options)
{
    $identities_level = get_identities_level();

    if ($identities_level > 1) {
        rcube::raise_error("Identities level doesn't allow this action.", false, true);
    }

    if (count($options) === 1) {
        echo "delete: Deletes an identity.\n";
        echo "This marks the identity as deleted, making it inactive, but it is not removed from the database.\n\n";
        echo "Mandatory arguments:\n";
        echo "-u <username> - the identity holder e.g. -u mainmail@example.com\n";
        echo "-i <id> - the id of the identity being queried e.g. -i 70) use list sub-command to get identity id\n\n";
        exit;
    }

    $identity_id = get_option_value($options, 'identity_id', '', false, true, 'Enter the identity id e.g. -i 70');

    $user = get_user($options);

    $identity = $user->delete_identity($identity_id);

    if (!$identity) {
        rcube::raise_error('Invalid identity ID.');
        exit;
    }

    echo "Identity deleted.\n";
}

function add_identity($options)
{
    $identities_level = get_identities_level();

    if ($identities_level > 1) {
        rcube::raise_error("Identities level doesn't allow this action.", false, true);
    }

    if (count($options) === 1) {
        echo "add: Creates a new identity for a given user.\n\n";
        echo "Mandatory arguments:\n";
        echo "-u <username> - the identity holder e.g. -u mainmail@example.com\n";
        echo "-e <email> - the email of the identity e.g. identityemail@example.com\n";
        echo "-n <name> - the name of the identity - e.g. -n 'John Smith'\n\n";
        echo "Optional arguments:\n";
        echo_shared_options();
        exit;
    }

    $new_identity = [];
    $setAsDefault = false;

    if (isset($options['email'])) {
        validate_email($options['email'], 'email');
    }
    if (isset($options['bcc_email'])) {
        validate_email($options['bcc_email'], 'bcc email');
    }
    if (isset($options['reply_to_email'])) {
        validate_email($options['reply_to_email'], 'reply-to email');
    }
    if (isset($options['is_default'])) {
        validate_boolean($options['is_default'], 'is default identity (S)');
        $setAsDefault = filter_var($options['is_default'], \FILTER_VALIDATE_BOOLEAN);
    }

    $new_identity['email'] = get_option_value($options, 'email', '', false, true, 'Enter the email e.g. -e somemail@example.com');
    $new_identity['name'] = get_option_value($options, 'name', '', false, true, "Enter the name of an identity e.g. -n 'John Smith'");
    $new_identity['organization'] = get_option_value($options, 'organization', '', false, false);

    $new_identity['html_signature'] = 0;
    $new_identity['signature'] = get_option_value($options, 'plain_text_signature', '', false, false);

    if (isset($options['html_signature'])) {
        $new_identity['html_signature'] = 1;
        $new_identity['signature'] = get_option_value($options, 'html_signature', '', false, false);
    }

    $new_identity['bcc'] = get_option_value($options, 'bcc_email', '', false, false);
    $new_identity['reply-to'] = get_option_value($options, 'reply_to_email', '', false, false);

    $user = get_user($options);

    $id = $user->insert_identity($new_identity);

    if ($setAsDefault) {
        $user->set_default($id);
    }

    echo "Identity created successfully with ID: {$id}.\n";
}

function update_identity($options)
{
    $identities_level = get_identities_level();

    if ($identities_level > 1) {
        rcube::raise_error("Identities level doesn't allow this action.", false, true);
    }

    if (count($options) === 1) {
        echo "update: Update an existing identity with provided values.\n\n";
        echo "Mandatory arguments:\n";
        echo "-u <username> - the identity holder e.g. -u mainmail@example.com\n";
        echo "-i <id> - the id of the identity being queried e.g. -i 70 (use list sub-command to get identity id)\n\n";
        echo "Optional arguments (at least one must be specified):\n";
        echo "-e <email> - the email of the identity e.g. identityemail@example.com\n";
        echo "-n <name> - the name of the identity - e.g. -n 'John Smith'\n";
        echo_shared_options();
        exit;
    }

    $identity_id = get_option_value($options, 'identity_id', '', false, true, 'Enter the identity id e.g. -i 70');

    $updated_identity = [];

    if (isset($options['email'])) {
        validate_email($options['email'], 'email');
    }
    if (isset($options['bcc_email'])) {
        validate_email($options['bcc_email'], 'bcc email');
    }
    if (isset($options['reply_to_email'])) {
        validate_email($options['reply_to_email'], 'reply-to email');
    }

    $setAsDefault = false;
    if (isset($options['is_default'])) {
        validate_boolean($options['is_default'], 'is default identity (S)');
        $setAsDefault = filter_var($options['is_default'], \FILTER_VALIDATE_BOOLEAN);
    }

    $email = get_option_value($options, 'email', null, false, false);
    $name = get_option_value($options, 'name', null, false, false);
    $organization = get_option_value($options, 'organization', null, false, false);
    $plain_text_signature = get_option_value($options, 'plain_text_signature', null, false, false);
    $html_signature = get_option_value($options, 'html_signature', null, false, false);
    $bcc = get_option_value($options, 'bcc_email', null, false, false);
    $reply_to = get_option_value($options, 'reply_to_email', null, false, false);

    if ($html_signature !== null) {
        $updated_identity['html_signature'] = 1;
        $updated_identity['signature'] = $html_signature;
    } elseif ($plain_text_signature !== null) {
        $updated_identity['html_signature'] = 0;
        $updated_identity['signature'] = $plain_text_signature;
    }

    if ($email !== null) {
        if ($identities_level > 0) {
            rcube::raise_error("Identities level doesn't allow setting email.", false, true);
        }

        $updated_identity['email'] = $email;
    }
    if ($name !== null) {
        $updated_identity['name'] = $name;
    }
    if ($organization !== null) {
        $updated_identity['organization'] = $organization;
    }
    if ($bcc !== null) {
        $updated_identity['bcc'] = $bcc;
    }
    if ($reply_to !== null) {
        $updated_identity['reply-to'] = $reply_to;
    }

    if (count($updated_identity) === 0) {
        rcube::raise_error('No attributes changed. Set some new values.', false, true);
    }

    $user = get_user($options);

    $identity = $user->update_identity($identity_id, $updated_identity);

    if (!$identity) {
        rcube::raise_error('Identity not updated. Either the identity id is incorrect or provided values are invalid.', false, true);
    }

    if ($setAsDefault) {
        $user->set_default($id);
    }

    echo "Identity updated successfully. ID: {$identity_id}.\n";
}

// Helpers

function get_option_value($options, $key, $fallback, $isBoolean, $isMandatory, $message = '')
{
    $isValid = false;

    if (isset($options[$key])) {
        if ($isBoolean || !is_bool($options[$key])) {
            $isValid = true;
        }
    }

    if ($isValid) {
        return $options[$key];
    }

    if ($isMandatory) {
        rcube::raise_error($message, false, true);
    }

    return $fallback;
}

function validate_email($email, $fieldName)
{
    if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
        rcube::raise_error("invalid {$fieldName} format", false, true);
    }
}

function validate_boolean($value, $fieldName)
{
    if (!is_bool($value) && $value !== '0' && $value !== '1') {
        rcube::raise_error("{$fieldName} can either be set to 1 (true), 0 (false) or without a value (true)", false, true);
    }
}

function echo_identities($identities)
{
    for ($i = 0; $i < count($identities); $i++) {
        foreach ($identities[$i] as $key => $val) {
            $diff = 17 - strlen($key);
            $separator = $diff > 0 ? str_repeat(' ', $diff) : '';

            echo "{$key}{$separator}: {$val}\n";
        }

        if ($i < count($identities) - 1) {
            echo "\n-----\n\n";
        }
    }
}

function echo_shared_options()
{
    echo "-o <organization> - organization name - e.g. -o 'Your Organization Name'\n";
    echo "-r <reply-to> - Reply-To email - e.g. -r replytothisemail@example.com\n";
    echo "-b <bcc> - Bcc email - e.g. -b bcc@example.com\n";
    echo "-s <sig> - Plain text signature (only works if HTML signature is not set) - e.g. -s 'Sincerely, John Smith'\n";
    echo "-h <html-dig> - HTML signature content - e.g. -h '<h1>Sincerely, John Smith</h1>'\n";
    echo "-S <default> - Should this be set as a default identity for the user\n";
    echo "               (only 1 available so it disables all other. Empty value or 1 for yes, 0 for no) e.g. -S 1\n\n";
}

function get_user($options)
{
    $rcmail = rcube::get_instance();

    $db = $rcmail->get_dbh();

    $username = get_option_value($options, 'username', '', false, true, 'Enter the username e.g. -u user@example.com');
    $host = rcmail_utils::get_host($options);

    // find user in local database
    $user = rcube_user::query($username, $host);

    if (empty($user)) {
        rcube::raise_error("User does not exist: {$username}", false, true);
    }

    return $user;
}

function get_identities_level()
{
    $rcmail = rcube::get_instance();
    $identities_level = intval($rcmail->config->get('identities_level', 0));

    return $identities_level;
}
