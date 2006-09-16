<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/bugs.inc                                              |
 |                                                                       |
 | This file is part of the BQube Webmail client                         |
 | Copyright (C) 2005, BQube Dev - Switzerland                           |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide error handling and logging functions                        |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/


// throw system error and show error page
function raise_error($arg=array(), $log=FALSE, $terminate=FALSE)
  {
  global $__page_content, $CONFIG, $OUTPUT, $ERROR_CODE, $ERROR_MESSAGE;
  
  /* $arg keys:
       int     code
       string  type (php, xpath, db, imap, javascript)
       string  message
       sring   file
       int     line
  */

  // report bug (if not incompatible browser)
  if ($log && $arg['type'] && $arg['message'])
    log_bug($arg);

  // display error page and terminate script
  if ($terminate)
    {
    $ERROR_CODE = $arg['code'];
    $ERROR_MESSAGE = $arg['message'];
    include("program/steps/error.inc");
    exit;
    }
  }


// report error
function log_bug($arg_arr)
  {
  global $CONFIG, $INSTALL_PATH;
  $program = $arg_arr['type']=='xpath' ? 'XPath' : strtoupper($arg_arr['type']);

  // write error to local log file
  if ($CONFIG['debug_level'] & 1)
    {
    $log_entry = sprintf("[%s] %s Error: %s in %s on line %d\n",
                 date("d-M-Y H:i:s O", mktime()),
                 $program,
                 $arg_arr['message'],
                 $arg_arr['file'],
                 $arg_arr['line']);
                 
    if (empty($CONFIG['log_dir']))
      $CONFIG['log_dir'] = $INSTALL_PATH.'logs';
      
    // try to open specific log file for writing
    if ($fp = @fopen($CONFIG['log_dir'].'/errors', 'a'))
    
      {
      fwrite($fp, $log_entry);
      fclose($fp);
      }
    else
      {
      // send error to PHPs error handler
      trigger_error($arg_arr['message']);
      }
    }

/*
  // resport the bug to the global bug reporting system
  if ($CONFIG['debug_level'] & 2)
    {
    $delm = '%AC';
    http_request(sprintf('http://roundcube.net/log/bug.php?_type=%s&_domain=%s&_server_ip=%s&_client_ip=%s&_useragent=%s&_url=%s%%3A//%s&_errors=%s%s%s%s%s',
                 $arg_arr['type'],
            	 $GLOBALS['HTTP_HOST'],
                 $GLOBALS['SERVER_ADDR'],
                 $GLOBALS['REMOTE_ADDR'],
                 rawurlencode($GLOBALS['HTTP_USER_AGENT']),
           		 $GLOBALS['SERVER_PORT']==43 ? 'https' : 'http',
           		 $GLOBALS['HTTP_HOST'].$GLOBALS['REQUEST_URI'],
           		 $arg_arr['file'], $delm,
                 $arg_arr['line'], $delm,
                 rawurlencode($arg_arr['message'])));
    }
*/

  // show error if debug_mode is on
  if ($CONFIG['debug_level'] & 4)
    {
    print "<b>$program Error";

    if (!empty($arg_arr['file']) && !empty($arg_arr['line']))
      print " in $arg_arr[file] ($arg_arr[line])";

    print ":</b>&nbsp;";
    print nl2br($arg_arr['message']);
    print '<br />';
    flush();
    }
  }

?>
