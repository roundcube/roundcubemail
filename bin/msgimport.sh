#!/usr/bin/php
<?php

define('INSTALL_PATH', realpath(dirname(__FILE__) . '/..') . '/' );
ini_set('memory_limit', -1);

require_once INSTALL_PATH.'program/include/iniset.php';

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

function print_usage()
{
	print "Usage:  msgimport -h imap-host -u user-name -m mailbox -f message-file\n";
	print "--host   IMAP host\n";
	print "--user   IMAP user name\n";
	print "--mbox   Target mailbox\n";
	print "--file   Message file to upload\n";
}


// get arguments
$args = get_opt(array('h' => 'host', 'u' => 'user', 'p' => 'pass', 'm' => 'mbox', 'f' => 'file')) + array('host' => 'localhost', 'mbox' => 'INBOX');

if ($_SERVER['argv'][1] == 'help')
{
	print_usage();
	exit;
}
else if (!($args['host'] && $args['file']))
{
	print "Missing required parameters.\n";
	print_usage();
	exit;
}
else if (!is_file($args['file']))
{
	print "Cannot read message file\n";
	exit;
}

// prompt for username if not set
if (empty($args['user']))
{
	//fwrite(STDOUT, "Please enter your name\n");
	echo "IMAP user: ";
	$args['user'] = trim(fgets(STDIN));
}

// prompt for password
if (empty($args['pass']))
{
	echo "Password: ";
	$args['pass'] = trim(fgets(STDIN));

	// clear password input
	echo chr(8)."\rPassword: ".str_repeat("*", strlen($args['pass']))."\n";
}

// parse $host URL
$a_host = parse_url($args['host']);
if ($a_host['host'])
{
	$host = $a_host['host'];
	$imap_ssl = (isset($a_host['scheme']) && in_array($a_host['scheme'], array('ssl','imaps','tls'))) ? TRUE : FALSE;
	$imap_port = isset($a_host['port']) ? $a_host['port'] : ($imap_ssl ? 993 : 143);
}
else
{
	$host = $args['host'];
	$imap_port = 143;
}

// instantiate IMAP class
$IMAP = new rcube_imap(null);

// try to connect to IMAP server
if ($IMAP->connect($host, $args['user'], $args['pass'], $imap_port, $imap_ssl))
{
	print "IMAP login successful.\n";
	print "Uploading messages...\n";
	
	$count = 0;
	$message = $lastline = '';
	
	$fp = fopen($args['file'], 'r');
	while (($line = fgets($fp)) !== false)
	{
		if (preg_match('/^From\s+/', $line) && $lastline == '')
		{
			if (!empty($message))
			{
				if ($IMAP->save_message($args['mbox'], rtrim($message)))
					$count++;
				else
					die("Failed to save message to {$args['mbox']}\n");
				$message = '';
			}
			continue;
		}

		$message .= $line;
		$lastline = rtrim($line);
	}

	if (!empty($message) && $IMAP->save_message($args['mbox'], rtrim($message)))
		$count++;

	// upload message from file
	if ($count)
		print "$count messages successfully added to {$args['mbox']}.\n";
	else
		print "Adding messages failed!\n";
}
else
{
	print "IMAP login failed.\n";
}

?>