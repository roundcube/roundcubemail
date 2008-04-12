<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_browser.php                                     |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2007-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Class representing the client browser's properties                  |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: rcube_browser.php 328 2006-08-30 17:41:21Z thomasb $

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
        $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];

        $this->ver = 0;
        $this->win = stristr($HTTP_USER_AGENT, 'win');
        $this->mac = stristr($HTTP_USER_AGENT, 'mac');
        $this->linux = stristr($HTTP_USER_AGENT, 'linux');
        $this->unix  = stristr($HTTP_USER_AGENT, 'unix');

        $this->ns4 = stristr($HTTP_USER_AGENT, 'mozilla/4') && !stristr($HTTP_USER_AGENT, 'msie');
        $this->ns  = ($this->ns4 || stristr($HTTP_USER_AGENT, 'netscape'));
        $this->ie  = stristr($HTTP_USER_AGENT, 'msie');
        $this->mz  = stristr($HTTP_USER_AGENT, 'mozilla/5');
        $this->opera = stristr($HTTP_USER_AGENT, 'opera');
        $this->safari = stristr($HTTP_USER_AGENT, 'safari');

        if ($this->ns) {
            $test = eregi("mozilla\/([0-9\.]+)", $HTTP_USER_AGENT, $regs);
            $this->ver = $test ? (float)$regs[1] : 0;
        }
        if ($this->mz) {
            $test = ereg("rv:([0-9\.]+)", $HTTP_USER_AGENT, $regs);
            $this->ver = $test ? (float)$regs[1] : 0;
        }
        if($this->ie) {
            $test = eregi("msie ([0-9\.]+)", $HTTP_USER_AGENT, $regs);
            $this->ver = $test ? (float)$regs[1] : 0;
        }
        if ($this->opera) {
            $test = eregi("opera ([0-9\.]+)", $HTTP_USER_AGENT, $regs);
            $this->ver = $test ? (float)$regs[1] : 0;
        }

        if (eregi(" ([a-z]{2})-([a-z]{2})", $HTTP_USER_AGENT, $regs))
            $this->lang =  $regs[1];
        else
            $this->lang =  'en';

        $this->dom = ($this->mz || $this->safari || ($this->ie && $this->ver>=5) || ($this->opera && $this->ver>=7));
        $this->pngalpha = $this->mz || $this->safari || ($this->ie && $this->ver>=5.5) ||
            ($this->ie && $this->ver>=5 && $this->mac) || ($this->opera && $this->ver>=7) ? true : false;
    }
  }

