<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

if (!class_exists('rcmail_install', false) || !isset($RCI)) {
    die("Not allowed! Please open installer/index.php instead.");
}

$required_php_exts = [
    'PCRE'      => 'pcre',
    'DOM'       => 'dom',
    'Session'   => 'session',
    'XML'       => 'xml',
    'Intl'      => 'intl',
    'JSON'      => 'json',
    'PDO'       => 'PDO',
    'Multibyte' => 'mbstring',
    'OpenSSL'   => 'openssl',
    'Filter'    => 'filter',
    'Ctype'     => 'ctype',
];

$optional_php_exts = [
    'cURL'      => 'curl',
    'FileInfo'  => 'fileinfo',
    'Exif'      => 'exif',
    'Iconv'     => 'iconv',
    'LDAP'      => 'ldap',
    'GD'        => 'gd',
    'Imagick'   => 'imagick',
    'XMLWriter' => 'xmlwriter',
    'Zip'       => 'zip',
];

$required_libs = [
    'PEAR'      => 'pear.php.net',
    'Auth_SASL' => 'pear.php.net',
    'Net_SMTP'  => 'pear.php.net',
    'Mail_mime' => 'pear.php.net',
    'GuzzleHttp\Client' => 'github.com/guzzle/guzzle',
];

$optional_libs = [
    'Net_LDAP3' => 'git.kolab.org',
];

$ini_checks = [
    'file_uploads'            => 1,
    'session.auto_start'      => 0,
    'mbstring.func_overload'  => 0,
    'suhosin.session.encrypt' => 0,
];

$optional_checks = [
    'date.timezone' => '-VALID-',
];

$source_urls = [
    'cURL'      => 'https://www.php.net/manual/en/book.curl.php',
    'Sockets'   => 'https://www.php.net/manual/en/book.sockets.php',
    'Session'   => 'https://www.php.net/manual/en/book.session.php',
    'PCRE'      => 'https://www.php.net/manual/en/book.pcre.php',
    'FileInfo'  => 'https://www.php.net/manual/en/book.fileinfo.php',
    'Multibyte' => 'https://www.php.net/manual/en/book.mbstring.php',
    'OpenSSL'   => 'https://www.php.net/manual/en/book.openssl.php',
    'JSON'      => 'https://www.php.net/manual/en/book.json.php',
    'DOM'       => 'https://www.php.net/manual/en/book.dom.php',
    'Iconv'     => 'https://www.php.net/manual/en/book.iconv.php',
    'Intl'      => 'https://www.php.net/manual/en/book.intl.php',
    'Exif'      => 'https://www.php.net/manual/en/book.exif.php',
    'oci8'      => 'https://www.php.net/manual/en/book.oci8.php',
    'PDO'       => 'https://www.php.net/manual/en/book.pdo.php',
    'LDAP'      => 'https://www.php.net/manual/en/book.ldap.php',
    'GD'        => 'https://www.php.net/manual/en/book.image.php',
    'Imagick'   => 'https://www.php.net/manual/en/book.imagick.php',
    'XML'       => 'https://www.php.net/manual/en/book.xml.php',
    'XMLWriter' => 'https://www.php.net/manual/en/book.xmlwriter.php',
    'Zip'       => 'https://www.php.net/manual/en/book.zip.php',
    'Filter'    => 'https://www.php.net/manual/en/book.filter.php',
    'Ctype'     => 'https://www.php.net/manual/en/book.ctype.php',
    'pdo_mysql'   => 'https://www.php.net/manual/en/ref.pdo-mysql.php',
    'pdo_pgsql'   => 'https://www.php.net/manual/en/ref.pdo-pgsql.php',
    'pdo_sqlite'  => 'https://www.php.net/manual/en/ref.pdo-sqlite.php',
    'pdo_sqlite2' => 'https://www.php.net/manual/en/ref.pdo-sqlite.php',
    'pdo_sqlsrv'  => 'https://www.php.net/manual/en/ref.pdo-sqlsrv.php',
    'pdo_dblib'   => 'https://www.php.net/manual/en/ref.pdo-dblib.php',
    'PEAR'      => 'https://pear.php.net',
    'Net_SMTP'  => 'https://pear.php.net/package/Net_SMTP',
    'Mail_mime' => 'https://pear.php.net/package/Mail_mime',
    'Net_LDAP3' => 'https://git.kolab.org/diffusion/PNL',
];

?>
<form action="index.php" method="get">

<?php
echo '<input type="hidden" name="_step" value="' . ($RCI->configured ? 3 : 2) . '" />';
?>

<h3>Checking PHP version</h3>
<?php

define('MIN_PHP_VERSION', '7.3.0');
if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '>=')) {
    $RCI->pass('Version', 'PHP ' . PHP_VERSION . ' detected');
}
else {
    $RCI->fail('Version', 'PHP Version ' . MIN_PHP_VERSION . ' or greater is required ' . PHP_VERSION . ' detected');
}
?>

<h3>Checking PHP extensions</h3>
<p class="hint">The following modules/extensions are <em>required</em> to run Roundcube:</p>
<?php

// get extensions location
$ext_dir = ini_get('extension_dir');

$prefix = PHP_SHLIB_SUFFIX === 'dll' ? 'php_' : '';
foreach ($required_php_exts as $name => $ext) {
    if (extension_loaded($ext)) {
        $RCI->pass($name);
    }
    else {
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

$prefix = PHP_SHLIB_SUFFIX === 'dll' ? 'php_' : '';
foreach ($RCI->supported_dbs as $database => $ext) {
    if (extension_loaded($ext)) {
        $RCI->pass($database);
        $found_db_driver = true;
    }
    else {
        $_ext = $ext_dir . '/' . $prefix . $ext . '.' . PHP_SHLIB_SUFFIX;
        $msg = @is_readable($_ext) ? 'Could be loaded. Please add in php.ini' : '';
        $RCI->na($database, $msg, $source_urls[$ext]);
    }
    echo '<br />';
}
if (empty($found_db_driver)) {
  $RCI->failures++;
}

?>

<h3>Check for required 3rd party libs</h3>
<p class="hint">This also checks if the include path is set correctly.</p>

<?php

foreach ($required_libs as $classname => $vendor) {
    if (class_exists($classname)) {
        $RCI->pass($classname);
    }
    else {
        $RCI->fail($classname, "Failed to load class $classname from $vendor", $source_urls[$classname]);
    }
    echo "<br />";
}

foreach ($optional_libs as $classname => $vendor) {
    if (class_exists($classname)) {
        $RCI->pass($classname);
    }
    else {
        $RCI->na($classname, "Recommended to install $classname from $vendor", $source_urls[$classname]);
    }
    echo "<br />";
}

?>

<h3>Checking php.ini/.htaccess settings</h3>
<p class="hint">The following settings are <em>required</em> to run Roundcube:</p>

<?php

foreach ($ini_checks as $var => $val) {
    $status = ini_get($var);
    if ($val === '-NOTEMPTY-') {
        if (empty($status)) {
            $RCI->fail($var, "empty value detected");
        }
        else {
            $RCI->pass($var);
        }
    }
    else if (filter_var($status, FILTER_VALIDATE_BOOLEAN) == $val) {
        $RCI->pass($var);
    }
    else {
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
        }
        else {
            $RCI->pass($var);
        }
        echo '<br />';
        continue;
    }
    if ($val === '-VALID-') {
        if ($var == 'date.timezone') {
            try {
                $tz = new DateTimeZone($status);
                $RCI->pass($var);
            }
            catch (Exception $e) {
                $RCI->optfail($var, empty($status) ? "not set" : "invalid value detected: $status");
            }
        }
        else {
            $RCI->pass($var);
        }
    }
    else if (filter_var($status, FILTER_VALIDATE_BOOLEAN) == $val) {
        $RCI->pass($var);
    }
    else {
      $RCI->optfail($var, "is '$status', could be '$val'");
    }
    echo '<br />';
}
?>

<?php

if ($RCI->failures) {
    echo '<p class="warning">Sorry but your webserver does not meet the requirements for Roundcube!<br />
            Please install the missing modules or fix the php.ini settings according to the above check results.<br />
            Hint: only checks showing <span class="fail">NOT OK</span> need to be fixed.</p>';
}
echo '<p><br /><input type="submit" value="NEXT" ' . ($RCI->failures ? 'disabled' : '') . ' /></p>';

?>

</form>
