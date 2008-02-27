<form action="index.php" method="post">
<input type="hidden" name="_step" value="2" />
<?php

ini_set('display_errors', 1);
require_once 'include/rcube_html.inc';

// also load the default config to fill in the fields
$RCI->load_defaults();

if (!empty($_POST['submit'])) {
  
  echo '<p class="notice">Copy the following configurations and save them in two files (names above the text box)';
  echo ' within the <tt>config/</tt> directory of your RoundCube installation.</p>';
  
  $textbox = new textarea(array('rows' => 16, 'cols' => 60, 'class' => "configfile"));
  
  echo '<div><em>main.inc.php</em></div>';
  echo $textbox->show($RCI->create_config('main'));
  
  echo '<div style="margin-top:1em"><em>db.inc.php</em></div>';
  echo $textbox->show($RCI->create_config('db'));

  echo '<p><input type="button" onclick="location.href=\'./index.php?_step=3\'" value="CONTINUE" /></p>';
  
  // echo '<style type="text/css"> .configblock { display:none } </style>';
  echo "\n<hr style='margin-bottom:1.6em' />\n";
}

?>
<fieldset>
<legend>General configuration</legend>
<dl class="configblock">
<!--
<dt id="cgfblockgeneral" class="propname">debug_level</dt>
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
<label for="cfgipcheck">Check client IP in session authorization</label><br />

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

<dt class="propname">enable_spellcheck</dt>
<dd>
<?php

$check_caching = new checkbox(array('name' => '_enable_spellcheck', 'id' => "cfgspellcheck"));
echo $check_caching->show(intval($RCI->getprop('enable_spellcheck')), array('value' => 1));

?>
<label for="cfgspellcheck">Make use of the built-in spell checker</label><br />

<p class="hint">It is based on GoogieSpell what implies that the message content will be sent to Google in order to check the spelling.</p>
</dd>

<dt class="propname">mdn_requests</dt>
<dd>
<?php

$select_mdnreq = new select(array('name' => '_mdn_requests', 'id' => "cfgmdnreq"));
$select_mdnreq->add(array('ask the user', 'send automatically', 'ignore'), array(0, 1, 2));
echo $select_mdnreq->show(intval($RCI->getprop('mdn_requests')));

?>
<div>Behavior if a received message requests a message delivery notification (read receipt)</div>
</dd>

</dl>
</fieldset>

<fieldset>
<legend>Database setup</legend>
<dl class="configblock" id="cgfblockdb">
<dt class="propname">db_dsnw</dt>
<dd>
<p>Database settings for read/write operations:</p>
<?php

require_once 'DB.php';

$supported_dbs = array('MySQL' => 'mysql', 'MySQLi' => 'mysqli',
    'PgSQL' => 'pgsql', 'SQLite' => 'sqlite');

$select_dbtype = new select(array('name' => '_dbtype', 'id' => "cfgdbtype"));
foreach ($supported_dbs AS $database => $ext) {
    if (extension_loaded($ext)) {
        $select_dbtype->add($database, $ext);
    }
}

$input_dbhost = new textfield(array('name' => '_dbhost', 'size' => 20, 'id' => "cfgdbhost"));
$input_dbname = new textfield(array('name' => '_dbname', 'size' => 20, 'id' => "cfgdbname"));
$input_dbuser = new textfield(array('name' => '_dbuser', 'size' => 20, 'id' => "cfgdbuser"));
$input_dbpass = new textfield(array('name' => '_dbpass', 'size' => 20, 'id' => "cfgdbpass"));

$dsnw = DB::parseDSN($RCI->getprop('db_dsnw'));

echo $select_dbtype->show($_POST['_dbtype'] ? $_POST['_dbtype'] : $dsnw['phptype']);
echo '<label for="cfgdbtype">Database type</label><br />';
echo $input_dbhost->show($_POST['_dbhost'] ? $_POST['_dbhost'] : $dsnw['hostspec']);
echo '<label for="cfgdbhost">Database server</label><br />';
echo $input_dbname->show($_POST['_dbname'] ? $_POST['_dbname'] : $dsnw['database']);
echo '<label for="cfgdbname">Database name</label><br />';
echo $input_dbuser->show($_POST['_dbuser'] ? $_POST['_dbuser'] : $dsnw['username']);
echo '<label for="cfgdbuser">Database user name (needs write permissions)</label><br />';
echo $input_dbpass->show($_POST['_dbpass'] ? $_POST['_dbpass'] : $dsnw['password']);
echo '<label for="cfgdbpass">Database password</label><br />';

?>
</dd>

<dt class="propname">db_backend</dt>
<dd>
<?php

// check for existing PEAR classes
@include_once 'DB.php';
@include_once 'MDB2.php';

$select_dbba = new select(array('name' => '_db_backend', 'id' => "cfgdbba"));

if (class_exists('DB'))
  $select_dbba->add('DB', 'db');
if (class_exists('MDB2'))
  $select_dbba->add('MDB2', 'mdb2');

echo $select_dbba->show($RCI->getprop('db_backend'));

?>
<div>PEAR Database backend to use</div>
</dd>

</dl>
</fieldset>


<fieldset>
<legend>IMAP Settings</legend>
<dl class="configblock" id="cgfblockimap">
<dt class="propname">default_host</dt>
<dd>
<div>The IMAP host(s) chosen to perform the log-in</div>
<div id="defaulthostlist">
<?php

$default_hosts = (array)$RCI->getprop('default_host');
$text_imaphost = new textfield(array('name' => '_default_host[]', 'size' => 30));

$i = 0;
foreach ($default_hosts as $key => $name) {
  if (empty($name))
    continue;
  $host = is_numeric($key) ? $name : $key;
  echo '<div id="defaulthostentry'.$i.'">' . $text_imaphost->show($host);
  if ($i++ > 0)
    echo '<a href="#" onclick="removehostfield(this.parentNode);return false" class="removelink" title="Remove this entry">remove</a>';
  echo '</div>';
}

?>
</div>
<div><a href="javascript:addhostfield()" class="addlink" title="Add another field">add</a></div>

<p class="hint">Leave blank to show a textbox at login. To use SSL/IMAPS connection, type ssl://hostname</p>
</dd>

<dt class="propname">default_port</dt>
<dd>
<?php

$text_imapport = new textfield(array('name' => '_default_port', 'size' => 6, 'id' => "cfgimapport"));
echo $text_imapport->show($RCI->getprop('default_port'));

?>
<div>TCP port used for IMAP connections</div>
</dd>

<dt class="propname">username_domain</dt>
<dd>
<?php

$text_userdomain = new textfield(array('name' => '_username_domain', 'size' => 30, 'id' => "cfguserdomain"));
echo $text_userdomain->show($RCI->getprop('username_domain'));

?>
<div>Automatically add this domain to user names for login</div>

<p class="hint">Only for IMAP servers that require full e-mail addresses for login</p>
</dd>

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

<dt class="propname">sent_mbox</dt>
<dd>
<?php

$text_sentmbox = new textfield(array('name' => '_sent_mbox', 'size' => 20, 'id' => "cfgsentmbox"));
echo $text_sentmbox->show($RCI->getprop('sent_mbox'));

?>
<div>Store sent messages is this folder</div>

<p class="hint">Leave blank if sent messages should not be stored</p>
</dd>

<dt class="propname">trash_mbox</dt>
<dd>
<?php

$text_trashmbox = new textfield(array('name' => '_trash_mbox', 'size' => 20, 'id' => "cfgtrashmbox"));
echo $text_trashmbox->show($RCI->getprop('trash_mbox'));

?>
<div>Move messages to this folder when deleting them</div>

<p class="hint">Leave blank if they should be deleted directly</p>
</dd>

<dt class="propname">drafts_mbox</dt>
<dd>
<?php

$text_draftsmbox = new textfield(array('name' => '_drafts_mbox', 'size' => 20, 'id' => "cfgdraftsmbox"));
echo $text_draftsmbox->show($RCI->getprop('drafts_mbox'));

?>
<div>Store draft messages is this folder</div>
</dd>

</dl>
</fieldset>


<fieldset>
<legend>SMTP Settings</legend>
<dl class="configblock" id="cgfblocksmtp">
<dt class="propname">smtp_server</dt>
<dd>
<?php

$text_smtphost = new textfield(array('name' => '_smtp_server', 'size' => 30, 'id' => "cfgsmtphost"));
echo $text_smtphost->show($RCI->getprop('smtp_server'));

?>
<div>Use this host for sending mails</div>

<p class="hint">To use SSL connection, set ssl://smtp.host.com. If left blank, the PHP mail() function is used</p>
</dd>

<dt class="propname">smtp_port</dt>
<dd>
<?php

$text_smtpport = new textfield(array('name' => '_smtp_port', 'size' => 6, 'id' => "cfgsmtpport"));
echo $text_smtpport->show($RCI->getprop('smtp_port'));

?>
<div>SMTP port (default is 25; 465 for SSL)</div>
</dd>

<dt class="propname">smtp_user/smtp_pass</dt>
<dd>
<?php

$text_smtpuser = new textfield(array('name' => '_smtp_user', 'size' => 20, 'id' => "cfgsmtpuser"));
$text_smtppass = new textfield(array('name' => '_smtp_pass', 'size' => 20, 'id' => "cfgsmtppass"));
echo $text_smtpuser->show($RCI->getprop('smtp_user'));
echo $text_smtppass->show($RCI->getprop('smtp_pass'));

?>
<div>SMTP username and password (if required)</div>
<p>
<?php

$check_smtpuser = new checkbox(array('name' => '_smtp_user_u', 'id' => "cfgsmtpuseru"));
echo $check_smtpuser->show($RCI->getprop('smtp_user') == '%u' || $_POST['_smtp_user_u'] ? 1 : 0, array('value' => 1));

?>
<label for="cfgsmtpuseru">Use the current IMAP username and password for SMTP authentication</label>
</p>
</dd>
<!--
<dt class="propname">smtp_auth_type</dt>
<dd>
<?php
/*
$select_smtpauth = new select(array('name' => '_smtp_auth_type', 'id' => "cfgsmtpauth"));
$select_smtpauth->add(array('(auto)', 'PLAIN', 'DIGEST-MD5', 'CRAM-MD5', 'LOGIN'), array('0', 'PLAIN', 'DIGEST-MD5', 'CRAM-MD5', 'LOGIN'));
echo $select_smtpauth->show(intval($RCI->getprop('smtp_auth_type')));
*/
?>
<div>Method to authenticate at the SMTP server. Choose (auto) if you don't know what this is</div>
</dd>
-->
<dt class="propname">smtp_log</dt>
<dd>
<?php

$check_smtplog = new checkbox(array('name' => '_smtp_log', 'id' => "cfgsmtplog"));
echo $check_smtplog->show(intval($RCI->getprop('smtp_log')), array('value' => 1));

?>
<label for="cfgsmtplog">Log sent messages in <tt>logs/sendmail</tt></label><br />
</dd>

</dl>
</fieldset>


<fieldset>
<legend>Display settings</legend>
<dl class="configblock" id="cgfblockdisplay">

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

echo '<p><input type="submit" name="submit" value="' . ($RCI->configured ? 'UPDATE' : 'CREATE') . ' CONFIG" ' . ($RCI->failures ? 'disabled' : '') . ' /></p>';

?>
</form>
