<form action="index.php" method="post">
<input type="hidden" name="_step" value="2" />
<?php

// also load the default config to fill in the fields
$RCI->load_defaults();

// register these boolean fields
$RCI->bool_config_props = array(
  'ip_check' => 1,
  'enable_caching' => 1,
  'enable_spellcheck' => 1,
  'auto_create_user' => 1,
  'smtp_log' => 1,
  'prefer_html' => 1,
  'preview_pane' => 1,
  'debug_level' => 1,
);

// allow the current user to get to the next step
$_SESSION['allowinstaller'] = true;

if (!empty($_POST['submit'])) {
  
  echo '<p class="notice">Copy or download the following configurations and save them in two files';
  echo ' (names above the text box) within the <tt>'.RCMAIL_CONFIG_DIR.'</tt> directory of your Roundcube installation.<br/>';
  echo ' Make sure that there are no characters outside the <tt>&lt;?php ?&gt;</tt> brackets when saving the files.</p>';
  
  $textbox = new html_textarea(array('rows' => 16, 'cols' => 60, 'class' => "configfile"));
  
  echo '<div><em>main.inc.php (<a href="index.php?_getfile=main">download</a>)</em></div>';
  echo $textbox->show(($_SESSION['main.inc.php'] = $RCI->create_config('main')));
  
  echo '<div style="margin-top:1em"><em>db.inc.php (<a href="index.php?_getfile=db">download</a>)</em></div>';
  echo $textbox->show($_SESSION['db.inc.php'] = $RCI->create_config('db'));

  echo '<p class="hint">Of course there are more options to configure.
    Have a look at the config files or visit <a href="http://trac.roundcube.net/wiki/Howto_Config">Howto_Config</a> to find out.</p>';

  echo '<p><input type="button" onclick="location.href=\'./index.php?_step=3\'" value="CONTINUE" /></p>';
  
  // echo '<style type="text/css"> .configblock { display:none } </style>';
  echo "\n<hr style='margin-bottom:1.6em' />\n";
}

?>
<fieldset>
<legend>General configuration</legend>
<dl class="configblock">

<dt class="propname">product_name</dt>
<dd>
<?php

$input_prodname = new html_inputfield(array('name' => '_product_name', 'size' => 30, 'id' => "cfgprodname"));
echo $input_prodname->show($RCI->getprop('product_name'));

?>
<div>The name of your service (used to compose page titles)</div>
</dd>

<dt class="propname">temp_dir</dt>
<dd>
<?php

$input_tempdir = new html_inputfield(array('name' => '_temp_dir', 'size' => 30, 'id' => "cfgtempdir"));
echo $input_tempdir->show($RCI->getprop('temp_dir'));

?>
<div>Use this folder to store temp files (must be writeable for webserver)</div>
</dd>


<dt class="propname">ip_check</dt>
<dd>
<?php

$check_ipcheck = new html_checkbox(array('name' => '_ip_check', 'id' => "cfgipcheck"));
echo $check_ipcheck->show(intval($RCI->getprop('ip_check')), array('value' => 1));

?>
<label for="cfgipcheck">Check client IP in session authorization</label><br />

<p class="hint">This increases security but can cause sudden logouts when someone uses a proxy with changeing IPs.</p>
</dd>

<dt class="propname">des_key</dt>
<dd>
<?php

$input_deskey = new html_inputfield(array('name' => '_des_key', 'size' => 30, 'id' => "cfgdeskey"));
echo $input_deskey->show($RCI->getprop('des_key'));

?>
<div>This key is used to encrypt the users imap password before storing in the session record</div>
<p class="hint">It's a random generated string to ensure that every installation has it's own key.
If you enter it manually please provide a string of exactly 24 chars.</p>
</dd>

<dt class="propname">enable_caching</dt>
<dd>
<?php

$check_caching = new html_checkbox(array('name' => '_enable_caching', 'id' => "cfgcache"));
echo $check_caching->show(intval($RCI->getprop('enable_caching')), array('value' => 1));

?>
<label for="cfgcache">Cache messages in local database</label><br />
</dd>

<dt class="propname">enable_spellcheck</dt>
<dd>
<?php
$check_spell = new html_checkbox(array('name' => '_enable_spellcheck', 'id' => "cfgspellcheck"));
echo $check_spell->show(intval($RCI->getprop('enable_spellcheck')), array('value' => 1));
?>
<label for="cfgspellcheck">Make use of the spell checker</label><br />
</dd>

<dt class="propname">spellcheck_engine</dt>
<dd>
<?php
$select_spell = new html_select(array('name' => '_spellcheck_engine', 'id' => "cfgspellcheckengine"));
if (extension_loaded('pspell'))
  $select_spell->add('pspell', 'pspell');
$select_spell->add('Googie', 'googie');

echo $select_spell->show($RCI->is_post ? $_POST['_spellcheck_engine'] : 'pspell');

?>
<label for="cfgspellcheckengine">Which spell checker to use</label><br />

<p class="hint">GoogieSpell implies that the message content will be sent to Google in order to check the spelling.</p>
</dd>

<dt class="propname">identities_level</dt>
<dd>
<?php

$input_ilevel = new html_select(array('name' => '_identities_level', 'id' => "cfgidentitieslevel"));
$input_ilevel->add('many identities with possibility to edit all params', 0);
$input_ilevel->add('many identities with possibility to edit all params but not email address', 1);
$input_ilevel->add('one identity with possibility to edit all params', 2);
$input_ilevel->add('one identity with possibility to edit all params but not email address', 3);
echo $input_ilevel->show($RCI->getprop('identities_level'), 0);

?>
<div>Level of identities access</div>
<p class="hint">Defines what users can do with their identities.</p>
</dd>

</dl>
</fieldset>

<fieldset>
<legend>Logging & Debugging</legend>
<dl class="loggingblock">

<dt class="propname">debug_level</dt>
<dd>
<?php

$value = $RCI->getprop('debug_level');
$check_debug = new html_checkbox(array('name' => '_debug_level[]'));
echo $check_debug->show(($value & 1) ? 1 : 0 , array('value' => 1, 'id' => 'cfgdebug1'));
echo '<label for="cfgdebug1">Log errors</label><br />';

echo $check_debug->show(($value & 4) ? 4 : 0, array('value' => 4, 'id' => 'cfgdebug4'));
echo '<label for="cfgdebug4">Print errors (to the browser)</label><br />';

echo $check_debug->show(($value & 8) ? 8 : 0, array('value' => 8, 'id' => 'cfgdebug8'));
echo '<label for="cfgdebug8">Verbose display (enables debug console)</label><br />';

?>
</dd>

<dt class="propname">log_driver</dt>
<dd>
<?php

$select_log_driver = new html_select(array('name' => '_log_driver', 'id' => "cfglogdriver"));
$select_log_driver->add(array('file', 'syslog'), array('file', 'syslog'));
echo $select_log_driver->show($RCI->getprop('log_driver', 'file'));

?>
<div>How to do logging? 'file' - write to files in the log directory, 'syslog' - use the syslog facility.</div>
</dd>

<dt class="propname">log_dir</dt>
<dd>
<?php

$input_logdir = new html_inputfield(array('name' => '_log_dir', 'size' => 30, 'id' => "cfglogdir"));
echo $input_logdir->show($RCI->getprop('log_dir'));

?>
<div>Use this folder to store log files (must be writeable for webserver). Note that this only applies if you are using the 'file' log_driver.</div>
</dd>

<dt class="propname">syslog_id</dt>
<dd>
<?php

$input_syslogid = new html_inputfield(array('name' => '_syslog_id', 'size' => 30, 'id' => "cfgsyslogid"));
echo $input_syslogid->show($RCI->getprop('syslog_id', 'roundcube'));

?>
<div>What ID to use when logging with syslog. Note that this only applies if you are using the 'syslog' log_driver.</div>
</dd>

<dt class="propname">syslog_facility</dt>
<dd>
<?php

$input_syslogfacility = new html_select(array('name' => '_syslog_facility', 'id' => "cfgsyslogfacility"));
$input_syslogfacility->add('user-level messages', LOG_USER);
$input_syslogfacility->add('mail subsystem', LOG_MAIL);
$input_syslogfacility->add('local level 0', LOG_LOCAL0);
$input_syslogfacility->add('local level 1', LOG_LOCAL1);
$input_syslogfacility->add('local level 2', LOG_LOCAL2);
$input_syslogfacility->add('local level 3', LOG_LOCAL3);
$input_syslogfacility->add('local level 4', LOG_LOCAL4);
$input_syslogfacility->add('local level 5', LOG_LOCAL5);
$input_syslogfacility->add('local level 6', LOG_LOCAL6);
$input_syslogfacility->add('local level 7', LOG_LOCAL7);
echo $input_syslogfacility->show($RCI->getprop('syslog_facility'), LOG_USER);

?>
<div>What ID to use when logging with syslog.  Note that this only applies if you are using the 'syslog' log_driver.</div>
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

require_once 'MDB2.php';

$supported_dbs = array('MySQL' => 'mysql', 'MySQLi' => 'mysqli',
    'PgSQL' => 'pgsql', 'SQLite' => 'sqlite');

$select_dbtype = new html_select(array('name' => '_dbtype', 'id' => "cfgdbtype"));
foreach ($supported_dbs AS $database => $ext) {
    if (extension_loaded($ext)) {
        $select_dbtype->add($database, $ext);
    }
}

$input_dbhost = new html_inputfield(array('name' => '_dbhost', 'size' => 20, 'id' => "cfgdbhost"));
$input_dbname = new html_inputfield(array('name' => '_dbname', 'size' => 20, 'id' => "cfgdbname"));
$input_dbuser = new html_inputfield(array('name' => '_dbuser', 'size' => 20, 'id' => "cfgdbuser"));
$input_dbpass = new html_passwordfield(array('name' => '_dbpass', 'size' => 20, 'id' => "cfgdbpass"));

$dsnw = MDB2::parseDSN($RCI->getprop('db_dsnw'));

echo $select_dbtype->show($RCI->is_post ? $_POST['_dbtype'] : $dsnw['phptype']);
echo '<label for="cfgdbtype">Database type</label><br />';
echo $input_dbhost->show($RCI->is_post ? $_POST['_dbhost'] : $dsnw['hostspec']);
echo '<label for="cfgdbhost">Database server (omit for sqlite)</label><br />';
echo $input_dbname->show($RCI->is_post ? $_POST['_dbname'] : $dsnw['database']);
echo '<label for="cfgdbname">Database name (use absolute path and filename for sqlite)</label><br />';
echo $input_dbuser->show($RCI->is_post ? $_POST['_dbuser'] : $dsnw['username']);
echo '<label for="cfgdbuser">Database user name (needs write permissions)(omit for sqlite)</label><br />';
echo $input_dbpass->show($RCI->is_post ? $_POST['_dbpass'] : $dsnw['password']);
echo '<label for="cfgdbpass">Database password (omit for sqlite)</label><br />';

?>
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

$text_imaphost = new html_inputfield(array('name' => '_default_host[]', 'size' => 30));
$default_hosts = $RCI->get_hostlist();

if (empty($default_hosts))
  $default_hosts = array('');

$i = 0;
foreach ($default_hosts as $host) {
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

$text_imapport = new html_inputfield(array('name' => '_default_port', 'size' => 6, 'id' => "cfgimapport"));
echo $text_imapport->show($RCI->getprop('default_port'));

?>
<div>TCP port used for IMAP connections</div>
</dd>

<dt class="propname">username_domain</dt>
<dd>
<?php

$text_userdomain = new html_inputfield(array('name' => '_username_domain', 'size' => 30, 'id' => "cfguserdomain"));
echo $text_userdomain->show($RCI->getprop('username_domain'));

?>
<div>Automatically add this domain to user names for login</div>

<p class="hint">Only for IMAP servers that require full e-mail addresses for login</p>
</dd>

<dt class="propname">auto_create_user</dt>
<dd>
<?php

$check_autocreate = new html_checkbox(array('name' => '_auto_create_user', 'id' => "cfgautocreate"));
echo $check_autocreate->show(intval($RCI->getprop('auto_create_user')), array('value' => 1));

?>
<label for="cfgautocreate">Automatically create a new Roundcube user when log-in the first time</label><br />

<p class="hint">A user is authenticated by the IMAP server but it requires a local record to store settings
and contacts. With this option enabled a new user record will automatically be created once the IMAP login succeeds.</p>

<p class="hint">If this option is disabled, the login only succeeds if there's a matching user-record in the local Roundcube database
what means that you have to create those records manually or disable this option after the first login.</p>
</dd>

<dt class="propname">sent_mbox</dt>
<dd>
<?php

$text_sentmbox = new html_inputfield(array('name' => '_sent_mbox', 'size' => 20, 'id' => "cfgsentmbox"));
echo $text_sentmbox->show($RCI->getprop('sent_mbox'));

?>
<div>Store sent messages in this folder</div>

<p class="hint">Leave blank if sent messages should not be stored</p>
</dd>

<dt class="propname">trash_mbox</dt>
<dd>
<?php

$text_trashmbox = new html_inputfield(array('name' => '_trash_mbox', 'size' => 20, 'id' => "cfgtrashmbox"));
echo $text_trashmbox->show($RCI->getprop('trash_mbox'));

?>
<div>Move messages to this folder when deleting them</div>

<p class="hint">Leave blank if they should be deleted directly</p>
</dd>

<dt class="propname">drafts_mbox</dt>
<dd>
<?php

$text_draftsmbox = new html_inputfield(array('name' => '_drafts_mbox', 'size' => 20, 'id' => "cfgdraftsmbox"));
echo $text_draftsmbox->show($RCI->getprop('drafts_mbox'));

?>
<div>Store draft messages in this folder</div>

<p class="hint">Leave blank if they should not be stored</p>
</dd>

<dt class="propname">junk_mbox</dt>
<dd>
<?php

$text_junkmbox = new html_inputfield(array('name' => '_junk_mbox', 'size' => 20, 'id' => "cfgjunkmbox"));
echo $text_junkmbox->show($RCI->getprop('junk_mbox'));

?>
<div>Store spam messages in this folder</div>
</dd>
</dl>
</fieldset>


<fieldset>
<legend>SMTP Settings</legend>
<dl class="configblock" id="cgfblocksmtp">
<dt class="propname">smtp_server</dt>
<dd>
<?php

$text_smtphost = new html_inputfield(array('name' => '_smtp_server', 'size' => 30, 'id' => "cfgsmtphost"));
echo $text_smtphost->show($RCI->getprop('smtp_server'));

?>
<div>Use this host for sending mails</div>

<p class="hint">To use SSL connection, set ssl://smtp.host.com. If left blank, the PHP mail() function is used</p>
</dd>

<dt class="propname">smtp_port</dt>
<dd>
<?php

$text_smtpport = new html_inputfield(array('name' => '_smtp_port', 'size' => 6, 'id' => "cfgsmtpport"));
echo $text_smtpport->show($RCI->getprop('smtp_port'));

?>
<div>SMTP port (default is 25; 465 for SSL; 587 for submission)</div>
</dd>

<dt class="propname">smtp_user/smtp_pass</dt>
<dd>
<?php

$text_smtpuser = new html_inputfield(array('name' => '_smtp_user', 'size' => 20, 'id' => "cfgsmtpuser"));
$text_smtppass = new html_passwordfield(array('name' => '_smtp_pass', 'size' => 20, 'id' => "cfgsmtppass"));
echo $text_smtpuser->show($RCI->getprop('smtp_user'));
echo $text_smtppass->show($RCI->getprop('smtp_pass'));

?>
<div>SMTP username and password (if required)</div>
<p>
<?php

$check_smtpuser = new html_checkbox(array('name' => '_smtp_user_u', 'id' => "cfgsmtpuseru"));
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
$select_smtpauth = new html_select(array('name' => '_smtp_auth_type', 'id' => "cfgsmtpauth"));
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

$check_smtplog = new html_checkbox(array('name' => '_smtp_log', 'id' => "cfgsmtplog"));
echo $check_smtplog->show(intval($RCI->getprop('smtp_log')), array('value' => 1));

?>
<label for="cfgsmtplog">Log sent messages in <tt>{log_dir}/sendmail</tt> or to syslog.</label><br />
</dd>

</dl>
</fieldset>


<fieldset>
<legend>Display settings &amp; user prefs</legend>
<dl class="configblock" id="cgfblockdisplay">

<dt class="propname">language <span class="userconf">*</span></dt>
<dd>
<?php

$input_locale = new html_inputfield(array('name' => '_language', 'size' => 6, 'id' => "cfglocale"));
echo $input_locale->show($RCI->getprop('language'));

?>
<div>The default locale setting. This also defines the language of the login screen.<br/>Leave it empty to auto-detect the user agent language.</div>
<p class="hint">Enter a <a href="http://www.faqs.org/rfcs/rfc1766">RFC1766</a> formatted language name. Examples: en_US, de_DE, de_CH, fr_FR, pt_BR</p>
</dd>

<dt class="propname">skin <span class="userconf">*</span></dt>
<dd>
<?php

$input_skin = new html_inputfield(array('name' => '_skin', 'size' => 30, 'id' => "cfgskin"));
echo $input_skin->show($RCI->getprop('skin'));

?>
<div>Name of interface skin (folder in /skins)</div>
</dd>

<dt class="propname">pagesize <span class="userconf">*</span></dt>
<dd>
<?php

$input_pagesize = new html_inputfield(array('name' => '_pagesize', 'size' => 6, 'id' => "cfgpagesize"));
echo $input_pagesize->show($RCI->getprop('pagesize'));

?>
<div>Show up to X items in list view.</div>
</dd>

<dt class="propname">prefer_html <span class="userconf">*</span></dt>
<dd>
<?php

$check_htmlview = new html_checkbox(array('name' => '_prefer_html', 'id' => "cfghtmlview", 'value' => 1));
echo $check_htmlview->show(intval($RCI->getprop('prefer_html')));

?>
<label for="cfghtmlview">Prefer displaying HTML messages</label><br />
</dd>

<dt class="propname">preview_pane <span class="userconf">*</span></dt>
<dd>
<?php

$check_prevpane = new html_checkbox(array('name' => '_preview_pane', 'id' => "cfgprevpane", 'value' => 1));
echo $check_prevpane->show(intval($RCI->getprop('preview_pane')));

?>
<label for="cfgprevpane">If preview pane is enabled</label><br />
</dd>

<dt class="propname">htmleditor <span class="userconf">*</span></dt>
<dd>
<label for="cfghtmlcompose">Compose HTML formatted messages</label>
<?php

$select_htmlcomp = new html_select(array('name' => '_htmleditor', 'id' => "cfghtmlcompose"));
$select_htmlcomp->add('never', 0);
$select_htmlcomp->add('always', 1);
$select_htmlcomp->add('on reply to HTML message only', 2);
echo $select_htmlcomp->show(intval($RCI->getprop('htmleditor')));

?>
</dd>

<dt class="propname">draft_autosave <span class="userconf">*</span></dt>
<dd>
<label for="cfgautosave">Save compose message every</label>
<?php

$select_autosave = new html_select(array('name' => '_draft_autosave', 'id' => 'cfgautosave'));
$select_autosave->add('never', 0);
foreach (array(1, 3, 5, 10) as $i => $min)
  $select_autosave->add("$min min", $min*60);

echo $select_autosave->show(intval($RCI->getprop('draft_autosave')));

?>
</dd>

<dt class="propname">mdn_requests <span class="userconf">*</span></dt>
<dd>
<?php

$mdn_opts = array(
    0 => 'ask the user',
    1 => 'send automatically',
    3 => 'send receipt to user contacts, otherwise ask the user',
    4 => 'send receipt to user contacts, otherwise ignore',
    2 => 'ignore',
);

$select_mdnreq = new html_select(array('name' => '_mdn_requests', 'id' => "cfgmdnreq"));
$select_mdnreq->add(array_values($mdn_opts), array_keys($mdn_opts));
echo $select_mdnreq->show(intval($RCI->getprop('mdn_requests')));

?>
<div>Behavior if a received message requests a message delivery notification (read receipt)</div>
</dd>

<dt class="propname">mime_param_folding <span class="userconf">*</span></dt>
<dd>
<?php

$select_param_folding = new html_select(array('name' => '_mime_param_folding', 'id' => "cfgmimeparamfolding"));
$select_param_folding->add('Full RFC 2231 (Roundcube, Thunderbird)', '0'); 
$select_param_folding->add('RFC 2047/2231 (MS Outlook, OE)', '1');
$select_param_folding->add('Full RFC 2047 (deprecated)', '2');

echo $select_param_folding->show(intval($RCI->getprop('mime_param_folding')));

?>
<div>How to encode attachment long/non-ascii names</div>
</dd>

</dl>

<p class="hint"><span class="userconf">*</span>&nbsp; These settings are defaults for the user preferences</p>
</fieldset>

<?php

echo '<p><input type="submit" name="submit" value="' . ($RCI->configured ? 'UPDATE' : 'CREATE') . ' CONFIG" ' . ($RCI->failures ? 'disabled' : '') . ' /></p>';

?>
</form>
