<?php

namespace Roundcube\Tests;

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   A class for catching STDERR output                                  |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * A class for catching STDERR output
 */
class StderrMock extends \php_user_filter
{
    public static $registered = false;
    public static $redirect;
    public static $output = '';

    #[\Override]
    #[\ReturnTypeWillChange]
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $consumed += (int) $bucket->datalen;
            // stream_bucket_append($out, $bucket);
            self::$output .= $bucket->data;
        }

        return \PSFS_PASS_ON;
    }

    public static function start()
    {
        if (!self::$registered) {
            stream_filter_register('redirect', self::class) || exit('Failed to register filter');
            self::$registered = true;
        }

        self::$output = '';
        self::$redirect = stream_filter_prepend(\STDERR, 'redirect', \STREAM_FILTER_WRITE);
    }

    public static function stop()
    {
        stream_filter_remove(self::$redirect);
    }
}
