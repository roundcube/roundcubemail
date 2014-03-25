<?php
/**
 * Database Attachments
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

require_once INSTALL_PATH . 'plugins/filesystem_attachments/filesystem_attachments.php';

class database_attachments extends filesystem_attachments
{
    // Cache object
    protected $cache;

    // A prefix for the cache key used in the session and in the key field of the cache table
    protected $prefix = "db_attach";

    /**
     * Save a newly uploaded attachment
     */
    function upload($args)
    {
        $args['status'] = false;

        $cache = $this->get_cache();
        $key   = $this->_key($args);
        $data  = file_get_contents($args['path']);

        if ($data === false) {
            return $args;
        }

        $data   = base64_encode($data);
        $status = $cache->write($key, $data);

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

        $cache = $this->get_cache();
        $key   = $this->_key($args);

        if ($args['path']) {
            $args['data'] = file_get_contents($args['path']);

            if ($args['data'] === false) {
                return $args;
            }
        }

        $data   = base64_encode($args['data']);
        $status = $cache->write($key, $data);

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
        $cache  = $this->get_cache();
        $status = $cache->remove($args['id']);

        $args['status'] = true;

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
        $cache = $this->get_cache();
        $data  = $cache->read($args['id']);

        if ($data) {
            $args['data'] = base64_decode($data);
            $args['status'] = true;
        }

        return $args;
    }

    /**
     * Delete all temp files associated with this user
     */
    function cleanup($args)
    {
        // check if cache object exist, it may be empty on session_destroy (#1489726)
        if ($cache = $this->get_cache()) {
            $cache->remove($args['group'], true);
        }
    }

    /**
     * Helper method to generate a unique key for the given attachment file
     */
    protected function _key($args)
    {
        $uname = $args['path'] ? $args['path'] : $args['name'];
        return $args['group'] . md5(mktime() . $uname . $_SESSION['user_id']);
    }

    /**
     * Initialize and return cache object
     */
    protected function get_cache()
    {
        if (!$this->cache) {
            $this->load_config();

            $rcmail = rcube::get_instance();
            $ttl    = 12 * 60 * 60; // default: 12 hours
            $ttl    = $rcmail->config->get('database_attachments_cache_ttl', $ttl);
            $type   = $rcmail->config->get('database_attachments_cache', 'db');

            // Init SQL cache (disable cache data serialization)
            $this->cache = $rcmail->get_cache($this->prefix, 'db', $ttl, false);
        }

        return $this->cache;
    }
}
