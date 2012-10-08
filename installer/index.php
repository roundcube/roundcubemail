<?php

/*
 +-------------------------------------------------------------------------+
 | Roundcube Webmail setup tool                                            |
 | Version 0.8                                                             |
 |                                                                         |
 | Copyright (C) 2009-2012, The Roundcube Dev Team                         |
 |                                                                         |
 | This program is free software: you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License (with exceptions   |
 | for skins & plugins) as published by the Free Software Foundation,      |
 | either version 3 of the License, or (at your option) any later version. |
 |                                                                         |
 | This file forms part of the Roundcube Webmail Software for which the    |
 | following exception is added: Plugins and Skins which merely make       |
 | function calls to the Roundcube Webmail Software, and for that purpose  |
 | include it by reference shall not be considered modifications of        |
 | the software.                                                           |
 |                                                                         |
 | If you wish to use this file in another project or create a modified    |
 | version that will not be part of the Roundcube Webmail Software, you    |
 | may remove the exception above and use this source code under the       |
 | original version of the license.                                        |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the            |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License       |
 | along with this program.  If not, see http://www.gnu.org/licenses/.     |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                          |
 +-------------------------------------------------------------------------+

 $Id$

*/

ini_set('error_reporting', E_ALL&~E_NOTICE);
ini_set('display_errors', 1);

define('INSTALL_PATH', realpath(dirname(__FILE__) . '/../').'/');
define('RCMAIL_CONFIG_DIR', INSTALL_PATH . 'config');
define('RCMAIL_CHARSET', 'UTF-8');

$include_path  = INSTALL_PATH . 'program/lib' . PATH_SEPARATOR;
$include_path .= INSTALL_PATH . 'program' . PATH_SEPARATOR;
$include_path .= INSTALL_PATH . 'program/include' . PATH_SEPARATOR;
$include_path .= ini_get('include_path');

set_include_path($include_path);

require_once 'utils.php';
require_once 'main.inc';

session_start();

$RCI = rcube_install::get_instance();
$RCI->load_config();

if (isset($_GET['_getfile']) && in_array($_GET['_getfile'], array('main', 'db'))) {
  $filename = $_GET['_getfile'] . '.inc.php';
  if (!empty($_SESSION[$filename])) {
    header('Content-type: text/plain');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    echo $_SESSION[$filename];
    exit;
  }
  else {
    header('HTTP/1.0 404 Not found');
    die("The requested configuration was not found. Please run the installer from the beginning.");
  }
}

if ($RCI->configured && ($RCI->getprop('enable_installer') || $_SESSION['allowinstaller']) &&
    isset($_GET['_mergeconfig']) && in_array($_GET['_mergeconfig'], array('main', 'db'))) {
  $filename = $_GET['_mergeconfig'] . '.inc.php';

  header('Content-type: text/plain');
  header('Content-Disposition: attachment; filename="'.$filename.'"');

  $RCI->merge_config();
  echo $RCI->create_config($_GET['_mergeconfig'], true);
  exit;
}

// go to 'check env' step if we have a local configuration
if ($RCI->configured && empty($_REQUEST['_step'])) {
  header("Location: ./?_step=1");
  exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Roundcube Webmail Installer</title>
<meta name="Robots" content="noindex,nofollow" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="styles.css" />
<script type="text/javascript" src="client.js"></script>
</head>

<body>

<div id="banner">
  <div class="banner-bg"></div>
  <div class="banner-logo"><a href="http://roundcube.net"><img src="images/roundcube_logo.png" width="210" height="55" border="0" alt="Roundcube - open source webmail software" /></a></div>
</div>

<div id="topnav">
  <a href="http://trac.roundcube.net/wiki/Howto_Install">How-to Wiki</a>
</div>

<div id="content">

<?php

  // exit if installation is complete
  if ($RCI->configured && !$RCI->getprop('enable_installer') && !$_SESSION['allowinstaller']) {
    // header("HTTP/1.0 404 Not Found");
    echo '<h2 class="error">The installer is disabled!</h2>';
    echo '<p>To enable it again, set <tt>$rcmail_config[\'enable_installer\'] = true;</tt> in RCMAIL_CONFIG_DIR/main.inc.php</p>';
    echo '</div></body></html>';
    exit;
  }

?>

<h1>Roundcube Webmail Installer</h1>

<ol id="progress">
<?php
  $include_steps = array(
    1 => './check.php',
    2 => './config.php',
    3 => './test.php',
  );

  if (!in_array($RCI->step, array_keys($include_steps))) {
    $RCI->step = 1;
  }

  foreach (array('Check environment', 'Create config', 'Test config') as $i => $item) {
    $j = $i + 1;
    $link = ($RCI->step >= $j || $RCI->configured) ? '<a href="./index.php?_step='.$j.'">' . Q($item) . '</a>' : Q($item);
    printf('<li class="step%d%s">%s</li>', $j+1, $RCI->step > $j ? ' passed' : ($RCI->step == $j ? ' current' : ''), $link);
  }
?>
</ol>

<?php

include $include_steps[$RCI->step];

?>
</div>

<div id="footer">
  Installer by the Roundcube Dev Team. Copyright &copy; 2008-2012 – Published under the GNU Public License;&nbsp;
  Icons by <a href="http://famfamfam.com">famfamfam</a>
</div>
</body>
</html>
