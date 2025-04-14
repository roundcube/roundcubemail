<?php

/**
 * hMailserver password driver (mySQL db version)
 *
 * @version 1.0
 *
 * @author Andrea Lanfranchi <andrea.lanfranchi@gmail.com>
 * 
 * Note: This code is based on the hMailServer published code
 * here https://github.com/hmailserver/hmailserver/tree/master/hmailserver/source
 * 
 * Warning: Unlike the hmail password plugin (DCOM), this plugin does not use 
 * the hmailserver COM object to change the password. As a result while the password
 * is correctly changed in the database, hMailServer will not be notified of the change
 * and will not close the active connections of the user that is changing the password.
 * This means that the new password will not be used until the user logs out and logs in again.
 *
 * Copyright (C) The Roundcube Dev Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://www.gnu.org/licenses/.
 */

class rcube_hmaildb_password
{

    private const SALT_LENGTH = 6;  // Length of the salt to be used in the password hashing (SHA256)
    private const ENC_NONE = 0;     // No encryption (plaintext case insensitive ! Ugh !)
    private const ENC_BLOWFISH = 1; // Blowfish encryption (deprecated)
    private const ENC_MD5 = 2;      // MD5 encryption (deprecated)
    private const ENC_SHA256 = 3;   // SHA256 encryption with salt (default)

    /**
     * This is main RoundCoube function to be called when the password is changed.
     * It will be called by the password plugin when the user changes their password.
     * What this function does is :
     * 1. Check if the user exists in the database
     * 2. Check if the current password is correct (i.e. matches the one in the database)
     * 3. Hash the new password using SHA256 with a new random salt
     * 4. Update the password hash in the database
     */
    public function save($curpass, $passwd, $username)
    {
        $rcmail = rcmail::get_instance();
        $dbconf = $rcmail->config->get('hmaildb');

        if (!is_string($dbconf['dsn']) || !is_string($dbconf['user']) || !is_string($dbconf['password']) ||
            empty($dbconf['dsn']) || empty($dbconf['user']) || empty($dbconf['password'])) {
            return PASSWORD_CONNECT_ERROR;
        }

        $db = null;
        try {
            $db = new PDO($dbconf['dsn'], $dbconf['user'], $dbconf['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true,
                ]
            );
        } catch (Exception $e) {
            return PASSWORD_CONNECT_ERROR;
        }

        // Check if the user exists in the database
        $stmt = $db->prepare('SELECT accountid as id, accountaddress as addr, accountpassword as encpw, accountpwencryption as enctype FROM hm_accounts WHERE accountaddress = :username AND accountactive = 1');
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return PASSWORD_COMPARE_CURRENT;
        }

        // Check if the current password matches the one in the database
        // The password is stored in the database as a hash, so we need to hash the current password with the same salt and compare it
        $salt = $this->get_salt_($data['encpw']);
        if ($salt === null) {
            return PASSWORD_CRYPT_ERROR;
        }

        $encType = $data['enctype'];
        $curEncPass = $this->encrypt_($curpass, $encType, $salt);
        if ($curEncPass === null) {
            return PASSWORD_CRYPT_ERROR;
        } else {
            if (strcasecmp($curEncPass, $data['encpw']) !== 0) {
                rcube::raise_error([
                    'code'    => 403,
                    'type'    => 'php',
                    'message' => 'Password plugin: Current password does not match'
                ], false);
                return PASSWORD_COMPARE_CURRENT;
            }
        }

        // Hash the new password using SHA256 with a new random salt
        $newSalt = $this->gen_salt_();
        if ($newSalt === null) {
            return PASSWORD_CRYPT_ERROR;
        }
        $newEncPass = $this->encrypt_($passwd, $encType, $newSalt);
        if ($newEncPass === null) {
            return PASSWORD_CRYPT_ERROR;
        }

        // Update the password hash in the database
        $stmt = $db->prepare('UPDATE hm_accounts SET accountpassword = :newpw WHERE accountid = :id AND accountactive = 1 AND accountpassword = :oldpw');
        $stmt->bindParam(':oldpw', $data['encpw'], PDO::PARAM_STR);
        $stmt->bindParam(':newpw', $newEncPass, PDO::PARAM_STR);
        $stmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            return PASSWORD_COMPARE_CURRENT;
        }
        // If the update was successful, we can assume the password has been changed
        return PASSWORD_SUCCESS; // All done, return success
    }


    /**
     * Extracts the salt part from a given hash.
     *
     * @param string $input The full hash string from which to extract the salt.
     * @return string|null The extracted salt if valid, or null on failure.
     */
    private function get_salt_(string $input): ?string
    {
        if (strlen($input) < self::SALT_LENGTH || !ctype_xdigit($input)) {
            return null;
        }
        return substr($input, 0, self::SALT_LENGTH);
    }

    /**
     * Generates a random salt string
     *
     * @return string The generated salt string.
     * @throws Exception If the random bytes generation fails.
     */
    private function gen_salt_(): string
    {
        return bin2hex(random_bytes(intval(self::SALT_LENGTH / 2)));
    }

    /**
     * Encrypts a password using SHA256 with a given salt.
     *
     * @param string $password The password to hash.
     * @param int $encType The encryption type (currently only SHA256 is supported).
     * @param string $salt The hexadecimal-encoded salt string.
     * @return string The resulting hash string.
     * @throws Exception If the salt length is invalid or the hash length is incorrect.
     */
    private function encrypt_(string $password, int $encType, string $salt): ?string
    {
        // Latest hmailserver implement by default SHA256 with salt by default
        // Other encryption types are still in hMailServer code for backward compatibility.
        // I might decide to implement them in the future if some requets come in.
        // For now, only SHA256 is supported.
        if ($encType != self::ENC_SHA256) {
            return null;
        }

        // If no salt provided, generate a new one
        if (empty($salt)) {
            $salt = $this->gen_salt_();
        }

        // Validate the salt length and format
        if (strlen($salt) != self::SALT_LENGTH || !ctype_xdigit($salt)) {
            return null;
        }

        // Hash the password using SHA256 with the provided salt
        $ret = hash('sha256', $salt . $password);
        if (strlen($ret) != 64) {
            return null;
        }
        return $salt . $ret;
    }

}
