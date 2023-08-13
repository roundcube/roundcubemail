<?php
/**
 * Roundcube Reconnect Plugin
 *
 * @version 0.2
 * @author Sandro Knauß <hefee@debian.org>
 * @license GPLv3+
 */
class reconnect extends rcube_plugin
{
    private $imap_max_attempts;

    /**
     * Plugin initialization
     */
    function init()
    {
        $this->add_hook('storage_connect', [$this, 'storage_connect']);
    }

    /**
     * Storage_connect hook handler
     */
    function storage_connect($args)
    {
        $rcmail = rcmail::get_instance();

        $this->load_config();

        $this->imap_max_attempts = $rcmail->config->get('reconnect_imap_max_attempts', 5);

        $args['retry'] = ($args['attempt'] <= $this->imap_max_attempts);

        if ($args['attempt'] == 1) {
            return $args;
        }

        $storage = rcmail::get_instance()->get_storage();

        switch ($storage->get_error_code()) {
        case rcube_imap_generic::ERROR_NO:
        case rcube_imap_generic::ERROR_BAD:
        case rcube_imap_generic::ERROR_BYE:
            $args['retry'] = false;
            break;
        }

        if ($args['retry']) {
            // if we do a new attempt, sleep 50 to 150ms before retry.
            usleep(rand(50*1000, 150*1000));
        }

        return $args;
    }
}
