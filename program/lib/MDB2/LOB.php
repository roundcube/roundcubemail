<?php
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2004 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith                                         |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Lukas Smith <smith@pooteeweet.org>                           |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * @package  MDB2
 * @category Database
 * @author   Lukas Smith <smith@pooteeweet.org>
 */

require_once 'MDB2.php';

class MDB2_LOB
{
    var $db_index;
    var $lob_index;
    var $lob;

    function stream_open($path, $mode, $options, &$opened_path)
    {
        if (!preg_match('/^rb?\+?$/', $mode)) {
            return false;
        }
        $url = parse_url($path);
        if (!array_key_exists('host', $url) && !array_key_exists('user', $url)) {
            return false;
        }
        $this->db_index = $url['host'];
        if (!isset($GLOBALS['_MDB2_databases'][$this->db_index])) {
            return false;
        }
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $this->lob_index = $url['user'];
        if (!isset($db->datatype->lobs[$this->lob_index])) {
            return false;
        }
        $this->lob =& $db->datatype->lobs[$this->lob_index];
        $db->datatype->_retrieveLOB($this->lob);
        return true;
    }

    function stream_read($count)
    {
        if (isset($GLOBALS['_MDB2_databases'][$this->db_index])) {
            $db =& $GLOBALS['_MDB2_databases'][$this->db_index];

            $data = $db->datatype->_readLOB($this->lob, $count);
            $length = strlen($data);
            if ($length == 0) {
                $this->lob['endOfLOB'] = true;
            }
            $this->lob['position'] += $length;
            return $data;
        }
   }

    function stream_write($data)
    {
        return 0;
    }

    function stream_tell()
    {
        return $this->lob['position'];
    }

    function stream_eof()
    {
        if (!isset($GLOBALS['_MDB2_databases'][$this->db_index])) {
            return true;
        }
        $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
        $result = $db->datatype->_endOfLOB($this->lob);
        if (version_compare(phpversion(), "5.0", ">=")
            && version_compare(phpversion(), "5.1", "<")
        ) {
            return !$result;
        }
        return $result;
    }

    function stream_seek($offset, $whence)
    {
        return false;
    }

    function stream_close()
    {
        if (isset($GLOBALS['_MDB2_databases'][$this->db_index])) {
            $db =& $GLOBALS['_MDB2_databases'][$this->db_index];
            if (isset($db->datatype->lobs[$this->lob_index])) {
                $db->datatype->_destroyLOB($this->lob_index);
                unset($db->datatype->lobs[$this->lob_index]);
            }
        }
    }
}

if (!stream_wrapper_register("MDB2LOB", "MDB2_LOB")) {
    MDB2::raiseError();
    return false;
}

?>