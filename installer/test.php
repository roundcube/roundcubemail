<form action="index.php?_step=3" method="post">

<h3>Check config files</h3>
<?php

// load local config files
$RCI->load_config();

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

<h3>Check configured database settings</h3>
<?php

$db_working = false;
if (!empty($RCI->config)) {
    if (!empty($RCI->config['db_backend']) && !empty($RCI->config['db_dsnw'])) {

        echo 'Backend: ';
        echo 'PEAR::' . strtoupper($RCI->config['db_backend']) . '<br />';

        $_class = 'rcube_' . strtolower($RCI->config['db_backend']);
        require_once 'include/' . $_class . '.inc';

        $DB = new $_class($RCI->config['db_dsnw'], '', false);
        $DB->db_connect('w');
        if (!($db_error_msg = $DB->is_error())) {
            $RCI->pass('DSN (write)');
            $db_working = true;
        }
        else {
            $RCI->fail('DSN (write)', "Error: $db_error_msg");
        }
        echo '<br />';
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
    $engine = preg_match('/^([a-z]+):/i', $RCI->config['db_dsnw'], $regs) ? $regs[1] : 'mysql';
    $fname = '../SQL/' . ($engine == 'pgsql' ? 'postgres' : $engine) . '.initial.sql';
    if ($sql = @file_get_contents($fname)) {
        $DB->query($sql);
    }
    else {
        $RCI->fail('DB Schema', "Cannot read the schema file: $fname");
    }
    
    if ($err = $RCI->get_error()) {
        $RCI->fail('DB Schema', "Error creating database schema: $err");
        $db_working = false;
        echo '<p class="warning">Please try to inizialize the database manually as described in the INSTALL guide.</p>';
        echo '<br />';
    }
}

if ($db_working) {
    $success = $DB->query("SELECT count(*) FROM {$RCI->config['db_table_users']}");
    if (!$success) {
        $RCI->fail('DB Schema', "Database not initialized");
        echo '<p><input type="submit" name="initdb" value="Initialize database" /></p>';
    }
    else {
        $RCI->pass('DB Schema');
    }
    echo '<br />';
    
    $tz_db = 'SELECT ' . $DB->unixtimestamp($DB->now()) . ' AS tz_db';
    $tz_db = $DB->query($tz_db);
    $tz_db = $DB->fetch_assoc($tz_db);
    $tz_db = (int) $tz_db['tz_db'];
    $tz_local = (int) time();
    $tz_diff  = $tz_local - $tz_db;

    // sometimes db and web servers are on separate hosts, so allow a 30 minutes delta
    if (abs($tz_diff) > 1800) {
        $RCI->fail('DB Time', "Database time differs {$td_ziff}s from PHP time");
    } else {
        $RCI->pass('DB Time');
    }
    
}


?>

<p>[@todo Add tests for IMAP and SMTP settings]</p>

</form>

<p class="warning">

After completing the installation and the final tests please <b>remove</b> the whole
installer folder from the document root of the webserver.<br />
<br />

These files may expose sensitive configuration data like server passwords and encryption keys
to the public. Make sure you cannot access this installer from your browser.

</p>
