<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Crypt_GPG is a package to use GPG from PHP
 *
 * This package provides an object oriented interface to GNU Privacy
 * Guard (GPG). It requires the GPG executable to be on the system.
 *
 * Though GPG can support symmetric-key cryptography, this package is intended
 * only to facilitate public-key cryptography.
 *
 * This file contains the main GPG class. The class in this file lets you
 * encrypt, decrypt, sign and verify data; import and delete keys; and perform
 * other useful GPG tasks.
 *
 * Example usage:
 * <code>
 * <?php
 * // encrypt some data
 * $gpg = new Crypt_GPG();
 * $gpg->addEncryptKey($mySecretKeyId);
 * $encryptedData = $gpg->encrypt($data);
 * ?>
 * </code>
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; either version 2.1 of the
 * License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @category  Encryption
 * @package   Crypt_GPG
 * @author    Nathan Fredrickson <nathan@silverorange.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2005-2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   CVS: $Id: GPG.php 302814 2010-08-26 15:43:07Z gauthierm $
 * @link      http://pear.php.net/package/Crypt_GPG
 * @link      http://pear.php.net/manual/en/package.encryption.crypt-gpg.php
 * @link      http://www.gnupg.org/
 */

/**
 * Signature handler class
 */
require_once 'Crypt/GPG/VerifyStatusHandler.php';

/**
 * Decryption handler class
 */
require_once 'Crypt/GPG/DecryptStatusHandler.php';

/**
 * GPG key class
 */
require_once 'Crypt/GPG/Key.php';

/**
 * GPG sub-key class
 */
require_once 'Crypt/GPG/SubKey.php';

/**
 * GPG user id class
 */
require_once 'Crypt/GPG/UserId.php';

/**
 * GPG process and I/O engine class
 */
require_once 'Crypt/GPG/Engine.php';

/**
 * GPG exception classes
 */
require_once 'Crypt/GPG/Exceptions.php';

// {{{ class Crypt_GPG

/**
 * A class to use GPG from PHP
 *
 * This class provides an object oriented interface to GNU Privacy Guard (GPG).
 *
 * Though GPG can support symmetric-key cryptography, this class is intended
 * only to facilitate public-key cryptography.
 *
 * @category  Encryption
 * @package   Crypt_GPG
 * @author    Nathan Fredrickson <nathan@silverorange.com>
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2005-2010 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link      http://pear.php.net/package/Crypt_GPG
 * @link      http://www.gnupg.org/
 */
class Crypt_GPG
{
    // {{{ class error constants

    /**
     * Error code returned when there is no error.
     */
    const ERROR_NONE = 0;

    /**
     * Error code returned when an unknown or unhandled error occurs.
     */
    const ERROR_UNKNOWN = 1;

    /**
     * Error code returned when a bad passphrase is used.
     */
    const ERROR_BAD_PASSPHRASE = 2;

    /**
     * Error code returned when a required passphrase is missing.
     */
    const ERROR_MISSING_PASSPHRASE = 3;

    /**
     * Error code returned when a key that is already in the keyring is
     * imported.
     */
    const ERROR_DUPLICATE_KEY = 4;

    /**
     * Error code returned the required data is missing for an operation.
     *
     * This could be missing key data, missing encrypted data or missing
     * signature data.
     */
    const ERROR_NO_DATA = 5;

    /**
     * Error code returned when an unsigned key is used.
     */
    const ERROR_UNSIGNED_KEY = 6;

    /**
     * Error code returned when a key that is not self-signed is used.
     */
    const ERROR_NOT_SELF_SIGNED = 7;

    /**
     * Error code returned when a public or private key that is not in the
     * keyring is used.
     */
    const ERROR_KEY_NOT_FOUND = 8;

    /**
     * Error code returned when an attempt to delete public key having a
     * private key is made.
     */
    const ERROR_DELETE_PRIVATE_KEY = 9;

    /**
     * Error code returned when one or more bad signatures are detected.
     */
    const ERROR_BAD_SIGNATURE = 10;

    /**
     * Error code returned when there is a problem reading GnuPG data files.
     */
    const ERROR_FILE_PERMISSIONS = 11;

    // }}}
    // {{{ class constants for data signing modes

    /**
     * Signing mode for normal signing of data. The signed message will not
     * be readable without special software.
     *
     * This is the default signing mode.
     *
     * @see Crypt_GPG::sign()
     * @see Crypt_GPG::signFile()
     */
    const SIGN_MODE_NORMAL = 1;

    /**
     * Signing mode for clearsigning data. Clearsigned signatures are ASCII
     * armored data and are readable without special software. If the signed
     * message is unencrypted, the message will still be readable. The message
     * text will be in the original encoding.
     *
     * @see Crypt_GPG::sign()
     * @see Crypt_GPG::signFile()
     */
    const SIGN_MODE_CLEAR = 2;

    /**
     * Signing mode for creating a detached signature. When using detached
     * signatures, only the signature data is returned. The original message
     * text may be distributed separately from the signature data. This is
     * useful for miltipart/signed email messages as per
     * {@link http://www.ietf.org/rfc/rfc3156.txt RFC 3156}.
     *
     * @see Crypt_GPG::sign()
     * @see Crypt_GPG::signFile()
     */
    const SIGN_MODE_DETACHED = 3;

    // }}}
    // {{{ class constants for fingerprint formats

    /**
     * No formatting is performed.
     *
     * Example: C3BC615AD9C766E5A85C1F2716D27458B1BBA1C4
     *
     * @see Crypt_GPG::getFingerprint()
     */
    const FORMAT_NONE = 1;

    /**
     * Fingerprint is formatted in the format used by the GnuPG gpg command's
     * default output.
     *
     * Example: C3BC 615A D9C7 66E5 A85C  1F27 16D2 7458 B1BB A1C4
     *
     * @see Crypt_GPG::getFingerprint()
     */
    const FORMAT_CANONICAL = 2;

    /**
     * Fingerprint is formatted in the format used when displaying X.509
     * certificates
     *
     * Example: C3:BC:61:5A:D9:C7:66:E5:A8:5C:1F:27:16:D2:74:58:B1:BB:A1:C4
     *
     * @see Crypt_GPG::getFingerprint()
     */
    const FORMAT_X509 = 3;

    // }}}
    // {{{ other class constants

    /**
     * URI at which package bugs may be reported.
     */
    const BUG_URI = 'http://pear.php.net/bugs/report.php?package=Crypt_GPG';

    // }}}
    // {{{ protected class properties

    /**
     * Engine used to control the GPG subprocess
     *
     * @var Crypt_GPG_Engine
     *
     * @see Crypt_GPG::setEngine()
     */
    protected $engine = null;

    /**
     * Keys used to encrypt
     *
     * The array is of the form:
     * <code>
     * array(
     *   $key_id => array(
     *     'fingerprint' => $fingerprint,
     *     'passphrase'  => null
     *   )
     * );
     * </code>
     *
     * @var array
     * @see Crypt_GPG::addEncryptKey()
     * @see Crypt_GPG::clearEncryptKeys()
     */
    protected $encryptKeys = array();

    /**
     * Keys used to decrypt
     *
     * The array is of the form:
     * <code>
     * array(
     *   $key_id => array(
     *     'fingerprint' => $fingerprint,
     *     'passphrase'  => $passphrase
     *   )
     * );
     * </code>
     *
     * @var array
     * @see Crypt_GPG::addSignKey()
     * @see Crypt_GPG::clearSignKeys()
     */
    protected $signKeys = array();

    /**
     * Keys used to sign
     *
     * The array is of the form:
     * <code>
     * array(
     *   $key_id => array(
     *     'fingerprint' => $fingerprint,
     *     'passphrase'  => $passphrase
     *   )
     * );
     * </code>
     *
     * @var array
     * @see Crypt_GPG::addDecryptKey()
     * @see Crypt_GPG::clearDecryptKeys()
     */
    protected $decryptKeys = array();

    // }}}
    // {{{ __construct()

    /**
     * Creates a new GPG object
     *
     * Available options are:
     *
     * - <kbd>string  homedir</kbd>        - the directory where the GPG
     *                                       keyring files are stored. If not
     *                                       specified, Crypt_GPG uses the
     *                                       default of <kbd>~/.gnupg</kbd>.
     * - <kbd>string  publicKeyring</kbd>  - the file path of the public
     *                                       keyring. Use this if the public
     *                                       keyring is not in the homedir, or
     *                                       if the keyring is in a directory
     *                                       not writable by the process
     *                                       invoking GPG (like Apache). Then
     *                                       you can specify the path to the
     *                                       keyring with this option
     *                                       (/foo/bar/pubring.gpg), and specify
     *                                       a writable directory (like /tmp)
     *                                       using the <i>homedir</i> option.
     * - <kbd>string  privateKeyring</kbd> - the file path of the private
     *                                       keyring. Use this if the private
     *                                       keyring is not in the homedir, or
     *                                       if the keyring is in a directory
     *                                       not writable by the process
     *                                       invoking GPG (like Apache). Then
     *                                       you can specify the path to the
     *                                       keyring with this option
     *                                       (/foo/bar/secring.gpg), and specify
     *                                       a writable directory (like /tmp)
     *                                       using the <i>homedir</i> option.
     * - <kbd>string  trustDb</kbd>        - the file path of the web-of-trust
     *                                       database. Use this if the trust
     *                                       database is not in the homedir, or
     *                                       if the database is in a directory
     *                                       not writable by the process
     *                                       invoking GPG (like Apache). Then
     *                                       you can specify the path to the
     *                                       trust database with this option
     *                                       (/foo/bar/trustdb.gpg), and specify
     *                                       a writable directory (like /tmp)
     *                                       using the <i>homedir</i> option.
     * - <kbd>string  binary</kbd>         - the location of the GPG binary. If
     *                                       not specified, the driver attempts
     *                                       to auto-detect the GPG binary
     *                                       location using a list of known
     *                                       default locations for the current
     *                                       operating system. The option
     *                                       <kbd>gpgBinary</kbd> is a
     *                                       deprecated alias for this option.
     * - <kbd>boolean debug</kbd>          - whether or not to use debug mode.
     *                                       When debug mode is on, all
     *                                       communication to and from the GPG
     *                                       subprocess is logged. This can be
     *
     * @param array $options optional. An array of options used to create the
     *                       GPG object. All options are optional and are
     *                       represented as key-value pairs.
     *
     * @throws Crypt_GPG_FileException if the <kbd>homedir</kbd> does not exist
     *         and cannot be created. This can happen if <kbd>homedir</kbd> is
     *         not specified, Crypt_GPG is run as the web user, and the web
     *         user has no home directory. This exception is also thrown if any
     *         of the options <kbd>publicKeyring</kbd>,
     *         <kbd>privateKeyring</kbd> or <kbd>trustDb</kbd> options are
     *         specified but the files do not exist or are are not readable.
     *         This can happen if the user running the Crypt_GPG process (for
     *         example, the Apache user) does not have permission to read the
     *         files.
     *
     * @throws PEAR_Exception if the provided <kbd>binary</kbd> is invalid, or
     *         if no <kbd>binary</kbd> is provided and no suitable binary could
     *         be found.
     */
    public function __construct(array $options = array())
    {
        $this->setEngine(new Crypt_GPG_Engine($options));
    }

    // }}}
    // {{{ importKey()

    /**
     * Imports a public or private key into the keyring
     *
     * Keys may be removed from the keyring using
     * {@link Crypt_GPG::deletePublicKey()} or
     * {@link Crypt_GPG::deletePrivateKey()}.
     *
     * @param string $data the key data to be imported.
     *
     * @return array an associative array containing the following elements:
     *               - <kbd>fingerprint</kbd>       - the fingerprint of the
     *                                                imported key,
     *               - <kbd>public_imported</kbd>   - the number of public
     *                                                keys imported,
     *               - <kbd>public_unchanged</kbd>  - the number of unchanged
     *                                                public keys,
     *               - <kbd>private_imported</kbd>  - the number of private
     *                                                keys imported,
     *               - <kbd>private_unchanged</kbd> - the number of unchanged
     *                                                private keys.
     *
     * @throws Crypt_GPG_NoDataException if the key data is missing or if the
     *         data is is not valid key data.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function importKey($data)
    {
        return $this->_importKey($data, false);
    }

    // }}}
    // {{{ importKeyFile()

    /**
     * Imports a public or private key file into the keyring
     *
     * Keys may be removed from the keyring using
     * {@link Crypt_GPG::deletePublicKey()} or
     * {@link Crypt_GPG::deletePrivateKey()}.
     *
     * @param string $filename the key file to be imported.
     *
     * @return array an associative array containing the following elements:
     *               - <kbd>fingerprint</kbd>       - the fingerprint of the
     *                                                imported key,
     *               - <kbd>public_imported</kbd>   - the number of public
     *                                                keys imported,
     *               - <kbd>public_unchanged</kbd>  - the number of unchanged
     *                                                public keys,
     *               - <kbd>private_imported</kbd>  - the number of private
     *                                                keys imported,
     *               - <kbd>private_unchanged</kbd> - the number of unchanged
     *                                                private keys.
     *                                                  private keys.
     *
     * @throws Crypt_GPG_NoDataException if the key data is missing or if the
     *         data is is not valid key data.
     *
     * @throws Crypt_GPG_FileException if the key file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function importKeyFile($filename)
    {
        return $this->_importKey($filename, true);
    }

    // }}}
    // {{{ exportPublicKey()

    /**
     * Exports a public key from the keyring
     *
     * The exported key remains on the keyring. To delete the public key, use
     * {@link Crypt_GPG::deletePublicKey()}.
     *
     * If more than one key fingerprint is available for the specified
     * <kbd>$keyId</kbd> (for example, if you use a non-unique uid) only the
     * first public key is exported.
     *
     * @param string  $keyId either the full uid of the public key, the email
     *                       part of the uid of the public key or the key id of
     *                       the public key. For example,
     *                       "Test User (example) <test@example.com>",
     *                       "test@example.com" or a hexadecimal string.
     * @param boolean $armor optional. If true, ASCII armored data is returned;
     *                       otherwise, binary data is returned. Defaults to
     *                       true.
     *
     * @return string the public key data.
     *
     * @throws Crypt_GPG_KeyNotFoundException if a public key with the given
     *         <kbd>$keyId</kbd> is not found.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function exportPublicKey($keyId, $armor = true)
    {
        $fingerprint = $this->getFingerprint($keyId);

        if ($fingerprint === null) {
            throw new Crypt_GPG_KeyNotFoundException(
                'Public key not found: ' . $keyId,
                Crypt_GPG::ERROR_KEY_NOT_FOUND, $keyId);
        }

        $keyData   = '';
        $operation = '--export ' . escapeshellarg($fingerprint);
        $arguments = ($armor) ? array('--armor') : array();

        $this->engine->reset();
        $this->engine->setOutput($keyData);
        $this->engine->setOperation($operation, $arguments);
        $this->engine->run();

        $code = $this->engine->getErrorCode();

        if ($code !== Crypt_GPG::ERROR_NONE) {
            throw new Crypt_GPG_Exception(
                'Unknown error exporting public key. Please use the ' .
                '\'debug\' option when creating the Crypt_GPG object, and ' .
                'file a bug report at ' . self::BUG_URI, $code);
        }

        return $keyData;
    }

    // }}}
    // {{{ deletePublicKey()

    /**
     * Deletes a public key from the keyring
     *
     * If more than one key fingerprint is available for the specified
     * <kbd>$keyId</kbd> (for example, if you use a non-unique uid) only the
     * first public key is deleted.
     *
     * The private key must be deleted first or an exception will be thrown.
     * See {@link Crypt_GPG::deletePrivateKey()}.
     *
     * @param string $keyId either the full uid of the public key, the email
     *                      part of the uid of the public key or the key id of
     *                      the public key. For example,
     *                      "Test User (example) <test@example.com>",
     *                      "test@example.com" or a hexadecimal string.
     *
     * @return void
     *
     * @throws Crypt_GPG_KeyNotFoundException if a public key with the given
     *         <kbd>$keyId</kbd> is not found.
     *
     * @throws Crypt_GPG_DeletePrivateKeyException if the specified public key
     *         has an associated private key on the keyring. The private key
     *         must be deleted first.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function deletePublicKey($keyId)
    {
        $fingerprint = $this->getFingerprint($keyId);

        if ($fingerprint === null) {
            throw new Crypt_GPG_KeyNotFoundException(
                'Public key not found: ' . $keyId,
                Crypt_GPG::ERROR_KEY_NOT_FOUND, $keyId);
        }

        $operation = '--delete-key ' . escapeshellarg($fingerprint);
        $arguments = array(
            '--batch',
            '--yes'
        );

        $this->engine->reset();
        $this->engine->setOperation($operation, $arguments);
        $this->engine->run();

        $code = $this->engine->getErrorCode();

        switch ($code) {
        case Crypt_GPG::ERROR_NONE:
            break;
        case Crypt_GPG::ERROR_DELETE_PRIVATE_KEY:
            throw new Crypt_GPG_DeletePrivateKeyException(
                'Private key must be deleted before public key can be ' .
                'deleted.', $code, $keyId);
        default:
            throw new Crypt_GPG_Exception(
                'Unknown error deleting public key. Please use the ' .
                '\'debug\' option when creating the Crypt_GPG object, and ' .
                'file a bug report at ' . self::BUG_URI, $code);
        }
    }

    // }}}
    // {{{ deletePrivateKey()

    /**
     * Deletes a private key from the keyring
     *
     * If more than one key fingerprint is available for the specified
     * <kbd>$keyId</kbd> (for example, if you use a non-unique uid) only the
     * first private key is deleted.
     *
     * Calls GPG with the <kbd>--delete-secret-key</kbd> command.
     *
     * @param string $keyId either the full uid of the private key, the email
     *                      part of the uid of the private key or the key id of
     *                      the private key. For example,
     *                      "Test User (example) <test@example.com>",
     *                      "test@example.com" or a hexadecimal string.
     *
     * @return void
     *
     * @throws Crypt_GPG_KeyNotFoundException if a private key with the given
     *         <kbd>$keyId</kbd> is not found.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function deletePrivateKey($keyId)
    {
        $fingerprint = $this->getFingerprint($keyId);

        if ($fingerprint === null) {
            throw new Crypt_GPG_KeyNotFoundException(
                'Private key not found: ' . $keyId,
                Crypt_GPG::ERROR_KEY_NOT_FOUND, $keyId);
        }

        $operation = '--delete-secret-key ' . escapeshellarg($fingerprint);
        $arguments = array(
            '--batch',
            '--yes'
        );

        $this->engine->reset();
        $this->engine->setOperation($operation, $arguments);
        $this->engine->run();

        $code = $this->engine->getErrorCode();

        switch ($code) {
        case Crypt_GPG::ERROR_NONE:
            break;
        case Crypt_GPG::ERROR_KEY_NOT_FOUND:
            throw new Crypt_GPG_KeyNotFoundException(
                'Private key not found: ' . $keyId,
                $code, $keyId);
        default:
            throw new Crypt_GPG_Exception(
                'Unknown error deleting private key. Please use the ' .
                '\'debug\' option when creating the Crypt_GPG object, and ' .
                'file a bug report at ' . self::BUG_URI, $code);
        }
    }

    // }}}
    // {{{ getKeys()

    /**
     * Gets the available keys in the keyring
     *
     * Calls GPG with the <kbd>--list-keys</kbd> command and grabs keys. See
     * the first section of <b>doc/DETAILS</b> in the
     * {@link http://www.gnupg.org/download/ GPG package} for a detailed
     * description of how the GPG command output is parsed.
     *
     * @param string $keyId optional. Only keys with that match the specified
     *                      pattern are returned. The pattern may be part of
     *                      a user id, a key id or a key fingerprint. If not
     *                      specified, all keys are returned.
     *
     * @return array an array of {@link Crypt_GPG_Key} objects. If no keys
     *               match the specified <kbd>$keyId</kbd> an empty array is
     *               returned.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     *
     * @see Crypt_GPG_Key
     */
    public function getKeys($keyId = '')
    {
        // get private key fingerprints
        if ($keyId == '') {
            $operation = '--list-secret-keys';
        } else {
            $operation = '--list-secret-keys ' . escapeshellarg($keyId);
        }

        // According to The file 'doc/DETAILS' in the GnuPG distribution, using
        // double '--with-fingerprint' also prints the fingerprint for subkeys.
        $arguments = array(
            '--with-colons',
            '--with-fingerprint',
            '--with-fingerprint',
            '--fixed-list-mode'
        );

        $output = '';

        $this->engine->reset();
        $this->engine->setOutput($output);
        $this->engine->setOperation($operation, $arguments);
        $this->engine->run();

        $code = $this->engine->getErrorCode();

        switch ($code) {
        case Crypt_GPG::ERROR_NONE:
        case Crypt_GPG::ERROR_KEY_NOT_FOUND:
            // ignore not found key errors
            break;
        case Crypt_GPG::ERROR_FILE_PERMISSIONS:
            $filename = $this->engine->getErrorFilename();
            if ($filename) {
                throw new Crypt_GPG_FileException(sprintf(
                    'Error reading GnuPG data file \'%s\'. Check to make ' .
                    'sure it is readable by the current user.', $filename),
                    $code, $filename);
            }
            throw new Crypt_GPG_FileException(
                'Error reading GnuPG data file. Check to make GnuPG data ' .
                'files are readable by the current user.', $code);
        default:
            throw new Crypt_GPG_Exception(
                'Unknown error getting keys. Please use the \'debug\' option ' .
                'when creating the Crypt_GPG object, and file a bug report ' .
                'at ' . self::BUG_URI, $code);
        }

        $privateKeyFingerprints = array();

        $lines = explode(PHP_EOL, $output);
        foreach ($lines as $line) {
            $lineExp = explode(':', $line);
            if ($lineExp[0] == 'fpr') {
                $privateKeyFingerprints[] = $lineExp[9];
            }
        }

        // get public keys
        if ($keyId == '') {
            $operation = '--list-public-keys';
        } else {
            $operation = '--list-public-keys ' . escapeshellarg($keyId);
        }

        $output = '';

        $this->engine->reset();
        $this->engine->setOutput($output);
        $this->engine->setOperation($operation, $arguments);
        $this->engine->run();

        $code = $this->engine->getErrorCode();

        switch ($code) {
        case Crypt_GPG::ERROR_NONE:
        case Crypt_GPG::ERROR_KEY_NOT_FOUND:
            // ignore not found key errors
            break;
        case Crypt_GPG::ERROR_FILE_PERMISSIONS:
            $filename = $this->engine->getErrorFilename();
            if ($filename) {
                throw new Crypt_GPG_FileException(sprintf(
                    'Error reading GnuPG data file \'%s\'. Check to make ' .
                    'sure it is readable by the current user.', $filename),
                    $code, $filename);
            }
            throw new Crypt_GPG_FileException(
                'Error reading GnuPG data file. Check to make GnuPG data ' .
                'files are readable by the current user.', $code);
        default:
            throw new Crypt_GPG_Exception(
                'Unknown error getting keys. Please use the \'debug\' option ' .
                'when creating the Crypt_GPG object, and file a bug report ' .
                'at ' . self::BUG_URI, $code);
        }

        $keys = array();

        $key    = null; // current key
        $subKey = null; // current sub-key

        $lines = explode(PHP_EOL, $output);
        foreach ($lines as $line) {
            $lineExp = explode(':', $line);

            if ($lineExp[0] == 'pub') {

                // new primary key means last key should be added to the array
                if ($key !== null) {
                    $keys[] = $key;
                }

                $key = new Crypt_GPG_Key();

                $subKey = Crypt_GPG_SubKey::parse($line);
                $key->addSubKey($subKey);

            } elseif ($lineExp[0] == 'sub') {

                $subKey = Crypt_GPG_SubKey::parse($line);
                $key->addSubKey($subKey);

            } elseif ($lineExp[0] == 'fpr') {

                $fingerprint = $lineExp[9];

                // set current sub-key fingerprint
                $subKey->setFingerprint($fingerprint);

                // if private key exists, set has private to true
                if (in_array($fingerprint, $privateKeyFingerprints)) {
                    $subKey->setHasPrivate(true);
                }

            } elseif ($lineExp[0] == 'uid') {

                $string = stripcslashes($lineExp[9]); // as per documentation
                $userId = new Crypt_GPG_UserId($string);

                if ($lineExp[1] == 'r') {
                    $userId->setRevoked(true);
                }

                $key->addUserId($userId);

            }
        }

        // add last key
        if ($key !== null) {
            $keys[] = $key;
        }

        return $keys;
    }

    // }}}
    // {{{ getFingerprint()

    /**
     * Gets a key fingerprint from the keyring
     *
     * If more than one key fingerprint is available (for example, if you use
     * a non-unique user id) only the first key fingerprint is returned.
     *
     * Calls the GPG <kbd>--list-keys</kbd> command with the
     * <kbd>--with-fingerprint</kbd> option to retrieve a public key
     * fingerprint.
     *
     * @param string  $keyId  either the full user id of the key, the email
     *                        part of the user id of the key, or the key id of
     *                        the key. For example,
     *                        "Test User (example) <test@example.com>",
     *                        "test@example.com" or a hexadecimal string.
     * @param integer $format optional. How the fingerprint should be formatted.
     *                        Use {@link Crypt_GPG::FORMAT_X509} for X.509
     *                        certificate format,
     *                        {@link Crypt_GPG::FORMAT_CANONICAL} for the format
     *                        used by GnuPG output and
     *                        {@link Crypt_GPG::FORMAT_NONE} for no formatting.
     *                        Defaults to <code>Crypt_GPG::FORMAT_NONE</code>.
     *
     * @return string the fingerprint of the key, or null if no fingerprint
     *                is found for the given <kbd>$keyId</kbd>.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function getFingerprint($keyId, $format = Crypt_GPG::FORMAT_NONE)
    {
        $output    = '';
        $operation = '--list-keys ' . escapeshellarg($keyId);
        $arguments = array(
            '--with-colons',
            '--with-fingerprint'
        );

        $this->engine->reset();
        $this->engine->setOutput($output);
        $this->engine->setOperation($operation, $arguments);
        $this->engine->run();

        $code = $this->engine->getErrorCode();

        switch ($code) {
        case Crypt_GPG::ERROR_NONE:
        case Crypt_GPG::ERROR_KEY_NOT_FOUND:
            // ignore not found key errors
            break;
        default:
            throw new Crypt_GPG_Exception(
                'Unknown error getting key fingerprint. Please use the ' .
                '\'debug\' option when creating the Crypt_GPG object, and ' .
                'file a bug report at ' . self::BUG_URI, $code);
        }

        $fingerprint = null;

        $lines = explode(PHP_EOL, $output);
        foreach ($lines as $line) {
            if (substr($line, 0, 3) == 'fpr') {
                $lineExp     = explode(':', $line);
                $fingerprint = $lineExp[9];

                switch ($format) {
                case Crypt_GPG::FORMAT_CANONICAL:
                    $fingerprintExp = str_split($fingerprint, 4);
                    $format         = '%s %s %s %s %s  %s %s %s %s %s';
                    $fingerprint    = vsprintf($format, $fingerprintExp);
                    break;

                case Crypt_GPG::FORMAT_X509:
                    $fingerprintExp = str_split($fingerprint, 2);
                    $fingerprint    = implode(':', $fingerprintExp);
                    break;
                }

                break;
            }
        }

        return $fingerprint;
    }

    // }}}
    // {{{ encrypt()

    /**
     * Encrypts string data
     *
     * Data is ASCII armored by default but may optionally be returned as
     * binary.
     *
     * @param string  $data  the data to be encrypted.
     * @param boolean $armor optional. If true, ASCII armored data is returned;
     *                       otherwise, binary data is returned. Defaults to
     *                       true.
     *
     * @return string the encrypted data.
     *
     * @throws Crypt_GPG_KeyNotFoundException if no encryption key is specified.
     *         See {@link Crypt_GPG::addEncryptKey()}.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     *
     * @sensitive $data
     */
    public function encrypt($data, $armor = true)
    {
        return $this->_encrypt($data, false, null, $armor);
    }

    // }}}
    // {{{ encryptFile()

    /**
     * Encrypts a file
     *
     * Encrypted data is ASCII armored by default but may optionally be saved
     * as binary.
     *
     * @param string  $filename      the filename of the file to encrypt.
     * @param string  $encryptedFile optional. The filename of the file in
     *                               which to store the encrypted data. If null
     *                               or unspecified, the encrypted data is
     *                               returned as a string.
     * @param boolean $armor         optional. If true, ASCII armored data is
     *                               returned; otherwise, binary data is
     *                               returned. Defaults to true.
     *
     * @return void|string if the <kbd>$encryptedFile</kbd> parameter is null,
     *                     a string containing the encrypted data is returned.
     *
     * @throws Crypt_GPG_KeyNotFoundException if no encryption key is specified.
     *         See {@link Crypt_GPG::addEncryptKey()}.
     *
     * @throws Crypt_GPG_FileException if the output file is not writeable or
     *         if the input file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function encryptFile($filename, $encryptedFile = null, $armor = true)
    {
        return $this->_encrypt($filename, true, $encryptedFile, $armor);
    }

    // }}}
    // {{{ encryptAndSign()

    /**
     * Encrypts and signs data
     *
     * Data is encrypted and signed in a single pass.
     *
     * NOTE: Until GnuPG version 1.4.10, it was not possible to verify
     * encrypted-signed data without decrypting it at the same time. If you try
     * to use {@link Crypt_GPG::verify()} method on encrypted-signed data with
     * earlier GnuPG versions, you will get an error. Please use
     * {@link Crypt_GPG::decryptAndVerify()} to verify encrypted-signed data.
     *
     * @param string  $data  the data to be encrypted and signed.
     * @param boolean $armor optional. If true, ASCII armored data is returned;
     *                       otherwise, binary data is returned. Defaults to
     *                       true.
     *
     * @return string the encrypted signed data.
     *
     * @throws Crypt_GPG_KeyNotFoundException if no encryption key is specified
     *         or if no signing key is specified. See
     *         {@link Crypt_GPG::addEncryptKey()} and
     *         {@link Crypt_GPG::addSignKey()}.
     *
     * @throws Crypt_GPG_BadPassphraseException if a specified passphrase is
     *         incorrect or if a required passphrase is not specified.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     *
     * @see Crypt_GPG::decryptAndVerify()
     */
    public function encryptAndSign($data, $armor = true)
    {
        return $this->_encryptAndSign($data, false, null, $armor);
    }

    // }}}
    // {{{ encryptAndSignFile()

    /**
     * Encrypts and signs a file
     *
     * The file is encrypted and signed in a single pass.
     *
     * NOTE: Until GnuPG version 1.4.10, it was not possible to verify
     * encrypted-signed files without decrypting them at the same time. If you
     * try to use {@link Crypt_GPG::verify()} method on encrypted-signed files
     * with earlier GnuPG versions, you will get an error. Please use
     * {@link Crypt_GPG::decryptAndVerifyFile()} to verify encrypted-signed
     * files.
     *
     * @param string  $filename   the name of the file containing the data to
     *                            be encrypted and signed.
     * @param string  $signedFile optional. The name of the file in which the
     *                            encrypted, signed data should be stored. If
     *                            null or unspecified, the encrypted, signed
     *                            data is returned as a string.
     * @param boolean $armor      optional. If true, ASCII armored data is
     *                            returned; otherwise, binary data is returned.
     *                            Defaults to true.
     *
     * @return void|string if the <kbd>$signedFile</kbd> parameter is null, a
     *                     string containing the encrypted, signed data is
     *                     returned.
     *
     * @throws Crypt_GPG_KeyNotFoundException if no encryption key is specified
     *         or if no signing key is specified. See
     *         {@link Crypt_GPG::addEncryptKey()} and
     *         {@link Crypt_GPG::addSignKey()}.
     *
     * @throws Crypt_GPG_BadPassphraseException if a specified passphrase is
     *         incorrect or if a required passphrase is not specified.
     *
     * @throws Crypt_GPG_FileException if the output file is not writeable or
     *         if the input file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     *
     * @see Crypt_GPG::decryptAndVerifyFile()
     */
    public function encryptAndSignFile($filename, $signedFile = null,
        $armor = true
    ) {
        return $this->_encryptAndSign($filename, true, $signedFile, $armor);
    }

    // }}}
    // {{{ decrypt()

    /**
     * Decrypts string data
     *
     * This method assumes the required private key is available in the keyring
     * and throws an exception if the private key is not available. To add a
     * private key to the keyring, use the {@link Crypt_GPG::importKey()} or
     * {@link Crypt_GPG::importKeyFile()} methods.
     *
     * @param string $encryptedData the data to be decrypted.
     *
     * @return string the decrypted data.
     *
     * @throws Crypt_GPG_KeyNotFoundException if the private key needed to
     *         decrypt the data is not in the user's keyring.
     *
     * @throws Crypt_GPG_NoDataException if specified data does not contain
     *         GPG encrypted data.
     *
     * @throws Crypt_GPG_BadPassphraseException if a required passphrase is
     *         incorrect or if a required passphrase is not specified. See
     *         {@link Crypt_GPG::addDecryptKey()}.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function decrypt($encryptedData)
    {
        return $this->_decrypt($encryptedData, false, null);
    }

    // }}}
    // {{{ decryptFile()

    /**
     * Decrypts a file
     *
     * This method assumes the required private key is available in the keyring
     * and throws an exception if the private key is not available. To add a
     * private key to the keyring, use the {@link Crypt_GPG::importKey()} or
     * {@link Crypt_GPG::importKeyFile()} methods.
     *
     * @param string $encryptedFile the name of the encrypted file data to
     *                              decrypt.
     * @param string $decryptedFile optional. The name of the file to which the
     *                              decrypted data should be written. If null
     *                              or unspecified, the decrypted data is
     *                              returned as a string.
     *
     * @return void|string if the <kbd>$decryptedFile</kbd> parameter is null,
     *                     a string containing the decrypted data is returned.
     *
     * @throws Crypt_GPG_KeyNotFoundException if the private key needed to
     *         decrypt the data is not in the user's keyring.
     *
     * @throws Crypt_GPG_NoDataException if specified data does not contain
     *         GPG encrypted data.
     *
     * @throws Crypt_GPG_BadPassphraseException if a required passphrase is
     *         incorrect or if a required passphrase is not specified. See
     *         {@link Crypt_GPG::addDecryptKey()}.
     *
     * @throws Crypt_GPG_FileException if the output file is not writeable or
     *         if the input file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function decryptFile($encryptedFile, $decryptedFile = null)
    {
        return $this->_decrypt($encryptedFile, true, $decryptedFile);
    }

    // }}}
    // {{{ decryptAndVerify()

    /**
     * Decrypts and verifies string data
     *
     * This method assumes the required private key is available in the keyring
     * and throws an exception if the private key is not available. To add a
     * private key to the keyring, use the {@link Crypt_GPG::importKey()} or
     * {@link Crypt_GPG::importKeyFile()} methods.
     *
     * @param string $encryptedData the encrypted, signed data to be decrypted
     *                              and verified.
     *
     * @return array two element array. The array has an element 'data'
     *               containing the decrypted data and an element
     *               'signatures' containing an array of
     *               {@link Crypt_GPG_Signature} objects for the signed data.
     *
     * @throws Crypt_GPG_KeyNotFoundException if the private key needed to
     *         decrypt the data is not in the user's keyring.
     *
     * @throws Crypt_GPG_NoDataException if specified data does not contain
     *         GPG encrypted data.
     *
     * @throws Crypt_GPG_BadPassphraseException if a required passphrase is
     *         incorrect or if a required passphrase is not specified. See
     *         {@link Crypt_GPG::addDecryptKey()}.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function decryptAndVerify($encryptedData)
    {
        return $this->_decryptAndVerify($encryptedData, false, null);
    }

    // }}}
    // {{{ decryptAndVerifyFile()

    /**
     * Decrypts and verifies a signed, encrypted file
     *
     * This method assumes the required private key is available in the keyring
     * and throws an exception if the private key is not available. To add a
     * private key to the keyring, use the {@link Crypt_GPG::importKey()} or
     * {@link Crypt_GPG::importKeyFile()} methods.
     *
     * @param string $encryptedFile the name of the signed, encrypted file to
     *                              to decrypt and verify.
     * @param string $decryptedFile optional. The name of the file to which the
     *                              decrypted data should be written. If null
     *                              or unspecified, the decrypted data is
     *                              returned in the results array.
     *
     * @return array two element array. The array has an element 'data'
     *               containing the decrypted data and an element
     *               'signatures' containing an array of
     *               {@link Crypt_GPG_Signature} objects for the signed data.
     *               If the decrypted data is written to a file, the 'data'
     *               element is null.
     *
     * @throws Crypt_GPG_KeyNotFoundException if the private key needed to
     *         decrypt the data is not in the user's keyring.
     *
     * @throws Crypt_GPG_NoDataException if specified data does not contain
     *         GPG encrypted data.
     *
     * @throws Crypt_GPG_BadPassphraseException if a required passphrase is
     *         incorrect or if a required passphrase is not specified. See
     *         {@link Crypt_GPG::addDecryptKey()}.
     *
     * @throws Crypt_GPG_FileException if the output file is not writeable or
     *         if the input file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function decryptAndVerifyFile($encryptedFile, $decryptedFile = null)
    {
        return $this->_decryptAndVerify($encryptedFile, true, $decryptedFile);
    }

    // }}}
    // {{{ sign()

    /**
     * Signs data
     *
     * Data may be signed using any one of the three available signing modes:
     * - {@link Crypt_GPG::SIGN_MODE_NORMAL}
     * - {@link Crypt_GPG::SIGN_MODE_CLEAR}
     * - {@link Crypt_GPG::SIGN_MODE_DETACHED}
     *
     * @param string  $data     the data to be signed.
     * @param boolean $mode     optional. The data signing mode to use. Should
     *                          be one of {@link Crypt_GPG::SIGN_MODE_NORMAL},
     *                          {@link Crypt_GPG::SIGN_MODE_CLEAR} or
     *                          {@link Crypt_GPG::SIGN_MODE_DETACHED}. If not
     *                          specified, defaults to
     *                          <kbd>Crypt_GPG::SIGN_MODE_NORMAL</kbd>.
     * @param boolean $armor    optional. If true, ASCII armored data is
     *                          returned; otherwise, binary data is returned.
     *                          Defaults to true. This has no effect if the
     *                          mode <kbd>Crypt_GPG::SIGN_MODE_CLEAR</kbd> is
     *                          used.
     * @param boolean $textmode optional. If true, line-breaks in signed data
     *                          are normalized. Use this option when signing
     *                          e-mail, or for greater compatibility between
     *                          systems with different line-break formats.
     *                          Defaults to false. This has no effect if the
     *                          mode <kbd>Crypt_GPG::SIGN_MODE_CLEAR</kbd> is
     *                          used as clear-signing always uses textmode.
     *
     * @return string the signed data, or the signature data if a detached
     *                signature is requested.
     *
     * @throws Crypt_GPG_KeyNotFoundException if no signing key is specified.
     *         See {@link Crypt_GPG::addSignKey()}.
     *
     * @throws Crypt_GPG_BadPassphraseException if a specified passphrase is
     *         incorrect or if a required passphrase is not specified.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function sign($data, $mode = Crypt_GPG::SIGN_MODE_NORMAL,
        $armor = true, $textmode = false
    ) {
        return $this->_sign($data, false, null, $mode, $armor, $textmode);
    }

    // }}}
    // {{{ signFile()

    /**
     * Signs a file
     *
     * The file may be signed using any one of the three available signing
     * modes:
     * - {@link Crypt_GPG::SIGN_MODE_NORMAL}
     * - {@link Crypt_GPG::SIGN_MODE_CLEAR}
     * - {@link Crypt_GPG::SIGN_MODE_DETACHED}
     *
     * @param string  $filename   the name of the file containing the data to
     *                            be signed.
     * @param string  $signedFile optional. The name of the file in which the
     *                            signed data should be stored. If null or
     *                            unspecified, the signed data is returned as a
     *                            string.
     * @param boolean $mode       optional. The data signing mode to use. Should
     *                            be one of {@link Crypt_GPG::SIGN_MODE_NORMAL},
     *                            {@link Crypt_GPG::SIGN_MODE_CLEAR} or
     *                            {@link Crypt_GPG::SIGN_MODE_DETACHED}. If not
     *                            specified, defaults to
     *                            <kbd>Crypt_GPG::SIGN_MODE_NORMAL</kbd>.
     * @param boolean $armor      optional. If true, ASCII armored data is
     *                            returned; otherwise, binary data is returned.
     *                            Defaults to true. This has no effect if the
     *                            mode <kbd>Crypt_GPG::SIGN_MODE_CLEAR</kbd> is
     *                            used.
     * @param boolean $textmode   optional. If true, line-breaks in signed data
     *                            are normalized. Use this option when signing
     *                            e-mail, or for greater compatibility between
     *                            systems with different line-break formats.
     *                            Defaults to false. This has no effect if the
     *                            mode <kbd>Crypt_GPG::SIGN_MODE_CLEAR</kbd> is
     *                            used as clear-signing always uses textmode.
     *
     * @return void|string if the <kbd>$signedFile</kbd> parameter is null, a
     *                     string containing the signed data (or the signature
     *                     data if a detached signature is requested) is
     *                     returned.
     *
     * @throws Crypt_GPG_KeyNotFoundException if no signing key is specified.
     *         See {@link Crypt_GPG::addSignKey()}.
     *
     * @throws Crypt_GPG_BadPassphraseException if a specified passphrase is
     *         incorrect or if a required passphrase is not specified.
     *
     * @throws Crypt_GPG_FileException if the output file is not writeable or
     *         if the input file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    public function signFile($filename, $signedFile = null,
        $mode = Crypt_GPG::SIGN_MODE_NORMAL, $armor = true, $textmode = false
    ) {
        return $this->_sign(
            $filename,
            true,
            $signedFile,
            $mode,
            $armor,
            $textmode
        );
    }

    // }}}
    // {{{ verify()

    /**
     * Verifies signed data
     *
     * The {@link Crypt_GPG::decrypt()} method may be used to get the original
     * message if the signed data is not clearsigned and does not use a
     * detached signature.
     *
     * @param string $signedData the signed data to be verified.
     * @param string $signature  optional. If verifying data signed using a
     *                           detached signature, this must be the detached
     *                           signature data. The data that was signed is
     *                           specified in <kbd>$signedData</kbd>.
     *
     * @return array an array of {@link Crypt_GPG_Signature} objects for the
     *               signed data. For each signature that is valid, the
     *               {@link Crypt_GPG_Signature::isValid()} will return true.
     *
     * @throws Crypt_GPG_NoDataException if the provided data is not signed
     *         data.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     *
     * @see Crypt_GPG_Signature
     */
    public function verify($signedData, $signature = '')
    {
        return $this->_verify($signedData, false, $signature);
    }

    // }}}
    // {{{ verifyFile()

    /**
     * Verifies a signed file
     *
     * The {@link Crypt_GPG::decryptFile()} method may be used to get the
     * original message if the signed data is not clearsigned and does not use
     * a detached signature.
     *
     * @param string $filename  the signed file to be verified.
     * @param string $signature optional. If verifying a file signed using a
     *                          detached signature, this must be the detached
     *                          signature data. The file that was signed is
     *                          specified in <kbd>$filename</kbd>.
     *
     * @return array an array of {@link Crypt_GPG_Signature} objects for the
     *               signed data. For each signature that is valid, the
     *               {@link Crypt_GPG_Signature::isValid()} will return true.
     *
     * @throws Crypt_GPG_NoDataException if the provided data is not signed
     *         data.
     *
     * @throws Crypt_GPG_FileException if the input file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     *
     * @see Crypt_GPG_Signature
     */
    public function verifyFile($filename, $signature = '')
    {
        return $this->_verify($filename, true, $signature);
    }

    // }}}
    // {{{ addDecryptKey()

    /**
     * Adds a key to use for decryption
     *
     * @param mixed  $key        the key to use. This may be a key identifier,
     *                           user id, fingerprint, {@link Crypt_GPG_Key} or
     *                           {@link Crypt_GPG_SubKey}. The key must be able
     *                           to encrypt.
     * @param string $passphrase optional. The passphrase of the key required
     *                           for decryption.
     *
     * @return void
     *
     * @see Crypt_GPG::decrypt()
     * @see Crypt_GPG::decryptFile()
     * @see Crypt_GPG::clearDecryptKeys()
     * @see Crypt_GPG::_addKey()
     * @see Crypt_GPG_DecryptStatusHandler
     *
     * @sensitive $passphrase
     */
    public function addDecryptKey($key, $passphrase = null)
    {
        $this->_addKey($this->decryptKeys, true, false, $key, $passphrase);
    }

    // }}}
    // {{{ addEncryptKey()

    /**
     * Adds a key to use for encryption
     *
     * @param mixed $key the key to use. This may be a key identifier, user id
     *                   user id, fingerprint, {@link Crypt_GPG_Key} or
     *                   {@link Crypt_GPG_SubKey}. The key must be able to
     *                   encrypt.
     *
     * @return void
     *
     * @see Crypt_GPG::encrypt()
     * @see Crypt_GPG::encryptFile()
     * @see Crypt_GPG::clearEncryptKeys()
     * @see Crypt_GPG::_addKey()
     */
    public function addEncryptKey($key)
    {
        $this->_addKey($this->encryptKeys, true, false, $key);
    }

    // }}}
    // {{{ addSignKey()

    /**
     * Adds a key to use for signing
     *
     * @param mixed  $key        the key to use. This may be a key identifier,
     *                           user id, fingerprint, {@link Crypt_GPG_Key} or
     *                           {@link Crypt_GPG_SubKey}. The key must be able
     *                           to sign.
     * @param string $passphrase optional. The passphrase of the key required
     *                           for signing.
     *
     * @return void
     *
     * @see Crypt_GPG::sign()
     * @see Crypt_GPG::signFile()
     * @see Crypt_GPG::clearSignKeys()
     * @see Crypt_GPG::handleSignStatus()
     * @see Crypt_GPG::_addKey()
     *
     * @sensitive $passphrase
     */
    public function addSignKey($key, $passphrase = null)
    {
        $this->_addKey($this->signKeys, false, true, $key, $passphrase);
    }

    // }}}
    // {{{ clearDecryptKeys()

    /**
     * Clears all decryption keys
     *
     * @return void
     *
     * @see Crypt_GPG::decrypt()
     * @see Crypt_GPG::addDecryptKey()
     */
    public function clearDecryptKeys()
    {
        $this->decryptKeys = array();
    }

    // }}}
    // {{{ clearEncryptKeys()

    /**
     * Clears all encryption keys
     *
     * @return void
     *
     * @see Crypt_GPG::encrypt()
     * @see Crypt_GPG::addEncryptKey()
     */
    public function clearEncryptKeys()
    {
        $this->encryptKeys = array();
    }

    // }}}
    // {{{ clearSignKeys()

    /**
     * Clears all signing keys
     *
     * @return void
     *
     * @see Crypt_GPG::sign()
     * @see Crypt_GPG::addSignKey()
     */
    public function clearSignKeys()
    {
        $this->signKeys = array();
    }

    // }}}
    // {{{ handleSignStatus()

    /**
     * Handles the status output from GPG for the sign operation
     *
     * This method is responsible for sending the passphrase commands when
     * required by the {@link Crypt_GPG::sign()} method. See <b>doc/DETAILS</b>
     * in the {@link http://www.gnupg.org/download/ GPG distribution} for
     * detailed information on GPG's status output.
     *
     * @param string $line the status line to handle.
     *
     * @return void
     *
     * @see Crypt_GPG::sign()
     */
    public function handleSignStatus($line)
    {
        $tokens = explode(' ', $line);
        switch ($tokens[0]) {
        case 'NEED_PASSPHRASE':
            $subKeyId = $tokens[1];
            if (array_key_exists($subKeyId, $this->signKeys)) {
                $passphrase = $this->signKeys[$subKeyId]['passphrase'];
                $this->engine->sendCommand($passphrase);
            } else {
                $this->engine->sendCommand('');
            }
            break;
        }
    }

    // }}}
    // {{{ handleImportKeyStatus()

    /**
     * Handles the status output from GPG for the import operation
     *
     * This method is responsible for building the result array that is
     * returned from the {@link Crypt_GPG::importKey()} method. See
     * <b>doc/DETAILS</b> in the
     * {@link http://www.gnupg.org/download/ GPG distribution} for detailed
     * information on GPG's status output.
     *
     * @param string $line    the status line to handle.
     * @param array  &$result the current result array being processed.
     *
     * @return void
     *
     * @see Crypt_GPG::importKey()
     * @see Crypt_GPG::importKeyFile()
     * @see Crypt_GPG_Engine::addStatusHandler()
     */
    public function handleImportKeyStatus($line, array &$result)
    {
        $tokens = explode(' ', $line);
        switch ($tokens[0]) {
        case 'IMPORT_OK':
            $result['fingerprint'] = $tokens[2];
            break;

        case 'IMPORT_RES':
            $result['public_imported']   = intval($tokens[3]);
            $result['public_unchanged']  = intval($tokens[5]);
            $result['private_imported']  = intval($tokens[11]);
            $result['private_unchanged'] = intval($tokens[12]);
            break;
        }
    }

    // }}}
    // {{{ setEngine()

    /**
     * Sets the I/O engine to use for GnuPG operations
     *
     * Normally this method does not need to be used. It provides a means for
     * dependency injection.
     *
     * @param Crypt_GPG_Engine $engine the engine to use.
     *
     * @return void
     */
    public function setEngine(Crypt_GPG_Engine $engine)
    {
        $this->engine = $engine;
    }

    // }}}
    // {{{ _addKey()

    /**
     * Adds a key to one of the internal key arrays
     *
     * This handles resolving full key objects from the provided
     * <kbd>$key</kbd> value.
     *
     * @param array   &$array     the array to which the key should be added.
     * @param boolean $encrypt    whether or not the key must be able to
     *                            encrypt.
     * @param boolean $sign       whether or not the key must be able to sign.
     * @param mixed   $key        the key to add. This may be a key identifier,
     *                            user id, fingerprint, {@link Crypt_GPG_Key} or
     *                            {@link Crypt_GPG_SubKey}.
     * @param string  $passphrase optional. The passphrase associated with the
     *                            key.
     *
     * @return void
     *
     * @sensitive $passphrase
     */
    private function _addKey(array &$array, $encrypt, $sign, $key,
        $passphrase = null
    ) {
        $subKeys = array();

        if (is_scalar($key)) {
            $keys = $this->getKeys($key);
            if (count($keys) == 0) {
                throw new Crypt_GPG_KeyNotFoundException(
                    'Key "' . $key . '" not found.', 0, $key);
            }
            $key = $keys[0];
        }

        if ($key instanceof Crypt_GPG_Key) {
            if ($encrypt && !$key->canEncrypt()) {
                throw new InvalidArgumentException(
                    'Key "' . $key . '" cannot encrypt.');
            }

            if ($sign && !$key->canSign()) {
                throw new InvalidArgumentException(
                    'Key "' . $key . '" cannot sign.');
            }

            foreach ($key->getSubKeys() as $subKey) {
                $canEncrypt = $subKey->canEncrypt();
                $canSign    = $subKey->canSign();
                if (   ($encrypt && $sign && $canEncrypt && $canSign)
                    || ($encrypt && !$sign && $canEncrypt)
                    || (!$encrypt && $sign && $canSign)
                ) {
                    // We add all subkeys that meet the requirements because we
                    // were not told which subkey is required.
                    $subKeys[] = $subKey;
                }
            }
        } elseif ($key instanceof Crypt_GPG_SubKey) {
            $subKeys[] = $key;
        }

        if (count($subKeys) === 0) {
            throw new InvalidArgumentException(
                'Key "' . $key . '" is not in a recognized format.');
        }

        foreach ($subKeys as $subKey) {
            if ($encrypt && !$subKey->canEncrypt()) {
                throw new InvalidArgumentException(
                    'Key "' . $key . '" cannot encrypt.');
            }

            if ($sign && !$subKey->canSign()) {
                throw new InvalidArgumentException(
                    'Key "' . $key . '" cannot sign.');
            }

            $array[$subKey->getId()] = array(
                'fingerprint' => $subKey->getFingerprint(),
                'passphrase'  => $passphrase
            );
        }
    }

    // }}}
    // {{{ _importKey()

    /**
     * Imports a public or private key into the keyring
     *
     * @param string  $key    the key to be imported.
     * @param boolean $isFile whether or not the input is a filename.
     *
     * @return array an associative array containing the following elements:
     *               - <kbd>fingerprint</kbd>       - the fingerprint of the
     *                                                imported key,
     *               - <kbd>public_imported</kbd>   - the number of public
     *                                                keys imported,
     *               - <kbd>public_unchanged</kbd>  - the number of unchanged
     *                                                public keys,
     *               - <kbd>private_imported</kbd>  - the number of private
     *                                                keys imported,
     *               - <kbd>private_unchanged</kbd> - the number of unchanged
     *                                                private keys.
     *
     * @throws Crypt_GPG_NoDataException if the key data is missing or if the
     *         data is is not valid key data.
     *
     * @throws Crypt_GPG_FileException if the key file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    private function _importKey($key, $isFile)
    {
        $result = array();

        if ($isFile) {
            $input = @fopen($key, 'rb');
            if ($input === false) {
                throw new Crypt_GPG_FileException('Could not open key file "' .
                    $key . '" for importing.', 0, $key);
            }
        } else {
            $input = strval($key);
            if ($input == '') {
                throw new Crypt_GPG_NoDataException(
                    'No valid GPG key data found.', Crypt_GPG::ERROR_NO_DATA);
            }
        }

        $arguments = array();
        $version   = $this->engine->getVersion();

        if (   version_compare($version, '1.0.5', 'ge')
            && version_compare($version, '1.0.7', 'lt')
        ) {
            $arguments[] = '--allow-secret-key-import';
        }

        $this->engine->reset();
        $this->engine->addStatusHandler(
            array($this, 'handleImportKeyStatus'),
            array(&$result)
        );

        $this->engine->setOperation('--import', $arguments);
        $this->engine->setInput($input);
        $this->engine->run();

        if ($isFile) {
            fclose($input);
        }

        $code = $this->engine->getErrorCode();

        switch ($code) {
        case Crypt_GPG::ERROR_DUPLICATE_KEY:
        case Crypt_GPG::ERROR_NONE:
            // ignore duplicate key import errors
            break;
        case Crypt_GPG::ERROR_NO_DATA:
            throw new Crypt_GPG_NoDataException(
                'No valid GPG key data found.', $code);
        default:
            throw new Crypt_GPG_Exception(
                'Unknown error importing GPG key. Please use the \'debug\' ' .
                'option when creating the Crypt_GPG object, and file a bug ' .
                'report at ' . self::BUG_URI, $code);
        }

        return $result;
    }

    // }}}
    // {{{ _encrypt()

    /**
     * Encrypts data
     *
     * @param string  $data       the data to encrypt.
     * @param boolean $isFile     whether or not the data is a filename.
     * @param string  $outputFile the filename of the file in which to store
     *                            the encrypted data. If null, the encrypted
     *                            data is returned as a string.
     * @param boolean $armor      if true, ASCII armored data is returned;
     *                            otherwise, binary data is returned.
     *
     * @return void|string if the <kbd>$outputFile</kbd> parameter is null, a
     *                     string containing the encrypted data is returned.
     *
     * @throws Crypt_GPG_KeyNotFoundException if no encryption key is specified.
     *         See {@link Crypt_GPG::addEncryptKey()}.
     *
     * @throws Crypt_GPG_FileException if the output file is not writeable or
     *         if the input file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    private function _encrypt($data, $isFile, $outputFile, $armor)
    {
        if (count($this->encryptKeys) === 0) {
            throw new Crypt_GPG_KeyNotFoundException(
                'No encryption keys specified.');
        }

        if ($isFile) {
            $input = @fopen($data, 'rb');
            if ($input === false) {
                throw new Crypt_GPG_FileException('Could not open input file "' .
                    $data . '" for encryption.', 0, $data);
            }
        } else {
            $input = strval($data);
        }

        if ($outputFile === null) {
            $output = '';
        } else {
            $output = @fopen($outputFile, 'wb');
            if ($output === false) {
                if ($isFile) {
                    fclose($input);
                }
                throw new Crypt_GPG_FileException('Could not open output ' .
                    'file "' . $outputFile . '" for storing encrypted data.',
                    0, $outputFile);
            }
        }

        $arguments = ($armor) ? array('--armor') : array();
        foreach ($this->encryptKeys as $key) {
            $arguments[] = '--recipient ' . escapeshellarg($key['fingerprint']);
        }

        $this->engine->reset();
        $this->engine->setInput($input);
        $this->engine->setOutput($output);
        $this->engine->setOperation('--encrypt', $arguments);
        $this->engine->run();

        if ($isFile) {
            fclose($input);
        }

        if ($outputFile !== null) {
            fclose($output);
        }

        $code = $this->engine->getErrorCode();

        if ($code !== Crypt_GPG::ERROR_NONE) {
            throw new Crypt_GPG_Exception(
                'Unknown error encrypting data. Please use the \'debug\' ' .
                'option when creating the Crypt_GPG object, and file a bug ' .
                'report at ' . self::BUG_URI, $code);
        }

        if ($outputFile === null) {
            return $output;
        }
    }

    // }}}
    // {{{ _decrypt()

    /**
     * Decrypts data
     *
     * @param string  $data       the data to be decrypted.
     * @param boolean $isFile     whether or not the data is a filename.
     * @param string  $outputFile the name of the file to which the decrypted
     *                            data should be written. If null, the decrypted
     *                            data is returned as a string.
     *
     * @return void|string if the <kbd>$outputFile</kbd> parameter is null, a
     *                     string containing the decrypted data is returned.
     *
     * @throws Crypt_GPG_KeyNotFoundException if the private key needed to
     *         decrypt the data is not in the user's keyring.
     *
     * @throws Crypt_GPG_NoDataException if specified data does not contain
     *         GPG encrypted data.
     *
     * @throws Crypt_GPG_BadPassphraseException if a required passphrase is
     *         incorrect or if a required passphrase is not specified. See
     *         {@link Crypt_GPG::addDecryptKey()}.
     *
     * @throws Crypt_GPG_FileException if the output file is not writeable or
     *         if the input file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    private function _decrypt($data, $isFile, $outputFile)
    {
        if ($isFile) {
            $input = @fopen($data, 'rb');
            if ($input === false) {
                throw new Crypt_GPG_FileException('Could not open input file "' .
                    $data . '" for decryption.', 0, $data);
            }
        } else {
            $input = strval($data);
            if ($input == '') {
                throw new Crypt_GPG_NoDataException(
                    'Cannot decrypt data. No PGP encrypted data was found in '.
                    'the provided data.', Crypt_GPG::ERROR_NO_DATA);
            }
        }

        if ($outputFile === null) {
            $output = '';
        } else {
            $output = @fopen($outputFile, 'wb');
            if ($output === false) {
                if ($isFile) {
                    fclose($input);
                }
                throw new Crypt_GPG_FileException('Could not open output ' .
                    'file "' . $outputFile . '" for storing decrypted data.',
                    0, $outputFile);
            }
        }

        $handler = new Crypt_GPG_DecryptStatusHandler($this->engine,
            $this->decryptKeys);

        $this->engine->reset();
        $this->engine->addStatusHandler(array($handler, 'handle'));
        $this->engine->setOperation('--decrypt');
        $this->engine->setInput($input);
        $this->engine->setOutput($output);
        $this->engine->run();

        if ($isFile) {
            fclose($input);
        }

        if ($outputFile !== null) {
            fclose($output);
        }

        // if there was any problem decrypting the data, the handler will
        // deal with it here.
        $handler->throwException();

        if ($outputFile === null) {
            return $output;
        }
    }

    // }}}
    // {{{ _sign()

    /**
     * Signs data
     *
     * @param string  $data       the data to be signed.
     * @param boolean $isFile     whether or not the data is a filename.
     * @param string  $outputFile the name of the file in which the signed data
     *                            should be stored. If null, the signed data is
     *                            returned as a string.
     * @param boolean $mode       the data signing mode to use. Should be one of
     *                            {@link Crypt_GPG::SIGN_MODE_NORMAL},
     *                            {@link Crypt_GPG::SIGN_MODE_CLEAR} or
     *                            {@link Crypt_GPG::SIGN_MODE_DETACHED}.
     * @param boolean $armor      if true, ASCII armored data is returned;
     *                            otherwise, binary data is returned. This has
     *                            no effect if the mode
     *                            <kbd>Crypt_GPG::SIGN_MODE_CLEAR</kbd> is
     *                            used.
     * @param boolean $textmode   if true, line-breaks in signed data be
     *                            normalized. Use this option when signing
     *                            e-mail, or for greater compatibility between
     *                            systems with different line-break formats.
     *                            Defaults to false. This has no effect if the
     *                            mode <kbd>Crypt_GPG::SIGN_MODE_CLEAR</kbd> is
     *                            used as clear-signing always uses textmode.
     *
     * @return void|string if the <kbd>$outputFile</kbd> parameter is null, a
     *                     string containing the signed data (or the signature
     *                     data if a detached signature is requested) is
     *                     returned.
     *
     * @throws Crypt_GPG_KeyNotFoundException if no signing key is specified.
     *         See {@link Crypt_GPG::addSignKey()}.
     *
     * @throws Crypt_GPG_BadPassphraseException if a specified passphrase is
     *         incorrect or if a required passphrase is not specified.
     *
     * @throws Crypt_GPG_FileException if the output file is not writeable or
     *         if the input file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    private function _sign($data, $isFile, $outputFile, $mode, $armor,
        $textmode
    ) {
        if (count($this->signKeys) === 0) {
            throw new Crypt_GPG_KeyNotFoundException(
                'No signing keys specified.');
        }

        if ($isFile) {
            $input = @fopen($data, 'rb');
            if ($input === false) {
                throw new Crypt_GPG_FileException('Could not open input ' .
                    'file "' . $data . '" for signing.', 0, $data);
            }
        } else {
            $input = strval($data);
        }

        if ($outputFile === null) {
            $output = '';
        } else {
            $output = @fopen($outputFile, 'wb');
            if ($output === false) {
                if ($isFile) {
                    fclose($input);
                }
                throw new Crypt_GPG_FileException('Could not open output ' .
                    'file "' . $outputFile . '" for storing signed ' .
                    'data.', 0, $outputFile);
            }
        }

        switch ($mode) {
        case Crypt_GPG::SIGN_MODE_DETACHED:
            $operation = '--detach-sign';
            break;
        case Crypt_GPG::SIGN_MODE_CLEAR:
            $operation = '--clearsign';
            break;
        case Crypt_GPG::SIGN_MODE_NORMAL:
        default:
            $operation = '--sign';
            break;
        }

        $arguments  = array();

        if ($armor) {
            $arguments[] = '--armor';
        }
        if ($textmode) {
            $arguments[] = '--textmode';
        }

        foreach ($this->signKeys as $key) {
            $arguments[] = '--local-user ' .
                escapeshellarg($key['fingerprint']);
        }

        $this->engine->reset();
        $this->engine->addStatusHandler(array($this, 'handleSignStatus'));
        $this->engine->setInput($input);
        $this->engine->setOutput($output);
        $this->engine->setOperation($operation, $arguments);
        $this->engine->run();

        if ($isFile) {
            fclose($input);
        }

        if ($outputFile !== null) {
            fclose($output);
        }

        $code = $this->engine->getErrorCode();

        switch ($code) {
        case Crypt_GPG::ERROR_NONE:
            break;
        case Crypt_GPG::ERROR_KEY_NOT_FOUND:
            throw new Crypt_GPG_KeyNotFoundException(
                'Cannot sign data. Private key not found. Import the '.
                'private key before trying to sign data.', $code,
                $this->engine->getErrorKeyId());
        case Crypt_GPG::ERROR_BAD_PASSPHRASE:
            throw new Crypt_GPG_BadPassphraseException(
                'Cannot sign data. Incorrect passphrase provided.', $code);
        case Crypt_GPG::ERROR_MISSING_PASSPHRASE:
            throw new Crypt_GPG_BadPassphraseException(
                'Cannot sign data. No passphrase provided.', $code);
        default:
            throw new Crypt_GPG_Exception(
                'Unknown error signing data. Please use the \'debug\' option ' .
                'when creating the Crypt_GPG object, and file a bug report ' .
                'at ' . self::BUG_URI, $code);
        }

        if ($outputFile === null) {
            return $output;
        }
    }

    // }}}
    // {{{ _encryptAndSign()

    /**
     * Encrypts and signs data
     *
     * @param string  $data       the data to be encrypted and signed.
     * @param boolean $isFile     whether or not the data is a filename.
     * @param string  $outputFile the name of the file in which the encrypted,
     *                            signed data should be stored. If null, the
     *                            encrypted, signed data is returned as a
     *                            string.
     * @param boolean $armor      if true, ASCII armored data is returned;
     *                            otherwise, binary data is returned.
     *
     * @return void|string if the <kbd>$outputFile</kbd> parameter is null, a
     *                     string containing the encrypted, signed data is
     *                     returned.
     *
     * @throws Crypt_GPG_KeyNotFoundException if no encryption key is specified
     *         or if no signing key is specified. See
     *         {@link Crypt_GPG::addEncryptKey()} and
     *         {@link Crypt_GPG::addSignKey()}.
     *
     * @throws Crypt_GPG_BadPassphraseException if a specified passphrase is
     *         incorrect or if a required passphrase is not specified.
     *
     * @throws Crypt_GPG_FileException if the output file is not writeable or
     *         if the input file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     */
    private function _encryptAndSign($data, $isFile, $outputFile, $armor)
    {
        if (count($this->signKeys) === 0) {
            throw new Crypt_GPG_KeyNotFoundException(
                'No signing keys specified.');
        }

        if (count($this->encryptKeys) === 0) {
            throw new Crypt_GPG_KeyNotFoundException(
                'No encryption keys specified.');
        }


        if ($isFile) {
            $input = @fopen($data, 'rb');
            if ($input === false) {
                throw new Crypt_GPG_FileException('Could not open input ' .
                    'file "' . $data . '" for encrypting and signing.', 0,
                    $data);
            }
        } else {
            $input = strval($data);
        }

        if ($outputFile === null) {
            $output = '';
        } else {
            $output = @fopen($outputFile, 'wb');
            if ($output === false) {
                if ($isFile) {
                    fclose($input);
                }
                throw new Crypt_GPG_FileException('Could not open output ' .
                    'file "' . $outputFile . '" for storing encrypted, ' .
                    'signed data.', 0, $outputFile);
            }
        }

        $arguments  = ($armor) ? array('--armor') : array();

        foreach ($this->signKeys as $key) {
            $arguments[] = '--local-user ' .
                escapeshellarg($key['fingerprint']);
        }

        foreach ($this->encryptKeys as $key) {
            $arguments[] = '--recipient ' . escapeshellarg($key['fingerprint']);
        }

        $this->engine->reset();
        $this->engine->addStatusHandler(array($this, 'handleSignStatus'));
        $this->engine->setInput($input);
        $this->engine->setOutput($output);
        $this->engine->setOperation('--encrypt --sign', $arguments);
        $this->engine->run();

        if ($isFile) {
            fclose($input);
        }

        if ($outputFile !== null) {
            fclose($output);
        }

        $code = $this->engine->getErrorCode();

        switch ($code) {
        case Crypt_GPG::ERROR_NONE:
            break;
        case Crypt_GPG::ERROR_KEY_NOT_FOUND:
            throw new Crypt_GPG_KeyNotFoundException(
                'Cannot sign encrypted data. Private key not found. Import '.
                'the private key before trying to sign the encrypted data.',
                $code, $this->engine->getErrorKeyId());
        case Crypt_GPG::ERROR_BAD_PASSPHRASE:
            throw new Crypt_GPG_BadPassphraseException(
                'Cannot sign encrypted data. Incorrect passphrase provided.',
                $code);
        case Crypt_GPG::ERROR_MISSING_PASSPHRASE:
            throw new Crypt_GPG_BadPassphraseException(
                'Cannot sign encrypted data. No passphrase provided.', $code);
        default:
            throw new Crypt_GPG_Exception(
                'Unknown error encrypting and signing data. Please use the ' .
                '\'debug\' option when creating the Crypt_GPG object, and ' .
                'file a bug report at ' . self::BUG_URI, $code);
        }

        if ($outputFile === null) {
            return $output;
        }
    }

    // }}}
    // {{{ _verify()

    /**
     * Verifies data
     *
     * @param string  $data      the signed data to be verified.
     * @param boolean $isFile    whether or not the data is a filename.
     * @param string  $signature if verifying a file signed using a detached
     *                           signature, this must be the detached signature
     *                           data. Otherwise, specify ''.
     *
     * @return array an array of {@link Crypt_GPG_Signature} objects for the
     *               signed data.
     *
     * @throws Crypt_GPG_NoDataException if the provided data is not signed
     *         data.
     *
     * @throws Crypt_GPG_FileException if the input file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     *
     * @see Crypt_GPG_Signature
     */
    private function _verify($data, $isFile, $signature)
    {
        if ($signature == '') {
            $operation = '--verify';
            $arguments = array();
        } else {
            // Signed data goes in FD_MESSAGE, detached signature data goes in
            // FD_INPUT.
            $operation = '--verify - "-&' . Crypt_GPG_Engine::FD_MESSAGE. '"';
            $arguments = array('--enable-special-filenames');
        }

        $handler = new Crypt_GPG_VerifyStatusHandler();

        if ($isFile) {
            $input = @fopen($data, 'rb');
            if ($input === false) {
                throw new Crypt_GPG_FileException('Could not open input ' .
                    'file "' . $data . '" for verifying.', 0, $data);
            }
        } else {
            $input = strval($data);
            if ($input == '') {
                throw new Crypt_GPG_NoDataException(
                    'No valid signature data found.', Crypt_GPG::ERROR_NO_DATA);
            }
        }

        $this->engine->reset();
        $this->engine->addStatusHandler(array($handler, 'handle'));

        if ($signature == '') {
            // signed or clearsigned data
            $this->engine->setInput($input);
        } else {
            // detached signature
            $this->engine->setInput($signature);
            $this->engine->setMessage($input);
        }

        $this->engine->setOperation($operation, $arguments);
        $this->engine->run();

        if ($isFile) {
            fclose($input);
        }

        $code = $this->engine->getErrorCode();

        switch ($code) {
        case Crypt_GPG::ERROR_NONE:
        case Crypt_GPG::ERROR_BAD_SIGNATURE:
            break;
        case Crypt_GPG::ERROR_NO_DATA:
            throw new Crypt_GPG_NoDataException(
                'No valid signature data found.', $code);
        case Crypt_GPG::ERROR_KEY_NOT_FOUND:
            throw new Crypt_GPG_KeyNotFoundException(
                'Public key required for data verification not in keyring.',
                $code, $this->engine->getErrorKeyId());
        default:
            throw new Crypt_GPG_Exception(
                'Unknown error validating signature details. Please use the ' .
                '\'debug\' option when creating the Crypt_GPG object, and ' .
                'file a bug report at ' . self::BUG_URI, $code);
        }

        return $handler->getSignatures();
    }

    // }}}
    // {{{ _decryptAndVerify()

    /**
     * Decrypts and verifies encrypted, signed data
     *
     * @param string  $data       the encrypted signed data to be decrypted and
     *                            verified.
     * @param boolean $isFile     whether or not the data is a filename.
     * @param string  $outputFile the name of the file to which the decrypted
     *                            data should be written. If null, the decrypted
     *                            data is returned in the results array.
     *
     * @return array two element array. The array has an element 'data'
     *               containing the decrypted data and an element
     *               'signatures' containing an array of
     *               {@link Crypt_GPG_Signature} objects for the signed data.
     *               If the decrypted data is written to a file, the 'data'
     *               element is null.
     *
     * @throws Crypt_GPG_KeyNotFoundException if the private key needed to
     *         decrypt the data is not in the user's keyring or it the public
     *         key needed for verification is not in the user's keyring.
     *
     * @throws Crypt_GPG_NoDataException if specified data does not contain
     *         GPG signed, encrypted data.
     *
     * @throws Crypt_GPG_BadPassphraseException if a required passphrase is
     *         incorrect or if a required passphrase is not specified. See
     *         {@link Crypt_GPG::addDecryptKey()}.
     *
     * @throws Crypt_GPG_FileException if the output file is not writeable or
     *         if the input file is not readable.
     *
     * @throws Crypt_GPG_Exception if an unknown or unexpected error occurs.
     *         Use the <kbd>debug</kbd> option and file a bug report if these
     *         exceptions occur.
     *
     * @see Crypt_GPG_Signature
     */
    private function _decryptAndVerify($data, $isFile, $outputFile)
    {
        if ($isFile) {
            $input = @fopen($data, 'rb');
            if ($input === false) {
                throw new Crypt_GPG_FileException('Could not open input ' .
                    'file "' . $data . '" for decrypting and verifying.', 0,
                    $data);
            }
        } else {
            $input = strval($data);
            if ($input == '') {
                throw new Crypt_GPG_NoDataException(
                    'No valid encrypted signed data found.',
                    Crypt_GPG::ERROR_NO_DATA);
            }
        }

        if ($outputFile === null) {
            $output = '';
        } else {
            $output = @fopen($outputFile, 'wb');
            if ($output === false) {
                if ($isFile) {
                    fclose($input);
                }
                throw new Crypt_GPG_FileException('Could not open output ' .
                    'file "' . $outputFile . '" for storing decrypted data.',
                    0, $outputFile);
            }
        }

        $verifyHandler = new Crypt_GPG_VerifyStatusHandler();

        $decryptHandler = new Crypt_GPG_DecryptStatusHandler($this->engine,
            $this->decryptKeys);

        $this->engine->reset();
        $this->engine->addStatusHandler(array($verifyHandler, 'handle'));
        $this->engine->addStatusHandler(array($decryptHandler, 'handle'));
        $this->engine->setInput($input);
        $this->engine->setOutput($output);
        $this->engine->setOperation('--decrypt');
        $this->engine->run();

        if ($isFile) {
            fclose($input);
        }

        if ($outputFile !== null) {
            fclose($output);
        }

        $return = array(
            'data'       => null,
            'signatures' => $verifyHandler->getSignatures()
        );

        // if there was any problem decrypting the data, the handler will
        // deal with it here.
        try {
            $decryptHandler->throwException();
        } catch (Exception $e) {
            if ($e instanceof Crypt_GPG_KeyNotFoundException) {
                throw new Crypt_GPG_KeyNotFoundException(
                    'Public key required for data verification not in ',
                    'the keyring. Either no suitable private decryption key ' .
                    'is in the keyring or the public key required for data ' .
                    'verification is not in the keyring. Import a suitable ' .
                    'key before trying to decrypt and verify this data.',
                    self::ERROR_KEY_NOT_FOUND, $this->engine->getErrorKeyId());
            }

            if ($e instanceof Crypt_GPG_NoDataException) {
                throw new Crypt_GPG_NoDataException(
                    'Cannot decrypt and verify data. No PGP encrypted data ' .
                    'was found in the provided data.', self::ERROR_NO_DATA);
            }

            throw $e;
        }

        if ($outputFile === null) {
            $return['data'] = $output;
        }

        return $return;
    }

    // }}}
}

// }}}

?>
