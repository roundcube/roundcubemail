<?php

ini_set('error_reporting', E_ALL&~E_NOTICE);
ini_set('display_errors', 1);

session_start();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>RoundCube Webmail Installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="styles.css" />
<script type="text/javascript" src="client.js"></script>
</head>

<body>

<div id="banner">
  <div id="header">
    <div class="banner-logo"><a href="http://www.roundcube.net"><img src="images/banner_logo.gif" width="200" height="56" border="0" alt="RoundCube Webmal Project" /></a></div>
    <div class="banner-right"><img src="images/banner_right.gif" width="10" height="56" alt="" /></div>
  </div>
  <div id="topnav">
    <a href="http://trac.roundcube.net/wiki/Howto_Install">How-to Wiki</a>
  </div>
 </div>

<div id="content">

<?php

  $docroot = realpath(dirname(__FILE__) . '/../');
  $include_path  = $docroot . '/program/lib' . PATH_SEPARATOR . $docroot . '/program' . PATH_SEPARATOR . ini_get('include_path');
  set_include_path($include_path);

  require_once 'rcube_install.php';
  $RCI = rcube_install::get_instance();
  $RCI->load_config();

  // exit if installation is complete
  if ($RCI->configured && !$RCI->getprop('enable_installer') && !$_SESSION['allowinstaller']) {
    header("HTTP/1.0 404 Not Found");
    echo '<h2 class="error">The installer is disabled!</h2>';
    echo '<p>To enable it again, set <tt>$rcmail_config[\'enable_installer\'] = true;</tt> in config/main.inc.php</p>';
    echo '</div></body></html>';
    exit;
  }
  
?>

<h1>RoundCube Webmail Installer</h1>

<ol id="progress">
<?php
  
  foreach (array('Check environment', 'Create config', 'Test config') as $i => $item) {
    $j = $i + 1;
    $link = ($RCI->step >= $j || $RCI->configured) ? '<a href="./index.php?_step='.$j.'">' . Q($item) . '</a>' : Q($item);
    printf('<li class="step%d%s">%s</li>', $j+1, $RCI->step > $j ? ' passed' : ($RCI->step == $j ? ' current' : ''), $link);
  }
?>
</ol>

<?php
$include_steps = array('welcome.html', 'check.php', 'config.php', 'test.php');

if ($include_steps[$RCI->step]) {
  include $include_steps[$RCI->step];
}
else {
  header("HTTP/1.0 404 Not Found");
  echo '<h2 class="error">Invalid step</h2>';
}

?>
</div>

<div id="footer">
  Installer by the RoundCube Dev Team. Copyright &copy; 2008 - Published under the GNU Public License;&nbsp;
  Icons by <a href="http://famfamfam.com">famfamfam</a>
</div>
</body>
</html>
