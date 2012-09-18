<?php
/**
 * Filesystem Attachments
 *
 * This plugin which provides database backed storage for temporary
 * attachment file handling.  The primary advantage of this plugin
 * is its compatibility with round-robin dns multi-server roundcube
 * installations.
 *
 * This plugin relies on the core filesystem_attachments plugin
 *
 * @author Ziba Scott <ziba@umich.edu>
 * @author Aleksander Machniak <alec@alec.pl>
 * @version @package_version@
 */
require_once('plugins/filesystem_attachments/filesystem_attachments.php');
class database_attachments extends filesystem_attachments
{

    // A prefix for the cache key used in the session and in the key field of the cache table
    private $cache_prefix = "db_attach";

    /**
     * Helper method to generate a unique key for the given attachment file
     */
    private function _key($args)
    {
        $uname = $args['path'] ? $args['path'] : $args['name'];
        return  $this->cache_prefix . $args['group'] . md5(mktime() . $uname . $_SESSION['user_id']);
    }

    /**
     * Save a newly uploaded attachment
     */
    function upload($args)
    {
        $args['status'] = false;
        $rcmail = rcmail::get_instance();
        $key = $this->_key($args);

        $data = file_get_contents($args['path']);

        if ($data === false)
            return $args;

        $data = base64_encode($data);

        $status = $rcmail->db->query(
            "INSERT INTO ".get_table_name('cache')
             ." (created, user_id, cache_key, data)"
             ." VALUES (".$rcmail->db->now().", ?, ?, ?)",
            $_SESSION['user_id'],
            $key,
            $data);

        if ($status) {
            $args['id'] = $key;
            $args['status'] = true;
            unset($args['path']);
        }

        return $args;
    }

    /**
     * Save an attachment from a non-upload source (draft or forward)
     */
    function save($args)
    {
        $args['status'] = false;
        $rcmail = rcmail::get_instance();

        $key = $this->_key($args);

        if ($args['path']) {
            $args['data'] = file_get_contents($args['path']);

            if ($args['data'] === false)
                return $args;
        }

        $data = base64_encode($args['data']);

        $status = $rcmail->db->query(
            "INSERT INTO ".get_table_name('cache')
             ." (created, user_id, cache_key, data)"
             ." VALUES (".$rcmail->db->now().", ?, ?, ?)",
            $_SESSION['user_id'],
            $key,
            $data);

        if ($status) {
            $args['id'] = $key;
            $args['status'] = true;
        }

        return $args;
    }

    /**
     * Remove an attachment from storage
     * This is triggered by the remove attachment button on the compose screen
     */
    function remove($args)
    {
        $args['status'] = false;
        $rcmail = rcmail::get_instance();
        $status = $rcmail->db->query(
            "DELETE FROM ".get_table_name('cache')
             ." WHERE user_id = ?"
                ." AND cache_key = ?",
            $_SESSION['user_id'],
            $args['id']);

        if ($status) {
            $args['status'] = true;
        }

        return $args;
    }

    /**
     * When composing an html message, image attachments may be shown
     * For this plugin, $this->get() will check the file and
     * return it's contents
     */
    function display($args)
    {
        return $this->get($args);
    }

    /**
     * When displaying or sending the attachment the file contents are fetched
     * using this method. This is also called by the attachment_display hook.
     */
    function get($args)
    {
        $rcmail = rcmail::get_instance();

        $sql_result = $rcmail->db->query(
            "SELECT data"
             ." FROM ".get_table_name('cache')
             ." WHERE user_id=?"
                ." AND cache_key=?",
            $_SESSION['user_id'],
            $args['id']);

        if ($sql_arr = $rcmail->db->fetch_assoc($sql_result)) {
            $args['data'] = base64_decode($sql_arr['data']);
            $args['status'] = true;
        }

        return $args;
    }

    /**
     * Delete all temp files associated with this user
     */
    function cleanup($args)
    {
        $prefix = $this->cache_prefix . $args['group'];
        $rcmail = rcmail::get_instance();
        $rcmail->db->query(
            "DELETE FROM ".get_table_name('cache')
            ." WHERE user_id = ?"
                ." AND cache_key LIKE '{$prefix}%'",
            $_SESSION['user_id']);
    }
}
