<?php

/**
 * Additional Message Headers
 *
 * Very simple plugin which will add additional headers
 * to or remove them from outgoing messages.
 *
 * Enable the plugin in config.inc.php and add your desired headers:
 * $config['additional_message_headers'] = array('User-Agent' => 'My-Very-Own-Webmail');
 *
 * @version @package_version@
 * @author Ziba Scott
 * @website http://roundcube.net
 */
class additional_message_headers extends rcube_plugin
{
    function init()
    {
        $this->add_hook('message_before_send', array($this, 'message_headers'));
    }

    function message_headers($args)
    {
        $this->load_config();

        $rcube = rcube::get_instance();

        // additional email headers
        $additional_headers = $rcube->config->get('additional_message_headers', array());

        if (!empty($additional_headers)) {
            // Mail_mime >= 1.9.0
            if (method_exists($message, 'isMultipart')) {
                $args['message']->headers($additional_headers, true);
            }
            else {
                $headers = $args['message']->headers();

                foreach ((array) $additional_headers as $header => $value) {
                    if ($value === null) {
                        unset($headers[$header]);
                    }
                    else {
                        $headers[$header] = $value;
                    }
                }

                $args['message']->_headers = array();
                $args['message']->headers($headers);
            }
        }

        return $args;
    }
}
