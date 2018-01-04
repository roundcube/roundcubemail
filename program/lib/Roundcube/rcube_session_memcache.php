<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2014, The Roundcube Dev Team                       |
 | Copyright (C) 2011, Kolab Systems AG                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide database supported session management                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 | Author: Cor Bosman <cor@roundcu.bet>                                  |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to provide memcache session storage
 *
 * @package    Framework
 * @subpackage Core
 * @author     Thomas Bruederli <roundcube@gmail.com>
 * @author     Aleksander Machniak <alec@alec.pl>
 * @author     Cor Bosman <cor@roundcu.be>
 */
class rcube_session_memcache extends rcube_session_php
{

    /**
     * rcube_session_memcache constructor.
     *
     * @param rcube_config $config
     */
    public function __construct($config)
    {
        parent::__construct($config);

        if (!class_exists('Memcached')) {
            rcube::raise_error(
                array(
                    'code' => 604,
                    'type' => 'session',
                    'line' => __LINE__,
                    'file' => __FILE__,
                    'message' => 'Please enable memcached extension for php'
                ), true, true
            );
        }

        $hosts = $config->get('memcache_hosts', array('localhost:11211'));

        if ($hosts === array()) {
            rcube::raise_error(
                array(
                    'code' => 604,
                    'type' => 'session',
                    'line' => __LINE__,
                    'file' => __FILE__,
                    'message' => 'Please specify at least one memcache instance'
                ), true, true
            );
        }

        ini_set('session.save_handler', 'memcached');
        ini_set('session.save_path', implode(',', $hosts));
    }

}
