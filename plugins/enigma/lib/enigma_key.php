<?php

/**
 +-------------------------------------------------------------------------+
 | Key class for the Enigma Plugin                                         |
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

class enigma_key
{
    public $id;
    public $name;
    public $users   = [];
    public $subkeys = [];
    public $reference;
    public $password;

    const TYPE_UNKNOWN = 0;
    const TYPE_KEYPAIR = 1;
    const TYPE_PUBLIC  = 2;

    const CAN_ENCRYPT      = 1;
    const CAN_SIGN         = 2;
    const CAN_CERTIFY      = 4;
    const CAN_AUTHENTICATE = 8;


    /**
     * Keys list sorting callback for usort()
     */
    static function cmp($a, $b)
    {
        return strcmp($a->name, $b->name);
    }

    /**
     * Returns key type
     *
     * @return int One of self::TYPE_* constant values
     */
    function get_type()
    {
        if (!empty($this->subkeys[0]) && $this->subkeys[0]->has_private) {
            return enigma_key::TYPE_KEYPAIR;
        }
        else if (!empty($this->subkeys[0])) {
            return enigma_key::TYPE_PUBLIC;
        }

        return enigma_key::TYPE_UNKNOWN;
    }

    /**
     * Returns true if all subkeys are revoked
     *
     * @return bool
     */
    function is_revoked()
    {
        foreach ($this->subkeys as $subkey) {
            if (!$subkey->revoked) {
                return false;
            }
        }

        return !empty($this->subkeys);
    }

    /**
     * Returns true if any user ID is valid
     *
     * @return bool
     */
    function is_valid()
    {
        foreach ($this->users as $user) {
            if ($user->valid) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if any of subkeys is a private key
     *
     * @return bool
     */
    function is_private()
    {
        foreach ($this->subkeys as $subkey) {
            if ($subkey->has_private) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get key ID by user email
     *
     * @param string $email Email address
     * @param int    $mode  Key mode (see self::CAN_* constants)
     *
     * @return enigma_subkey|null Subkey object
     */
    function find_subkey($email, $mode)
    {
        foreach ($this->users as $user) {
            if (strcasecmp($user->email, $email) === 0 && $user->valid && !$user->revoked) {
                foreach ($this->subkeys as $subkey) {
                    if (!$subkey->revoked && !$subkey->is_expired()) {
                        if ($subkey->usage & $mode) {
                            return $subkey;
                        }
                    }
                }
            }
        }
    }

    /**
     * Converts long ID or Fingerprint to short ID
     * Crypt_GPG uses internal, but e.g. Thunderbird's Enigmail displays short ID
     *
     * @param string $id Key ID or fingerprint
     *
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
     * @param string $fingerprint Key fingerprint
     *
     * @return string Formatted fingerprint (with spaces)
     */
    static function format_fingerprint($fingerprint)
    {
        if (!$fingerprint) {
            return '';
        }

        $result = '';
        for ($i=0; $i<40; $i++) {
            if ($i % 4 == 0) {
                $result .= ' ';
            }
            $result .= $fingerprint[$i];
        }

        return $result;
    }
}
