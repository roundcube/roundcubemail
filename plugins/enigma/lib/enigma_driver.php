<?php
/*
 +-------------------------------------------------------------------------+
 | Abstract driver for the Enigma Plugin                                   |
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

abstract class enigma_driver
{
    /**
     * Class constructor.
     *
     * @param string User name (email address)
     */
    abstract function __construct($user);

    /**
     * Driver initialization.
     *
     * @return mixed NULL on success, enigma_error on failure
     */
    abstract function init();

    /**
     * Encryption.
     */
    abstract function encrypt($text, $keys);

    /**
     * Decryption..
     */
    abstract function decrypt($text, $key, $passwd);

    /**
     * Signing.
     */
    abstract function sign($text, $key, $passwd);

    /**
     * Signature verification.
     *
     * @param string Message body
     * @param string Signature, if message is of type PGP/MIME and body doesn't contain it
     *
     * @return mixed Signature information (enigma_signature) or enigma_error
     */
    abstract function verify($text, $signature);

    /**
     * Key/Cert file import.
     *
     * @param string  File name or file content
     * @param bollean True if first argument is a filename
     *
     * @return mixed Import status array or enigma_error
     */
    abstract function import($content, $isfile=false);

    /**
     * Keys listing.
     *
     * @param string Optional pattern for key ID, user ID or fingerprint
     *
     * @return mixed Array of enigma_key objects or enigma_error
     */
    abstract function list_keys($pattern='');
    
    /**
     * Single key information.
     *
     * @param string Key ID, user ID or fingerprint
     *
     * @return mixed Key (enigma_key) object or enigma_error
     */
    abstract function get_key($keyid);

    /**
     * Key pair generation.
     *
     * @param array Key/User data
     *
     * @return mixed Key (enigma_key) object or enigma_error
     */
    abstract function gen_key($data);
    
    /**
     * Key deletion.
     */
    abstract function del_key($keyid);
}
