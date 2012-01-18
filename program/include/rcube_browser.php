<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_browser.php                                     |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2007-2009, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Class representing the client browser's properties                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

/** 
 * rcube_browser
 * 
 * Provide details about the client's browser based on the User-Agent header
 *
 * @package Core
 */
class rcube_browser
{
    function __construct()
    {
        $HTTP_USER_AGENT = strtolower($_SERVER['HTTP_USER_AGENT']);

        $this->ver = 0;
        $this->win = strpos($HTTP_USER_AGENT, 'win') != false;
        $this->mac = strpos($HTTP_USER_AGENT, 'mac') != false;
        $this->linux = strpos($HTTP_USER_AGENT, 'linux') != false;
        $this->unix  = strpos($HTTP_USER_AGENT, 'unix') != false;

        $this->opera = strpos($HTTP_USER_AGENT, 'opera') !== false;
        $this->ns4 = strpos($HTTP_USER_AGENT, 'mozilla/4') !== false && strpos($HTTP_USER_AGENT, 'msie') === false;
        $this->ns  = ($this->ns4 || strpos($HTTP_USER_AGENT, 'netscape') !== false);
        $this->ie  = !$this->opera && strpos($HTTP_USER_AGENT, 'compatible; msie') !== false;
        $this->mz  = !$this->ie && strpos($HTTP_USER_AGENT, 'mozilla/5') !== false;
        $this->chrome = strpos($HTTP_USER_AGENT, 'chrome') !== false;
        $this->khtml = strpos($HTTP_USER_AGENT, 'khtml') !== false;
        $this->safari = !$this->chrome && ($this->khtml || strpos($HTTP_USER_AGENT, 'safari') !== false);

        if ($this->ns || $this->chrome) {
            $test = preg_match('/(mozilla|chrome)\/([0-9.]+)/', $HTTP_USER_AGENT, $regs);
            $this->ver = $test ? (float)$regs[2] : 0;
        }
        else if ($this->mz) {
            $test = preg_match('/rv:([0-9.]+)/', $HTTP_USER_AGENT, $regs);
            $this->ver = $test ? (float)$regs[1] : 0;
        }
        else if ($this->ie || $this->opera) {
            $test = preg_match('/(msie|opera) ([0-9.]+)/', $HTTP_USER_AGENT, $regs);
            $this->ver = $test ? (float)$regs[2] : 0;
        }

        if (preg_match('/ ([a-z]{2})-([a-z]{2})/', $HTTP_USER_AGENT, $regs))
            $this->lang =  $regs[1];
        else
            $this->lang =  'en';

        $this->dom = ($this->mz || $this->safari || ($this->ie && $this->ver>=5) || ($this->opera && $this->ver>=7));
        $this->pngalpha = $this->mz || $this->safari || ($this->ie && $this->ver>=5.5) ||
            ($this->ie && $this->ver>=5 && $this->mac) || ($this->opera && $this->ver>=7) ? true : false;
        $this->imgdata = !$this->ie;
    }
}

