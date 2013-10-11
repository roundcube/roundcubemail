<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) 2008-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Spellchecking backend implementation to work with Googiespell       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Spellchecking backend implementation to work with a Googiespell service
 *
 * @package    Framework
 * @subpackage Utils
 */
class rcube_spellcheck_googie extends rcube_spellcheck_engine
{
    const GOOGLE_HOST = 'ssl://www.google.com';
    const GOOGLE_PORT = 443;

    private $matches = array();
    private $content;

    /**
     * Set content and check spelling
     *
     * @see rcube_spellcheck_engine::check()
     */
    function check($text)
    {
        $this->content = $text;

        // spell check uri is configured
        $url = rcube::get_instance()->config->get('spellcheck_uri');

        if ($url) {
            $a_uri = parse_url($url);
            $ssl   = ($a_uri['scheme'] == 'https' || $a_uri['scheme'] == 'ssl');
            $port  = $a_uri['port'] ? $a_uri['port'] : ($ssl ? 443 : 80);
            $host  = ($ssl ? 'ssl://' : '') . $a_uri['host'];
            $path  = $a_uri['path'] . ($a_uri['query'] ? '?'.$a_uri['query'] : '') . $this->lang;
        }
        else {
            $host = self::GOOGLE_HOST;
            $port = self::GOOGLE_PORT;
            $path = '/tbproxy/spell?lang=' . $this->lang;
        }

        // Google has some problem with spaces, use \n instead
        $gtext = str_replace(' ', "\n", $text);

        $gtext = '<?xml version="1.0" encoding="utf-8" ?>'
            .'<spellrequest textalreadyclipped="0" ignoredups="0" ignoredigits="1" ignoreallcaps="1">'
            .'<text>' . $gtext . '</text>'
            .'</spellrequest>';

        $store = '';
        if ($fp = fsockopen($host, $port, $errno, $errstr, 30)) {
            $out = "POST $path HTTP/1.0\r\n";
            $out .= "Host: " . str_replace('ssl://', '', $host) . "\r\n";
            $out .= "Content-Length: " . strlen($gtext) . "\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "Connection: Close\r\n\r\n";
            $out .= $gtext;
            fwrite($fp, $out);

            while (!feof($fp))
                $store .= fgets($fp, 128);
            fclose($fp);
        }

        // parse HTTP response
        if (preg_match('!^HTTP/1.\d (\d+)(.+)!', $store, $m)) {
            $http_status = $m[1];
            if ($http_status != '200')
                $this->error = 'HTTP ' . $m[1] . $m[2];
        }

        if (!$store) {
            $this->error = "Empty result from spelling engine";
        }
        else if (preg_match('/<spellresult error="([^"]+)"/', $store, $m) && $m[1]) {
            $this->error = "Error code $m[1] returned";
        }

        preg_match_all('/<c o="([^"]*)" l="([^"]*)" s="([^"]*)">([^<]*)<\/c>/', $store, $matches, PREG_SET_ORDER);

        // skip exceptions (if appropriate options are enabled)
        foreach ($matches as $idx => $m) {
            $word = mb_substr($text, $m[1], $m[2], RCUBE_CHARSET);
            // skip  exceptions
            if ($this->dictionary->is_exception($word)) {
                unset($matches[$idx]);
            }
        }

        $this->matches = $matches;
        return $matches;
    }

    /**
     * Returns suggestions for the specified word
     *
     * @see rcube_spellcheck_engine::get_words()
     */
    function get_suggestions($word)
    {
        $matches = $word ? $this->check($word) : $this->matches;

        if ($matches[0][4]) {
            $suggestions = explode("\t", $matches[0][4]);
            if (sizeof($suggestions) > self::MAX_SUGGESTIONS) {
                $suggestions = array_slice($suggestions, 0, self::MAX_SUGGESTIONS);
            }

            return $suggestions;
        }

        return array();
    }

    /**
     * Returns misspelled words
     *
     * @see rcube_spellcheck_engine::get_suggestions()
     */
    function get_words($text = null)
    {
        if ($text) {
            $matches = $this->check($text);
        }
        else {
            $matches = $this->matches;
            $text    = $this->content;
        }

        $result = array();

        foreach ($matches as $m) {
            $result[] = mb_substr($text, $m[1], $m[2], RCUBE_CHARSET);
        }

        return $result;
    }

}

