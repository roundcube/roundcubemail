<?php

/**
 +-------------------------------------------------------------------------+
 | SubKey class for the Enigma Plugin                                      |
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

class enigma_subkey
{
    public $id;
    public $fingerprint;
    public $expires;
    public $created;
    public $revoked;
    public $has_private;
    public $algorithm;
    public $length;
    public $usage;


    /**
     * Converts internal ID to short ID
     * Crypt_GPG uses internal, but e.g. Thunderbird's Enigmail displays short ID
     *
     * @return string Key ID
     */
    function get_short_id()
    {
        // E.g. 04622F2089E037A5 => 89E037A5
        return enigma_key::format_id($this->id);
    }

    /**
     * Getter for formatted fingerprint
     *
     * @return string Formatted fingerprint
     */
    function get_fingerprint()
    {
        return enigma_key::format_fingerprint($this->fingerprint);
    }

    /**
     * Returns human-readable name of the key's algorithm
     *
     * @return string Algorithm name
     */
    function get_algorithm()
    {
        // https://datatracker.ietf.org/doc/html/rfc4880#section-9.1
        switch ($this->algorithm) {
        case 1:
        case 2:
        case 3:
            return 'RSA';
        case 16:
        case 20:
            return 'Elgamal';
        case 17:
            return 'DSA';
        case 18:
            return 'Elliptic Curve';
        case 19:
            return 'ECDSA';
        case 21:
            return 'Diffie-Hellman';
        case 22:
            return 'EdDSA';
        }
    }

    /**
     * Checks if the subkey has expired
     *
     * @return bool
     */
    function is_expired()
    {
        $now = new DateTime('now');

        return !empty($this->expires) && $this->expires < $now;
    }

    /**
     * Returns subkey creation date-time string
     *
     * @return string|null
     */
    function get_creation_date()
    {
        if (empty($this->created)) {
            return null;
        }

        $date_format = rcube::get_instance()->config->get('date_format', 'Y-m-d');

        return $this->created->format($date_format);
    }

    /**
     * Returns subkey expiration date-time string
     *
     * @return string|null
     */
    function get_expiration_date()
    {
        if (empty($this->expires)) {
            return null;
        }

        $date_format = rcube::get_instance()->config->get('date_format', 'Y-m-d');

        return $this->expires->format($date_format);
    }
}
