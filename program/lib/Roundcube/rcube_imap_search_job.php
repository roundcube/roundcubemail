<?php

namespace Roundcube\WIP;

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
 |   Execute (multi-threaded) searches in multiple IMAP folders          |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Stackable item to run the search on a specific IMAP folder
 */
class rcube_imap_search_job // extends Stackable
{
    /** @var \rcube_imap_search The job worker */
    public $worker;

    /** @var string IMAP folder to search in */
    private $folder;

    /** @var string Search criteria */
    private $search;

    /** @var string Search charset */
    private $charset;

    /** @var string Header field to sort by */
    private $sort_field;

    /** @var bool True if threaded listing is active */
    private $threading;

    /** @var \rcube_result_index|\rcube_result_thread Search result */
    private $result;

    /**
     * Class constructor
     *
     * @param string $folder     IMAP folder to search in
     * @param string $str        Search criteria
     * @param string $charset    Search charset
     * @param string $sort_field Header field to sort by
     * @param bool   $threading  True if threaded listing is active
     */
    public function __construct($folder, $str, $charset = null, $sort_field = null, $threading = false)
    {
        $this->folder = $folder;
        $this->search = $str;
        $this->charset = $charset;
        $this->sort_field = $sort_field;
        $this->threading = $threading;

        $this->result = new \rcube_result_index($folder);
        $this->result->incomplete = true;
    }

    /**
     * Executes the IMAP search
     */
    public function run()
    {
        $this->result = $this->search_index();
    }

    /**
     * Copy of rcube_imap::search_index()
     *
     * @return \rcube_result_index|\rcube_result_thread Search result
     */
    protected function search_index()
    {
        $criteria = $this->search;
        $charset = $this->charset;
        $imap = $this->worker->get_imap();

        if (!$imap->connected()) {
            trigger_error("No IMAP connection for {$this->folder}", \E_USER_WARNING);

            if ($this->threading) {
                return new \rcube_result_thread($this->folder);
            }

            return new \rcube_result_index($this->folder);
        }

        if ($this->worker->options['skip_deleted'] && !preg_match('/UNDELETED/', $criteria)) {
            $criteria = 'UNDELETED ' . $criteria;
        }

        // unset CHARSET if criteria string is ASCII, this way
        // SEARCH won't be re-sent after "unsupported charset" response
        if ($charset && $charset != 'US-ASCII' && is_ascii($criteria)) {
            $charset = 'US-ASCII';
        }

        if ($this->threading) {
            $threads = $imap->thread($this->folder, $this->threading, $criteria, true, $charset);

            // Error, try with US-ASCII (RFC5256: SORT/THREAD must support US-ASCII and UTF-8,
            // but I've seen that Courier doesn't support UTF-8)
            if ($threads->is_error() && $charset && $charset != 'US-ASCII') {
                $threads = $imap->thread($this->folder, $this->threading,
                    \rcube_imap::convert_criteria($criteria, $charset), true, 'US-ASCII');
            }

            return $threads;
        }

        if ($this->sort_field) {
            $messages = $imap->sort($this->folder, $this->sort_field, $criteria, true, $charset);

            // Error, try with US-ASCII (RFC5256: SORT/THREAD must support US-ASCII and UTF-8,
            // but I've seen Courier with disabled UTF-8 support)
            if ($messages->is_error() && $charset && $charset != 'US-ASCII') {
                $messages = $imap->sort($this->folder, $this->sort_field,
                    \rcube_imap::convert_criteria($criteria, $charset), true, 'US-ASCII');
            }
        }

        if (empty($messages) || $messages->is_error()) {
            $messages = $imap->search($this->folder,
                ($charset && $charset != 'US-ASCII' ? "CHARSET {$charset} " : '') . $criteria, true);

            // Error, try with US-ASCII (some servers may support only US-ASCII)
            if ($messages->is_error() && $charset && $charset != 'US-ASCII') {
                $messages = $imap->search($this->folder,
                    \rcube_imap::convert_criteria($criteria, $charset), true);
            }
        }

        return $messages;
    }

    /**
     * Return the saved search set as a array
     *
     * @return array Search set
     */
    public function get_search_set()
    {
        return [
            $this->search,
            $this->result,
            $this->charset,
            $this->sort_field,
            $this->threading,
        ];
    }

    /**
     * Returns the search result.
     *
     * @return \rcube_result_index|\rcube_result_thread Search result
     */
    public function get_result()
    {
        return $this->result;
    }
}
