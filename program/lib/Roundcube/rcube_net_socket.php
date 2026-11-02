<?php

/** Net_Socket adapter. Authentication and STARTTLS stay in Net_SMTP. GPL-3.0-or-later. */
class rcube_net_socket extends \Net_Socket
{
    public function connect($addr, $port = 0, $persistent = null, $timeout = null, $options = null)
    {
        if ($persistent || $this->persistent) {
            return parent::connect($addr, $port, $persistent, $timeout, $options);
        }
        if (is_resource($this->fp)) {
            fclose($this->fp);
            $this->fp = null;
        }
        $connector = new rcube_socket_connector();
        $stream = $connector->connect($addr, $port, $timeout, (array) $options, $errno, $errstr);
        if ($stream === null) {
            return parent::connect($addr, $port, $persistent, $timeout, $options);
        }
        if (!$stream) {
            return $this->raiseError($errstr, $errno);
        }
        $this->addr = $addr;
        $this->port = $port;
        $this->fp = $stream;
        $this->setTimeout();
        return $this->setBlocking($this->blocking);
    }
}
