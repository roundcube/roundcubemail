--TEST--
Test of Sieve relational extension (RFC5231)
--SKIPIF--
--FILE--
<?php
include '../lib/rcube_sieve_script.php';

$txt = '
require ["relational","comparator-i;ascii-numeric"];
# rule:[redirect]
if header :value "ge" :comparator "i;ascii-numeric"
    ["X-Spam-score"] ["14"] {redirect "test@test.tld";}
';

$s = new rcube_sieve_script($txt);
echo $s->as_text();

?>
--EXPECT--
require ["relational","comparator-i;ascii-numeric"];
# rule:[redirect]
if header :value "ge" :comparator "i;ascii-numeric" "X-Spam-score" "14"
{
	redirect "test@test.tld";
}
