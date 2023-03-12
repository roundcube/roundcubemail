<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   E-mail/Domain name spoofing detection                               |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <machniak@kolabsys.com>                   |
 +-----------------------------------------------------------------------+
*/

/**
 * Helper class for spoofing detection.
 *
 * @package    Framework
 * @subpackage Utils
 */
class rcube_spoofchecker
{
    /** @var array In-memory cache of checked domains */
    protected static $results = [];

    /**
     * Detects (potential) spoofing in an e-mail address or a domain.
     *
     * @param string $domain Email address or domain (UTF8 not punycode)
     *
     * @return bool True if spoofed/suspicious, False otherwise
     */
    public static function check($domain)
    {
        if (($pos = strrpos($domain, '@')) !== false) {
            $domain = substr($domain, $pos + 1);
        }

        if (isset(self::$results[$domain])) {
            return self::$results[$domain];
        }

        // Spoofchecker is part of ext-intl (requires ICU >= 4.2)
        try {
            $checker = new Spoofchecker();

            // Note: The constant (and method?) added in PHP 7.3.0
            if (defined('Spoofchecker::HIGHLY_RESTRICTIVE')) {
                $checker->setRestrictionLevel(Spoofchecker::HIGHLY_RESTRICTIVE);
            }
            else {
                $checker->setChecks(Spoofchecker::SINGLE_SCRIPT | Spoofchecker::INVISIBLE);
            }

            $result = $checker->isSuspicious($domain);
        }
        catch (Throwable $e) {
            rcube::raise_error($e, true);
            $result = false;
        }

        // TODO: Use areConfusable() to detect ascii-spoofing of some domains, e.g. paypa1.com?
        // TODO: Domains with non-printable characters should be considered spoofed

        return self::$results[$domain] = $result;
    }
}
