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
            $map = [
                '/(^|[^%])%u/' => '${1}' . $rcube->get_user_name(),
                '/(^|[^%])%l/' => '${1}' . $rcube->user->get_username('local'),
                '/(^|[^%])%d/' => '${1}' . $rcube->user->get_username('domain'),
            ];
            $search = array_keys($map);
            $replace = array_values($map);

            // Loop the array and see whether we're dealing with a CALLABLE or implying a STRING
            foreach ($additional_headers as $key => $val) {
                if (is_callable($val)) {
                    $additional_headers[$key] = $val();
                } else {
                    $additional_headers[$key] = preg_replace($search, $replace, $val);
                    // replace %%<variable> with %<variable>
                    $additional_headers[$key] = preg_replace('/%(%[uld])/', '\1', $val);                    
                }
            }

            $args['message']->headers($additional_headers, true);
        }

        return $args;
    }
}
