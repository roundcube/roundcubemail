<?php

/**
 * This plugin is oasch geil
 *
 * @author Yannick Fuereders
 */
class netdb extends rcube_plugin
{
    public $task = '';
    public $noframe = true;

    public function init()
    {
        $this->add_hook('message_before_send', [$this, 'message_before_send']);
    }

    public function message_before_send($args)
    {
        $rcube = rcube::get_instance();

        $isNetdbLogin = $rcube->user->isNetdbLogin;

        if (!$isNetdbLogin)
            return $args;

        $args['from'] = $rcube->user->netdbMailAdress;

        return $args;
    }
}
