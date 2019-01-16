#!/usr/bin/env php
<?php

/**
 * Push plugin helper for Cyrus IMAP
 *
 * @author Aleksander Machniak <alec@alec.pl>
 *
 * Copyright (C) 2010-2019 The Roundcube Dev Team
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
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

///////////////////// CONFIGURATION /////////////////////

$URL   = "http://127.0.0.1:9501";
$TOKEN = "xyz";

/////////////////////////////////////////////////////////

if (php_sapi_name() != 'cli') {
    die('Not on the "shell" (php-cli).');
}

$input = file_get_contents('php://stdin');

// Debug
file_put_contents('/tmp/notify.log', "$input\n", FILE_APPEND);

$curl = curl_init($URL);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $input);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($input),
        'X-Token: ' . $TOKEN
));

curl_exec($curl);
curl_close($curl);
