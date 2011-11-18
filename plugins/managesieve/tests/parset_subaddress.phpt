--TEST--
Test of Sieve subaddress extension (RFC5233)
--SKIPIF--
--FILE--
<?php
include '../lib/rcube_sieve_script.php';

$txt = '
require ["envelope","subaddress","fileinto"];
if envelope :user "To" "postmaster"
{
	fileinto "postmaster";
	stop;
}
if envelope :detail :is "To" "mta-filters"
{
	fileinto "mta-filters";
	stop;
}
';

$s = new rcube_sieve_script($txt);
echo $s->as_text();

// -------------------------------------------------------------------------------
?>
--EXPECT--
require ["envelope","subaddress","fileinto"];
if envelope :user "To" "postmaster"
{
	fileinto "postmaster";
	stop;
}
if envelope :detail :is "To" "mta-filters"
{
	fileinto "mta-filters";
	stop;
}
