#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | bin/install-jsdeps.sh                                                 |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2016, The Roundcube Dev Team                            |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Utility script to fetch and install all 3rd party javascript        |
 |   libraries unsed in Roundcube from source.                           |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <thomas@roundcube.net>                       |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/' );

require_once INSTALL_PATH . 'program/include/clisetup.php';

if (!function_exists('exec')) {
  die("PHP exec() function is required. Check disable_functions in php.ini\n");
}

$SOURCES = json_decode(file_get_contents(INSTALL_PATH . 'jsdeps.json'), true);

if (empty($SOURCES['dependencies'])) {
  die("ERROR: Failed to read sources from " . INSTALL_PATH . "jsdeps.json\n");
}

$CURL     = trim(`which curl`);
$WGET     = trim(`which wget`);
$UNZIP    = trim(`which unzip`);
$FILEINFO = trim(`which file`);

if (($CACHEDIR = getenv("CACHEDIR")) && is_writeable($CACHEDIR)) {
  // use $CACHEDIR
}
else if (is_writeable(INSTALL_PATH . 'temp/js_cache') || @mkdir(INSTALL_PATH . 'temp/js_cache', 0774, true)) {
  $CACHEDIR = INSTALL_PATH . 'temp/js_cache';
}
else {
  $CACHEDIR = sys_get_temp_dir();
}


//////////////// License definitions

$LICENSES = array();
$LICENSES['MIT'] = <<<EOM
 * Licensed under the MIT licenses
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

EOM;

$LICENSES['GPLv3'] = <<<EOG
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.

EOG;

$LICENSES['LGPL'] = <<<EOL
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.

EOL;


//////////////// Functions

/**
 * Fetch package file from source
 */
function fetch_from_source($package, $useCache = true, &$filetype = null)
{
  global $CURL, $WGET;

  $cache_file = extract_filetype($package, $filetype);

  if (!is_readable($cache_file) || !$useCache) {
    if (empty($CURL) && empty($WGET)) {
      die("ERROR: Required program 'wget' or 'curl' not found\n");
    }

    $url = str_replace('$v', $package['version'], $package['url']);

    echo "Fetching $url\n";

    if ($CURL)
        exec(sprintf('%s -L -s %s -o %s', $CURL, escapeshellarg($url), $cache_file), $out, $retval);
    else
        exec(sprintf('%s -q %s -O %s', $WGET, escapeshellarg($url), $cache_file), $out, $retval);

    // Try Github API as a fallback (#6248)
    if ($retval !== 0 && $package['api_url']) {
      $url    = str_replace('$v', $package['version'], $package['api_url']);
      $header = 'Accept:application/vnd.github.v3.raw';

      echo "Fetching failed. Using Github API on $url\n";

      if ($CURL)
        exec(sprintf('%s -L -H %s -s %s -o %s', $CURL, escapeshellarg($header), escapeshellarg($url), $cache_file), $out, $retval);
      else
        exec(sprintf('%s --header %s -q %s -O %s', $WGET, escapeshellarg($header), escapeshellarg($url), $cache_file), $out, $retval);
    }

    if ($retval !== 0) {
      die("ERROR: Failed to download source file from " . $url . "\n");
    }
  }

  return $cache_file;
}

/**
 * Returns package source file location and type
 */
function extract_filetype($package, &$filetype = null)
{
  global $FILEINFO, $CACHEDIR;

  $filetype   = pathinfo($package['url'], PATHINFO_EXTENSION) ?: 'tmp';
  $cache_file = $CACHEDIR . '/' . $package['lib'] . '-' . $package['version'] . '.' . $filetype;

  if (empty($FILEINFO)) {
    die("ERROR: Required program 'file' not found\n");
  }

  // detect downloaded/cached file type
  exec(sprintf('%s -b %s', $FILEINFO, $cache_file), $out);
  if (stripos($out[0], 'zip') === 0) {
    $filetype = 'zip';
  }

  return $cache_file;
}

/**
 * Create a destination javascript file with copyright and license header
 */
function compose_destfile($package, $srcfile)
{
  global $LICENSES;

  $header = sprintf("/**\n * %s - v%s\n *\n", $package['name'], $package['version']);

  if (!empty($package['source'])) {
    $header .= " * @source " . str_replace('$v', $package['version'], $package['source']) . "\n";
    $header .= " *\n";
  }

  if (!empty($package['license']) && isset($LICENSES[$package['license']])) {
    $header .= " * @licstart  The following is the entire license notice for the\n";
    $header .= " * JavaScript code in this file.\n";
    $header .= " *\n";
    if (!empty($package['copyright'])) {
      $header .= " * " . $package['copyright'] . "\n";
      $header .= " *\n";
    }

    $header .= $LICENSES[$package['license']];
    $header .= " *\n";
    $header .= " * @licend  The above is the entire license notice\n";
    $header .= " * for the JavaScript code in this file.\n";
  }

  $header .= " */\n";

  if (file_put_contents(INSTALL_PATH . $package['dest'], $header . file_get_contents($srcfile))) {
    echo "Wrote file " . INSTALL_PATH . $package['dest'] . "\n";
  }
  else {
    die("ERROR: Failed to write destination file " . INSTALL_PATH . $package['dest'] . "\n");
  }
}

/**
 * Extract a Zip archive into the destination specified by the package config
 */
function extract_zipfile($package, $srcfile)
{
  global $UNZIP, $CACHEDIR;

  if (empty($UNZIP)) {
    die("ERROR: Required program 'unzip' not found\n");
  }

  $destdir = INSTALL_PATH . $package['dest'];
  if (!is_dir($destdir)) {
    mkdir($destdir, 0775, true);
  }

  if (!is_writeable($destdir)) {
    die("ERROR: Cannot write to destination directory $destdir\n");
  }

  // pick files from zip archive
  if (!empty($package['pick'])) {
    foreach ($package['pick'] as $pattern) {
      echo "Extracting files $pattern into $destdir\n";
      exec(sprintf('%s -o %s %s -d %s', $UNZIP, escapeshellarg($srcfile), escapeshellarg($pattern), $destdir), $out, $retval);
      if ($retval !== 0) {
        echo "ERROR: Failed to unpack $pattern; " . join('; ' . $out) . "\n";
      }
    }
  }
  // unzip the archive and map source to dest files/directories
  else if (!empty($package['map'])) {
    $extract = $CACHEDIR . '/' . $package['lib'] . '-extract';
    if (!is_dir($extract)) {
      mkdir($extract, 0774, true);
    }

    $zip_command = '%s -' . ($package['flat'] ? 'j' : 'o') . ' %s -d %s';
    exec(sprintf($zip_command, $UNZIP, escapeshellarg($srcfile), $extract), $out, $retval);

    // get the root folder of the extracted package
    $extract_tree = glob("$extract/*", GLOB_ONLYDIR);
    $sourcedir    = count($extract_tree) ? $extract_tree[0] : $extract;

    foreach ($package['map'] as $src => $dest) {
      echo "Installing $sourcedir/$src into $destdir/$dest\n";

      // make sure the destination's parent directory exists
      if (strpos($dest, '/') !== false) {
        $parentdir = dirname($destdir . '/' . $dest);
        if (!is_dir($parentdir)) {
          mkdir($parentdir, 0775, true);
        }
      }

      // avoid copying source directory as a child into destination
      if (is_dir($sourcedir . '/' . $src) && is_dir($destdir . '/' . $dest)) {
        exec(sprintf('rm -rf %s/%s', $destdir, $dest));
      }

      exec(sprintf('mv -f %s/%s %s/%s', $sourcedir, $src, $destdir, $dest), $out, $retval);
      if ($retval !== 0) {
        echo "ERROR: Failed to move $src into $destdir/$dest; " . join('; ' . $out) . "\n";
      }
    }

    // remove temp extraction dir
    exec('rm -rf ' . $extract);
  }
  // extract the archive into the destination directory
  else {
    echo "Extracting zip archive into $destdir\n";
    exec(sprintf('%s -o %s -d %s', $UNZIP, escapeshellarg($srcfile), $destdir), $out, $retval);
    if ($retval !== 0) {
      echo "ERROR: Failed to unzip $srcfile; " . join('; ' . $out) . "\n";
    }
  }

  // remove some files from the destination
  if (!empty($package['omit'])) {
    foreach ((array)$package['omit'] as $glob) {
      exec(sprintf('rm -rf %s/%s', $destdir, escapeshellarg($glob)));
    }
  }

  // prepend license header to extracted files
  if (!empty($package['addlicense'])) {
    foreach ((array)$package['addlicense'] as $filename) {
      $pkg = $package;
      $pkg['dest'] = $package['dest'] . '/' . $filename;
      compose_destfile($pkg, $destdir . '/' . $filename);
    }
  }
}

/**
 * Delete the package destination file/dir
 */
function delete_destfile($package)
{
  $destdir = INSTALL_PATH . ($package['rm'] ?: $package['dest']);

  if (file_exists($destdir)) {
    if (PHP_OS === 'Windows') {
      exec(sprintf("rd /s /q %s", escapeshellarg($destdir)));
    }
    else {
      exec(sprintf("rm -rf %s", escapeshellarg($destdir)));
    }
  }
}


//////////////// Execution

$args = rcube_utils::get_opt(array('f' => 'force:bool', 'd' => 'delete:bool', 'g' => 'get:bool', 'e' => 'extract:bool'))
        + array('force' => false, 'delete' => false, 'get' => false, 'extract' => false);
$WHAT = $args[0];
$useCache = !$args['force'] && !$args['get'];

if (!$args['get'] && !$args['extract'] && !$args['delete']) {
  $args['get'] = $args['extract'] = 1;
}

foreach ($SOURCES['dependencies'] as $package) {
  if (!isset($package['name'])) {
    $package['name'] = $package['lib'];
  }

  if ($WHAT && $package['lib'] !== $WHAT) {
    continue;
  }

  if ($args['delete']) {
    delete_destfile($package);
    continue;
  }

  if ($args['get']) {
    $srcfile = fetch_from_source($package, $useCache, $filetype);
  }
  else {
    $srcfile = extract_filetype($package, $filetype);
  }

  if (!empty($package['sha1']) && ($sum = sha1_file($srcfile)) !== $package['sha1']) {
    die("ERROR: Incorrect sha1 sum of $srcfile. Expected: {$package['sha1']}, got: $sum\n");
  }

  if ($args['extract']) {
    echo "Installing {$package['name']}...\n";

    if ($filetype === 'zip') {
      extract_zipfile($package, $srcfile);
    }
    else {
      compose_destfile($package, $srcfile);
    }

    echo "Done.\n";
  }
}
