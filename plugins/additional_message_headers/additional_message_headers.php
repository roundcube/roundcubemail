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
 *
 * @website http://roundcube.net
 */
class additional_message_headers extends rcube_plugin
{
    /**
     * Plugin initialization
     */
    #[Override]
    public function init()
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
    public function message_headers($args)
    {
        $this->load_config();

        $rcube = rcube::get_instance();

        // additional email headers
        $additional_headers = $rcube->config->get('additional_message_headers', []);

        if (!empty($additional_headers)) {
            // Expand the % config variables
            $search = [
                    '/(^|[^%])%u/',
                    '/(^|[^%])%l/',
                    '/(^|[^%])%d/',
            ];

            $replace = [
                    '${1}' . $rcube->get_user_name(),
                    '${1}' . $rcube->user->get_username('local'),
                    '${1}' . $rcube->user->get_username('domain'),
            ];

            $additional_headers = preg_replace($search, $replace, $additional_headers);

            // replace %%<variable> with %<variable>
            $additional_headers = preg_replace('/%(%[uld])/', '\1', $additional_headers);

            $args['message']->headers($additional_headers, true);
        }

        return $args;
    }
}
