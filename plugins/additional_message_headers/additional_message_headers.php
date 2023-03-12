<?php

/**
 * Additional Message Headers
 *
 * Very simple plugin which will add additional headers
 * to or remove them from outgoing messages.
 *
 * Enable the plugin in config.inc.php and add your desired headers:
 * $config['additional_message_headers'] = ['User-Agent' => 'My-Very-Own-Webmail'];
 *
 * @author Ziba Scott
 * @website http://roundcube.net
 */
class additional_message_headers extends rcube_plugin
{
    /**
     * Plugin initialization
     */
    function init()
    {
        $this->add_hook('message_before_send', [$this, 'message_headers']);
    }

    /**
     * 'message_before_send' hook handler
     *
     * @param array $args Hook arguments
     *
     * @return array Modified hook arguments
     */
    function message_headers($args)
    {
        $this->load_config();

        $rcube = rcube::get_instance();

        // additional email headers
        $additional_headers = $rcube->config->get('additional_message_headers', []);

        if (!empty($additional_headers)) {
            $args['message']->headers($additional_headers, true);
        }

        return $args;
    }
}
