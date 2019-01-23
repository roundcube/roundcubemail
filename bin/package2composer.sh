#!/usr/bin/env php
<?php
/*
 +-----------------------------------------------------------------------+
 | bin/package2composer.sh                                               |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2013, The Roundcube Dev Team                            |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |  Convert a plugin's package.xml file into a composer.json description |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <thomas@roundcube.net>                       |
 +-----------------------------------------------------------------------+
*/

ini_set('error_reporting', E_ALL & ~E_NOTICE);

list(, $filename, $vendor) = $_SERVER['argv'];

if (!$filename || !is_readable($filename)) {
    die("Invalid input file name!\nUsage: " . $_SERVER['argv'][0] . " XMLFILE VENDOR\n");
}

if (!$vendor) {
    $vendor = 'anonymous';
}

$package = new SimpleXMLElement(file_get_contents($filename));

$data = array(
    'name' => $vendor . '/' . strval($package->name),
    'type' => 'roundcube-plugin',
    'description' => trim(strval($package->description), '- ') ? trim(strval($package->description)) : trim(strval($package->summary)),
    'homepage' => strval($package->uri),
    'license' => 'GPLv3+',
    'version' => strval($package->version->release),
    'authors' => array(),
    'repositories' => array(
        array('type' => 'composer', 'url' => 'https://plugins.roundcube.net'),
    ),
    'require' => array(
        'php' => '>=5.3.0',
        'roundcube/plugin-installer' => '>=0.1.3',
    ),
);

if ($package->license) {
    $data['license'] = strval($package->license);
}

if ($package->lead) {
    foreach ($package->lead as $lead) {
        if (strval($lead->active) == 'no') {
            continue;
        }
        $data['authors'][] = array(
            'name' => strval($lead->name),
            'email' => strval($lead->email),
            'role' => 'Lead',
        );
    }
}

if ($devs = $package->developer) {
    foreach ($package->developer as $dev) {
        $data['authors'][] = array(
            'name' => strval($dev->name),
            'email' => strval($dev->email),
            'role' => 'Developer',
        );
    }
}

if ($package->dependencies->required->extension) {
    foreach ($package->dependencies->required->extension as $ext) {
        $data['require']['ext-' . strval($ext->name)] = '*';
    }
}

// remove empty values
$data = array_filter($data);

// use the JSON encoder from the Composer package
if (is_file('composer.phar')) {
    include 'phar://composer.phar/src/Composer/Json/JsonFile.php';
    echo \Composer\Json\JsonFile::encode($data);
}
// PHP 5.4's json_encode() does the job, too
else if (defined('JSON_PRETTY_PRINT')) {
    $flags = defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT & JSON_UNESCAPED_SLASHES : 0;
    echo json_encode($data, $flags);
}
else {
    fwrite(STDERR,
"FAILED! composer.phar not found in current directory.

Please download it from http://getcomposer.org/download/ or with
  curl -s http://getcomposer.org/installer | php
");
}

echo "\n";

