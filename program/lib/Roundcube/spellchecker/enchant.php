<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Spellchecking backend implementation to work with Enchant           |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+
*/

/**
 * Spellchecking backend implementation to work with Pspell
 */
class rcube_spellchecker_enchant extends rcube_spellchecker_engine
{
    private $enchant_broker;
    private $enchant_dictionary;

    /**
     * Free object's resources
     */
    public function __destruct()
    {
        // If we don't do this we get "dictionaries weren't free'd" warnings in tests
        if ($this->enchant_dictionary) {
            $this->enchant_dictionary = null;
        }
    }

    /**
     * Return a list of languages supported by this backend
     *
     * @see rcube_spellchecker_engine::languages()
     */
    #[Override]
    public function languages()
    {
        $this->init();

        if (!$this->enchant_broker) {
            return [];
        }

        $langs = [];
        if ($dicts = enchant_broker_list_dicts($this->enchant_broker)) {
            foreach ($dicts as $dict) {
                $langs[] = preg_replace('/-.*$/', '', $dict['lang_tag']);
            }
        }

        return array_unique($langs);
    }

    /**
     * Initializes Enchant dictionary
     */
    private function init()
    {
        if (!$this->enchant_broker) {
            if (!extension_loaded('enchant')) {
                $this->error = 'Enchant extension not available';
                return;
            }

            $this->enchant_broker = enchant_broker_init();
        }

        if (!enchant_broker_dict_exists($this->enchant_broker, $this->lang)) {
            $this->error = 'Unable to load dictionary for selected language using Enchant';
            return;
        }

        $this->enchant_dictionary = enchant_broker_request_dict($this->enchant_broker, $this->lang);
    }

    /**
     * Set content and check spelling
     *
     * @see rcube_spellchecker_engine::check()
     */
    #[Override]
    public function check($text)
    {
        $this->init();

        if (!$this->enchant_dictionary) {
            return true;
        }

        // tokenize
        $text = preg_split($this->separator, $text, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_OFFSET_CAPTURE);

        $diff = 0;
        $matches = [];

        foreach ($text as $w) {
            $word = trim($w[0]);
            $pos = $w[1] - $diff;
            $len = mb_strlen($word);

            if ($this->dictionary->is_exception($word)) {
                // skip exceptions
            } elseif (!enchant_dict_check($this->enchant_dictionary, $word)) {
                $suggestions = enchant_dict_suggest($this->enchant_dictionary, $word);

                if (count($suggestions) > self::MAX_SUGGESTIONS) {
                    $suggestions = array_slice($suggestions, 0, self::MAX_SUGGESTIONS);
                }

                $matches[] = [$word, $pos, $len, null, $suggestions];
            }

            $diff += (strlen($word) - $len);
        }

        $this->matches = $matches;

        return count($matches) == 0;
    }

    /**
     * Returns suggestions for the specified word
     *
     * @see rcube_spellchecker_engine::get_words()
     */
    #[Override]
    public function get_suggestions($word)
    {
        $this->init();

        if (!$this->enchant_dictionary) {
            return [];
        }

        $suggestions = enchant_dict_suggest($this->enchant_dictionary, $word);

        if (count($suggestions) > self::MAX_SUGGESTIONS) {
            $suggestions = array_slice($suggestions, 0, self::MAX_SUGGESTIONS);
        }

        return $suggestions;
    }

    /**
     * Returns misspelled words
     *
     * @see rcube_spellchecker_engine::get_suggestions()
     */
    #[Override]
    public function get_words($text = null)
    {
        $result = [];

        if ($text) {
            // init spellchecker
            $this->init();

            if (!$this->enchant_dictionary) {
                return [];
            }

            // With Enchant we don't need to get suggestions to return misspelled words
            $text = preg_split($this->separator, $text, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_OFFSET_CAPTURE);

            foreach ($text as $w) {
                $word = trim($w[0]);

                // skip exceptions
                if ($this->dictionary->is_exception($word)) {
                    continue;
                }

                if (!enchant_dict_check($this->enchant_dictionary, $word)) {
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
