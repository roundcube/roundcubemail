<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_spellchecker.php                                |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 | Copyright (C) 2008-2011, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Spellchecking using different backends                              |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


/**
 * Helper class for spellchecking with Googielspell and PSpell support.
 *
 * @package Core
 */
class rcube_spellchecker
{
    private $matches = array();
    private $engine;
    private $lang;
    private $rc;
    private $error;
    private $separator = '/[ !"#$%&()*+\\,\/\n:;<=>?@\[\]^_{|}-]+|\.[^\w]/';
    

    // default settings
    const GOOGLE_HOST = 'ssl://www.google.com';
    const GOOGLE_PORT = 443;
    const MAX_SUGGESTIONS = 10;


    /**
     * Constructor
     *
     * @param string $lang Language code
     */
    function __construct($lang = 'en')
    {
        $this->rc = rcmail::get_instance();
        $this->engine = $this->rc->config->get('spellcheck_engine', 'googie');
        $this->lang = $lang ? $lang : 'en';

        if ($this->engine == 'pspell' && !extension_loaded('pspell')) {
            raise_error(array(
                'code' => 500, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Pspell extension not available"), true, true);
        }
    }


    /**
     * Set content and check spelling
     *
     * @param string $text    Text content for spellchecking
     * @param bool   $is_html Enables HTML-to-Text conversion
     *
     * @return bool True when no mispelling found, otherwise false
     */
    function check($text, $is_html=false)
    {
        // convert to plain text
        if ($is_html) {
            $this->content = $this->html2text($text);
        }
        else {
            $this->content = $text;
        }

        if ($this->engine == 'pspell') {
            $this->matches = $this->_pspell_check($this->content);
        }
        else {
            $this->matches = $this->_googie_check($this->content);
        }

        return $this->found() == 0;
    }


    /**
     * Number of mispellings found (after check)
     *
     * @return int Number of mispellings
     */
    function found()
    {
        return count($this->matches);
    }


    /**
     * Returns suggestions for the specified word
     *
     * @param string $word The word
     *
     * @return array Suggestions list
     */
    function get_suggestions($word)
    {
        if ($this->engine == 'pspell') {
            return $this->_pspell_suggestions($word);
        }

        return $this->_googie_suggestions($word);    
    }
    

    /**
     * Returns mispelled words
     *
     * @param string $text The content for spellchecking. If empty content
     *                     used for check() method will be used.
     *
     * @return array List of mispelled words
     */
    function get_words($text = null, $is_html=false)
    {
        if ($this->engine == 'pspell') {
            return $this->_pspell_words($text, $is_html);
        }

        return $this->_googie_words($text, $is_html);
    }


    /**
     * Returns checking result in XML (Googiespell) format
     *
     * @return string XML content
     */
    function get_xml()
    {
        // send output
        $out = '<?xml version="1.0" encoding="'.RCMAIL_CHARSET.'"?><spellresult charschecked="'.mb_strlen($this->content).'">';

        foreach ($this->matches as $item) {
            $out .= '<c o="'.$item[1].'" l="'.$item[2].'">';
            $out .= is_array($item[4]) ? implode("\t", $item[4]) : $item[4];
            $out .= '</c>';
        }

        $out .= '</spellresult>';

        return $out;
    }


    /**
     * Returns checking result (mispelled words with suggestions)
     *
     * @return array Spellchecking result. An array indexed by word.
     */
    function get()
    {
        $result = array();

        foreach ($this->matches as $item) {
            if ($this->engine == 'pspell') {
                $word = $item[0];
            }
            else {
                $word = mb_substr($this->content, $item[1], $item[2], RCMAIL_CHARSET);
            }
            $result[$word] = is_array($item[4]) ? implode("\t", $item[4]) : $item[4];
        }

        return $out;
    }


    /**
     * Returns error message
     *
     * @return string Error message
     */
    function error()
    {
        return $this->error;
    }


    /**
     * Checks the text using pspell
     *
     * @param string $text Text content for spellchecking
     */
    private function _pspell_check($text)
    {
        // init spellchecker
        $this->_pspell_init();

        if (!$this->plink) {
            return array();
        }

        // tokenize
        $text = preg_split($this->separator, $text, NULL, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);

        $diff = 0;
        $matches = array();

        foreach ($text as $w) {
            $word = trim($w[0]);
            $pos  = $w[1] - $diff;
            $len  = mb_strlen($word);

            if ($word && preg_match('/[^0-9\.]/', $word) && !pspell_check($this->plink, $word)) {
                $suggestions = pspell_suggest($this->plink, $word);

	            if (sizeof($suggestions) > self::MAX_SUGGESTIONS)
	                $suggestions = array_slice($suggestions, 0, self::MAX_SUGGESTIONS);

                $matches[] = array($word, $pos, $len, null, $suggestions);
            }

            $diff += (strlen($word) - $len);
        }

        return $matches;
    }


    /**
     * Returns the mispelled words
     */
    private function _pspell_words($text = null, $is_html=false)
    {
        if ($text) {
            // init spellchecker
            $this->_pspell_init();

            if (!$this->plink) {
                return array();
            }

            // With PSpell we don't need to get suggestions to return mispelled words
            if ($is_html) {
                $text = $this->html2text($text);
            }

            $text = preg_split($this->separator, $text, NULL, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);

            foreach ($text as $w) {
                $word = trim($w[0]);
                if ($word && preg_match('/[^0-9\.]/', $word) && !pspell_check($this->plink, $word)) {
                    $result[] = $word;
                }
            }

            return $result;
        }

        $result = array();

        foreach ($this->matches as $m) {
            $result[] = $m[0];
        }

        return $result;
    }


    /**
     * Returns suggestions for mispelled word
     */
    private function _pspell_suggestions($word)
    {
        // init spellchecker
        $this->_pspell_init();

        if (!$this->plink) {
            return array();
        }

        $suggestions = pspell_suggest($this->plink, $word);

        if (sizeof($suggestions) > self::MAX_SUGGESTIONS)
            $suggestions = array_slice($suggestions, 0, self::MAX_SUGGESTIONS);

        return is_array($suggestions) ? $suggestions : array();
    }


    /**
     * Initializes PSpell dictionary
     */
    private function _pspell_init()
    {
        if (!$this->plink) {
            $this->plink = pspell_new($this->lang, null, null, RCMAIL_CHARSET, PSPELL_FAST);
        }

        if (!$this->plink) {
            $this->error = "Unable to load Pspell engine for selected language";
        }
    }


    private function _googie_check($text)
    {
        // spell check uri is configured
        $url = $this->rc->config->get('spellcheck_uri');

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
        $text = str_replace(' ', "\n", $text);

        $text = '<?xml version="1.0" encoding="utf-8" ?>'
            .'<spellrequest textalreadyclipped="0" ignoredups="0" ignoredigits="1" ignoreallcaps="1">'
            .'<text>' . $text . '</text>'
            .'</spellrequest>';

        $store = '';
        if ($fp = fsockopen($host, $port, $errno, $errstr, 30)) {
            $out = "POST $path HTTP/1.0\r\n";
            $out .= "Host: " . str_replace('ssl://', '', $host) . "\r\n";
            $out .= "Content-Length: " . strlen($text) . "\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "Connection: Close\r\n\r\n";
            $out .= $text;
            fwrite($fp, $out);

            while (!feof($fp))
                $store .= fgets($fp, 128);
            fclose($fp);
        }

        if (!$store) {
            $this->error = "Empty result from spelling engine";
        }

        preg_match_all('/<c o="([^"]*)" l="([^"]*)" s="([^"]*)">([^<]*)<\/c>/', $store, $matches, PREG_SET_ORDER);

        return $matches;
    }


    private function _googie_words($text = null, $is_html=false)
    {
        if ($text) {
            if ($is_html) {
                $text = $this->html2text($text);
            }

            $matches = $this->_googie_check($text);
        }
        else {
            $matches = $this->matches;
            $text    = $this->content;
        }

        $result = array();

        foreach ($matches as $m) {
            $result[] = mb_substr($text, $m[1], $m[2], RCMAIL_CHARSET);
        }

        return $result;
    }


    private function _googie_suggestions($word)
    {
        if ($word) {
            $matches = $this->_googie_check($word);
        }
        else {
            $matches = $this->matches;
        }

        if ($matches[0][4]) {
            $suggestions = explode("\t", $matches[0][4]);
            if (sizeof($suggestions) > self::MAX_SUGGESTIONS) {
                $suggestions = array_slice($suggestions, 0, MAX_SUGGESTIONS);
            }

            return $suggestions;
        }

        return array();
    }


    private function html2text($text)
    {
        $h2t = new html2text($text, false, true, 0);
        return $h2t->get_text();
    }
}
