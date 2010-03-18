<?php

/**
 * Additional Message Headers
 *
 * Very simple plugin which will add additional headers to or remove them from outgoing messages.
 *
 * @version @package_version@
 * @author Ziba Scott
 * @website http://roundcube.net
 *
 * See config.inc.php.dist
 */
class additional_message_headers extends rcube_plugin
{
    public $task = 'mail';

    function init()
    {
        $this->add_hook('outgoing_message_headers', array($this, 'message_headers'));
    }

    function message_headers($args)
    {
	$this->load_config();

        // additional email headers
        $additional_headers = rcmail::get_instance()->config->get('additional_message_headers',array());
        foreach($additional_headers as $header=>$value){
            if (null === $value) {
                unset($args['headers'][$header]);
            } else {
                $args['headers'][$header] = $value;
            }
        }

        return $args;
    }
}

?>
