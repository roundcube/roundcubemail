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
 |   Spellchecking backend implementation to work with Pspell            |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Spellchecking backend implementation to work with Pspell
 *
 * @package    Framework
 * @subpackage Utils
 */
class rcube_spellchecker_pspell extends rcube_spellchecker_engine
{
    private $plink;
    private $matches = [];

    /**
     * Return a list of languages supported by this backend
     *
     * @see rcube_spellchecker_engine::languages()
     */
    function languages()
    {
        $defaults = ['en'];
        $langs    = [];

        // get aspell dictionaries
        exec('aspell dump dicts', $dicts);
        if (!empty($dicts)) {
            $seen = [];
            foreach ($dicts as $lang) {
                $lang  = preg_replace('/-.*$/', '', $lang);
                $langc = strlen($lang) == 2 ? $lang.'_'.strtoupper($lang) : $lang;

                if (empty($seen[$langc])) {
                    $langs[] = $lang;
                    $seen[$langc] = true;
                }
            }

            $langs = array_unique($langs);
        }
        else {
            $langs = $defaults;
        }

        return $langs;
    }

    /**
     * Initializes PSpell dictionary
     */
    private function init()
    {
        if (!$this->plink) {
            if (!extension_loaded('pspell')) {
                $this->error = "Pspell extension not available";
                return;
            }

            $this->plink = pspell_new($this->lang, '', '', RCUBE_CHARSET, PSPELL_FAST);
        }

        if (!$this->plink) {
            $this->error = "Unable to load Pspell engine for selected language";
        }
    }

    /**
     * Set content and check spelling
     *
     * @see rcube_spellchecker_engine::check()
     */
    function check($text)
    {
        $this->init();

        if (!$this->plink) {
            return [];
        }

        // tokenize
        $text = preg_split($this->separator, $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);

        $diff    = 0;
        $matches = [];

        foreach ($text as $w) {
            $word = trim($w[0]);
            $pos  = $w[1] - $diff;
            $len  = mb_strlen($word);

            if ($this->dictionary->is_exception($word)) {
                // skip exceptions
            }
            else if (!pspell_check($this->plink, $word)) {
                $suggestions = pspell_suggest($this->plink, $word);

                if (count($suggestions) > self::MAX_SUGGESTIONS) {
                    $suggestions = array_slice($suggestions, 0, self::MAX_SUGGESTIONS);
                }

                $matches[] = [$word, $pos, $len, null, $suggestions];
            }

            $diff += (strlen($word) - $len);
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
        $this->init();

        if (!$this->plink) {
            return [];
        }

        $suggestions = pspell_suggest($this->plink, $word);

        if (count($suggestions) > self::MAX_SUGGESTIONS) {
            $suggestions = array_slice($suggestions, 0, self::MAX_SUGGESTIONS);
        }

        return $suggestions ?: [];
    }

    /**
     * Returns misspelled words
     *
     * @see rcube_spellchecker_engine::get_suggestions()
     */
    function get_words($text = null)
    {
        $result = [];

        if ($text) {
            // init spellchecker
            $this->init();

            if (!$this->plink) {
                return [];
            }

            // With PSpell we don't need to get suggestions to return misspelled words
            $text = preg_split($this->separator, $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);

            foreach ($text as $w) {
                $word = trim($w[0]);

                // skip exceptions
                if ($this->dictionary->is_exception($word)) {
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
}
