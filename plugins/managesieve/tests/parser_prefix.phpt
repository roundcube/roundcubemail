--TEST--
Test of prefix comments handling
--SKIPIF--
--FILE--
<?php
include '../lib/rcube_sieve_script.php';

$txt = '
# this is a comment
# and the second line

require ["variables"];
set "b" "c";
';

$s = new rcube_sieve_script($txt, array(), array('variables'));
echo $s->as_text();

?>
--EXPECT--
# this is a comment
# and the second line

require ["variables"];
set "b" "c";
