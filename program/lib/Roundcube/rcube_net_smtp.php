<?php

/** Net_SMTP adapter using Roundcube's staggered TCP connector. GPL-3.0-or-later. */
class rcube_net_smtp extends \Net_SMTP
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
        // Net_SMTP's PHPDoc says resource, but its constructor stores a Net_Socket object.
        $this->socket = new rcube_net_socket(); // @phpstan-ignore assign.propertyType
    }
}
