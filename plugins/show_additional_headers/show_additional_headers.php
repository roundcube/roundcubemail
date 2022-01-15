<?php

/**
 * Show additional message headers
 *
 * Proof-of-concept plugin which will fetch additional headers
 * and display them in the message view.
 *
 * Enable the plugin in config.inc.php and add your desired headers:
 *   $config['show_additional_headers'] = ['User-Agent'];
 *
 * @author Thomas Bruederli
 * @license GNU GPLv3+
 */
class show_additional_headers extends rcube_plugin
{
    public $task = 'mail';

    /**
     * Plugin initialization
     */
    function init()
    {
        $rcmail = rcmail::get_instance();

        if ($rcmail->action == 'show' || $rcmail->action == 'preview') {
            $this->add_hook('storage_init', [$this, 'storage_init']);
            $this->add_hook('message_headers_output', [$this, 'message_headers']);
        }
        else if ($rcmail->action == '') {
            // with enabled_caching we're fetching additional headers before show/preview
            $this->add_hook('storage_init', [$this, 'storage_init']);
        }
    }

    /**
     * Handler for 'storage_init' hook, where we tell the core to
     * fetch specified additional headers from IMAP.
     *
     * @params array @p Hook parameters
     *
     * @return array Modified hook parameters
     */
    function storage_init($p)
    {
        $rcmail      = rcmail::get_instance();
        $add_headers = $rcmail->config->get('show_additional_headers', []);

        if (!empty($add_headers)) {
            $add_headers = strtoupper(join(' ', (array) $add_headers));
            if (isset($p['fetch_headers'])) {
                $p['fetch_headers'] .= ' ' . $add_headers;
            }
            else {
                $p['fetch_headers'] = $add_headers;
            }
        }

        return $p;
    }

    /**
     * Handler for 'message_headers_output' hook, where we add the additional
     * headers to the output.
     *
     * @params array @p Hook parameters
     *
     * @return array Modified hook parameters
     */
    function message_headers($p)
    {
        $rcmail      = rcmail::get_instance();
        $add_headers = $rcmail->config->get('show_additional_headers', []);

        foreach ((array) $add_headers as $header) {
            if ($value = $p['headers']->get($header)) {
                if (is_array($value)) {
                    foreach ($value as $idx => $v) {
                        $p['output']["$header:$idx"] = ['title' => $header, 'value' => $v];
                    }
                }
                else {
                    $p['output'][$header] = ['title' => $header, 'value' => $value];
                }
            }
        }

        return $p;
    }
}
