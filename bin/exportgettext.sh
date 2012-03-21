#!/usr/bin/env php
<?php
/*

 +-----------------------------------------------------------------------+
 | bin/exportgettext.sh                                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2011, The Roundcube Dev Team                            |
 | Licensed under the GNU General Public License                         |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Export PHP-based localization files to PO files for gettext         |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

define('INSTALL_PATH', realpath(dirname(__FILE__) . '/..') . '/' );
require INSTALL_PATH.'program/include/clisetup.php';

if ($argc < 2) {
	die("Usage: " . basename($argv[0]) . " SRCDIR DESTDIR\n");
}

$srcdir = unslashify(realpath($argv[1]));
$destdir = unslashify($argv[2]);
$layout = 'launchpad';  # or 'narro';
$langcode_map = array(
	'hy_AM' => 'hy',
	'ar_SA' => 'ar',
	'az_AZ' => 'az',
	'bg_BG' => 'bg',
	'bs_BA' => 'bs',
	'ca_ES' => 'ca',
	'cs_CZ' => 'cs',
	'cy_GB' => 'cy',
	'da_DK' => 'da',
	'et_EE' => 'et',
	'el_GR' => 'el',
	'eu_ES' => 'eu',
	'fa_IR' => 'fa',
	'ga_IE' => 'ga',
	'ka_GE' => 'ka',
	'gl_ES' => 'gl',
	'he_IL' => 'he',
	'hi_IN' => 'hi',
	'hr_HR' => 'hr',
	'ja_JP' => 'ja',
	'ko_KR' => 'ko',
	'km_KH' => 'km',
	'ms_MY' => 'ms',
	'mr_IN' => 'mr',
	'pl_PL' => 'pl',
	'si_LK' => 'si',
	'sl_SI' => 'sl',
	'sq_AL' => 'sq',
	'sr_CS' => 'sr',
	'sv_SE' => 'sv',
	'uk_UA' => 'uk',
	'vi_VN' => 'vi',
);


// converting roundcube localization dir
if (is_dir($srcdir.'/en_US')) {
	load_en_US($srcdir.'/en_US');
	
	foreach (glob($srcdir.'/*') as $locdir) {
		if (is_dir($locdir)) {
			$lang = basename($locdir);
			//echo "$locdir => $destdir$lang\n";
			convert_dir($locdir, $destdir . ($layout != 'launchpad' ? $lang : ''));
		}
	}
}
// converting single localization directory
else if (is_dir($srcdir)) {
	if (is_file($srcdir.'/en_US.inc'))  // plugin localization
		load_en_US($srcdir.'/en_US.inc');
	else
		load_en_US(realpath($srcdir.'/../en_US'));  // single language
	convert_dir($srcdir, $destdir);
}
// converting a single file
else if (is_file($srcdir)) {
	//load_en_US();
	convert_file($srcdir, $destdir);
}


/**
 * Load en_US localization which is used to build msgids
 */
function load_en_US($fn)
{
	$texts = array();
	
	if (is_dir($fn)) {
		foreach (glob($fn.'/*.inc') as $ifn) {
			include($ifn);
			$texts = array_merge($texts, (array)$labels, (array)$messages);
		}
	}
	else if (is_file($fn)) {
		include($fn);
		$texts = array_merge($texts, (array)$labels, (array)$messages);
	}
	
	$GLOBALS['en_US'] = $texts;
}

/**
 * Convert all .inc files in the given src directory
 */
function convert_dir($indir, $outdir)
{
	global $layout;
	
	if (!is_dir($outdir))  // attempt to create destination dir
		mkdir($outdir, 0777, true);

	foreach (glob($indir.'/*.inc') as $fn) {
		$filename = basename($fn);

		// create subdir for each template (launchpad rules)
		if ($layout == 'launchpad' && preg_match('/^(labels|messages)/', $filename, $m)) {
			$lang = end(explode('/', $indir));
			$destdir = $outdir . '/' . $m[1];
			if (!is_dir($destdir))
				mkdir($destdir, 0777, true);
			$outfn = $destdir . '/' . $lang . '.po';
		}
		else {
			$outfn = $outdir . '/' . preg_replace('/\.[a-z0-9]+$/i', '', basename($fn)) . '.po';
		}

		convert_file($fn, $outfn);
	}
}

/**
 * Convert the given Roundcube localization file into a gettext .po file
 */
function convert_file($fn, $outfn)
{
	global $layout, $langcode_map;

	$basename =  basename($fn);
	$srcname = str_replace(INSTALL_PATH, '', $fn);
	$product = preg_match('!plugins/(\w+)!', $srcname, $m) ? 'roundcube-plugin-' . $m[1] : 'roundcubemail';
	$lang = preg_match('!/([a-z]{2}(_[A-Z]{2})?)[./]!', $outfn, $m) ? $m[1] : '';
	$labels = $messages = $seen = array();

	if (is_dir($outfn))
		$outfn .= '/' . $basename . '.po';

	// launchpad requires the template file to have the same name as the directory
	if (strstr($outfn, '/en_US') && $layout == 'launchpad') {
		$a = explode('/', $outfn);
		array_pop($a);
		$templ = end($a);
		$a[] = $templ . '.pot';
		$outfn = join('/', $a);
		$is_pot = true;
	}
	// launchpad is very picky about file names
	else if ($layout == 'launchpad' && preg_match($regex = '!/([a-z]{2})_([A-Z]{2})!', $outfn, $m)) {
		if ($shortlang = $langcode_map[$lang])
			$outfn = preg_replace($regex, '/'.$shortlang, $outfn);
		else if ($m[1] == strtolower($m[2]))
			$outfn = preg_replace($regex, '/\1', $outfn);
	}

	include($fn);
	$texts = array_merge($labels, $messages);
	
	// write header
	$header = <<<EOF
# Converted from Roundcube PHP localization files
# Copyright (C) 2011 The Roundcube Dev Team
# This file is distributed under the same license as the Roundcube package.
#
#: %s
msgid ""
msgstr ""
"Project-Id-Version: %s\\n"
"Report-Msgid-Bugs-To: \\n"
"%s: %s\\n"
"Last-Translator: \\n"
"Language-Team: Translations <hello@roundcube.net>\\n"
"Language: %s\\n"
"Content-Type: text/plain; charset=UTF-8\\n"
"Content-Transfer-Encoding: 8bit\\n"
EOF;
	
	$out = sprintf($header, $srcname, $product, $is_pot ? "POT-Creation-Date" : "PO-Revision-Date", date('c'), $shortlang ? $shortlang : $lang);
	$out .= "\n";
	
	$messages = array();
	foreach ((array)$texts as $label => $msgstr) {
		$msgid = $is_pot ? $msgstr : ($GLOBALS['en_US'][$label] ?: $label);
		$messages[$msgid][] = $label;
	}
	
	foreach ($messages as $msgid => $labels) {
		$out .= "\n";
		foreach ($labels as $label)
			$out .= "#: $srcname:$label\n";
		$msgstr = $texts[$label];
		$out .= 'msgid ' . gettext_quote($msgid) . "\n";
		$out .= 'msgstr ' . gettext_quote(!$is_pot ? $msgstr : '') . "\n";
	}

	if ($outfn == '-')
		echo $out;
	else {
		echo "$fn\t=>\t$outfn\n";
		file_put_contents($outfn, $out);
	}
}

function gettext_quote($str)
{
	$out = "";
	$lines = explode("\n", wordwrap(stripslashes($str)));
	$last = count($lines) - 1;
	foreach ($lines as $i => $line)
		$out .= '"' . addcslashes($line, '"') . ($i < $last ? ' ' : '') . "\"\n";
	return rtrim($out);
}

?>
