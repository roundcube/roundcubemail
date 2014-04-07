<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) 2013, The Roundcube Dev Team                            |
 | Copyright (C) 2013, Kolab Systems AG                                  |
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

// create classes defined by the pthreads module if that isn't installed
if (!defined('PTHREADS_INHERIT_ALL')) {
    class Worker { }
    class Stackable { }
}

/**
 * Class to control search jobs on multiple IMAP folders.
 * This implement a simple threads pool using the pthreads extension.
 *
 * @package    Framework
 * @subpackage Storage
 * @author     Thomas Bruederli <roundcube@gmail.com>
 */
class rcube_imap_search
{
    public $options = array();

    private $size = 10;
    private $next = 0;
    private $workers = array();
    private $states = array();
    private $jobs = array();
    private $conn;

    /**
     * Default constructor
     */
    public function __construct($options, $conn)
    {
        $this->options = $options;
        $this->conn = $conn;
    }

    /**
     * Invoke search request to IMAP server
     *
     * @param  array   $folders    List of IMAP folders to search in
     * @param  string  $str        Search criteria
     * @param  string  $charset    Search charset
     * @param  string  $sort_field Header field to sort by
     * @param  boolean $threading  True if threaded listing is active
     */
    public function exec($folders, $str, $charset = null, $sort_field = null, $threading=null)
    {
        $pthreads = defined('PTHREADS_INHERIT_ALL');

        $results = new rcube_result_multifolder($folders);

        // start a search job for every folder to search in
        foreach ($folders as $folder) {
            $job = new rcube_imap_search_job($folder, $str, $charset, $sort_field, $threading);
            if ($pthreads && $this->submit($job)) {
                $this->jobs[] = $job;
            }
            else {
                $job->worker = $this;
                $job->run();
                $this->jobs[] = $job;
            }
        }

        // wait for all workers to be done
        $this->shutdown();

        // gather results
        foreach ($this->jobs as $job) {
            $results->add($job->get_result());
        }

        return $results;
    }

    /**
     * Assign the given job object to one of the worker threads for execution
     */
    public function submit(Stackable $job)
    {
        if (count($this->workers) < $this->size) {
            $id = count($this->workers);
            $this->workers[$id] = new rcube_imap_search_worker($id, $this->options);
            $this->workers[$id]->start(PTHREADS_INHERIT_ALL);

            if ($this->workers[$id]->stack($job)) {
                return $job;
            }
            else {
                // trigger_error(sprintf("Failed to push Stackable onto %s", $id), E_USER_WARNING);
            }
        }
        if (($worker = $this->workers[$this->next])) {
            $this->next = ($this->next+1) % $this->size;
            if ($worker->stack($job)) {
                return $job;
            }
            else {
                // trigger_error(sprintf("Failed to stack onto selected worker %s", $worker->id), E_USER_WARNING);
            }
        }
        else {
            // trigger_error(sprintf("Failed to select a worker for Stackable"), E_USER_WARNING);
        }

        return false;
    }

    /**
     * Shutdown the pool of threads cleanly, retaining exit status locally
     */
    public function shutdown()
    {
        foreach ($this->workers as $worker) {
            $this->states[$worker->getThreadId()] = $worker->shutdown();
            $worker->close();
        }

        # console('shutdown', $this->states);
    }
    
    /**
     * Get connection to the IMAP server
     * (used for single-thread mode)
     */
    public function get_imap()
    {
        return $this->conn;
    }
}


/**
 * Stackable item to run the search on a specific IMAP folder
 */
class rcube_imap_search_job extends Stackable
{
    private $folder;
    private $search;
    private $charset;
    private $sort_field;
    private $threading;
    private $searchset;
    private $result;
    private $pagesize = 100;

    public function __construct($folder, $str, $charset = null, $sort_field = null, $threading=false)
    {
        $this->folder = $folder;
        $this->search = $str;
        $this->charset = $charset;
        $this->sort_field = $sort_field;
        $this->threading = $threading;
    }

    public function run()
    {
        // trigger_error("Start search $this->folder", E_USER_NOTICE);
        $this->result = $this->search_index();
        // trigger_error("End search $this->folder: " . $this->result->count(), E_USER_NOTICE);
    }

    /**
     * Copy of rcube_imap::search_index()
     */
    protected function search_index()
    {
        $pthreads = defined('PTHREADS_INHERIT_ALL');
        $criteria = $this->search;
        $charset = $this->charset;

        $imap = $this->worker->get_imap();

        if (!$imap->connected()) {
            trigger_error("No IMAP connection for $this->folder", E_USER_WARNING);

            if ($this->threading) {
                return new rcube_result_thread();
            }
            else {
                return new rcube_result_index();
            }
        }

        if ($this->worker->options['skip_deleted'] && !preg_match('/UNDELETED/', $criteria)) {
            $criteria = 'UNDELETED '.$criteria;
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
                    rcube_imap::convert_criteria($criteria, $charset), true, 'US-ASCII');
            }

            // close IMAP connection again
            if ($pthreads)
                $imap->closeConnection();

            return $threads;
        }

        if ($this->sort_field) {
            $messages = $imap->sort($this->folder, $this->sort_field, $criteria, true, $charset);

            // Error, try with US-ASCII (RFC5256: SORT/THREAD must support US-ASCII and UTF-8,
            // but I've seen Courier with disabled UTF-8 support)
            if ($messages->is_error() && $charset && $charset != 'US-ASCII') {
                $messages = $imap->sort($this->folder, $this->sort_field,
                    rcube_imap::convert_criteria($criteria, $charset), true, 'US-ASCII');
            }
        }

        if (!$messages || $messages->is_error()) {
            $messages = $imap->search($this->folder,
                ($charset && $charset != 'US-ASCII' ? "CHARSET $charset " : '') . $criteria, true);

            // Error, try with US-ASCII (some servers may support only US-ASCII)
            if ($messages->is_error() && $charset && $charset != 'US-ASCII') {
                $messages = $imap->search($this->folder,
                    rcube_imap::convert_criteria($criteria, $charset), true);
            }
        }

        // close IMAP connection again
        if ($pthreads)
            $imap->closeConnection();

        return $messages;
    }

    public function get_search_set()
    {
        return array(
            $this->search,
            $this->result,
            $this->charset,
            $this->sort_field,
            $this->threading,
        );
    }

    public function get_result()
    {
        return $this->result;
    }
}


/**
 * Worker thread to run search jobs while maintaining a common context
 */
class rcube_imap_search_worker extends Worker
{
    public $id;
    public $options;

    private $conn;
    private $counts = 0;

    /**
     * Default constructor
     */
    public function __construct($id, $options)
    {
        $this->id = $id;
        $this->options = $options;
    }

    /**
     * Get a dedicated connection to the IMAP server
     */
    public function get_imap()
    {
        // TODO: make this connection persistent for several jobs
        // This doesn't seem to work. Socket connections don't survive serialization which is used in pthreads

        $conn = new rcube_imap_generic();
        # $conn->setDebug(true, function($conn, $message){ trigger_error($message, E_USER_NOTICE); });

        if ($this->options['user'] && $this->options['password']) {
            $this->options['ident']['command'] = 'search-' . $this->id . 't' . ++$this->counts;
            $conn->connect($this->options['host'], $this->options['user'], $this->options['password'], $this->options);
        }

        if ($conn->error)
            trigger_error($conn->error, E_USER_WARNING);

        return $conn;
    }

    /**
     * @override
     */
    public function run()
    {
        
    }

    /**
     * Close IMAP connection
     */
    public function close()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

