<form action="index.php" method="post">
<input type="hidden" name="_step" value="2" />
<?php

ini_set('display_errors', 1);
require_once '../program/include/rcube_html.inc';

?>
<fieldset>
<legend>General configuration</legend>
<dl>
<!--
<dt class="propname">debug_level</dt>
<dd>
<?php
/*
$value = $RCI->getprop('debug_level');
$check_debug = new checkbox(array('name' => '_debug_level[]'));
echo $check_debug->show(($value & 1) ? 1 : 0 , array('value' => 1, 'id' => 'cfgdebug1'));
echo '<label for="cfgdebug1">Log errors</label><br />';

echo $check_debug->show(($value & 4) ? 4 : 0, array('value' => 4, 'id' => 'cfgdebug4'));
echo '<label for="cfgdebug4">Display errors</label><br />';

echo $check_debug->show(($value & 8) ? 8 : 0, array('value' => 8, 'id' => 'cfgdebug8'));
echo '<label for="cfgdebug8">Verbose display</label><br />';
*/
?>
</dd>
-->

<dt class="propname">product_name</dt>
<dd>
<?php

$input_prodname = new textfield(array('name' => '_product_name', 'size' => 30, 'id' => "cfgprodname"));
echo $input_prodname->show($RCI->getprop('product_name'));

?>
<div>The name of your service (used to compose page titles)</div>
</dd>

<dt class="propname">skin_path</dt>
<dd>
<?php

$input_skinpath = new textfield(array('name' => '_skin_path', 'size' => 30, 'id' => "cfgskinpath"));
echo $input_skinpath->show($RCI->getprop('skin_path'));

?>
<div>Relative path to the skin folder</div>
</dd>

<dt class="propname">temp_dir</dt>
<dd>
<?php

$input_tempdir = new textfield(array('name' => '_temp_dir', 'size' => 30, 'id' => "cfgtempdir"));
echo $input_tempdir->show($RCI->getprop('temp_dir'));

?>
<div>Use this folder to store temp files (must be writebale for webserver)</div>
</dd>

<dt class="propname">log_dir</dt>
<dd>
<?php

$input_logdir = new textfield(array('name' => '_log_dir', 'size' => 30, 'id' => "cfglogdir"));
echo $input_logdir->show($RCI->getprop('log_dir'));

?>
<div>Use this folder to store log files (must be writebale for webserver)</div>
</dd>

<dt class="propname">ip_check</dt>
<dd>
<?php

$check_ipcheck = new checkbox(array('name' => '_ip_check', 'id' => "cfgipcheck"));
echo $check_ipcheck->show(intval($RCI->getprop('ip_check')), array('value' => 1));

?>
<label for="cfgipcheck">Check client IP in session athorization</label><br />

<p class="hint">This increases security but can cause sudden logouts when someone uses a proxy with changeing IPs.</p>
</dd>

<dt class="propname">des_key</dt>
<dd>
<?php

$input_deskey = new textfield(array('name' => '_des_key', 'size' => 30, 'id' => "cfgdeskey"));
echo $input_deskey->show($RCI->getprop('des_key'));

?>
<div>This key is used to encrypt the users imap password before storing in the session record</div>
<p class="hint">It's a random generated string to ensure that every installation has it's own key.
If you enter it manually please provide a string of exactly 24 chars.</p>
</dd>

<dt class="propname">enable_caching</dt>
<dd>
<?php

$check_caching = new checkbox(array('name' => '_enable_caching', 'id' => "cfgcache"));
echo $check_caching->show(intval($RCI->getprop('enable_caching')), array('value' => 1));

?>
<label for="cfgcache">Cache messages in local database</label><br />
</dd>

</dl>
</fieldset>

<fieldset>
<legend>IMAP Settings</legend>
<dl>
<dt class="propname">auto_create_user</dt>
<dd>
<?php

$check_autocreate = new checkbox(array('name' => '_auto_create_user', 'id' => "cfgautocreate"));
echo $check_autocreate->show(intval($RCI->getprop('auto_create_user')), array('value' => 1));

?>
<label for="cfgautocreate">Automatically create a new RoundCube user when log-in the first time</label><br />

<p class="hint">A user is authenticated by the IMAP server but it requires a local record to store settings
and contacts. With this option enabled a new user record will automatically be created once the IMAP login succeeds.</p>

<p class="hint">If this option is disabled, the login only succeeds if there's a matching user-record in the local RoundCube database
what means that you have to create those records manually or disable this option after the first login.</p>
</dd>

</dl>
</fieldset>

<fieldset>
<legend>SMTP Settings</legend>
<dl>
<dd>TBD.</dd>
</dl>
</fieldset>

<fieldset>
<legend>Display settings</legend>
<dl>

<dt class="propname">locale_string</dt>
<dd>
<?php

$input_locale = new textfield(array('name' => '_locale_string', 'size' => 6, 'id' => "cfglocale"));
echo $input_locale->show($RCI->getprop('locale_string'));

?>
<div>The default locale setting. This also defines the language of the login screen.</div>
<p class="hint">Enter a <a href="http://www.faqs.org/rfcs/rfc1766">RFC1766</a> formatted locale name. Examples: en_US, de, de_CH, fr, pt_BR</p>
</dd>

</dl>
</fieldset>

<?php

echo '<p><input type="submit" name="submit" value="UPDATE" ' . ($RCI->failures ? 'disabled' : '') . ' /></p>';


if (!empty($_POST['submit'])) {
  echo "<hr />\n";
  
  echo '<p class="notice">Copy the following configurations and save them in two files (names above the text box)';
  echo ' within the <tt>config/</tt> directory of your RoundCube installation.</p>';
  
  $textbox = new textarea(array('rows' => 20, 'cols' => 60, 'class' => "configfile"));
  
  echo '<div><em>main.inc.php</em></div>';
  echo $textbox->show($RCI->create_config('main'));
  
  echo '<div style="margin-top:1em"><em>db.inc.php</em></div>';
  echo $textbox->show($RCI->create_config('db'));

  echo '<p><input type="button" onclick="location.href=\'./index.php?_step=3\'" value="CONTINUE" /></p>';
}

?>
</form>
