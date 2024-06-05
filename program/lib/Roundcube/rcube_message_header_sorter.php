<?php

namespace Roundcube\WIP;

/*
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
 |   E-mail message headers representation                               |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Class for sorting an array of rcube_message_header objects in a predetermined order.
 */
class rcube_message_header_sorter
{
    /** @var array Message UIDs */
    private $uids = [];

    /**
     * Set the predetermined sort order.
     *
     * @param array $index Numerically indexed array of IMAP UIDs
     */
    public function set_index($index)
    {
        $index = array_flip($index);

        $this->uids = $index;
    }

    /**
     * Sort the array of header objects
     *
     * @param array $headers Array of rcube_message_header objects indexed by UID
     */
    public function sort_headers(&$headers)
    {
        uksort($headers, [$this, 'compare_uids']);
    }

    /**
     * Sort method called by uksort()
     *
     * @param int $a Array key (UID)
     * @param int $b Array key (UID)
     */
    public function compare_uids($a, $b)
    {
        // then find each sequence number in my ordered list
        $posa = isset($this->uids[$a]) ? intval($this->uids[$a]) : -1;
        $posb = isset($this->uids[$b]) ? intval($this->uids[$b]) : -1;

        // return the relative position as the comparison value
        return $posa - $posb;
    }
}
