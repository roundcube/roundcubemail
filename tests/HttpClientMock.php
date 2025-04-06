<?php

namespace Roundcube\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

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
 |   An exception thrown by output classes instead of the `exit` call    |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * A helper to mock Roundcube HTTP client
 */
class HttpClientMock
{
    public static function setResponses(array $responses)
    {
        foreach ($responses as $idx => $response) {
            if ($response instanceof Response) {
                $responses[$idx] = $response;
            } elseif (is_array($response)) {
                $responses[$idx] = new Response(
                    $response[0] ?? 200,
                    $response[1] ?? [],
                    $response[2] ?? ''
                );
            }
        }

        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);
        $rcube = \rcube::get_instance();

        $rcube->config->set('http_client', ['handler' => $handler]);
    }
}
