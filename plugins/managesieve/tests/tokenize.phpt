--TEST--
Script parsing: tokenizer
--SKIPIF--
--FILE--
<?php
include('../lib/rcube_sieve.php');

$txt[1] = array(1, 'text: #test
This is test ; message;
Multi line
.
;
');
$txt[2] = array(0, '["test1","test2"]');
$txt[3] = array(1, '["test"]');
$txt[4] = array(1, '"te\\"st"');
$txt[5] = array(0, 'test #comment');
$txt[6] = array(0, 'text:
test
.
text:
test
.
');
$txt[7] = array(1, '"\\a\\\\\\"a"');

foreach ($txt as $idx => $t) {
    echo "[$idx]---------------\n"; 
    var_dump(rcube_sieve_script::tokenize($t[1], $t[0]));
}
?>
--EXPECT--
[1]---------------
string(34) "This is test ; message;
Multi line"
[2]---------------
array(1) {
  [0]=>
  array(2) {
    [0]=>
    string(5) "test1"
    [1]=>
    string(5) "test2"
  }
}
[3]---------------
array(1) {
  [0]=>
  string(4) "test"
}
[4]---------------
string(5) "te"st"
[5]---------------
array(1) {
  [0]=>
  string(4) "test"
}
[6]---------------
array(2) {
  [0]=>
  string(4) "test"
  [1]=>
  string(4) "test"
}
[7]---------------
string(4) "a\"a"
