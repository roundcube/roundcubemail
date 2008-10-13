<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/mail/spell_pspell.inc                                   |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Use the Pspell extension to check spelling, returns results         |
 |   compatible with spell_googie.inc.                                   |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Kris Steinhoff <steinhof@umich.edu>                           |
 +-----------------------------------------------------------------------+

 $Id$

*/

if (!extension_loaded('pspell')) {
    raise_error(array(
      'code' => 500,
      'file' => __FILE__,
      'message' => "Pspell extension not available"), true, false);
      
    header('HTTP/1.1 404 Not Found');
    exit;
}

$data = file_get_contents('php://input');
$xml = simplexml_load_string($data);
$text = (string)$xml->text;
$words = preg_split('/[ !"#$%&()*+\\,-.\/\n:;<=>?@\[\]^_{|}]+/', $text, NULL,  PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE );
$plink = pspell_new(get_input_value('lang', RCUBE_INPUT_GET), null, null, 'utf-8');
$out = '<?xml version="1.0" encoding="UTF-8"?><spellresult charschecked="'.rc_strlen($text).'">';

$diff = 0;
foreach ($words as $w) {
    $word = $w[0];
    $pos  = $w[1] - $diff;
    $len  = rc_strlen($word);
    if ($plink && !pspell_check($plink, $word)) {
        $suggestions = pspell_suggest($plink, $word);
        $out .= '<c o="'.$pos.'" l="'.$len.'">';
        $out .= implode("\t", $suggestions);
        $out .= '</c>';
    }
    $diff += (strlen($word) - $len);
}
$out .= '</spellresult>';

header("Content-Type: text/xml");
echo $out;
exit;

?>
