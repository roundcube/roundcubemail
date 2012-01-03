#!/usr/bin/env php
<?php
/*

 +-----------------------------------------------------------------------+
 | bin/importgettext.sh                                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2011, The Roundcube Dev Team                            |
 | Licensed under the GNU General Public License                         |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Import localizations from gettext PO format                         |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

define('INSTALL_PATH', realpath(dirname(__FILE__) . '/..') . '/' );
require INSTALL_PATH.'program/include/clisetup.php';

if ($argc < 2) {
	die("Usage: " . basename($argv[0]) . " SRCDIR\n");
}

$srcdir = unslashify(realpath($argv[1]));

if (is_dir($srcdir)) {
	$out = import_dir($srcdir);
}
else if (is_file($srcdir)) {
	$out = import_file($srcdir);
}

// write output files
foreach ($out as $outfn => $texts) {
	$lang = preg_match('!/([a-z]{2}(_[A-Z]{2})?)[./]!', $outfn, $m) ? $m[1] : '';
	$varname = strpos($outfn, 'messages.inc') !== false ? 'messages' : 'labels';
	
	$header = <<<EOF
<?php

/*
 +-----------------------------------------------------------------------+
 | localization/%s/%-51s|
 |                                                                       |
 | Language file of the Roundcube Webmail client                         |
 | Copyright (C) %s, The Roundcube Dev Team                            |
 | Licensed under the GNU General Public License                         |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: %-62s|
 +-----------------------------------------------------------------------+
 @version %s$
*/

$%s = array();

EOF;

	$output = sprintf($header, $lang, $varname.'.inc', date('Y'), $texts['_translator'], '$Id', $varname);

	foreach ($texts as $label => $value) {
	    if (is_array($value)) { var_dump($outfn, $label, $value); exit; }
		if ($label[0] != '_' && strlen($value))
			$output .= sprintf("\$%s['%s'] = '%s';\n", $varname, $label, strtr(addcslashes($value, "'"), array("\r" => '', "\n" => '\n')));
	}

	$output .= "\n";
	$dir = dirname($outfn);
	@mkdir($dir, 664, true);
	if (file_put_contents($outfn, $output))
		echo "-> $outfn\n";
}


/**
 * Convert all .po files in the given src directory
 */
function import_dir($indir)
{
	$out = array();
	foreach (glob($indir.'/*.po') as $fn) {
		$out = array_merge_recursive($out, import_file($fn));
	}
	return $out;
}

/**
 * Convert the given .po file into a Roundcube localization array
 */
function import_file($fn)
{
	$out = array();
	$lines = file($fn);
	$language = '';
	$translator = '';

	$is_header = true;
	$msgid = null;
	$msgstr = '';
	$dests = array();
	foreach ($lines as $i => $line) {
		$line = trim($line);

		// parse header
		if ($is_header && $line[0] == '"') {
			list($key, $val) = explode(": ", preg_replace('/\\\n$/', '', trim($line, '"')), 2);
			switch (strtolower($key)) {
				case 'language':
					$language = expand_langcode($val);
					break;
				case 'last-translator':
					$translator = $val;
					break;
			}
		}

		// empty line
		if ($line == '') {
			if ($msgid && $dests) {
				foreach ($dests as $dest) {
					list($file, $label) = explode(':', $dest);
					$out[$file][$label] = $msgstr;
				}
			}
			
			$msgid = null;
			$msgstr = '';
			$dests = array();
		}

		// meta line
		if ($line[0] == '#') {
			$value = trim(substr($line, 2));
			if ($line[1] == ':')
				$dests[] = str_replace('en_US', $language, $value);
		}
		else if (strpos($line, 'msgid') === 0) {
			$msgid = gettext_decode(substr($line, 6));

			if (!empty($msgid))
				$is_header = false;
		}
		else if (strpos($line, 'msgstr') === 0) {
			$msgstr = gettext_decode(substr($line, 7));
		}
		else if ($msgid && $line[0] == '"') {
			$msgstr .= gettext_decode($line);
		}
		else if ($msgid !== null && $line[0] == '"') {
			$msgid .= gettext_decode($line);
		}
	}

	if ($msgid && $dests) {
		foreach ($dests as $dest) {
			list($file, $label) = explode(':', $dest);
			$out[$file][$label] = $msgstr;
			$out[$file]['_translator'] = $translator;
		}
	}
	
	return $language ? $out : array();
}


function gettext_decode($str)
{
	return stripslashes(trim($str, '"'));
}

/**
 * Translate two-chars language codes to our internally used language identifiers
 */
function expand_langcode($lang)
{
	static $rcube_language_aliases, $rcube_languages;

	if (!$rcube_language_aliases)
		include(INSTALL_PATH . 'program/localization/index.inc');

	if ($rcube_language_aliases[$lang])
		return $rcube_language_aliases[$lang];
	else if (strlen($lang) == 2 && !isset($rcube_languages[$lang]))
		return strtolower($lang) . '_' . strtoupper($lang);
	else
		return $lang;
}


?>