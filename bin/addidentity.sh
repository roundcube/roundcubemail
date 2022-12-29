#!/usr/bin/env php

# example 
# ./addidentity.sh -u original.mail@example.com -e identity.email@example.com -n Bob -o 'Organization name' -s '<h2>Sent by Bob</h2>' -b bcc@example.com -r reply@example.com -S 1 -H 1

# Arguments:

# -u username (original)  - e.g. jonesmith@example.com
# -e email (new identity) - e.g. jonesmith@example.com
# -n name                 - e.g. John Smith
# -o organization         - e.g. Company ABC
# -s signature            - e.g. '<h2>Best Wishes</h2>'
# -b bcc                  - e.g. jonesmith@example.com
# -r reply-to             - e.g. jonesmith@example.com
# -H is html signature    - e.g. 0 (off) or 1 (on)
# -S standard (default)   - e.g. 0 (off) or 1 (on)

<?php

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/' );

require INSTALL_PATH.'program/include/clisetup.php';

$options = getopt('u:e:n:o:s:b:r:H:S:');

if (isset($options['u'])) {
  $username = $options['u'];
} else {
  rcube::raise_error("Enter username to create identity to");
  
  exit;
}

$new_identity = [];

if (isset($options['e'])) {
  validateEmail($options['e'], 'email');

  $new_identity['email'] = $options['e'];
} else {
  rcube::raise_error("Enter email e.g. -e somemail@example.com");  
  
  exit;
}

if (isset($options['n'])) {
  $new_identity['name'] = $options['n'];
} else {
  rcube::raise_error("Enter name e.g. -n Bob");
  
  exit;
}

if (isset($options['o'])) {
  $new_identity['organization'] = $options['o'];
}
if (isset($options['s'])) {
  $new_identity['signature'] = $options['s'];
}
if (isset($options['H'])) {
  $new_identity['html_signature'] = $options['H'];
}
if (isset($options['b'])) {
  validateEmail($options['b'], 'bcc email');

  $new_identity['bcc'] = $options['b'];
}
if (isset($options['r'])) {
  validateEmail($options['r'], 'reply-to email');
  
  $new_identity['reply-to'] = $options['r'];
}
if (isset($options['S'])) {
  $new_identity['standard'] = $options['S'];
}

$rcmail = rcube::get_instance();

$args = rcube_utils::get_opt([
        'u' => 'user',
        'h' => 'host',
        'd' => 'dir',
        'x' => 'dry-run',
]);

$host = get_host($args);
$user = get_user($username, $host);

$user->insert_identity($new_identity);

function validateEmail($email, $fieldName) {
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "invalid $fieldName format";

    exit;
  }
}

function get_host($args)
{
    global $rcmail;

    if (empty($args['host'])) {
        $hosts = $rcmail->config->get('default_host', '');
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

function get_user($username, $host)
{
    global $rcmail;

    $db = $rcmail->get_dbh();

    // find user in local database
    $user = rcube_user::query($username, $host);

    if (empty($user)) {
        rcube::raise_error("User does not exist: $username");
    }

    return $user;
}
