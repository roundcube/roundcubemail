<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/utils/spell_html.inc                                    |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Spellchecker for TinyMCE                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
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
    $result['result'] = empty($data) ? array() : $spellchecker->get_words($data);
}
else if ($request['method'] == 'getSuggestions') {
    $result['result'] = $spellchecker->get_suggestions($data);
}
else if ($request['method'] == 'learnWord') {
    $spellchecker->add_word($data);
    $result['result'] = true;
}

if ($error = $spellchecker->error()) {
    rcube::raise_error(array('code' => 500, 'type' => 'php',
        'file' => __FILE__, 'line' => __LINE__,
        'message' => sprintf("Spell check engine error: " . $error)),
        true, false);

    echo '{"error":{"errstr":"' . addslashes($error) . '","errfile":"","errline":null,"errcontext":"","level":"FATAL"}}';
    exit;
}

// send output
header("Content-Type: text/xml; charset=".RCUBE_CHARSET);
echo json_encode($result);
exit;

