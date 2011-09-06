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
    private $separator = '/[\s\r\n\t\(\)\/\[\]{}<>\\"]+|[:;?!,\.]([^\w]|$)/';
    private $options = array();
    private $dict;
    private $have_dict;


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
        $this->rc     = rcmail::get_instance();
        $this->engine = $this->rc->config->get('spellcheck_engine', 'googie');
        $this->lang   = $lang ? $lang : 'en';

        if ($this->engine == 'pspell' && !extension_loaded('pspell')) {
            raise_error(array(
                'code' => 500, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Pspell extension not available"), true, true);
        }

        $this->options = array(
            'ignore_syms' => $this->rc->config->get('spellcheck_ignore_syms'),
            'ignore_nums' => $this->rc->config->get('spellcheck_ignore_nums'),
            'ignore_caps' => $this->rc->config->get('spellcheck_ignore_caps'),
            'dictionary'  => $this->rc->config->get('spellcheck_dictionary'),
        );
    }


    /**
     * Set content and check spelling
     *
     * @param string $text    Text content for spellchecking
     * @param bool   $is_html Enables HTML-to-Text conversion
     *
     * @return bool True when no mispelling found, otherwise false
     */
    function check($text, $is_html = false)
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

        return $result;
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

        $diff       = 0;
        $matches    = array();

        foreach ($text as $w) {
            $word = trim($w[0]);
            $pos  = $w[1] - $diff;
            $len  = mb_strlen($word);

            // skip exceptions
            if ($this->is_exception($word)) {
            }
            else if (!pspell_check($this->plink, $word)) {
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
        $result = array();

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

                // skip exceptions
                if ($this->is_exception($word)) {
                    continue;
                }

                if (!pspell_check($this->plink, $word)) {
                    $result[] = $word;
                }
            }

            return $result;
        }

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

        if (!$store) {
            $this->error = "Empty result from spelling engine";
        }

        preg_match_all('/<c o="([^"]*)" l="([^"]*)" s="([^"]*)">([^<]*)<\/c>/', $store, $matches, PREG_SET_ORDER);

        // skip exceptions (if appropriate options are enabled)
        if (!empty($this->options['ignore_syms']) || !empty($this->options['ignore_nums'])
            || !empty($this->options['ignore_caps']) || !empty($this->options['dictionary'])
        ) {
            foreach ($matches as $idx => $m) {
                $word = mb_substr($text, $m[1], $m[2], RCMAIL_CHARSET);
                // skip  exceptions
                if ($this->is_exception($word)) {
                    unset($matches[$idx]);
                }
            }
        }

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


    /**
     * Check if the specified word is an exception accoring to 
     * spellcheck options.
     *
     * @param string  $word  The word
     *
     * @return bool True if the word is an exception, False otherwise
     */
    public function is_exception($word)
    {
        // Contain only symbols (e.g. "+9,0", "2:2")
        if (!$word || preg_match('/^[0-9@#$%^&_+~*=:;?!,.-]+$/', $word))
            return true;

        // Contain symbols (e.g. "g@@gle"), all symbols excluding separators
        if (!empty($this->options['ignore_syms']) && preg_match('/[@#$%^&_+~*=-]/', $word))
            return true;

        // Contain numbers (e.g. "g00g13")
        if (!empty($this->options['ignore_nums']) && preg_match('/[0-9]/', $word))
            return true;

        // Blocked caps (e.g. "GOOGLE")
        if (!empty($this->options['ignore_caps']) && $word == mb_strtoupper($word))
            return true;

        // Use exceptions from dictionary
        if (!empty($this->options['dictionary'])) {
            $this->load_dict();

            // @TODO: should dictionary be case-insensitive?
            if (!empty($this->dict) && in_array($word, $this->dict))
                return true;
        }

        return false;
    }


    /**
     * Add a word to dictionary
     *
     * @param string  $word  The word to add
     */
    public function add_word($word)
    {
        $this->load_dict();

        foreach (explode(' ', $word) as $word) {
            // sanity check
            if (strlen($word) < 512) {
                $this->dict[] = $word;
                $valid = true;
            }
        }

        if ($valid) {
            $this->dict = array_unique($this->dict);
            $this->update_dict();
        }
    }


    /**
     * Remove a word from dictionary
     *
     * @param string  $word  The word to remove
     */
    public function remove_word($word)
    {
        $this->load_dict();

        if (($key = array_search($word, $this->dict)) !== false) {
            unset($this->dict[$key]);
            $this->update_dict();
        }
    }


    /**
     * Update dictionary row in DB
     */
    private function update_dict()
    {
        if (strcasecmp($this->options['dictionary'], 'shared') != 0) {
            $userid = (int) $this->rc->user->ID;
        }

        $plugin = $this->rc->plugins->exec_hook('spell_dictionary_save', array(
            'userid' => $userid, 'language' => $this->lang, 'dictionary' => $this->dict));

        if (!empty($plugin['abort'])) {
            return;
        }

        if ($this->have_dict) {
            if (!empty($this->dict)) {
                $this->rc->db->query(
                    "UPDATE ".get_table_name('dictionary')
                    ." SET data = ?"
                    ." WHERE user_id " . ($plugin['userid'] ? "= ".$plugin['userid'] : "IS NULL")
                        ." AND " . $this->rc->db->quoteIdentifier('language') . " = ?",
                    implode(' ', $plugin['dictionary']), $plugin['language']);
            }
            // don't store empty dict
            else {
                $this->rc->db->query(
                    "DELETE FROM " . get_table_name('dictionary')
                    ." WHERE user_id " . ($plugin['userid'] ? "= ".$plugin['userid'] : "IS NULL")
                        ." AND " . $this->rc->db->quoteIdentifier('language') . " = ?",
                    $plugin['language']);
            }
        }
        else if (!empty($this->dict)) {
            $this->rc->db->query(
                "INSERT INTO " .get_table_name('dictionary')
                ." (user_id, " . $this->rc->db->quoteIdentifier('language') . ", data) VALUES (?, ?, ?)",
                $plugin['userid'], $plugin['language'], implode(' ', $plugin['dictionary']));
        }
    }


    /**
     * Get dictionary from DB
     */
    private function load_dict()
    {
        if (is_array($this->dict)) {
            return $this->dict;
        }

        if (strcasecmp($this->options['dictionary'], 'shared') != 0) {
            $userid = (int) $this->rc->user->ID;
        }

        $plugin = $this->rc->plugins->exec_hook('spell_dictionary_get', array(
            'userid' => $userid, 'language' => $this->lang, 'dictionary' => array()));

        if (empty($plugin['abort'])) {
            $dict = array();
            $this->rc->db->query(
                "SELECT data FROM ".get_table_name('dictionary')
                ." WHERE user_id ". ($plugin['userid'] ? "= ".$plugin['userid'] : "IS NULL")
                    ." AND " . $this->rc->db->quoteIdentifier('language') . " = ?",
                $plugin['language']);

            if ($sql_arr = $this->rc->db->fetch_assoc($sql_result)) {
                $this->have_dict = true;
                if (!empty($sql_arr['data'])) {
                    $dict = explode(' ', $sql_arr['data']);
                }
            }

            $plugin['dictionary'] = array_merge((array)$plugin['dictionary'], $dict);
        }

        if (!empty($plugin['dictionary']) && is_array($plugin['dictionary'])) {
            $this->dict = $plugin['dictionary'];
        }
        else {
            $this->dict = array();
        }

        return $this->dict;
    }

}
