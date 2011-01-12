<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/clisetup.php                                          |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2010, The Roundcube Dev Team                            |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Setup the command line environment and provide some utitlity        |
 |   functions.                                                          |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

if (php_sapi_name() != 'cli') {
  die('Not on the "shell" (php-cli).');
}

require_once 'iniset.php';


/**
 * Parse commandline arguments into a hash array
 */
function get_opt($aliases=array())
{
	$args = array();
	for ($i=1; $i<count($_SERVER['argv']); $i++)
	{
		$arg = $_SERVER['argv'][$i];
		if (substr($arg, 0, 2) == '--')
		{
			$sp = strpos($arg, '=');
			$key = substr($arg, 2, $sp - 2);
			$value = substr($arg, $sp+1);
		}
		else if ($arg{0} == '-')
		{
			$key = substr($arg, 1);
			$value = $_SERVER['argv'][++$i];
		}
		else
			continue;

		$args[$key] = preg_replace(array('/^["\']/', '/["\']$/'), '', $value);
		
		if ($alias = $aliases[$key])
			$args[$alias] = $args[$key];
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