<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Crypt_GPG is a package to use GPG from PHP
 *
 * This file contains an object that handles GPG's error output for the
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
 * Error line handler for the key generation operation
 *
 * This class is used internally by Crypt_GPG and does not need be used
 * directly. See the {@link Crypt_GPG} class for end-user API.
 *
 * @category  Encryption
 * @package   Crypt_GPG
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2011-2013 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link      http://pear.php.net/package/Crypt_GPG
 * @link      http://www.gnupg.org/
 */
class Crypt_GPG_KeyGeneratorErrorHandler
{
    // {{{ protected properties

    /**
     * Error code (if any) caused by key generation
     *
     * @var integer
     */
    protected $errorCode = Crypt_GPG::ERROR_NONE;

    /**
     * Line number at which the error occurred
     *
     * @var integer
     */
    protected $lineNumber = null;

    // }}}
    // {{{ handle()

    /**
     * Handles an error line
     *
     * @param string $line the error line to handle.
     *
     * @return void
     */
    public function handle($line)
    {
        $matches = array();
        $pattern = '/:([0-9]+): invalid algorithm$/';
        if (preg_match($pattern, $line, $matches) === 1) {
            $this->errorCode  = Crypt_GPG::ERROR_BAD_KEY_PARAMS;
            $this->lineNumber = intval($matches[1]);
        }
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
    // {{{ getLineNumber()

    /**
     * Gets the line number at which the error occurred
     *
     * @return integer the line number at which the error occurred. Null if
     *                 no error occurred.
     */
    public function getLineNumber()
    {
        return $this->lineNumber;
    }

    // }}}
}

?>
