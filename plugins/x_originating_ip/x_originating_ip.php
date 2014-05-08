<?php

/**
 * Additional Message Headers
 *
 * Add the X-Originating-IP header to outgoing messages
 *
 * @version @package_version@
 * @author Brendan Braybrook
 * @website http://opensource.tucows.com
 */
class x_originating_ip extends rcube_plugin
{
    function init() {
        $this->add_hook('message_before_send', array($this, 'ip_header'));
    }

    function ip_header($args) {
        $headers = $args['message']->headers();
        $headers['X-Originating-IP'] = '[' . $_SERVER['REMOTE_ADDR'] . ']';
        $args['message']->_headers = array();
        $args['message']->headers($headers);
        return $args;
    }
}
