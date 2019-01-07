<?php

/**
 * Push plugin
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

/**
 * Indexed shared memory storage for cross-process cache
 */
class push_cache extends swoole_table
{
    protected $index;

    function __construct($size)
    {
        parent::__construct($size);
        $this->column('data', swoole_table::TYPE_STRING, 2048);
        $this->create();

        $this->index = new swoole_table($size);
        $this->index->column('user', swoole_table::TYPE_STRING, 256);
        $this->index->create();
    }

    public function get($key, $field = null)
    {
        $result = parent::get($key);
        if ($result) {
            $result = json_decode($result['data'], true);

            if ($field) {
                $result = $result[$field];
            }
        }

        return $result;
    }

    public function set($key, $value)
    {
        return parent::set($key, array('data' => json_encode($value)));
    }

    public function add_client($client_id, $username, $metadata = array())
    {
        $this->index->set($client_id, array('user' => $username));

        $data = $this->get($username) ?: array('sockets' => array());
        $data = array_merge($data, $metadata);
        $data['sockets'][] = $client_id;
        $this->set($username, $data);
    }

    public function del_client($client_id)
    {
        $username = $this->index->get($client_id, 'user');
        $data     = $this->get($username) ?: array();

        if (($key = array_search($client_id, (array) $data['sockets'])) !== false) {
            unset($data['sockets'][$key]);
            $data['sockets'] = array_unique($data['sockets']);
        }

        $this->set($username, $data);

        return $this->index->del($client_id);
    }
}
