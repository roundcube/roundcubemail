--TEST--
Test of Sieve vacation extension (RFC5232)
--SKIPIF--
--FILE--
<?php
include '../lib/rcube_sieve_script.php';

$txt = '
require ["imapflags"];
# rule:[imapflags]
if header :matches "Subject" "^Test$" {
    setflag "\\\\Seen";
    addflag ["\\\\Answered","\\\\Deleted"];
}
';

$s = new rcube_sieve_script($txt, array('imapflags'));
echo $s->as_text();

?>
--EXPECT--
require ["imapflags"];
# rule:[imapflags]
if header :matches "Subject" "^Test$"
{
	setflag "\\Seen";
	addflag ["\\Answered","\\Deleted"];
}
