<?php

namespace Roundcube\WIP;

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
 |   Bounce/resend an email message                                      |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Stream filter to remove message headers from the streamed
 * message source (and store them in a variable)
 */
class rcmail_bounce_stream_filter extends \php_user_filter
{
    public static $headers;

    protected $in_body = false;

    #[\Override]
    public function onCreate(): bool
    {
        self::$headers = '';

        return true;
    }

    #[\Override]
    #[\ReturnTypeWillChange]
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            if (!$this->in_body) {
                self::$headers .= $bucket->data;
                if (($pos = strpos(self::$headers, "\r\n\r\n")) === false) {
                    continue;
                }

                $bucket->data = substr(self::$headers, $pos + 4);
                $bucket->datalen = strlen($bucket->data);

                self::$headers = substr(self::$headers, 0, $pos);
                $this->in_body = true;
            }

            $consumed += (int) $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return \PSFS_PASS_ON;
    }
}
