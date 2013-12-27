<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * A class for performing byte-wise string operations
 *
 * GPG I/O streams are managed using bytes rather than characters.
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
 * @copyright 2013 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Crypt_GPG
 */

// {{{ class Crypt_GPG_ByteUtils

/**
 * A class for performing byte-wise string operations
 *
 * GPG I/O streams are managed using bytes rather than characters. This class
 * requires the mbstring extension to be available.
 *
 * @category  Encryption
 * @package   Crypt_GPG
 * @author    Michael Gauthier <mike@silverorange.com>
 * @copyright 2013 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link      http://pear.php.net/package/Crypt_GPG
 * @link      http://php.net/mbstring
 */
class Crypt_GPG_ByteUtils
{
    // {{{ strlen()

    /**
     * Gets the length of a string in bytes
     *
     * This is used for stream-based communication with the GPG subprocess.
     *
     * @param string $string the string for which to get the length.
     *
     * @return integer the length of the string in bytes.
     */
    public static function strlen($string)
    {
        return mb_strlen($string, '8bit');
    }

    // }}}
    // {{{ substr()

    /**
     * Gets the substring of a string in bytes
     *
     * This is used for stream-based communication with the GPG subprocess.
     *
     * @param string  $string the input string.
     * @param integer $start  the starting point at which to get the substring.
     * @param integer $length optional. The length of the substring.
     *
     * @return string the extracted part of the string. Unlike the default PHP
     *                <kbd>substr()</kbd> function, the returned value is
     *                always a string and never false.
     */
    public static function substr($string, $start, $length = null)
    {
        if ($length === null) {
            return mb_substr(
                $string,
                $start,
                self::strlen($string) - $start, '8bit'
            );
        }

        return mb_substr($string, $start, $length, '8bit');
    }

    // }}}
}

// }}}

?>
