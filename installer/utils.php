<?php
/*
 +-------------------------------------------------------------------------+
 | RoundCube Webmail IMAP Client                                           |
 | Version 0.3-20090702                                                    |
 |                                                                         |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                   |
 |                                                                         |
 | This program is free software; you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License version 2          |
 | as published by the Free Software Foundation.                           |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License along |
 | with this program; if not, write to the Free Software Foundation, Inc., |
 | 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.             |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                          |
 +-------------------------------------------------------------------------+

 $Id: index.php 2696 2009-07-02 06:38:26Z thomasb $

*/

/**
 * Use PHP5 autoload for dynamic class loading
 * (copy from program/include/iniset.php)
 */
function __autoload($classname)
{
  $filename = preg_replace(
      array('/MDB2_(.+)/', '/Mail_(.+)/', '/Net_(.+)/', '/^html_.+/', '/^utf8$/'),
      array('MDB2/\\1', 'Mail/\\1', 'Net/\\1', 'html', 'utf8.class'),
      $classname
  );
  include_once $filename. '.php';
}


/**
 * Fake internal error handler to catch errors
 */
function raise_error($p)
{
  $rci = rcube_install::get_instance();
  $rci->raise_error($p);
}
