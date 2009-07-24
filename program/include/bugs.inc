<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/bugs.inc                                              |
 |                                                                       |
 | This file is part of the RoudCube Webmail client                      |
 | Copyright (C) 2005-2009, RoudCube Dev - Switzerland                   |
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
  $program = strtoupper($arg_arr['type']);

  // write error to local log file
  if ($CONFIG['debug_level'] & 1)
  {
    $post_query = ($_SERVER['REQUEST_METHOD'] == 'POST' ? '?_task='.urlencode($_POST['_task']).'&_action='.urlencode($_POST['_action']) : '');
    $log_entry = sprintf("%s Error: %s%s (%s %s)\n",
      $program,
      $arg_arr['message'],
      $arg_arr['file'] ? sprintf(' in %s on line %d', $arg_arr['file'], $arg_arr['line']) : '',
      $_SERVER['REQUEST_METHOD'],
      $_SERVER['REQUEST_URI'] . $post_query);
    
    if (!write_log('errors', $log_entry))
    {
      // send error to PHPs error handler if write_log didn't succeed
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
