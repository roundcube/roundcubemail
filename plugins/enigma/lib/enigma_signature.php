<?php

/**
 +-------------------------------------------------------------------------+
 | Signature class for the Enigma Plugin                                   |
 |                                                                         |
 | Copyright (C) The Roundcube Dev Team                                    |
 |                                                                         |
 | Licensed under the GNU General Public License version 3 or              |
 | any later version with exceptions for skins & plugins.                  |
 | See the README file for a full license statement.                       |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+
*/

class enigma_signature
{
    public $id;
    public $valid;
    public $fingerprint;
    public $created;
    public $expires;
    public $name;
    public $comment;
    public $email;

    // Set it to true if signature is valid, but part of the message
    // was out of the signed block
    public $partial;

    /**
     * Find key user id matching the email message sender
     *
     * @param enigma_engine $engine  Enigma engine
     * @param rcube_message $message Message object
     * @param string        $part_id Message part identifier
     *
     * @return string User identifier (name + email)
     */
    public function get_sender($engine, $message, $part_id = null)
    {
        if (!$this->email) {
            return $this->name;
        }

        if ($this->fingerprint && ($key = $engine->get_key($this->fingerprint))) {
            $from    = $message->headers->from;
            $charset = $message->headers->charset;

            // Get From: header from the parent part, if it's a forwarded message
            if ($part_id && strpos($part_id, '.') !== false) {
                $level = explode('.', $part_id);
                $parts = $message->mime_parts();

                while (array_pop($level) !== null) {
                    $parent = join('.', $level);
                    if (!empty($parts[$parent]) && $parts[$parent]->mimetype == 'message/rfc822') {
                        $from    = $parts[$parent]->headers['from'];
                        $charset = $parts[$parent]->charset;
                        break;
                    }
                }
            }

            $from = rcube_mime::decode_address_list($from, 1, true, $charset);
            $from = (array) $from[1];

            if (!empty($from)) {
                // Compare name and email
                foreach ($key->users as $user) {
                    if ($user->name == $from['name'] && $user->email == $from['mailto']) {
                        return sprintf('%s <%s>', $user->name, $user->email);
                    }
                }

                // Compare only email
                foreach ($key->users as $user) {
                    if ($user->email === $from['mailto']) {
                        return sprintf('%s <%s>', $this->name, $user->email);
                    }
                }
            }
        }

        return sprintf('%s <%s>', $this->name, $this->email);
    }
}
