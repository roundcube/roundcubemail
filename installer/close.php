<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

if (!class_exists('rcmail_install', false) || !isset($RCI)) {
    exit('Not allowed! Please use installer.php instead.');
}

?>

<p class="warning">After completing the installation and the final tests please <b>remove</b> the
installer.php file from the document root of the webserver or make sure that
<tt>enable_installer</tt> option in <tt>config.inc.php</tt> is disabled.</p>

<p>The installer may expose sensitive configuration data like server passwords and encryption keys
to the public. Make sure you cannot access it from your browser anymore.</p>

<p>The installer also created a session in your browser. Pressing the button below
will destroy the session.<br><br></p>

<form action="?_step=1" method="post">
  <input type="hidden" name="_destroy" value="<?php echo session_id(); ?>">
  <input type="submit" value="CLOSE" />
</form>
