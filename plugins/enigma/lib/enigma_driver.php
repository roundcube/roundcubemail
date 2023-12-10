<?php

/**
 +-------------------------------------------------------------------------+
 | Abstract driver for the Enigma Plugin                                   |
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

abstract class enigma_driver
{
    const SUPPORT_RSA = 'RSA';
    const SUPPORT_ECC = 'ECC';

    /**
     * Class constructor.
     *
     * @param string $user User name (email address)
     */
    abstract function __construct($user);

    /**
     * Driver initialization.
     *
     * @return mixed NULL on success, enigma_error on failure
     */
    abstract function init();

    /**
     * Encryption (and optional signing).
     *
     * @param string     $text     Message body
     * @param array      $keys     List of keys (enigma_key objects)
     * @param enigma_key $sign_key Optional signing Key ID
     *
     * @return mixed Encrypted message or enigma_error on failure
     */
    abstract function encrypt($text, $keys, $sign_key = null);

    /**
     * Decryption (and sig verification if sig exists).
     *
     * @param string           $text      Encrypted message
     * @param array            $keys      List of key-password
     * @param enigma_signature $signature Signature information (if available)
     *
     * @return mixed Decrypted message or enigma_error on failure
     */
    abstract function decrypt($text, $keys = [], &$signature = null);

    /**
     * Signing.
     *
     * @param string     $text Message body
     * @param enigma_key $key  The signing key
     * @param int        $mode Signing mode (enigma_engine::SIGN_*)
     *
     * @return mixed True on success or enigma_error on failure
     */
    abstract function sign($text, $key, $mode = null);

    /**
     * Signature verification.
     *
     * @param string $text      Message body
     * @param string $signature Signature, if message is of type PGP/MIME and body doesn't contain it
     *
     * @return mixed Signature information (enigma_signature) or enigma_error
     */
    abstract function verify($text, $signature);

    /**
     * Key/Cert file import.
     *
     * @param string $content   File name or file content
     * @param bool   $isfile    True if first argument is a filename
     * @param array  $passwords Optional key => password map
     *
     * @return mixed Import status array or enigma_error
     */
    abstract function import($content, $isfile = false, $passwords = []);

    /**
     * Key/Cert export.
     *
     * @param string $key          Key ID
     * @param bool   $with_private Include private key
     * @param array  $passwords    Optional key => password map
     *
     * @return mixed Key content or enigma_error
     */
    abstract function export($key, $with_private = false, $passwords = []);

    /**
     * Keys listing.
     *
     * @param string $pattern Optional pattern for key ID, user ID or fingerprint
     *
     * @return mixed Array of enigma_key objects or enigma_error
     */
    abstract function list_keys($pattern = '');

    /**
     * Single key information.
     *
     * @param string $keyid Key ID, user ID or fingerprint
     *
     * @return mixed Key (enigma_key) object or enigma_error
     */
    abstract function get_key($keyid);

    /**
     * Key pair generation.
     *
     * @param array $data Key/User data (name, email, password, size)
     *
     * @return mixed Key (enigma_key) object or enigma_error
     */
    abstract function gen_key($data);

    /**
     * Key deletion.
     *
     * @param string $keyid Key ID
     *
     * @return mixed True on success or enigma_error
     */
    abstract function delete_key($keyid);

    /**
     * Returns a name of the hash algorithm used for the last
     * signing operation.
     *
     * @return string Hash algorithm name e.g. sha1
     */
    abstract function signature_algorithm();

    /**
     * Returns a list of supported features.
     *
     * @return array Capabilities list
     */
    public function capabilities()
    {
        return [];
    }
}
