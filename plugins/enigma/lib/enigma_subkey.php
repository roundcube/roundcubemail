<?php
/*
 +-------------------------------------------------------------------------+
 | SubKey class for the Enigma Plugin                                      |
 |                                                                         |
 | Copyright (C) 2010-2015 The Roundcube Dev Team                          |
 |                                                                         |
 | Licensed under the GNU General Public License version 3 or              |
 | any later version with exceptions for skins & plugins.                  |
 | See the README file for a full license statement.                       |
 |                                                                         |
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
    public $can_sign;
    public $can_encrypt;

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

}
