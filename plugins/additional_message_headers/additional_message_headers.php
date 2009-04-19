<?php

/**
 * Additional Message Headers
 *
 * Very simple plugin which will read additional headers for outgoing messages from the config file.
 *
 * Enable the plugin in config/main.inc.php and add your desired headers.
 *
 * @version 1.0
 * @author Ziba Scott
 * @website http://roundcube.net
 * 
 * Example:
 *
 * $rcmail_config['additional_message_headers']['X-Remote-Browser'] = $_SERVER['HTTP_USER_AGENT'];
 * $rcmail_config['additional_message_headers']['X-Originating-IP'] = $_SERVER['REMOTE_ADDR'];
 * $rcmail_config['additional_message_headers']['X-RoundCube-Server'] = $_SERVER['SERVER_ADDR'];
 * if( isset( $_SERVER['MACHINE_NAME'] )) {
 *     $rcmail_config['additional_message_headers']['X-RoundCube-Server'] .= ' (' . $_SERVER['MACHINE_NAME'] . ')';
 * }
 */
class additional_message_headers extends rcube_plugin
{
    public $task = 'mail';
    
    function init()
    {
        $this->add_hook('outgoing_message_headers', array($this, 'message_headers'));
    }

    function message_headers($args){

        // additional email headers
        $additional_headers = rcmail::get_instance()->config->get('additional_message_headers',array());
        foreach($additional_headers as $header=>$value){
            $args['headers'][$header] = $value;
        }

        return $args;
    }
}
