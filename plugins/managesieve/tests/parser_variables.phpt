--TEST--
Test of Sieve variables extension
--SKIPIF--
--FILE--
<?php
include '../lib/rcube_sieve_script.php';

$txt = '
require ["variables"];
set "honorific" "Mr";
set "vacation" text:
Dear ${HONORIFIC} ${last_name},
I am out, please leave a message after the meep.
.
;
set :length "b" "${a}";
set :lower "b" "${a}";
set :upperfirst "b" "${a}";
set :upperfirst :lower "b" "${a}";
set :quotewildcard "b" "Rock*";
';

$s = new rcube_sieve_script($txt, array(), array('variables'));
echo $s->as_text();

?>
--EXPECT--
require ["variables"];
set "honorific" "Mr";
set "vacation" text:
Dear ${HONORIFIC} ${last_name},
I am out, please leave a message after the meep.
.
;
set :length "b" "${a}";
set :lower "b" "${a}";
set :upperfirst "b" "${a}";
set :upperfirst :lower "b" "${a}";
set :quotewildcard "b" "Rock*";
