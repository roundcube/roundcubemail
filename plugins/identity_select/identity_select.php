<?php

/**
 * Identity selection based on additional message headers.
 *
 * On reply to a message user identity selection is based on
 * content of standard headers i.e. From, To, Cc and Return-Path.
 * Here you can add header(s) set by your SMTP server (e.g.
 * Delivered-To, Envelope-To, X-Envelope-To, X-RCPT-TO) to make
 * identity selection more accurate.
 *
 * Enable the plugin in config.inc.php and add your desired headers:
 *   $config['identity_select_headers'] = array('Delivered-To');
 *
 * @version @package_version@
 * @author Aleksander Machniak <alec@alec.pl>
 * @license GNU GPLv3+
 */
class identity_select extends rcube_plugin
{
    public $task = 'mail';


    function init()
    {
        $this->add_hook('identity_select', array($this, 'select'));
        $this->add_hook('storage_init', array($this, 'storage_init'));
    }

    /**
     * Adds additional headers to supported headers list
     */
    function storage_init($p)
    {
        $rcmail = rcmail::get_instance();

        if ($add_headers = (array)$rcmail->config->get('identity_select_headers', array())) {
            $p['fetch_headers'] = trim($p['fetch_headers'] . ' ' . strtoupper(join(' ', $add_headers)));
        }

        return $p;
    }

    /**
     * Identity selection
     */
    function select($p)
    {
        if ($p['selected'] !== null || !is_object($p['message']->headers)) {
            return $p;
        }

        $rcmail = rcmail::get_instance();

        foreach ((array)$rcmail->config->get('identity_select_headers', array()) as $header) {
            if ($header = $p['message']->headers->get($header, false)) {
                foreach ($p['identities'] as $idx => $ident) {
                    if (in_array($ident['email_ascii'], (array)$header)) {
                        $p['selected'] = $idx;
                        break 2;
                    }
                }
            }
        }

        return $p;
    }
}
