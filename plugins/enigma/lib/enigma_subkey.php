<?php
/*
 +-------------------------------------------------------------------------+
 | SubKey class for the Enigma Plugin                                      |
 |                                                                         |
 | This program is free software; you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License version 2          |
 | as published by the Free Software Foundation.                           |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License along |
 | with this program; if not, write to the Free Software Foundation, Inc., |
 | 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.             |
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
