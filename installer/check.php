<form action="index.php" method="get">
<?php

$required_php_exts = array(
    'PCRE'      => 'pcre',
    'DOM'       => 'dom',
    'Session'   => 'session',
    'XML'       => 'xml',
    'JSON'      => 'json'
);

$optional_php_exts = array(
    'FileInfo'  => 'fileinfo',
    'Libiconv'  => 'iconv',
    'Multibyte' => 'mbstring',
    'OpenSSL'   => 'openssl',
    'Mcrypt'    => 'mcrypt',
);

$required_libs = array(
    'PEAR'      => 'PEAR.php',
    'MDB2'      => 'MDB2.php',
    'Net_SMTP'  => 'Net/SMTP.php',
    'Mail_mime' => 'Mail/mime.php',
);

$supported_dbs = array(
    'MySQL'         => 'mysql',
    'MySQLi'        => 'mysqli',
    'PostgreSQL'    => 'pgsql',
    'SQLite (v2)'   => 'sqlite',
);

$ini_checks = array(
    'file_uploads'                  => 1,
    'session.auto_start'            => 0,
    'zend.ze1_compatibility_mode'   => 0,
    'mbstring.func_overload'        => 0,
    'suhosin.session.encrypt'       => 0,
);

$optional_checks = array(
    'date.timezone' => '-NOTEMPTY-',
);

$source_urls = array(
    'Sockets' => 'http://www.php.net/manual/en/book.sockets.php',
    'Session' => 'http://www.php.net/manual/en/book.session.php',
    'PCRE' => 'http://www.php.net/manual/en/book.pcre.php',
    'FileInfo' => 'http://www.php.net/manual/en/book.fileinfo.php',
    'Libiconv' => 'http://www.php.net/manual/en/book.iconv.php',
    'Multibyte' => 'http://www.php.net/manual/en/book.mbstring.php',
    'Mcrypt' => 'http://www.php.net/manual/en/book.mcrypt.php',
    'OpenSSL' => 'http://www.php.net/manual/en/book.openssl.php',
    'JSON' => 'http://www.php.net/manual/en/book.json.php',
    'DOM' => 'http://www.php.net/manual/en/book.dom.php',
    'PEAR' => 'http://pear.php.net',
    'MDB2' => 'http://pear.php.net/package/MDB2',
    'Net_SMTP' => 'http://pear.php.net/package/Net_SMTP',
    'Mail_mime' => 'http://pear.php.net/package/Mail_mime',
);

echo '<input type="hidden" name="_step" value="' . ($RCI->configured ? 3 : 2) . '" />';
?>

<h3>Checking PHP version</h3>
<?php

define('MIN_PHP_VERSION', '5.2.0');
if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '>=')) {
    $RCI->pass('Version', 'PHP ' . PHP_VERSION . ' detected');
} else {
    $RCI->fail('Version', 'PHP Version ' . MIN_PHP_VERSION . ' or greater is required ' . PHP_VERSION . ' detected');
}
?>

<h3>Checking PHP extensions</h3>
<p class="hint">The following modules/extensions are <em>required</em> to run RoundCube:</p>
<?php

// get extensions location
$ext_dir = ini_get('extension_dir');

$prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
foreach ($required_php_exts as $name => $ext) {
    if (extension_loaded($ext)) {
        $RCI->pass($name);
    } else {
        $_ext = $ext_dir . '/' . $prefix . $ext . '.' . PHP_SHLIB_SUFFIX;
        $msg = @is_readable($_ext) ? 'Could be loaded. Please add in php.ini' : '';
        $RCI->fail($name, $msg, $source_urls[$name]);
    }
    echo '<br />';
}

?>

<p class="hint">The next couple of extensions are <em>optional</em> and recommended to get the best performance:</p>
<?php

foreach ($optional_php_exts as $name => $ext) {
    if (extension_loaded($ext)) {
        $RCI->pass($name);
    }
    else {
        $_ext = $ext_dir . '/' . $prefix . $ext . '.' . PHP_SHLIB_SUFFIX;
        $msg = @is_readable($_ext) ? 'Could be loaded. Please add in php.ini' : '';
        $RCI->na($name, $msg, $source_urls[$name]);
    }
    echo '<br />';
}

?>


<h3>Checking available databases</h3>
<p class="hint">Check which of the supported extensions are installed. At least one of them is required.</p>

<?php

$prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
foreach ($supported_dbs as $database => $ext) {
    if (extension_loaded($ext)) {
        $RCI->pass($database);
    }
    else {
        $_ext = $ext_dir . '/' . $prefix . $ext . '.' . PHP_SHLIB_SUFFIX;
        $msg = @is_readable($_ext) ? 'Could be loaded. Please add in php.ini' : 'Not installed';
        $RCI->na($database, $msg, $source_urls[$database]);
    }
    echo '<br />';
}

?>


<h3>Check for required 3rd party libs</h3>
<p class="hint">This also checks if the include path is set correctly.</p>

<?php

foreach ($required_libs as $classname => $file) {
    @include_once $file;
    if (class_exists($classname)) {
        $RCI->pass($classname);
    }
    else {
        $RCI->fail($classname, "Failed to load $file", $source_urls[$classname]);
    }
    echo "<br />";
}


?>

<h3>Checking php.ini/.htaccess settings</h3>
<p class="hint">The following settings are <em>required</em> to run RoundCube:</p>

<?php

foreach ($ini_checks as $var => $val) {
    $status = ini_get($var);
    if ($val === '-NOTEMPTY-') {
        if (empty($status)) {
            $RCI->fail($var, "cannot be empty and needs to be set");
        } else {
            $RCI->pass($var);
        }
        echo '<br />';
        continue;
    }
    if ($status == $val) {
        $RCI->pass($var);
    } else {
      $RCI->fail($var, "is '$status', should be '$val'");
    }
    echo '<br />';
}
?>

<p class="hint">The following settings are <em>optional</em> and recommended:</p>

<?php

foreach ($optional_checks as $var => $val) {
    $status = ini_get($var);
    if ($val === '-NOTEMPTY-') {
        if (empty($status)) {
            $RCI->optfail($var, "Could be set");
        } else {
            $RCI->pass($var);
        }
        echo '<br />';
        continue;
    }
    if ($status == $val) {
        $RCI->pass($var);
    } else {
      $RCI->optfail($var, "is '$status', could be '$val'");
    }
    echo '<br />';
}
?>

<?php

if ($RCI->failures) {
  echo '<p class="warning">Sorry but your webserver does not meet the requirements for RoundCube!<br />
            Please install the missing modules or fix the php.ini settings according to the above check results.<br />
            Hint: only checks showing <span class="fail">NOT OK</span> need to be fixed.</p>';
}
echo '<p><br /><input type="submit" value="NEXT" ' . ($RCI->failures ? 'disabled' : '') . ' /></p>';

?>

</form>
