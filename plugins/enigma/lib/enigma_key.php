<?php
/*
 +-------------------------------------------------------------------------+
 | Key class for the Enigma Plugin                                         |
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

class enigma_key
{
    public $id;
    public $name;
    public $users = array();
    public $subkeys = array();

    const TYPE_UNKNOWN = 0;
    const TYPE_KEYPAIR = 1;
    const TYPE_PUBLIC = 2;

    /**
     * Keys list sorting callback for usort()
     */
    static function cmp($a, $b)
    {
        return strcmp($a->name, $b->name);
    }

    /**
     * Returns key type
     */
    function get_type()
    {
        if ($this->subkeys[0]->has_private)
            return enigma_key::TYPE_KEYPAIR;
        else if (!empty($this->subkeys[0]))
            return enigma_key::TYPE_PUBLIC;

        return enigma_key::TYPE_UNKNOWN;
    }

    /**
     * Returns true if all user IDs are revoked
     */    
    function is_revoked()
    {
        foreach ($this->subkeys as $subkey)
            if (!$subkey->revoked)
                return false;

        return true;
    }

    /**
     * Returns true if any user ID is valid
     */    
    function is_valid()
    {
        foreach ($this->users as $user)
            if ($user->valid)
                return true;

        return false;
    }
    
    /**
     * Returns true if any of subkeys is not expired
     */    
    function is_expired()
    {
        $now = time();
        
        foreach ($this->subkeys as $subkey)
            if (!$subkey->expires || $subkey->expires > $now)
                return true;
    
        return false;
    }

    /**
     * Converts long ID or Fingerprint to short ID
     * Crypt_GPG uses internal, but e.g. Thunderbird's Enigmail displays short ID
     *
     * @param string Key ID or fingerprint
     * @return string Key short ID
     */
    static function format_id($id)
    {
        // E.g. 04622F2089E037A5 => 89E037A5
        
        return substr($id, -8);
    }

    /**
     * Formats fingerprint string
     *
     * @param string Key fingerprint
     *
     * @return string Formatted fingerprint (with spaces)
     */
    static function format_fingerprint($fingerprint)
    {
        if (!$fingerprint)
            return '';
    
        $result = '';
        for ($i=0; $i<40; $i++) {
            if ($i % 4 == 0)
                $result .= ' ';
            $result .= $fingerprint[$i];
        }
        return $result;
    }

}
