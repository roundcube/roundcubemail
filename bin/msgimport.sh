#!/usr/bin/php -qC 
<?php

define('INSTALL_PATH', preg_replace('/bin\/$/', '', getcwd()) . '/');
ini_set('memory_limit', -1);

require_once INSTALL_PATH.'program/include/iniset.php';

/**
 * Parse commandline arguments into a hash array
 */
function get_args($aliases=array())
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
	print "Usage:  msgimport -h imap-host -u user-name -f message-file\n";
	print "--host   IMAP host\n";
	print "--user   IMAP user name\n";
	print "--file   Message file to upload\n";
}


// get arguments
$args = get_args(array('h' => 'host', 'u' => 'user', 'p' => 'pass', 'f' => 'file')) + array('host' => 'localhost');

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
echo "Password: ";
$args['pass'] = trim(fgets(STDIN));

// clear password input
echo chr(8)."\rPassword: ".str_repeat("*", strlen($args['pass']))."\n";

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
	print "Uploading message...\n";
	
	// upload message from file
	if  ($IMAP->save_message('INBOX', file_get_contents($args['file'])))
		print "Message successfully added to INBOX.\n";
	else
		print "Adding message failed!\n";
}
else
{
	print "IMAP login failed.\n";
}

?>