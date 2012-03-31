--TEST--
Test of Sieve body extension (RFC5173)
--SKIPIF--
--FILE--
<?php
include '../lib/rcube_sieve_script.php';

$txt = '
require ["body","fileinto"];
if body :raw :contains "MAKE MONEY FAST"
{
	stop;
}
if body :content "text" :contains ["missile","coordinates"]
{
	fileinto "secrets";
}
if body :content "audio/mp3" :contains ""
{
	fileinto "jukebox";
}
if body :text :contains "project schedule"
{
	fileinto "project/schedule";
}
';

$s = new rcube_sieve_script($txt);
echo $s->as_text();

?>
--EXPECT--
require ["body","fileinto"];
if body :raw :contains "MAKE MONEY FAST"
{
	stop;
}
if body :content "text" :contains ["missile","coordinates"]
{
	fileinto "secrets";
}
if body :content "audio/mp3" :contains ""
{
	fileinto "jukebox";
}
if body :text :contains "project schedule"
{
	fileinto "project/schedule";
}
