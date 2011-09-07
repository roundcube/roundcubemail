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

    public $flag_fields = array('seen', 'deleted', 'answered', 'forwarded', 'flagged', 'mdnsent');


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
        if (array_key_exists('index', $this->icache[$mailbox])
            && ($sort_field == 'ANY' || $this->icache[$mailbox]['index']['sort_field'] == $sort_field)
        ) {
            if ($this->icache[$mailbox]['index']['sort_order'] == $sort_order)
                return $this->icache[$mailbox]['index']['result'];
            else
                return array_reverse($this->icache[$mailbox]['index']['result'], true);
        }

        // Get index from DB (if DB wasn't already queried)
        if (empty($this->icache[$mailbox]['index_queried'])) {
            $index = $this->get_index_row($mailbox);

            // set the flag that DB was already queried for index
            // this way we'll be able to skip one SELECT, when
            // get_index() is called more than once
            $this->icache[$mailbox]['index_queried'] = true;
        }
        $data  = null;

        // @TODO: Think about skipping validation checks.
        // If we could check only every 10 minutes, we would be able to skip
        // expensive checks, mailbox selection or even IMAP connection, this would require
        // additional logic to force cache invalidation in some cases
        // and many rcube_imap changes to connect when needed

        // Entry exist, check cache status
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
            // Got it in internal cache, so the row already exist
            $exists = array_key_exists('index', $this->icache[$mailbox]);

            if ($existing) {
                return null;
            }
            else if ($sort_field == 'ANY') {
                $sort_field = '';
            }
        }

        // Index not found, not valid or sort field changed, get index from IMAP server
        if ($data === null) {
            // Get mailbox data (UIDVALIDITY, counters, etc.) for status check
            $mbox_data = $this->imap->mailbox_data($mailbox);
            $data      = array();

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
                    if (array_key_exists('index', (array)$this->icache[$mailbox]))
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

            // insert/update
            $this->add_index_row($mailbox, $sort_field, $sort_order, $data, $mbox_data, $exists);
        }

        $this->icache[$mailbox]['index'] = array(
            'result'     => $data,
            'sort_field' => $sort_field,
            'sort_order' => $sort_order,
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

        // Get index from DB
        $index = $this->get_thread_row($mailbox);
        $data  = null;

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

        // Convert IDs to UIDs
        // @TODO: it would be nice if we could work with UIDs only
        // then, e.g. when fetching search result, index would be not needed
        if (!$is_uid) {
            $index = $this->get_index($mailbox, 'ANY');
            foreach ($msgs as $idx => $msgid)
                if ($uid = $index[$msgid])
                    $msgs[$idx] = $uid;
        }

        $flag_fields = implode(', ', array_map(array($this->db, 'quoteIdentifier'), $this->flag_fields));

        // Fetch messages from cache
        $sql_result = $this->db->query(
            "SELECT uid, data, ".$flag_fields
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
            // save memory, we don't need a body here
            $result[$uid]->body = null;
//@TODO: update message ID according to index data?

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
     *
     * @return rcube_mail_header Message data
     */
    function get_message($mailbox, $uid)
    {
        // Check internal cache
        if (($message = $this->icache['message'])
            && $message['mailbox'] == $mailbox && $message['object']->uid == $uid
        ) {
            return $this->icache['message']['object'];
        }

        $flag_fields = implode(', ', array_map(array($this->db, 'quoteIdentifier'), $this->flag_fields));

        $sql_result = $this->db->query(
            "SELECT data, ".$flag_fields
            ." FROM ".get_table_name('cache_messages')
            ." WHERE user_id = ?"
                ." AND mailbox = ?"
                ." AND uid = ?",
                $this->userid, $mailbox, (int)$uid);

        if ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            $message = $this->build_message($sql_arr);
            $found   = true;

//@TODO: update message ID according to index data?
        }

        // Get the message from IMAP server
        if (empty($message)) {
            $message = $this->imap->get_headers($uid, $mailbox, true);
            // cache will be updated in close(), see below
        }

        // Save the message in internal cache, will be written to DB in close()
        // Common scenario: user opens unseen message
        // - get message (SELECT)
        // - set message headers/structure (INSERT or UPDATE)
        // - set \Seen flag (UPDATE)
        // This way we can skip one UPDATE
        if (!empty($message)) {
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

        $msg = serialize($this->db->encode(clone $message));

        $flag_fields = array_map(array($this->db, 'quoteIdentifier'), $this->flag_fields);
        $flag_values = array();

        foreach ($this->flag_fields as $flag)
            $flag_values[] = (int) $message->$flag;

        // update cache record (even if it exists, the update
        // here will work as select, assume row exist if affected_rows=0)
        if (!$force) {
            foreach ($flag_fields as $key => $val)
                $flag_data[] = $val . " = " . $flag_values[$key];

            $res = $this->db->query(
                "UPDATE ".get_table_name('cache_messages')
                ." SET data = ?, changed = ".$this->db->now()
                .", " . implode(', ', $flag_data)
                ." WHERE user_id = ?"
                    ." AND mailbox = ?"
                    ." AND uid = ?",
                $msg, $this->userid, $mailbox, (int) $message->uid);

            if ($this->db->affected_rows())
                return;
        }

        // insert new record
        $this->db->query(
            "INSERT INTO ".get_table_name('cache_messages')
            ." (user_id, mailbox, uid, changed, data, " . implode(', ', $flag_fields) . ")"
            ." VALUES (?, ?, ?, ".$this->db->now().", ?, " . implode(', ', $flag_values) . ")",
            $this->userid, $mailbox, (int) $message->uid, $msg);
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
        $flag = strtolower($flag);

        if (in_array($flag, $this->flag_fields)) {
            // Internal cache update
            if ($uids && count($uids) == 1 && ($uid = current($uids))
                && ($message = $this->icache['message'])
                && $message['mailbox'] == $mailbox && $message['object']->uid == $uid
            ) {
                $message['object']->$flag = $enabled;
                return;
            }

            $this->db->query(
                "UPDATE ".get_table_name('cache_messages')
                ." SET changed = ".$this->db->now()
                .", " .$this->db->quoteIdentifier($flag) . " = " . intval($enabled)
                ." WHERE user_id = ?"
                    ." AND mailbox = ?"
                    .($uids !== null ? " AND uid IN (".$this->db->array2list((array)$uids, 'integer').")" : ""),
                $this->userid, $mailbox);
        }
        else {
            // @TODO: SELECT+UPDATE?
            $this->remove_message($mailbox, $uids);
        }
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
     */
    function remove_index($mailbox = null)
    {
        $this->db->query(
            "DELETE FROM ".get_table_name('cache_index')
            ." WHERE user_id = ".intval($this->userid)
                .(strlen($mailbox) ? " AND mailbox = ".$this->db->quote($mailbox) : "")
        );

        if (strlen($mailbox))
            unset($this->icache[$mailbox]['index']);
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

        if (strlen($mailbox))
            unset($this->icache[$mailbox]['thread']);
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
        $this->remove_index($mailbox);
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
            "SELECT data"
            ." FROM ".get_table_name('cache_index')
            ." WHERE user_id = ?"
                ." AND mailbox = ?",
            $this->userid, $mailbox);

        if ($sql_arr = $this->db->fetch_assoc($sql_result)) {
            $data = explode('@', $sql_arr['data']);

            return array(
                'seq'        => explode(',', $data[0]),
                'uid'        => explode(',', $data[1]),
                'sort_field' => $data[2],
                'sort_order' => $data[3],
                'deleted'    => $data[4],
                'validity'   => $data[5],
                'uidnext'    => $data[6],
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
        $data = array(), $mbox_data = array(), $exists = false)
    {
        $data = array(
            implode(',', array_keys($data)),
            implode(',', array_values($data)),
            $sort_field,
            $sort_order,
            (int) $this->skip_deleted,
            (int) $mbox_data['UIDVALIDITY'],
            (int) $mbox_data['UIDNEXT'],
        );
        $data = implode('@', $data);

        if ($exists)
            $sql_result = $this->db->query(
                "UPDATE ".get_table_name('cache_index')
                ." SET data = ?, changed = ".$this->db->now()
                ." WHERE user_id = ?"
                    ." AND mailbox = ?",
                $data, $this->userid, $mailbox);
        else
            $sql_result = $this->db->query(
                "INSERT INTO ".get_table_name('cache_index')
                ." (user_id, mailbox, data, changed)"
                ." VALUES (?, ?, ?, ".$this->db->now().")",
                $this->userid, $mailbox, $data);
    }


    /**
     * Saves thread data into database
     */
    private function add_thread_row($mailbox, $data = array(), $mbox_data = array(), $exists = false)
    {
        $data = array(
            serialize($data['tree']),
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
        // @TODO: while we're storing message sequence numbers in thread
        //        index, should UIDVALIDITY invalidate the thread data?
        if ($index['validity'] != $mbox_data['UIDVALIDITY']) {
            // the whole cache (all folders) is invalid
            $this->clear();
            $exists = false;
            return false;
        }

        // Folder is empty but cache isn't
        if (empty($mbox_data['EXISTS']) && (!empty($index['seq']) || !empty($index['tree']))) {
            $this->clear($mailbox);
            $exists = false;
            return false;
        }

        // Check UIDNEXT
        if ($index['uidnext'] != $mbox_data['UIDNEXT']) {
            unset($this->icache[$mailbox][$is_thread ? 'thread' : 'index']);
            return false;
        }

        // Index was created with different skip_deleted setting
        if ($this->skip_deleted != $index['deleted']) {
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
                    $index = null; // cache invalid
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
            foreach ($this->flag_fields as $field)
                $message->$field = (bool) $sql_arr[$field];
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
            $object = $message['object'];
            // remove body too big (>500kB)
            if ($object->body && strlen($object->body) > 500 * 1024)
                $object->body = null;

            // calculate current md5 sum
            $md5sum = md5(serialize($object));

            if ($message['md5sum'] != $md5sum) {
                $this->add_message($message['mailbox'], $object, !$message['exists']);
            }

            $this->icache['message']['md5sum'] = $md5sum;
        }
    }

}
