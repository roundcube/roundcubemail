<?php

/** Net_SMTP adapter using Roundcube's staggered TCP connector. GPL-3.0-or-later. */
class rcube_net_smtp extends \Net_SMTP
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->socket = new rcube_net_socket();
    }
}
