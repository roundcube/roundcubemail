<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/utils/spell_pspell_tiny.inc                             |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2010, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Use the Pspell extension to check spelling in TinyMCE               |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id: spell_pspell.inc 3780 2010-06-23 09:55:08Z alec $

*/

function json_error($str)
{
    echo '{"error":{"errstr":"' . addslashes($str) . '","errfile":"","errline":null,"errcontext":"","level":"FATAL"}}';
    exit;
}

if (!extension_loaded('pspell')) {
    raise_error(array(
      'code' => 500,
      'type' => 'php',
      'file' => __FILE__, 'line' => __LINE__,
      'message' => "Pspell extension not available"), true, false);

    json_error("Pspell extension not available");
}

// read input
$data = file_get_contents('php://input');

// Decode JSON input
$request = json_decode($data, true);
$result = array();

$lang = $request['params'][0];
$data = $request['params'][1];
$result['id'] = $request['id'];

// init spellchecker
$plink = pspell_new($lang, null, null, RCMAIL_CHARSET, PSPELL_FAST);

if (!$plink) {
    json_error("Unable to load Pspell engine for selected language");
}

if ($request['method'] == 'checkWords') {
    $result['result'] = array();
    foreach ((array)$data as $word) {
        if ($word && preg_match('/[^0-9\.]/', $word)
	        && !pspell_check($plink, $word)) {
            $result['result'][] = $word;
        }
    }
}
else if ($request['method'] == 'getSuggestions') {
    $suggestions = pspell_suggest($plink, $data);
    if (sizeof($suggestions)>MAX_SUGGESTIONS)
        $suggestions = array_slice($suggestions, 0, MAX_SUGGESTIONS);
    $result['result'] = $suggestions;
}

// send output
header("Content-Type: text/xml; charset=".RCMAIL_CHARSET);
echo json_encode($result);
exit;

