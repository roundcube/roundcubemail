<?php
/**
 * RoundCube Reconnect Plugin
 *
 * @version 0.1
 * @author Sandro Knauß <hefee@debian.org>
 * @license GPLv3+
 */
class reconnect extends rcube_plugin
{
    private $max_attempts;

    function init()
    {
        $rcmail = rcmail::get_instance();

        $this->load_config();

        $this->imap_max_attempts = $rcmail->config->get('reconnect_imap_max_attempts', 5);

        $this->add_hook('storage_connect', array($this, 'storage_connect'));
    }

    function storage_connect($args)
    {
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
