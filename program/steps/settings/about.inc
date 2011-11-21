<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/about.inc                                      |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Display license information about program and enabled plugins       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id: identities.inc 4410 2011-01-12 18:25:02Z thomasb $

*/


function rcmail_supportlink($attrib)
{
  global $RCMAIL;
  
  if ($url = $RCMAIL->config->get('support_url')) {
    $label = $attrub['label'] ? $attrub['label'] : 'support';
    $attrib['href'] = $url;
    return html::a($attrib, rcube_label($label));
  }
}

function rcmail_plugins_list($attrib)
{
  global $RCMAIL;

  if (!$attrib['id'])
    $attrib['id'] = 'rcmpluginlist';

  $plugins = array_filter((array) $RCMAIL->config->get('plugins'));
  $plugins = array_flip($plugins);

  foreach ($plugins as $name => $plugin) {
    rcube_plugin_data($name, $plugins);
  }

  if (empty($plugins)) {
    return '';
  }

  ksort($plugins, SORT_LOCALE_STRING);

  $table = new html_table($attrib);

  // add table header
  $table->add_header('name', rcube_label('plugin'));
  $table->add_header('version', rcube_label('version'));
  $table->add_header('license', rcube_label('license'));
  $table->add_header('source', rcube_label('source'));

  foreach ($plugins as $name => $data) {
    $uri = $data['srcuri'];
    if ($uri && stripos($uri, 'http') !== 0) {
      $uri = 'http://' . $uri;
    }
    
    $table->add_row();
    $table->add('name', Q($data['name'] ? $data['name'] : $name));
    $table->add('version', Q($data['version']));
    $table->add('license', $data['license_uri'] ? html::a(array('target' => '_blank', href=> Q($data['license_uri'])),
        Q($data['license'])) : $data['license']);
    $table->add('source', $uri ? html::a(array('target' => '_blank', href=> Q($uri)),
        Q(rcube_label('download'))) : '');
  }

  return $table->show();
}

function rcube_plugin_data($name, &$plugins = array())
{
  // XPaths of plugin metadata elements
  $metadata = array(
    'name'    => 'string(//rc:package/rc:name)',
    'version' => 'string(//rc:package/rc:version/rc:release)',
    'license' => 'string(//rc:package/rc:license)',
    'license_uri' => 'string(//rc:package/rc:license/@uri)',
    'srcuri' => 'string(//rc:package/rc:srcuri)',
  );

  $package = INSTALL_PATH . "/plugins/$name/package.xml";
  if (file_exists($package) && ($file = file_get_contents($package))) {
    $doc = new DOMDocument();
    $doc->loadXML($file);
    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('rc', "http://pear.php.net/dtd/package-2.0");
    $data = array();

    foreach ($metadata as $key => $path) {
      $data[$key] = $xpath->evaluate($path);
    }

    $plugins[$name] = $data;

    // dependent required plugins (can be used, but not included in config)
    $deps = $xpath->evaluate('//rc:package/rc:dependencies/rc:required/rc:package/rc:name');
    $cnt  = $deps->length;

    for ($i=0; $i<$cnt; $i++) {
      $dn = $deps->item($i)->nodeValue;
      if (!array_key_exists($dn, $plugins)) {
        rcube_plugin_data($dn, $plugins);
      }
    }
  }
  else {
    unset($plugins[$name]);
  }
}


$OUTPUT->set_pagetitle(rcube_label('about'));

$OUTPUT->add_handler('supportlink', 'rcmail_supportlink');
$OUTPUT->add_handler('pluginlist', 'rcmail_plugins_list');

$OUTPUT->send('about');
