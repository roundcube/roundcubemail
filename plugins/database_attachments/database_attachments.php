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
 * @author  Ziba Scott <ziba@umich.edu>
 * @author  Aleksander Machniak <alec@alec.pl>
 * @version @package_version@
 */

require_once INSTALL_PATH . 'plugins/filesystem_attachments/filesystem_attachments.php';

class database_attachments extends filesystem_attachments
{
    /**
     * @var object Cache object
     */
    protected $cache;

    /**
     * @var string A prefix for the cache key used in the session and in the key field of the cache table
     */
    protected $prefix = "db_attach";

    /**
     * Save a newly uploaded attachment
     *
     * @param array $args
     *
     * @return array
     */
    function upload($args)
    {
        $args['status'] = false;

        $cache = $this->get_cache();

        $key = $this->_key($args);

        $data = file_get_contents($args['path']);

        if ($data === false) return $args;

        $status = $cache->write($key, base64_encode($data));

        if ($status) {
            $args['id']     = $key;
            $args['status'] = true;

            unset($args['path']);
        }

        return $args;
    }

    /**
     * Save an attachment from a non-upload source (draft or forward)
     *
     * @param array $args
     *
     * @return array
     */
    function save($args)
    {
        $args['status'] = false;

        $cache = $this->get_cache();

        $key = $this->_key($args);

        if ($args['path']) {
            $args['data'] = file_get_contents($args['path']);

            if ($args['data'] === false) return $args;
        }

        $status = $cache->write($key, base64_encode($args['data']));

        if ($status) {
            $args['id']     = $key;
            $args['status'] = true;
        }

        return $args;
    }

    /**
     * Remove an attachment from storage
     * This is triggered by the remove attachment button on the compose screen
     *
     * @param array $args
     *
     * @return array
     */
    function remove($args)
    {
        $this->get_cache()->remove($args['id']);

        $args['status'] = true;

        return $args;
    }

    /**
     * When composing an html message, image attachments may be shown
     * For this plugin, $this->get() will check the file and
     * return it's contents
     *
     * @param array $args
     *
     * @return array
     */
    function display($args)
    {
        return $this->get($args);
    }

    /**
     * When displaying or sending the attachment the file contents are fetched
     * using this method. This is also called by the attachment_display hook.
     *
     * @param array $args
     *
     * @return array
     */
    function get($args)
    {
        $cache = $this->get_cache();

        $data = $cache->read($args['id']);

        if ($data) {
            $args['data']   = base64_decode($data);
            $args['status'] = true;
        }

        return $args;
    }

    /**
     * Delete all temp files associated with this user
     *
     * @param array $args
     */
    function cleanup($args)
    {
        $this->get_cache()->remove($args['group'], true);
    }

    /**
     * Helper method to generate a unique key for the given attachment file
     *
     * @param array $args
     */
    protected function _key($args)
    {
        return $args['group']
            . md5(mktime()
            . ($args['path'] ? $args['path'] : $args['name'])
            . $_SESSION['user_id']);
    }

    /**
     * Initialize and return cache object
     */
    protected function get_cache()
    {
        if (!$this->cache) {
            $this->load_config();

            $rcmail = rcube::get_instance();

            $ttl  = $rcmail->config->get('database_attachments_cache_ttl', 12 * 60 * 60); // default: 12 hours
            $type = $rcmail->config->get('database_attachments_cache', 'db');

            // Init SQL cache (disable cache data serialization)
            $this->cache = $rcmail->get_cache($this->prefix, 'db', $ttl, false);
        }

        return $this->cache;
    }
}
