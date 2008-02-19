<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>RoundCube Webmail Installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="styles.css" />
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

<h1>RoundCube Webmail Installer</h1>

<?php

  require_once 'rcube_install.php';
  $RCI = new rcube_install();

?>

<ol id="progress">
<?php
  
  foreach (array('Check environment', 'Create config', 'Test config') as $i => $item) {
    $j = $i + 1;
    $link = $RCI->step > $j ? '<a href="./index.php?_step='.$j.'">' . Q($item) . '</a>' : Q($item);
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
  Installer by the RoundCube Dev Team. Copyright &copy; 2008 - Published under the GNU Public License
</div>
</body>
</html>
