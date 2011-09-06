<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/utils/spell_html.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Spellchecker for TinyMCE                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/

// read input data
$data = file_get_contents('php://input');

// Decode JSON input
$request = json_decode($data, true);
$result = array();

$lang = $request['params'][0];
$data = $request['params'][1];
$data = implode("\n", (array) $data);

$result['id'] = $request['id'];

$spellchecker = new rcube_spellchecker($lang);

if ($request['method'] == 'checkWords') {
    $result['result'] = $spellchecker->get_words($data);
}
else if ($request['method'] == 'getSuggestions') {
    $result['result'] = $spellchecker->get_suggestions($data);
}
else if ($request['method'] == 'learnWord') {
    $spellchecker->add_word($data);
    $result['result'] = true;
}

if ($error = $spellchecker->error()) {
    echo '{"error":{"errstr":"' . addslashes($error) . '","errfile":"","errline":null,"errcontext":"","level":"FATAL"}}';
    exit;
}

// send output
header("Content-Type: text/xml; charset=".RCMAIL_CHARSET);
echo json_encode($result);
exit;

