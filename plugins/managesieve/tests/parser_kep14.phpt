--TEST--
Test of Kolab's KEP:14 implementation
--SKIPIF--
--FILE--
<?php
include '../lib/rcube_sieve_script.php';

$txt = '
# EDITOR Roundcube
# EDITOR_VERSION 123
';

$s = new rcube_sieve_script($txt, array('body'));
echo $s->as_text();

?>
--EXPECT--
# EDITOR Roundcube
# EDITOR_VERSION 123
