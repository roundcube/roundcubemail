--TEST--
Main test of script parser
--SKIPIF--
--FILE--
<?php
include '../lib/rcube_sieve_script.php';

$txt = '
require ["fileinto","reject","envelope"];
# rule:[spam]
if anyof (header :contains "X-DSPAM-Result" "Spam")
{
	fileinto "Spam";
	stop;
}
# rule:[test1]
if anyof (header :comparator "i;ascii-casemap" :contains ["From","To"] "test@domain.tld")
{
	discard;
	stop;
}
# rule:[test2]
if anyof (not header :comparator "i;octet" :contains ["Subject"] "[test]", header :contains "Subject" "[test2]")
{
	fileinto "test";
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
# rule:[false]
if false # size :over 5000K
{
	stop; /* rule disabled */
}
# rule:[true]
if true
{
	stop;
}
fileinto "Test";
# rule:[address test]
if address :all :is "From" "nagios@domain.tld"
{
	fileinto "domain.tld";
	stop;
}
# rule:[envelope test]
if envelope :domain :is "From" "domain.tld"
{
	fileinto "domain.tld";
	stop;
}
';

$s = new rcube_sieve_script($txt);
echo $s->as_text();

// -------------------------------------------------------------------------------
?>
--EXPECT--
require ["fileinto","reject","envelope"];
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
if anyof (not header :comparator "i;octet" :contains "Subject" "[test]", header :contains "Subject" "[test2]")
{
	fileinto "test";
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
# rule:[false]
if false # size :over 5000K
{
	stop;
}
# rule:[true]
if true
{
	stop;
}
fileinto "Test";
# rule:[address test]
if address :all :is "From" "nagios@domain.tld"
{
	fileinto "domain.tld";
	stop;
}
# rule:[envelope test]
if envelope :domain :is "From" "domain.tld"
{
	fileinto "domain.tld";
	stop;
}
