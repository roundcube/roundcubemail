<form action="index.php?_step=3" method="post">

<h3>Check config files</h3>
<?php

require_once 'include/rcube_html.inc';

$read_main = is_readable('../config/main.inc.php');
$read_db = is_readable('../config/db.inc.php');

if ($read_main && !empty($RCI->config)) {
  $RCI->pass('main.inc.php');
}
else if ($read_main) {
  $RCI->fail('main.inc.php', 'Syntax error');
}
else if (!$read_main) {
  $RCI->fail('main.inc.php', 'Unable to read file. Did you create the config files?');
}
echo '<br />';

if ($read_db && !empty($RCI->config['db_table_users'])) {
  $RCI->pass('db.inc.php');
}
else if ($read_db) {
  $RCI->fail('db.inc.php', 'Syntax error');
}
else if (!$read_db) {
  $RCI->fail('db.inc.php', 'Unable to read file. Did you create the config files?');
}

?>

<h3>Check if directories are writable</h3>
<p>RoundCube may need to write/save files into these directories</p>
<?php

if ($RCI->configured) {
    $pass = false;
    foreach (array($RCI->config['temp_dir'],$RCI->config['log_dir']) as $dir) {
        $dirpath = $dir{0} == '/' ? $dir : $docroot . '/' . $dir;
        if (is_writable(realpath($dirpath))) {
            $RCI->pass($dir);
            $pass = true;
        }
        else {
            $RCI->fail($dir, 'not writeable for the webserver');
        }
        echo '<br />';
    }
    
    if (!$pass)
        echo '<p class="hint">Use <tt>chmod</tt> or <tt>chown</tt> to grant write privileges to the webserver</p>';
}
else {
    $RCI->fail('Config', 'Could not read config files');
}

?>

<h3>Check configured database settings</h3>
<?php

$db_working = false;
if ($RCI->configured) {
    if (!empty($RCI->config['db_backend']) && !empty($RCI->config['db_dsnw'])) {

        echo 'Backend: ';
        echo 'PEAR::' . strtoupper($RCI->config['db_backend']) . '<br />';

        $_class = 'rcube_' . strtolower($RCI->config['db_backend']);
        require_once 'include/' . $_class . '.inc';

        $DB = new $_class($RCI->config['db_dsnw'], '', false);
        $DB->db_connect('w');
        if (!($db_error_msg = $DB->is_error())) {
            $RCI->pass('DSN (write)');
            echo '<br />';
            $db_working = true;
        }
        else {
            $RCI->fail('DSN (write)', $db_error_msg);
            echo '<p class="hint">Make sure that the configured database extists and that the user as write privileges<br />';
            echo 'DSN: ' . $RCI->config['db_dsnw'] . '</p>';
            if ($RCI->config['db_backend'] == 'mdb2')
              echo '<p class="hint">There are known problems with MDB2 running on PHP 4. Try setting <tt>db_backend</tt> to \'db\' instead</p>';
        }
    }
    else {
        $RCI->fail('DSN (write)', 'not set');
    }
}
else {
    $RCI->fail('Config', 'Could not read config files');
}

// initialize db with schema found in /SQL/*
if ($db_working && $_POST['initdb']) {
    if (!($success = $RCI->init_db($DB))) {
        $db_working = false;
        echo '<p class="warning">Please try to inizialize the database manually as described in the INSTALL guide.
          Make sure that the configured database extists and that the user as write privileges</p>';
    }
}

// test database
if ($db_working) {
    $db_read = $DB->query("SELECT count(*) FROM {$RCI->config['db_table_users']}");
    if (!$db_read) {
        $RCI->fail('DB Schema', "Database not initialized");
        $db_working = false;
        echo '<p><input type="submit" name="initdb" value="Initialize database" /></p>';
    }
    else {
        $RCI->pass('DB Schema');
    }
    echo '<br />';
}

// more database tests
if ($db_working) {
    // write test
    $db_write = $DB->query("INSERT INTO {$RCI->config['db_table_cache']} (session_id, cache_key, data, user_id) VALUES (?, ?, ?, 0)", '1234567890abcdef', 'test', 'test');
    $insert_id = $DB->insert_id($RCI->config['db_sequence_cache']);
    
    if ($db_write && $insert_id) {
      $RCI->pass('DB Write');
      $DB->query("DELETE FROM {$RCI->config['db_table_cache']} WHERE cache_id=?", $insert_id);
    }
    else {
      $RCI->fail('DB Write', $RCI->get_error());
    }
    echo '<br />';    
    
    // check timezone settings
    $tz_db = 'SELECT ' . $DB->unixtimestamp($DB->now()) . ' AS tz_db';
    $tz_db = $DB->query($tz_db);
    $tz_db = $DB->fetch_assoc($tz_db);
    $tz_db = (int) $tz_db['tz_db'];
    $tz_local = (int) time();
    $tz_diff  = $tz_local - $tz_db;

    // sometimes db and web servers are on separate hosts, so allow a 30 minutes delta
    if (abs($tz_diff) > 1800) {
        $RCI->fail('DB Time', "Database time differs {$td_ziff}s from PHP time");
    }
    else {
        $RCI->pass('DB Time');
    }
}

?>

<h3>Test SMTP settings</h3>

<p>
Server: <?php echo $RCI->getprop('smtp_server', 'PHP mail()'); ?><br />
Port: <?php echo $RCI->getprop('smtp_port'); ?><br />

<?php

if ($RCI->getprop('smtp_server')) {
  $user = $RCI->getprop('smtp_user', '(none)');
  $pass = $RCI->getprop('smtp_pass', '(none)');
  
  if ($user == '%u') {
    $user_field = new textfield(array('name' => '_user'));
    $user = $user_field->show($_POST['_user']);
  }
  if ($pass == '%p') {
    $pass_field = new passwordfield(array('name' => '_pass'));
    $pass = $pass_field->show();
  }
  
  echo "User: $user<br />";
  echo "Password: $pass<br />";
}

$from_field = new textfield(array('name' => '_from', 'id' => 'sendmailfrom'));
$to_field = new textfield(array('name' => '_to', 'id' => 'sendmailto'));

?>
</p>

<?php

if (isset($_POST['sendmail']) && !empty($_POST['_from']) && !empty($_POST['_to'])) {
  
  require_once 'lib/rc_mail_mime.inc';
  require_once 'include/rcube_smtp.inc';
  
  echo '<p>Trying to send email...<br />';
  
  if (preg_match('/^' . $RCI->email_pattern . '$/i', trim($_POST['_from'])) &&
      preg_match('/^' . $RCI->email_pattern . '$/i', trim($_POST['_to']))) {
  
    $headers = array(
      'From' => trim($_POST['_from']),
      'To'  => trim($_POST['_to']),
      'Subject' => 'Test message from RoundCube',
    );

    $body = 'This is a test to confirm that RoundCube can send email.';
    $smtp_response = array();
    
    // send mail using configured SMTP server
    if ($RCI->getprop('smtp_server')) {
      $CONFIG = $RCI->config;
      
      if (!empty($_POST['_user']))
        $CONFIG['smtp_user'] = $_POST['_user'];
      if (!empty($_POST['_pass']))
        $CONFIG['smtp_pass'] = $_POST['_pass'];
      
      $mail_object  = new rc_mail_mime();
      $send_headers = $mail_object->headers($headers);
      
      $status = smtp_mail($headers['From'], $headers['To'],
          ($foo = $mail_object->txtHeaders($send_headers)),
          $body, $smtp_response);
    }
    else {    // use mail()
      $header_str = 'From: ' . $headers['From'];
      
      if (ini_get('safe_mode'))
        $status = mail($headers['To'], $headers['Subject'], $body, $header_str);
      else
        $status = mail($headers['To'], $headers['Subject'], $body, $header_str, '-f'.$headers['From']);
      
      if (!$status)
        $smtp_response[] = 'Mail delivery with mail() failed. Check your error logs for details';
    }

    if ($status) {
        $RCI->pass('SMTP send');
    }
    else {
        $RCI->fail('SMTP send', join('; ', $smtp_response));
    }
  }
  else {
    $RCI->fail('SMTP send', 'Invalid sender or recipient');
  }
}

echo '</p>';

?>

<table>
<tbody>
  <tr>
    <td><label for="sendmailfrom">Sender</label></td>
    <td><?php echo $from_field->show($_POST['_from']); ?></td>
  </tr>
  <tr>
    <td><label for="sendmailto">Recipient</label></td>
    <td><?php echo $to_field->show($_POST['_to']); ?></td>
  </tr>
</tbody>
</table>

<p><input type="submit" name="sendmail" value="Send test mail" /></p>


<h3>Test IMAP configuration</h3>

<?php

$default_hosts = $RCI->get_hostlist();
if (!empty($default_hosts)) {
  $host_field = new select(array('name' => '_host', 'id' => 'imaphost'));
  $host_field->add($default_hosts);
}
else {
  $host_field = new textfield(array('name' => '_host', 'id' => 'imaphost'));
}

$user_field = new textfield(array('name' => '_user', 'id' => 'imapuser'));
$pass_field = new passwordfield(array('name' => '_pass', 'id' => 'imappass'));

?>

<table>
<tbody>
  <tr>
    <td><label for="imaphost">Server</label></td>
    <td><?php echo $host_field->show($_POST['_host']); ?></td>
  </tr>
  <tr>
    <td>Port</td>
    <td><?php echo $RCI->getprop('default_port'); ?></td>
  </tr>
    <tr>
      <td><label for="imapuser">Username</label></td>
      <td><?php echo $user_field->show($_POST['_user']); ?></td>
    </tr>
    <tr>
      <td><label for="imappass">Password</label></td>
      <td><?php echo $pass_field->show(); ?></td>
    </tr>
</tbody>
</table>

<?php

if (isset($_POST['imaptest']) && !empty($_POST['_host']) && !empty($_POST['_user'])) {
  
  require_once 'include/rcube_imap.inc';
  
  echo '<p>Connecting to ' . Q($_POST['_host']) . '...<br />';
  
  $a_host = parse_url($_POST['_host']);
  if ($a_host['host']) {
    $imap_host = $a_host['host'];
    $imap_ssl = (isset($a_host['scheme']) && in_array($a_host['scheme'], array('ssl','imaps','tls'))) ? $a_host['scheme'] : null;
    $imap_port = isset($a_host['port']) ? $a_host['port'] : ($imap_ssl ? 993 : $CONFIG['default_port']);
  }
  else {
    $imap_host = trim($_POST['_host']);
    $imap_port = $RCI->getprop('default_port');
  }
  
  $imap = new rcube_imap(null);
  if ($imap->connect($imap_host, $_POST['_user'], $_POST['_pass'], $imap_port, $imap_ssl)) {
    $RCI->pass('IMAP connect', 'SORT capability: ' . ($imap->get_capability('SORT') ? 'yes' : 'no'));
    $imap->close();
  }
  else {
    $RCI->fail('IMAP connect', $RCI->get_error());
  }
}

?>

<p><input type="submit" name="imaptest" value="Check login" /></p>

</form>

<hr />

<p class="warning">

After completing the installation and the final tests please <b>remove</b> the whole
installer folder from the document root of the webserver.<br />
<br />

These files may expose sensitive configuration data like server passwords and encryption keys
to the public. Make sure you cannot access this installer from your browser.

</p>
