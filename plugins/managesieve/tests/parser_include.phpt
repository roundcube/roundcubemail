--TEST--
Test of Sieve include extension
--SKIPIF--
--FILE--
<?php
include '../lib/rcube_sieve_script.php';

$txt = '
require ["include"];

include "script.sieve";
# rule:[two]
if true
{
    include :optional "second.sieve";
}
';

$s = new rcube_sieve_script($txt, array(), array('variables'));
echo $s->as_text();

?>
--EXPECT--
require ["include"];
include "script.sieve";
# rule:[two]
if true
{
	include :optional "second.sieve";
}
