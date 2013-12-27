<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Crypt_GPG is a package to use GPG from PHP
 *
 * This file contains an object that handles GPG's status output for the
 * key generation operation.
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
 * @copyright 2011-2013 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   CVS: $Id:$
 * @link      http://pear.php.net/package/Crypt_GPG
 * @link      http://www.gnupg.org/
 */

/**
 * Status line handler for the key generation operation
 *
 * This class is used internally by Crypt_GPG and does not need be used
 * directly. See the {@link Crypt_GPG} class for end-user API.
 *
 * This class is responsible for parsing the final key fingerprint from the
 * status output and for updating the key generation progress file. See
 * <b>doc/DETAILS</b> in the
 * {@link http://www.gnupg.org/download/ GPG distribution} for detailed
 * information on GPG's status output for the batch key generation operation.
 *
 * @category  Encryption
 * @package   Crypt_GPG
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2011-2013 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link      http://pear.php.net/package/Crypt_GPG
 * @link      http://www.gnupg.org/
 */
class Crypt_GPG_KeyGeneratorStatusHandler
{
    // {{{ protected properties

    /**
     * The key fingerprint
     *
     * Ths key fingerprint is emitted by GPG after the key generation is
     * complete.
     *
     * @var string
     */
    protected $keyFingerprint = '';

    /**
     * The unique key handle used by this handler
     *
     * The key handle is used to track GPG status output for a particular key
     * before the key has its own identifier.
     *
     * @var string
     *
     * @see Crypt_GPG_KeyGeneratorStatusHandler::setHandle()
     */
    protected $handle = '';

    /**
     * Error code (if any) caused by key generation
     *
     * @var integer
     */
    protected $errorCode = Crypt_GPG::ERROR_NONE;

    // }}}
    // {{{ setHandle()

    /**
     * Sets the unique key handle used by this handler
     *
     * The key handle is used to track GPG status output for a particular key
     * before the key has its own identifier.
     *
     * @param string $handle the key handle this status handle will use.
     *
     * @return Crypt_GPG_KeyGeneratorStatusHandler the current object, for
     *                                             fluent interface.
     */
    public function setHandle($handle)
    {
        $this->handle = strval($handle);
        return $this;
    }

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
        case 'KEY_CREATED':
            if ($tokens[3] == $this->handle) {
                $this->keyFingerprint = $tokens[2];
            }
            break;

        case 'KEY_NOT_CREATED':
            if ($tokens[1] == $this->handle) {
                $this->errorCode = Crypt_GPG::ERROR_KEY_NOT_CREATED;
            }
            break;

        case 'PROGRESS':
            // todo: at some point, support reporting status async
            break;
        }
    }

    // }}}
    // {{{ getKeyFingerprint()

    /**
     * Gets the key fingerprint parsed by this handler
     *
     * @return array the key fingerprint parsed by this handler.
     */
    public function getKeyFingerprint()
    {
        return $this->keyFingerprint;
    }

    // }}}
    // {{{ getErrorCode()

    /**
     * Gets the error code resulting from key gneration
     *
     * @return integer the error code resulting from key generation.
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    // }}}
}

?>
