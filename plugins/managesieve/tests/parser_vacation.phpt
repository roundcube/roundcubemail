--TEST--
Test of Sieve vacation extension (RFC5230)
--SKIPIF--
--FILE--
<?php
include '../lib/rcube_sieve_script.php';

$txt = '
require ["vacation"];
# rule:[test-vacation]
if anyof (header :contains "Subject" "vacation")
{
	vacation :days 1 text:
# test
test test /* test */
test
.
;
	stop;
}
';

$s = new rcube_sieve_script($txt);
echo $s->as_text();

?>
--EXPECT--
require ["vacation"];
# rule:[test-vacation]
if header :contains "Subject" "vacation"
{
	vacation :days 1 text:
# test
test test /* test */
test
.
;
	stop;
}
