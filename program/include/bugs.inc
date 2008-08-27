<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/bugs.inc                                              |
 |                                                                       |
 | This file is part of the RoudCube Webmail client                      |
 | Copyright (C) 2005-2007, RoudCube Dev - Switzerland                   |
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


/**
 * Error handling and logging functions
 *
 * @package Core
 */


/**
 * Throw system error and show error page
 *
 * @param array Named parameters
 *  - code: Error code (required)
 *  - type: Error type [php|db|imap|javascript] (required)
 *  - message: Error message
 *  - file: File where error occured
 *  - line: Line where error occured
 * @param boolean True to log the error
 * @param boolean Terminate script execution
 */
function raise_error($arg=array(), $log=false, $terminate=false)
  {
  global $__page_content, $CONFIG, $OUTPUT, $ERROR_CODE, $ERROR_MESSAGE;
  
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


/**
 * Report error according to configured debug_level
 *
 * @param array Named parameters
 * @see raise_error()
 */
function log_bug($arg_arr)
{
  global $CONFIG;
  $program = $arg_arr['type']=='xpath' ? 'XPath' : strtoupper($arg_arr['type']);

  // write error to local log file
  if ($CONFIG['debug_level'] & 1)
  {
    $log_entry = sprintf(
      "[%s] %s Error: %s in %s on line %d\n",
      date("d-M-Y H:i:s O", mktime()),
      $program,
      $arg_arr['message'],
      $arg_arr['file'],
      $arg_arr['line']);
                 
    if (empty($CONFIG['log_dir']))
      $CONFIG['log_dir'] = INSTALL_PATH.'logs';
      
    // try to open specific log file for writing
    if ($CONFIG['log_driver'] == 'syslog')
    {
      syslog(LOG_ERR, $log_entry);
    }
    else if ($fp = @fopen($CONFIG['log_dir'].'/errors', 'a'))
    {
      // log_driver == 'file' is the default, assumed here.
      fwrite($fp, $log_entry);
      fclose($fp);
    }
    else
    {
      // send error to PHPs error handler
      trigger_error($arg_arr['message']);
    }
  }

  // resport the bug to the global bug reporting system
  if ($CONFIG['debug_level'] & 2)
  {
    // TODO: Send error via HTTP
  }

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
