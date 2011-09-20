<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_imap_cache.php                                  |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2011, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Caching of IMAP folder contents (messages and index)                |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/


/**
 * Interface class for accessing Roundcube messages cache
 *
 * @package    Cache
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @author     Aleksander Machniak <alec@alec.pl>
 * @version    1.0
 */
class rcube_imap_cache
{
    /**
     * Instance of rcube_imap
     *
     * @var rcube_imap
     */
    private $imap;

    /**
     * Instance of rcube_mdb2
     *
     * @var rcube_mdb2
     */
    private $db;

    /**
     * User ID
     *
     * @var int
     */
    private $userid;

    /**
     * Internal (in-memory) cache
     *
     * @var array
     */
    private $icache = array();

    private $skip_deleted = false;

    /**
     * List of known flags. Thanks to this we can handle flag changes
     * with good performance. Bad thing is we need to know used flags.
     */
    public $flags = array(
        1       => 'SEEN',          // RFC3501
        2       => 'DELETED',       // RFC3501
        4       => 'ANSWERED',      // RFC3501
        8       => 'FLAGGED',       // RFC3501
        16      => 'DRAFT',         // RFC3501
        32      => 'MDNSENT',       // RFC3503
        64      => 'FORWARDED',     // RFC5550
        128     => 'SUBMITPENDING', // RFC5550
        256     => 'SUBMITTED',     // RFC5550
        512     => 'JUNK',
        1024    => 'NONJUNK',
        2048    => 'LABEL1',
        4096    => 'LABEL2',
        8192    => 'LABEL3',
        16384   => 'LABEL4',
        32768   => 'LABEL5',
    );


    /**
     * Object constructor.
     */
    function __construct($db, $imap, $userid, $skip_deleted)
    {
        $this->db           = $db;
        $this->imap         = $imap;
        $this->userid       = (int)$userid;
        $this->skip_deleted = $skip_deleted;
    }


    /**
     * Cleanup actions (on shutdown).
     */
    public function close()
    {
        $this->save_icache();
        $this->icache = null;
    }


    /**
     * Return (sorted) messages index.
     * If index doesn't exist or is invalid, will be updated.
     *
     * @param string  $mailbox     Folder name
     * @param string  $sort_field  Sorting column
     * @param string  $sort_order  Sorting order (ASC|DESC)
     * @param bool    $exiting     Skip index initialization if it doesn't exist in DB
     *
     * @return array Messages index
     */
    function get_index($mailbox, $sort_field = null, $sort_order = null, $existing = false)
    {
        if (empty($this->icache[$mailbox]))
            $this->icache[$mailbox] = array();

        $sort_order = strtoupper($sort_order) == 'ASC' ? 'ASC' : 'DESC';

        // Seek in internal cache
        if (array_key_exists('index', $this->icache[$mailbox])) {
            // The index was fetched from database already, but not validated yet
            if (!array_key_exists('result', $this->icache[$mailbox]['index'])) {
                $index = $this->icache[$mailbox]['index'];
            }
            // We've got a valid index
            else if ($sort_field == 'ANY' || $this->icache[$mailbox]['index']['sort_field'] == $sort_field
            ) {
                if ($this->icache[$mailbox]['index']['sort_order'] == $sort_order)
                    return $this->icache[$mailbox]['index']['result'];
                else
                    return array_reverse($this->icache[$mailbox]['index']['result'], true);
            }
        }

        // Get index from DB (if DB wasn't already queried)
        if (empty($index) && empty($this->icache[$mailbox]['index_queried'])) {
            $index = $this->get_index_row($mailbox);

            // set the flag that DB was already queried for index
            // this way we'll be able to skip one SELECT, when
            // get_index() is called more than once
            $this->icache[$mailbox]['index_queried'] = true;
        }

        $data = null;

        // @TODO: Think about skipping validation checks.
        // If we could check only every 10 minutes, we would be able to skip
        // expensive checks, mailbox selection or even IMAP connection, this would require
        // additional logic to force cache invalidation in some cases
        // and many rcube_imap changes to connect when needed

        // Entry exists, check cache status
        if (!empty($index)) {
            $exists = true;

            if ($sort_field == 'ANY') {
                $sort_field = $index['sort_field'];
            }

            if ($sort_field != $index['sort_field']) {
                $is_valid = false;
            }
            else {
                $is_valid = $this->validate($mailbox, $index, $exists);
            }

            if ($is_valid) {
                // build index, assign sequence IDs to unique IDs
                $data = array_combine($index['seq'], $index['uid']);
                // revert the order if needed
                if ($index['sort_order'] != $sort_order)
                    $data = array_reverse($data, true);
            }
        }
        else {
            if ($existing) {
                return null;
            }
            else if ($sort_field == 'ANY') {
                $sort_field = '';
            }

            // Got it in internal cache, so the row already exist
            $exists = array_key_exists('index', $this->icache[$mailbox]);
        }

        // Index not found, not valid or sort field changed, get index from IMAP server
        if ($data === null) {
            // Get mailbox data (UIDVALIDITY, counters, etc.) for status check
            $mbox_data = $this->imap->mailbox_data($mailbox);
            $data      = $this->get_index_data($mailbox, $sort_field, $sort_order, $mbox_data);

            // insert/update
            $this->add_index_row($mailbox, $sort_field, $sort_order, $data, $mbox_data,
                $exists, $index['modseq']);
        }

        $this->icache[$mailbox]['index'] = array(
            'result'     => $data,
            'sort_field' => $sort_field,
            'sort_order' => $sort_order,
            'modseq'     => !empty($index['modseq']) ? $index['modseq'] : $mbox_data['HIGHESTMODSEQ']
        );

        return $data;
    }


    /**
     * Return messages thread.
     * If threaded index doesn't exist or is invalid, will be updated.
     *
     * @param string  $mailbox     Folder name
     * @param string  $sort_field  Sorting column
     * @param string  $sort_order  Sorting order (ASC|DESC)
     *
     * @return array Messages threaded index
     */
    function get_thread($mailbox)
    {
        if (empty($this->icache[$mailbox]))
            $this->icache[$mailbox] = array();

        // Seek in internal cache
        if (array_key_exists('thread', $this->icache[$mailbox])) {
            return array(
                $this->icache[$mailbox]['thread']['tree'],
                $this->icache[$mailbox]['thread']['depth'],
                $this->icache[$mailbox]['thread']['children'],
            );
        }

        // Get thread from DB (if DB wasn't already queried)
        if (empty($this->icache[$mailbox]['thread_queried'])) {
            $index = $this->get_thread_row($mailbox);

            // set the flag that DB was already queried for thread
            // this way we'll be able to skip one SELECT, when
            // get_thread() is called more than once or after clear()
            $this->icache[$mailbox]['thread_queried'] = true;
        }

        $data = null;

        // Entry exist, check cache status
        if (!empty($index)) {
            $exists   = true;
            $is_valid = $this->validate($mailbox, $index, $exists);

            if (!$is_valid) {
                $index = null;
            }
        }

        // Index not found or not valid, get index from IMAP server
        if ($index === null) {
            // Get mailbox data (UIDVALIDITY, counters, etc.) for status check
            $mbox_data = $this->imap->mailbox_data($mailbox);

            if ($mbox_data['EXISTS']) {
                // get all threads (default sort order)
                list ($thread_tree, $msg_depth, $has_children) = $this->imap->fetch_threads($mailbox, true);
            }

            $index = array(
                'tree'     => !empty($thread_tree) ? $thread_tree : array(),
                'depth'    => !empty($msg_depth) ? $msg_depth : array(),
                'children' => !empty($has_children) ? $has_children : array(),
            );

            // insert/update
            $this->add_thread_row($mailbox, $index, $mbox_data, $exists);
        }

        $this->icache[$mailbox]['thread'] = $index;

        return array($index['tree'], $index['depth'], $index['children']);
    }


    /**
     * Returns list of messages (headers). See rcube_imap::fetch_headers().
     *
     * @param string $mailbox  Folder name
     * @param array  $msgs     Message sequence numbers
     * @param bool   $is_uid   True if $msgs contains message UIDs
     *
     * @return array The list of messages (rcube_mail_header) indexed by UID
     */
    function get_messages($mailbox, $msgs = array(), $is_uid = true)
    {
        if (empty($msgs)) {
            return array();
        }

        // @TODO: it would be nice if we could work with UIDs only
        // then index would be not needed. For now we need it to
        // map id to uid here and to update message id for cached message

        // Convert IDs to UIDs
        $index = $this->get_index($mailbox, 'ANY');
        if (!$is_uid) {
            foreach ($msgs as $idx => $msgid)
                if ($uid = $index[$msgid])
                    $msgs[$idx] = $uid;
        }

        // Fetch messages from cache
        $sql_result = $this->db->query(
            "SELECT uid, data, flags"
            ." FROM ".get_table_name('cache_messages')
            ." WHERE user_id = ?"
                ." AND mailbox = ?"
                ." AND uid IN (".$this->db->array2list($msgs, 'integer').")",
            $this->userid, $mailbox);

        $msgs   = array_flip($msgs);
        $result = array();

        while ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            $uid          = intval($sql_arr['uid']);
            $result[$uid] = $this->build_message($sql_arr);

            // save memory, we don't need message body here (?)
            $result[$uid]->body = null;

            // update message ID according to index data
            if (!empty($index) && ($id = array_search($uid, $index)))
                $result[$uid]->id = $id;

            if (!empty($result[$uid])) {
                unset($msgs[$uid]);
            }
        }

        // Fetch not found messages from IMAP server
        if (!empty($msgs)) {
            $messages = $this->imap->fetch_headers($mailbox, array_keys($msgs), true, true);

            // Insert to DB and add to result list
            if (!empty($messages)) {
                foreach ($messages as $msg) {
                    $this->add_message($mailbox, $msg, !array_key_exists($msg->uid, $result));
                    $result[$msg->uid] = $msg;
                }
            }
        }

        return $result;
    }


    /**
     * Returns message data.
     *
     * @param string $mailbox  Folder name
     * @param int    $uid      Message UID
     * @param bool   $update   If message doesn't exists in cache it will be fetched
     *                         from IMAP server
     * @param bool   $no_cache Enables internal cache usage
     *
     * @return rcube_mail_header Message data
     */
    function get_message($mailbox, $uid, $update = true, $cache = true)
    {
        // Check internal cache
        if (($message = $this->icache['message'])
            && $message['mailbox'] == $mailbox && $message['object']->uid == $uid
        ) {
            return $this->icache['message']['object'];
        }

        $sql_result = $this->db->query(
            "SELECT flags, data"
            ." FROM ".get_table_name('cache_messages')
            ." WHERE user_id = ?"
                ." AND mailbox = ?"
                ." AND uid = ?",
                $this->userid, $mailbox, (int)$uid);

        if ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            $message = $this->build_message($sql_arr);
            $found   = true;

            // update message ID according to index data
            $index = $this->get_index($mailbox, 'ANY');
            if (!empty($index) && ($id = array_search($uid, $index)))
                $message->id = $id;
        }

        // Get the message from IMAP server
        if (empty($message) && $update) {
            $message = $this->imap->get_headers($uid, $mailbox, true);
            // cache will be updated in close(), see below
        }

        // Save the message in internal cache, will be written to DB in close()
        // Common scenario: user opens unseen message
        // - get message (SELECT)
        // - set message headers/structure (INSERT or UPDATE)
        // - set \Seen flag (UPDATE)
        // This way we can skip one UPDATE
        if (!empty($message) && $cache) {
            // Save current message from internal cache
            $this->save_icache();

            $this->icache['message'] = array(
                'object'  => $message,
                'mailbox' => $mailbox,
                'exists'  => $found,
                'md5sum'  => md5(serialize($message)),
            );
        }

        return $message;
    }


    /**
     * Saves the message in cache.
     *
     * @param string            $mailbox  Folder name
     * @param rcube_mail_header $message  Message data
     * @param bool              $force    Skips message in-cache existance check
     */
    function add_message($mailbox, $message, $force = false)
    {
        if (!is_object($message) || empty($message->uid))
            return;

        $msg   = serialize($this->db->encode(clone $message));
        $flags = 0;

        if (!empty($message->flags)) {
            foreach ($this->flags as $idx => $flag)
                if (!empty($message->flags[$flag]))
                    $flags += $idx;
        }
        unset($msg->flags);

        // update cache record (even if it exists, the update
        // here will work as select, assume row exist if affected_rows=0)
        if (!$force) {
            $res = $this->db->query(
                "UPDATE ".get_table_name('cache_messages')
                ." SET flags = ?, data = ?, changed = ".$this->db->now()
                ." WHERE user_id = ?"
                    ." AND mailbox = ?"
                    ." AND uid = ?",
                $flags, $msg, $this->userid, $mailbox, (int) $message->uid);

            if ($this->db->affected_rows())
                return;
        }

        // insert new record
        $this->db->query(
            "INSERT INTO ".get_table_name('cache_messages')
            ." (user_id, mailbox, uid, flags, changed, data)"
            ." VALUES (?, ?, ?, ?, ".$this->db->now().", ?)",
            $this->userid, $mailbox, (int) $message->uid, $flags, $msg);
    }


    /**
     * Sets the flag for specified message.
     *
     * @param string  $mailbox  Folder name
     * @param array   $uids     Message UIDs or null to change flag
     *                          of all messages in a folder
     * @param string  $flag     The name of the flag
     * @param bool    $enabled  Flag state
     */
    function change_flag($mailbox, $uids, $flag, $enabled = false)
    {
        $flag = strtoupper($flag);
        $idx  = (int) array_search($flag, $this->flags);

        if (!$idx) {
            return;
        }

        // Internal cache update
        if ($uids && count($uids) == 1 && ($uid = current($uids))
            && ($message = $this->icache['message'])
            && $message['mailbox'] == $mailbox && $message['object']->uid == $uid
        ) {
            $message['object']->flags[$flag] = $enabled;
            return;
        }

        $this->db->query(
            "UPDATE ".get_table_name('cache_messages')
            ." SET changed = ".$this->db->now()
            .", flags = flags ".($enabled ? "+ $idx" : "- $idx")
            ." WHERE user_id = ?"
                ." AND mailbox = ?"
                .($uids !== null ? " AND uid IN (".$this->db->array2list((array)$uids, 'integer').")" : "")
                ." AND (flags & $idx) ".($enabled ? "= 0" : "= $idx"),
            $this->userid, $mailbox);
    }


    /**
     * Removes message(s) from cache.
     *
     * @param string $mailbox  Folder name
     * @param array  $uids     Message UIDs, NULL removes all messages
     */
    function remove_message($mailbox = null, $uids = null)
    {
        if (!strlen($mailbox)) {
            $this->db->query(
                "DELETE FROM ".get_table_name('cache_messages')
                ." WHERE user_id = ?",
                $this->userid);
        }
        else {
            // Remove the message from internal cache
            if (!empty($uids) && !is_array($uids) && ($message = $this->icache['message'])
                && $message['mailbox'] == $mailbox && $message['object']->uid == $uids
            ) {
                $this->icache['message'] = null;
            }

            $this->db->query(
                "DELETE FROM ".get_table_name('cache_messages')
                ." WHERE user_id = ?"
                    ." AND mailbox = ".$this->db->quote($mailbox)
                    .($uids !== null ? " AND uid IN (".$this->db->array2list((array)$uids, 'integer').")" : ""),
                $this->userid);
        }

    }


    /**
     * Clears index cache.
     *
     * @param string  $mailbox     Folder name
     * @param bool    $remove      Enable to remove the DB row
     */
    function remove_index($mailbox = null, $remove = false)
    {
        // The index should be only removed from database when
        // UIDVALIDITY was detected or the mailbox is empty
        // otherwise use 'valid' flag to not loose HIGHESTMODSEQ value
        if ($remove)
            $this->db->query(
                "DELETE FROM ".get_table_name('cache_index')
                ." WHERE user_id = ".intval($this->userid)
                    .(strlen($mailbox) ? " AND mailbox = ".$this->db->quote($mailbox) : "")
            );
        else
            $this->db->query(
                "UPDATE ".get_table_name('cache_index')
                ." SET valid = 0"
                ." WHERE user_id = ".intval($this->userid)
                    .(strlen($mailbox) ? " AND mailbox = ".$this->db->quote($mailbox) : "")
            );

        if (strlen($mailbox)) {
            unset($this->icache[$mailbox]['index']);
            // Index removed, set flag to skip SELECT query in get_index()
            $this->icache[$mailbox]['index_queried'] = true;
        }
        else
            $this->icache = array();
    }


    /**
     * Clears thread cache.
     *
     * @param string  $mailbox     Folder name
     */
    function remove_thread($mailbox = null)
    {
        $this->db->query(
            "DELETE FROM ".get_table_name('cache_thread')
            ." WHERE user_id = ".intval($this->userid)
                .(strlen($mailbox) ? " AND mailbox = ".$this->db->quote($mailbox) : "")
        );

        if (strlen($mailbox)) {
            unset($this->icache[$mailbox]['thread']);
            // Thread data removed, set flag to skip SELECT query in get_thread()
            $this->icache[$mailbox]['thread_queried'] = true;
        }
        else
            $this->icache = array();
    }


    /**
     * Clears the cache.
     *
     * @param string $mailbox  Folder name
     * @param array  $uids     Message UIDs, NULL removes all messages in a folder
     */
    function clear($mailbox = null, $uids = null)
    {
        $this->remove_index($mailbox, true);
        $this->remove_thread($mailbox);
        $this->remove_message($mailbox, $uids);
    }


    /**
     * @param string $mailbox Folder name
     * @param int    $id      Message (sequence) ID
     *
     * @return int Message UID
     */
    function id2uid($mailbox, $id)
    {
        if (!empty($this->icache['pending_index_update']))
            return null;

        // get index if it exists
        $index = $this->get_index($mailbox, 'ANY', null, true);

        return $index[$id];
    }


    /**
     * @param string $mailbox Folder name
     * @param int    $uid     Message UID
     *
     * @return int Message (sequence) ID
     */
    function uid2id($mailbox, $uid)
    {
        if (!empty($this->icache['pending_index_update']))
            return null;

        // get index if it exists
        $index = $this->get_index($mailbox, 'ANY', null, true);

        return array_search($uid, (array)$index);
    }

    /**
     * Fetches index data from database
     */
    private function get_index_row($mailbox)
    {
        // Get index from DB
        $sql_result = $this->db->query(
            "SELECT data, valid"
            ." FROM ".get_table_name('cache_index')
            ." WHERE user_id = ?"
                ." AND mailbox = ?",
            $this->userid, $mailbox);

        if ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            $data = explode('@', $sql_arr['data']);

            return array(
                'valid'      => $sql_arr['valid'],
                'seq'        => explode(',', $data[0]),
                'uid'        => explode(',', $data[1]),
                'sort_field' => $data[2],
                'sort_order' => $data[3],
                'deleted'    => $data[4],
                'validity'   => $data[5],
                'uidnext'    => $data[6],
                'modseq'     => $data[7],
            );
        }

        return null;
    }


    /**
     * Fetches thread data from database
     */
    private function get_thread_row($mailbox)
    {
        // Get thread from DB
        $sql_result = $this->db->query(
            "SELECT data"
            ." FROM ".get_table_name('cache_thread')
            ." WHERE user_id = ?"
                ." AND mailbox = ?",
            $this->userid, $mailbox);

        if ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            $data = explode('@', $sql_arr['data']);

            // Uncompress data, see add_thread_row()
  //          $data[0] = str_replace(array('*', '^', '#'), array(';a:0:{}', 'i:', ';a:1:'), $data[0]);
            $data[0] = unserialize($data[0]);

            // build 'depth' and 'children' arrays
            $depth = $children = array();
            $this->build_thread_data($data[0], $depth, $children);

            return array(
                'tree'     => $data[0],
                'depth'    => $depth,
                'children' => $children,
                'deleted'  => $data[1],
                'validity' => $data[2],
                'uidnext'  => $data[3],
            );
        }

        return null;
    }


    /**
     * Saves index data into database
     */
    private function add_index_row($mailbox, $sort_field, $sort_order,
        $data = array(), $mbox_data = array(), $exists = false, $modseq = null)
    {
        $data = array(
            implode(',', array_keys($data)),
            implode(',', array_values($data)),
            $sort_field,
            $sort_order,
            (int) $this->skip_deleted,
            (int) $mbox_data['UIDVALIDITY'],
            (int) $mbox_data['UIDNEXT'],
            $modseq ? $modseq : $mbox_data['HIGHESTMODSEQ'],
        );
        $data = implode('@', $data);

        if ($exists)
            $sql_result = $this->db->query(
                "UPDATE ".get_table_name('cache_index')
                ." SET data = ?, valid = 1, changed = ".$this->db->now()
                ." WHERE user_id = ?"
                    ." AND mailbox = ?",
                $data, $this->userid, $mailbox);
        else
            $sql_result = $this->db->query(
                "INSERT INTO ".get_table_name('cache_index')
                ." (user_id, mailbox, data, valid, changed)"
                ." VALUES (?, ?, ?, 1, ".$this->db->now().")",
                $this->userid, $mailbox, $data);
    }


    /**
     * Saves thread data into database
     */
    private function add_thread_row($mailbox, $data = array(), $mbox_data = array(), $exists = false)
    {
        $tree = serialize($data['tree']);
        // This significantly reduces data length
//        $tree = str_replace(array(';a:0:{}', 'i:', ';a:1:'), array('*', '^', '#'), $tree);

        $data = array(
            $tree,
            (int) $this->skip_deleted,
            (int) $mbox_data['UIDVALIDITY'],
            (int) $mbox_data['UIDNEXT'],
        );
        $data = implode('@', $data);

        if ($exists)
            $sql_result = $this->db->query(
                "UPDATE ".get_table_name('cache_thread')
                ." SET data = ?, changed = ".$this->db->now()
                ." WHERE user_id = ?"
                    ." AND mailbox = ?",
                $data, $this->userid, $mailbox);
        else
            $sql_result = $this->db->query(
                "INSERT INTO ".get_table_name('cache_thread')
                ." (user_id, mailbox, data, changed)"
                ." VALUES (?, ?, ?, ".$this->db->now().")",
                $this->userid, $mailbox, $data);
    }


    /**
     * Checks index/thread validity
     */
    private function validate($mailbox, $index, &$exists = true)
    {
        $is_thread = isset($index['tree']);

        // Get mailbox data (UIDVALIDITY, counters, etc.) for status check
        $mbox_data = $this->imap->mailbox_data($mailbox);

        // @TODO: Think about skipping validation checks.
        // If we could check only every 10 minutes, we would be able to skip
        // expensive checks, mailbox selection or even IMAP connection, this would require
        // additional logic to force cache invalidation in some cases
        // and many rcube_imap changes to connect when needed

        // Check UIDVALIDITY
        if ($index['validity'] != $mbox_data['UIDVALIDITY']) {
            $this->clear($mailbox);
            $exists = false;
            return false;
        }

        // Folder is empty but cache isn't
        if (empty($mbox_data['EXISTS'])) {
            if (!empty($index['seq']) || !empty($index['tree'])) {
                $this->clear($mailbox);
                $exists = false;
                return false;
            }
        }
        // Folder is not empty but cache is
        else if (empty($index['seq']) && empty($index['tree'])) {
            unset($this->icache[$mailbox][$is_thread ? 'thread' : 'index']);
            return false;
        }

        // Validation flag
        if (!$is_thread && empty($index['valid'])) {
            unset($this->icache[$mailbox][$is_thread ? 'thread' : 'index']);
            return false;
        }

        // Index was created with different skip_deleted setting
        if ($this->skip_deleted != $index['deleted']) {
            return false;
        }

        // Check HIGHESTMODSEQ
        if (!empty($index['modseq']) && !empty($mbox_data['HIGHESTMODSEQ'])
            && $index['modseq'] == $mbox_data['HIGHESTMODSEQ']
        ) {
            return true;
        }

        // Check UIDNEXT
        if ($index['uidnext'] != $mbox_data['UIDNEXT']) {
            unset($this->icache[$mailbox][$is_thread ? 'thread' : 'index']);
            return false;
        }

        // @TODO: find better validity check for threaded index
        if ($is_thread) {
            // check messages number...
            if ($mbox_data['EXISTS'] != max(array_keys($index['depth']))) {
                return false;
            }
            return true;
        }

        // The rest of checks, more expensive
        if (!empty($this->skip_deleted)) {
            // compare counts if available
            if ($mbox_data['COUNT_UNDELETED'] != null
                && $mbox_data['COUNT_UNDELETED'] != count($index['uid'])) {
                return false;
            }
            // compare UID sets
            if ($mbox_data['ALL_UNDELETED'] != null) {
                $uids_new = rcube_imap_generic::uncompressMessageSet($mbox_data['ALL_UNDELETED']);
                $uids_old = $index['uid'];

                if (count($uids_new) != count($uids_old)) {
                    return false;
                }

                sort($uids_new, SORT_NUMERIC);
                sort($uids_old, SORT_NUMERIC);

                if ($uids_old != $uids_new)
                    return false;
            }
            else {
                // get all undeleted messages excluding cached UIDs
                $ids = $this->imap->search_once($mailbox, 'ALL UNDELETED NOT UID '.
                    rcube_imap_generic::compressMessageSet($index['uid']));

                if (!empty($ids)) {
                    return false;
                }
            }
        }
        else {
            // check messages number...
            if ($mbox_data['EXISTS'] != max($index['seq'])) {
                return false;
            }
            // ... and max UID
            if (max($index['uid']) != $this->imap->id2uid($mbox_data['EXISTS'], $mailbox, true)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Synchronizes the mailbox.
     *
     * @param string $mailbox Folder name
     */
    function synchronize($mailbox)
    {
        // RFC4549: Synchronization Operations for Disconnected IMAP4 Clients
        // RFC4551: IMAP Extension for Conditional STORE Operation
        //          or Quick Flag Changes Resynchronization
        // RFC5162: IMAP Extensions for Quick Mailbox Resynchronization

        // @TODO: synchronize with other methods?
        $qresync   = $this->imap->get_capability('QRESYNC');
        $condstore = $qresync ? true : $this->imap->get_capability('CONDSTORE');

        if (!$qresync && !$condstore) {
            return;
        }

        // Get stored index
        $index = $this->get_index_row($mailbox);

        // database is empty
        if (empty($index)) {
            // set the flag that DB was already queried for index
            // this way we'll be able to skip one SELECT in get_index()
            $this->icache[$mailbox]['index_queried'] = true;
            return;
        }

        $this->icache[$mailbox]['index'] = $index;

        // no last HIGHESTMODSEQ value
        if (empty($index['modseq'])) {
            return;
        }

        // NOTE: make sure the mailbox isn't selected, before
        // enabling QRESYNC and invoking SELECT
        if ($this->imap->conn->selected !== null) {
            $this->imap->conn->close();
        }

        // Enable QRESYNC
        $res = $this->imap->conn->enable($qresync ? 'QRESYNC' : 'CONDSTORE');
        if (!is_array($res)) {
            return;
        }

        // Get mailbox data (UIDVALIDITY, HIGHESTMODSEQ, counters, etc.)
        $mbox_data = $this->imap->mailbox_data($mailbox);

        if (empty($mbox_data)) {
             return;
        }

        // Check UIDVALIDITY
        if ($index['validity'] != $mbox_data['UIDVALIDITY']) {
            $this->clear($mailbox);
            return;
        }

        // QRESYNC not supported on specified mailbox
        if (!empty($mbox_data['NOMODSEQ']) || empty($mbox_data['HIGHESTMODSEQ'])) {
            return;
        }

        // Nothing new
        if ($mbox_data['HIGHESTMODSEQ'] == $index['modseq']) {
            return;
        }

        // Get known uids
        $uids = array();
        $sql_result = $this->db->query(
            "SELECT uid"
            ." FROM ".get_table_name('cache_messages')
            ." WHERE user_id = ?"
                ." AND mailbox = ?",
            $this->userid, $mailbox);

        while ($sql_arr = $this->db->fetch_assoc($sql_result)) {
          $uids[] = $sql_arr['uid'];
        }

        // No messages in database, nothing to sync
        if (empty($uids)) {
            return;
        }

        // Get modified flags and vanished messages
        // UID FETCH 1:* (FLAGS) (CHANGEDSINCE 0123456789 VANISHED)
        $result = $this->imap->conn->fetch($mailbox,
            !empty($uids) ? $uids : '1:*', true, array('FLAGS'),
            $index['modseq'], $qresync);

        $invalidated = false;

        if (!empty($result)) {
            foreach ($result as $id => $msg) {
                $uid = $msg->uid;
                // Remove deleted message
                if ($this->skip_deleted && !empty($msg->flags['DELETED'])) {
                    $this->remove_message($mailbox, $uid);

                    if (!$invalidated) {
                        $invalidated = true;
                        // Invalidate thread indexes (?)
                        $this->remove_thread($mailbox);
                        // Invalidate index
                        $index['valid'] = false;
                    }
                    continue;
                }

                $flags = 0;
                if (!empty($msg->flags)) {
                    foreach ($this->flags as $idx => $flag)
                        if (!empty($msg->flags[$flag]))
                            $flags += $idx;
                }

                $this->db->query(
                    "UPDATE ".get_table_name('cache_messages')
                    ." SET flags = ?, changed = ".$this->db->now()
                    ." WHERE user_id = ?"
                        ." AND mailbox = ?"
                        ." AND uid = ?"
                        ." AND flags <> ?",
                    $flags, $this->userid, $mailbox, $uid, $flags);
            }
        }

        // Get VANISHED
        if ($qresync) {
            $mbox_data = $this->imap->mailbox_data($mailbox);

            // Removed messages
            if (!empty($mbox_data['VANISHED'])) {
                $uids = rcube_imap_generic::uncompressMessageSet($mbox_data['VANISHED']);
                if (!empty($uids)) {
                    // remove messages from database
                    $this->remove_message($mailbox, $uids);

                    // Invalidate thread indexes (?)
                    $this->remove_thread($mailbox);
                    // Invalidate index
                    $index['valid'] = false;
                }
            }
        }

        $sort_field = $index['sort_field'];
        $sort_order = $index['sort_order'];
        $exists     = true;

        // Validate index
        if (!$this->validate($mailbox, $index, $exists)) {
            // Update index
            $data = $this->get_index_data($mailbox, $sort_field, $sort_order, $mbox_data);
        }
        else {
            $data = array_combine($index['seq'], $index['uid']);
        }

        // update index and/or HIGHESTMODSEQ value
        $this->add_index_row($mailbox, $sort_field, $sort_order, $data, $mbox_data, $exists);

        // update internal cache for get_index()
        $this->icache[$mailbox]['index']['result'] = $data;
    }


    /**
     * Converts cache row into message object.
     *
     * @param array $sql_arr Message row data
     *
     * @return rcube_mail_header Message object
     */
    private function build_message($sql_arr)
    {
        $message = $this->db->decode(unserialize($sql_arr['data']));

        if ($message) {
            $message->flags = array();
            foreach ($this->flags as $idx => $flag)
                if (($sql_arr['flags'] & $idx) == $idx)
                    $message->flags[$flag] = true;
        }

        return $message;
    }


    /**
     * Creates 'depth' and 'children' arrays from stored thread 'tree' data.
     */
    private function build_thread_data($data, &$depth, &$children, $level = 0)
    {
        foreach ((array)$data as $key => $val) {
            $children[$key] = !empty($val);
            $depth[$key] = $level;
            if (!empty($val))
                $this->build_thread_data($val, $depth, $children, $level + 1);
        }
    }


    /**
     * Saves message stored in internal cache
     */
    private function save_icache()
    {
        // Save current message from internal cache
        if ($message = $this->icache['message']) {
            // clean up some object's data
            $object = $this->message_object_prepare($message['object']);

            // calculate current md5 sum
            $md5sum = md5(serialize($object));

            if ($message['md5sum'] != $md5sum) {
                $this->add_message($message['mailbox'], $object, !$message['exists']);
            }

            $this->icache['message']['md5sum'] = $md5sum;
        }
    }


    /**
     * Prepares message object to be stored in database.
     */
    private function message_object_prepare($msg)
    {
        // Remove body too big (>25kB)
        if ($msg->body && strlen($msg->body) > 25 * 1024) {
            unset($msg->body);
        }

        // Fix mimetype which might be broken by some code when message is displayed
        // Another solution would be to use object's copy in rcube_message class
        // to prevent related issues, however I'm not sure which is better
        if ($msg->mimetype) {
            list($msg->ctype_primary, $msg->ctype_secondary) = explode('/', $msg->mimetype);
        }

        if (is_array($msg->structure->parts)) {
            foreach ($msg->structure->parts as $idx => $part) {
                $msg->structure->parts[$idx] = $this->message_object_prepare($part);
            }
        }

        return $msg;
    }


    /**
     * Fetches index data from IMAP server
     */
    private function get_index_data($mailbox, $sort_field, $sort_order, $mbox_data = array())
    {
        $data = array();

        if (empty($mbox_data)) {
            $mbox_data = $this->imap->mailbox_data($mailbox);
        }

        // Prevent infinite loop.
        // It happens when rcube_imap::message_index_direct() is called.
        // There id2uid() is called which will again call get_index() and so on.
        if (!$sort_field && !$this->skip_deleted)
            $this->icache['pending_index_update'] = true;

        if ($mbox_data['EXISTS']) {
            // fetch sorted sequence numbers
            $data_seq = $this->imap->message_index_direct($mailbox, $sort_field, $sort_order);
            // fetch UIDs
            if (!empty($data_seq)) {
                // Seek in internal cache
                if (array_key_exists('index', (array)$this->icache[$mailbox])
                    && array_key_exists('result', (array)$this->icache[$mailbox]['index'])
                )
                    $data_uid = $this->icache[$mailbox]['index']['result'];
                else
                    $data_uid = $this->imap->conn->fetchUIDs($mailbox, $data_seq);

                // build index
                if (!empty($data_uid)) {
                    foreach ($data_seq as $seq)
                        if ($uid = $data_uid[$seq])
                            $data[$seq] = $uid;
                }
            }
        }

        // Reset internal flags
        $this->icache['pending_index_update'] = false;

        return $data;
    }
}
