#!/usr/bin/env php
<?php

define('INSTALL_PATH', realpath(dirname(__FILE__) . '/..') . '/' );
ini_set('memory_limit', -1);

require_once INSTALL_PATH.'program/include/clisetup.php';

function print_usage()
{
	print "Usage:  msgexport -h imap-host -u user-name -m mailbox name\n";
	print "--host   IMAP host\n";
	print "--user   IMAP user name\n";
	print "--mbox   Folder name, set to '*' for all\n";
	print "--file   Output file\n";
}

function vputs($str)
{
	$out = $GLOBALS['args']['file'] ? STDOUT : STDERR;
	fwrite($out, $str);
}

function progress_update($pos, $max)
{
	$percent = round(100 * $pos / $max);
	vputs(sprintf("%3d%% [%-51s] %d/%d\033[K\r", $percent, @str_repeat('=', $percent / 2) . '>', $pos, $max));
}

function export_mailbox($mbox, $filename)
{
	global $IMAP;

	$IMAP->set_mailbox($mbox);

	vputs("Getting message list of {$mbox}...");
	vputs($IMAP->messagecount()." messages\n");

	if ($filename)
	{
		if (!($out = fopen($filename, 'w')))
		{
			vputs("Cannot write to output file\n");
			return;
		}
		vputs("Writing to $filename\n");
	}
	else
		$out = STDOUT;

	for ($count = $IMAP->messagecount(), $i=1; $i <= $count; $i++)
	{
		$headers = $IMAP->get_headers($i, null, false);
		$from = current($IMAP->decode_address_list($headers->from, 1, false));
		
		fwrite($out, sprintf("From %s %s UID %d\n", $from['mailto'], $headers->date, $headers->uid));
		fwrite($out, $IMAP->conn->fetchPartHeader($mbox, $i));
		fwrite($out, $IMAP->conn->handlePartBody($mbox, $i));
		fwrite($out, "\n\n\n");
		
		progress_update($i, $count);
	}
	vputs("\ncomplete.\n");
	
	if ($filename)
		fclose($out);
}


// get arguments
$args = get_opt(array('h' => 'host', 'u' => 'user', 'p' => 'pass', 'm' => 'mbox', 'f' => 'file')) + array('host' => 'localhost', 'mbox' => 'INBOX');

if ($_SERVER['argv'][1] == 'help')
{
	print_usage();
	exit;
}
else if (!$args['host'])
{
	vputs("Missing required parameters.\n");
	print_usage();
	exit;
}

// prompt for username if not set
if (empty($args['user']))
{
	vputs("IMAP user: ");
	$args['user'] = trim(fgets(STDIN));
}

// prompt for password
$args['pass'] = prompt_silent("Password: ");


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
	vputs("IMAP login successful.\n");
	
	$filename = null;
	$mailboxes = $args['mbox'] == '*' ? $IMAP->list_mailboxes(null) : array($args['mbox']);

	foreach ($mailboxes as $mbox)
	{
		if ($args['file'])
			$filename = preg_replace('/\.[a-z0-9]{3,4}$/i', '', $args['file']) . asciiwords($mbox) . '.mbox';
		else if ($args['mbox'] == '*')
			$filename = asciiwords($mbox) . '.mbox';
			
		if ($args['mbox'] == '*' && in_array(strtolower($mbox), array('junk','spam','trash')))
			continue;

		export_mailbox($mbox, $filename);
	}
}
else
{
	vputs("IMAP login failed.\n");
}

?>
