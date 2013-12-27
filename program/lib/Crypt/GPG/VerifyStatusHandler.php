<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Crypt_GPG is a package to use GPG from PHP
 *
 * This file contains an object that handles GPG's status output for the verify
 * operation.
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
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Crypt_GPG
 * @link      http://www.gnupg.org/
 */

/**
 * Signature object class definition
 */
require_once 'Crypt/GPG/Signature.php';

/**
 * Status line handler for the verify operation
 *
 * This class is used internally by Crypt_GPG and does not need be used
 * directly. See the {@link Crypt_GPG} class for end-user API.
 *
 * This class is responsible for building signature objects that are returned
 * by the {@link Crypt_GPG::verify()} method. See <b>doc/DETAILS</b> in the
 * {@link http://www.gnupg.org/download/ GPG distribution} for detailed
 * information on GPG's status output for the verify operation.
 *
 * @category  Encryption
 * @package   Crypt_GPG
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2008 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link      http://pear.php.net/package/Crypt_GPG
 * @link      http://www.gnupg.org/
 */
class Crypt_GPG_VerifyStatusHandler
{
    // {{{ protected properties

    /**
     * The current signature id
     *
     * Ths signature id is emitted by GPG before the new signature line so we
     * must remember it temporarily.
     *
     * @var string
     */
    protected $signatureId = '';

    /**
     * List of parsed {@link Crypt_GPG_Signature} objects
     *
     * @var array
     */
    protected $signatures = array();

    /**
     * Array index of the current signature
     *
     * @var integer
     */
    protected $index = -1;

    // }}}
    // {{{ handle()

    /**
     * Handles a status line
     *
     * @param string $line the status line to handle.
     *
     * @return void
     */
    public function handle($line)
    {
        $tokens = explode(' ', $line);
        switch ($tokens[0]) {
        case 'GOODSIG':
        case 'EXPSIG':
        case 'EXPKEYSIG':
        case 'REVKEYSIG':
        case 'BADSIG':
            $signature = new Crypt_GPG_Signature();

            // if there was a signature id, set it on the new signature
            if ($this->signatureId != '') {
                $signature->setId($this->signatureId);
                $this->signatureId = '';
            }

            // Detect whether fingerprint or key id was returned and set
            // signature values appropriately. Key ids are strings of either
            // 16 or 8 hexadecimal characters. Fingerprints are strings of 40
            // hexadecimal characters. The key id is the last 16 characters of
            // the key fingerprint.
            if (strlen($tokens[1]) > 16) {
                $signature->setKeyFingerprint($tokens[1]);
                $signature->setKeyId(substr($tokens[1], -16));
            } else {
                $signature->setKeyId($tokens[1]);
            }

            // get user id string
            $string = implode(' ', array_splice($tokens, 2));
            $string = rawurldecode($string);

            $signature->setUserId(Crypt_GPG_UserId::parse($string));

            $this->index++;
            $this->signatures[$this->index] = $signature;
            break;

        case 'ERRSIG':
            $signature = new Crypt_GPG_Signature();

            // if there was a signature id, set it on the new signature
            if ($this->signatureId != '') {
                $signature->setId($this->signatureId);
                $this->signatureId = '';
            }

            // Detect whether fingerprint or key id was returned and set
            // signature values appropriately. Key ids are strings of either
            // 16 or 8 hexadecimal characters. Fingerprints are strings of 40
            // hexadecimal characters. The key id is the last 16 characters of
            // the key fingerprint.
            if (strlen($tokens[1]) > 16) {
                $signature->setKeyFingerprint($tokens[1]);
                $signature->setKeyId(substr($tokens[1], -16));
            } else {
                $signature->setKeyId($tokens[1]);
            }

            $this->index++;
            $this->signatures[$this->index] = $signature;

            break;

        case 'VALIDSIG':
            if (!array_key_exists($this->index, $this->signatures)) {
                break;
            }

            $signature = $this->signatures[$this->index];

            $signature->setValid(true);
            $signature->setKeyFingerprint($tokens[1]);

            if (strpos($tokens[3], 'T') === false) {
                $signature->setCreationDate($tokens[3]);
            } else {
                $signature->setCreationDate(strtotime($tokens[3]));
            }

            if (array_key_exists(4, $tokens)) {
                if (strpos($tokens[4], 'T') === false) {
                    $signature->setExpirationDate($tokens[4]);
                } else {
                    $signature->setExpirationDate(strtotime($tokens[4]));
                }
            }

            break;

        case 'SIG_ID':
            // note: signature id comes before new signature line and may not
            // exist for some signature types
            $this->signatureId = $tokens[1];
            break;
        }
    }

    // }}}
    // {{{ getSignatures()

    /**
     * Gets the {@link Crypt_GPG_Signature} objects parsed by this handler
     *
     * @return array the signature objects parsed by this handler.
     */
    public function getSignatures()
    {
        return $this->signatures;
    }

    // }}}
}

?>
