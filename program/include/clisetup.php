<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/clisetup.php                                          |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2010-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Setup the command line environment and provide some utitlity        |
 |   functions.                                                          |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

if (php_sapi_name() != 'cli') {
  die('Not on the "shell" (php-cli).');
}

require_once INSTALL_PATH . 'program/include/iniset.php';

// Unset max. execution time limit, set to 120 seconds in iniset.php
@set_time_limit(0);

/**
 * Parse commandline arguments into a hash array
 */
function get_opt($aliases = array())
{
    $args = array();

    for ($i=1; $i < count($_SERVER['argv']); $i++) {
        $arg   = $_SERVER['argv'][$i];
        $value = true;
        $key   = null;

        if ($arg[0] == '-') {
            $key = preg_replace('/^-+/', '', $arg);
            $sp  = strpos($arg, '=');
            if ($sp > 0) {
                $key   = substr($key, 0, $sp - 2);
                $value = substr($arg, $sp+1);
            }
            else if (strlen($_SERVER['argv'][$i+1]) && $_SERVER['argv'][$i+1][0] != '-') {
                $value = $_SERVER['argv'][++$i];
            }

            $args[$key] = is_string($value) ? preg_replace(array('/^["\']/', '/["\']$/'), '', $value) : $value;
        }
        else {
            $args[] = $arg;
        }

        if ($alias = $aliases[$key]) {
            $args[$alias] = $args[$key];
        }
    }

    return $args;
}


/**
 * from http://blogs.sitepoint.com/2009/05/01/interactive-cli-password-prompt-in-php/
 */
function prompt_silent($prompt = "Password:")
{
  if (preg_match('/^win/i', PHP_OS)) {
    $vbscript = sys_get_temp_dir() . 'prompt_password.vbs';
    file_put_contents($vbscript, 'wscript.echo(InputBox("' . addslashes($prompt) . '", "", "password here"))');
    $command = "cscript //nologo " . escapeshellarg($vbscript);
    $password = rtrim(shell_exec($command));
    unlink($vbscript);
    return $password;
  }
  else {
    $command = "/usr/bin/env bash -c 'echo OK'";
    if (rtrim(shell_exec($command)) !== 'OK') {
      echo $prompt;
      $pass = trim(fgets(STDIN));
      echo chr(8)."\r" . $prompt . str_repeat("*", strlen($pass))."\n";
      return $pass;
    }
    $command = "/usr/bin/env bash -c 'read -s -p \"" . addslashes($prompt) . "\" mypassword && echo \$mypassword'";
    $password = rtrim(shell_exec($command));
    echo "\n";
    return $password;
  }
}

?>
