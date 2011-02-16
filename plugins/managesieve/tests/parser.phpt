--TEST--
Main test of script parser
--SKIPIF--
--FILE--
<?php
include('../lib/rcube_sieve.php');

$txt = '
require ["fileinto","vacation","reject","relational","comparator-i;ascii-numeric"];
# rule:[spam]
if anyof (header :contains "X-DSPAM-Result" "Spam")
{
	fileinto "Spam";
	stop;
}
# rule:[test1]
if anyof (header :contains ["From","To"] "test@domain.tld")
{
	discard;
	stop;
}
# rule:[test2]
if anyof (not header :contains ["Subject"] "[test]", header :contains "Subject" "[test2]")
{
	fileinto "test";
	stop;
}
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
# rule:[comments]
if anyof (true) /* comment
 * "comment" #comment */ {
    /* comment */ stop;
# comment
}
# rule:[reject]
if size :over 5000K {
    reject "Message over 5MB size limit. Please contact me before sending this.";
}
# rule:[redirect]
if header :value "ge" :comparator "i;ascii-numeric"
    ["X-Spam-score"] ["14"] {redirect "test@test.tld";}
';

$s = new rcube_sieve_script($txt);
echo $s->as_text();

?>
--EXPECT--
require ["fileinto","vacation","reject","relational","comparator-i;ascii-numeric"];
# rule:[spam]
if header :contains "X-DSPAM-Result" "Spam"
{
	fileinto "Spam";
	stop;
}
# rule:[test1]
if header :contains ["From","To"] "test@domain.tld"
{
	discard;
	stop;
}
# rule:[test2]
if anyof (not header :contains "Subject" "[test]", header :contains "Subject" "[test2]")
{
	fileinto "test";
	stop;
}
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
# rule:[comments]
if true
{
	stop;
}
# rule:[reject]
if size :over 5000K
{
	reject "Message over 5MB size limit. Please contact me before sending this.";
}
# rule:[redirect]
if header :value "ge" :comparator "i;ascii-numeric" "X-Spam-score" "14"
{
	redirect "test@test.tld";
}
