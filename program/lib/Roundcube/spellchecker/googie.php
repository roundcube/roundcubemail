<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
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
class rcube_spellchecker_googie extends rcube_spellchecker_engine
{
    const GOOGIE_HOST = 'https://spell.roundcube.net';

    private $matches = [];
    private $content;

    /**
     * Return a list of languages supported by this backend
     *
     * @see rcube_spellchecker_engine::languages()
     */
    function languages()
    {
        return [
            'am','ar','ar','bg','br','ca','cs','cy','da',
            'de_CH','de_DE','el','en_GB','en_US',
            'eo','es','et','eu','fa','fi','fr_FR','ga','gl','gl',
            'he','hr','hu','hy','is','it','ku','lt','lv','nl',
            'pl','pt_BR','pt_PT','ro','ru',
            'sk','sl','sv','uk'
        ];
    }

    /**
     * Set content and check spelling
     *
     * @see rcube_spellchecker_engine::check()
     */
    function check($text)
    {
        $this->content = $text;

        $matches = [];

        if (empty($text)) {
            return $this->matches = $matches;
        }

        $rcube  = rcube::get_instance();
        $client = $rcube->get_http_client();

        // spell check uri is configured
        $url = $rcube->config->get('spellcheck_uri');

        if (!$url) {
            $url = self::GOOGIE_HOST . '/tbproxy/spell?lang=';
        }
        $url .= $this->lang;
        $url .= sprintf('&key=%06d', !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0);

        $gtext = '<?xml version="1.0" encoding="utf-8" ?>'
            .'<spellrequest textalreadyclipped="0" ignoredups="0" ignoredigits="1" ignoreallcaps="1">'
            .'<text>' . htmlspecialchars($text, ENT_QUOTES, RCUBE_CHARSET) . '</text>'
            .'</spellrequest>';

        try {
            $response = $client->post($url, [
                    'connect_timeout' => 5, // seconds
                    'headers' => [
                        'User-Agent' => "Roundcube Webmail/" . RCUBE_VERSION . " (Googiespell Wrapper)",
                            'Content-type' => 'text/xml'
                    ],
                    'body' => $gtext
                ]
            );
        }
        catch (Exception $e) {
            // Do nothing, the error set below should be logged by the caller
        }

        if (empty($response)) {
            $this->error = $e ? $e->getMessage() : "Spelling engine failure";
        }
        else if ($response->getStatusCode() != 200) {
            $this->error = 'HTTP ' . $response->getReasonPhrase();
        }
        else {
            $response_body = $response->getBody();
            if (preg_match('/<spellresult error="([^"]+)"/', $response_body, $m) && $m[1]) {
                $this->error = "Error code $m[1] returned";
                $this->error .= preg_match('/<errortext>([^<]+)/', $response_body, $m) ? ": " . html_entity_decode($m[1]) : '';
            }

            preg_match_all('/<c o="([^"]*)" l="([^"]*)" s="([^"]*)">([^<]*)<\/c>/', $response_body, $matches, PREG_SET_ORDER);

            // skip exceptions (if appropriate options are enabled)
            foreach ($matches as $idx => $m) {
                $word = mb_substr($text, $m[1], $m[2], RCUBE_CHARSET);
                // skip  exceptions
                if ($this->dictionary->is_exception($word)) {
                    unset($matches[$idx]);
                }
            }
        }

        return $this->matches = $matches;
    }

    /**
     * Returns suggestions for the specified word
     *
     * @see rcube_spellchecker_engine::get_words()
     */
    function get_suggestions($word)
    {
        $matches = $word ? $this->check($word) : $this->matches;

        if (!empty($matches[0][4])) {
            $suggestions = explode("\t", $matches[0][4]);
            if (count($suggestions) > self::MAX_SUGGESTIONS) {
                $suggestions = array_slice($suggestions, 0, self::MAX_SUGGESTIONS);
            }

            return $suggestions;
        }

        return [];
    }

    /**
     * Returns misspelled words
     *
     * @see rcube_spellchecker_engine::get_suggestions()
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

        $result = [];

        foreach ($matches as $m) {
            $result[] = mb_substr($text, $m[1], $m[2], RCUBE_CHARSET);
        }

        return $result;
    }
}
