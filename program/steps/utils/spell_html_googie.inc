<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/utils/spell_html_googie.inc                             |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2010, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Submit request to Google's spell checking engine                    |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id: spell_googie.inc 3780 2010-06-23 09:55:08Z alec $

*/

function json_error($str)
{
    echo '{"error":{"errstr":"' . addslashes($str) . '","errfile":"","errline":null,"errcontext":"","level":"FATAL"}}';
    exit;
}

function googie_get($host, $port, $path, $data)
{
    $store = '';
    if ($fp = fsockopen($host, $port, $errno, $errstr, 30)) {
        $out = "POST $path HTTP/1.0\r\n";
        $out .= "Host: $host\r\n";
        $out .= "Content-Length: " . strlen($data) . "\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $out .= $data;
        fwrite($fp, $out);

        while (!feof($fp))
            $store .= fgets($fp, 128);
        fclose($fp);
    }

    if (!$store) {
        json_error("Empty result from spelling engine");
    }

    $matches = array();
    preg_match_all('/<c o="([^"]*)" l="([^"]*)" s="([^"]*)">([^<]*)<\/c>/', $store, $matches, PREG_SET_ORDER);

    return $matches;
}

$REMOTE_REQUEST = TRUE;

// read input
$data = file_get_contents('php://input');

// Decode JSON input
$request = json_decode($data, true);
$result = array();

$lang = $request['params'][0];
$data = $request['params'][1];
$result['id'] = $request['id'];

// default settings
$host = "ssl://www.google.com";
$port = 443;
$path = "/tbproxy/spell?lang=$lang";

// spell check uri is configured
if (!empty($CONFIG['spellcheck_uri']))
  {
  $a_uri = parse_url($CONFIG['spellcheck_uri']);
  $ssl = ($a_uri['scheme']=='https' || $a_uri['scheme']=='ssl');
  $port = $a_uri['port'] ? $a_uri['port'] : ($ssl ? 443 : 80);
  $host = ($ssl ? 'ssl://' : '') . $a_uri['host'];
  $path = $a_uri['path'] . ($a_uri['query'] ? '?'.$a_uri['query'] : '') . $lang;
  }

$wordstr = implode(' ', (array) $data);
$data = '<?xml version="1.0" encoding="utf-8" ?>'
    .'<spellrequest textalreadyclipped="0" ignoredups="0" ignoredigits="1" ignoreallcaps="1">'
    .'<text>' . $wordstr . '</text>'
    .'</spellrequest>';
$matches = googie_get($host, $port, $path, $data);

if ($request['method'] == 'checkWords') {
    $result['result'] = array();
    for ($i=0, $len=count($matches); $i<$len; $i++)
        $result['result'][] = mb_substr($wordstr, $matches[$i][1], $matches[$i][2], RCMAIL_CHARSET);
}
else if ($request['method'] == 'getSuggestions') {
    if ($matches[0][4]) {
        $suggestions = explode("\t", $matches[0][4]);
        if (sizeof($suggestions)>MAX_SUGGESTIONS)
            $suggestions = array_slice($suggestions, 0, MAX_SUGGESTIONS);
        $result['result'] = $suggestions;
    }
    else
        $result['result'] = array();
}

// send output
header("Content-Type: text/xml; charset=".RCMAIL_CHARSET);
echo json_encode($result);
exit;

