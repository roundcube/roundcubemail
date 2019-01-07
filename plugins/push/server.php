<?php

/**
 * Push aka Instant Updates
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

if (php_sapi_name() != 'cli') {
    die('Not on the "shell" (php-cli).');
}

define('INSTALL_PATH', __DIR__ . '/../../');

require_once INSTALL_PATH . 'program/include/iniset.php';
require_once INSTALL_PATH . 'plugins/push/lib/service.php';

// Start the service
$PUSH = push_service::get_instance();
