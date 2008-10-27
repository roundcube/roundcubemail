<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_smtp.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide SMTP functionality using socket connections                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

/**
 * SMTP delivery functions
 *
 * @package Mail
 */

// include required PEAR classes
require_once('Net/SMTP.php');


// define headers delimiter
define('SMTP_MIME_CRLF', "\r\n");

$SMTP_CONN = null;

/**
 * Function for sending mail using SMTP.
 *
 * @param string Sender e-Mail address
 *
 * @param mixed  Either a comma-seperated list of recipients
 *               (RFC822 compliant), or an array of recipients,
 *               each RFC822 valid. This may contain recipients not
 *               specified in the headers, for Bcc:, resending
 *               messages, etc.
 *
 * @param mixed  The message headers to send with the mail
 *               Either as an associative array or a finally
 *               formatted string
 *
 * @param string The full text of the message body, including any Mime parts, etc.
 *
 * @return bool  Returns TRUE on success, or FALSE on error
 */
function smtp_mail($from, $recipients, &$headers, &$body, &$response)
  {
  global $SMTP_CONN, $CONFIG, $RCMAIL;
  $smtp_timeout = null;
  $smtp_host = $CONFIG['smtp_server'];
  $smtp_port = is_numeric($CONFIG['smtp_port']) ? $CONFIG['smtp_port'] : 25;
  $smtp_host_url = parse_url($CONFIG['smtp_server']);
  
  // overwrite port
  if (isset($smtp_host_url['host']) && isset($smtp_host_url['port']))
    {
    $smtp_host = $smtp_host_url['host'];
    $smtp_port = $smtp_host_url['port'];
    }

  // re-write smtp host
  if (isset($smtp_host_url['host']) && isset($smtp_host_url['scheme']))
    $smtp_host = sprintf('%s://%s', $smtp_host_url['scheme'], $smtp_host_url['host']);


  // create Net_SMTP object and connect to server
  if (!is_object($SMTP_CONN))
    {
    if (!empty($CONFIG['smtp_helo_host']))
      $helo_host = $CONFIG['smtp_helo_host'];
    else if (!empty($_SERVER['SERVER_NAME']))
      $helo_host = preg_replace('/:\d+$/', '', $_SERVER['SERVER_NAME']);
    else
      $helo_host = 'localhost';

    $SMTP_CONN = new Net_SMTP($smtp_host, $smtp_port, $helo_host);

    // set debugging
    if ($CONFIG['debug_level'] & 8)
      $SMTP_CONN->setDebug(TRUE);

    // try to connect to server and exit on failure
    $result = $SMTP_CONN->connect($smtp_timeout);
    if (PEAR::isError($result))
      {
      $SMTP_CONN = null;
      $response[] = "Connection failed: ".$result->getMessage();
      return FALSE;
      }
      
    // attempt to authenticate to the SMTP server
    if ($CONFIG['smtp_user'] && $CONFIG['smtp_pass'])
      {
      if (strstr($CONFIG['smtp_user'], '%u'))
        $smtp_user = str_replace('%u', $_SESSION['username'], $CONFIG['smtp_user']);
      else
        $smtp_user = $CONFIG['smtp_user'];

      if (strstr($CONFIG['smtp_pass'], '%p'))
        $smtp_pass = str_replace('%p', $RCMAIL->decrypt_passwd($_SESSION['password']), $CONFIG['smtp_pass']);
      else
        $smtp_pass = $CONFIG['smtp_pass'];

      $smtp_auth_type = empty($CONFIG['smtp_auth_type']) ? NULL : $CONFIG['smtp_auth_type'];
      $result = $SMTP_CONN->auth($smtp_user, $smtp_pass, $smtp_auth_type);
    
      if (PEAR::isError($result))
        {
        smtp_reset();
        $response[] .= 'Authentication failure: ' . $result->getMessage() . ' (Code: ' . $result->getCode() . ')';
        return FALSE;
        }
      }
    }


  // prepare message headers as string
  if (is_array($headers))
    {
    $headerElements = smtp_prepare_headers($headers);
    if (!$headerElements)
      {
      smtp_reset();
      return FALSE;
      }

    list($from, $text_headers) = $headerElements;
    }
  else if (is_string($headers))
    $text_headers = $headers;
  else
    {
    smtp_reset();
    $response[] .= "Invalid message headers";
    return FALSE;
    }

  // exit if no from address is given
  if (!isset($from))
    {
    smtp_reset();
    $response[] .= "No From address has been provided";
    return FALSE;
    }


  // set From: address
  if (PEAR::isError($SMTP_CONN->mailFrom($from)))
    {
    smtp_reset();
    $response[] .= "Failed to set sender '$from'";
    return FALSE;
    }


  // prepare list of recipients
  $recipients = smtp_parse_rfc822($recipients);
  if (PEAR::isError($recipients))
    {
    smtp_reset();
    return FALSE;
    }


  // set mail recipients
  foreach ($recipients as $recipient)
    {
    if (PEAR::isError($SMTP_CONN->rcptTo($recipient)))
      {
      smtp_reset();
      $response[] .= "Failed to add recipient '$recipient'";
      return FALSE;
      }
    }


  // Concatenate headers and body so it can be passed by reference to SMTP_CONN->data
  // so preg_replace in SMTP_CONN->quotedata will store a reference instead of a copy. 
  // We are still forced to make another copy here for a couple ticks so we don't really 
  // get to save a copy in the method call.
  $data = $text_headers . "\r\n" . $body;

  // unset old vars to save data and so we can pass into SMTP_CONN->data by reference.
  unset($text_headers, $body);
   
  // Send the message's headers and the body as SMTP data.
  if (PEAR::isError($SMTP_CONN->data($data)))
    {
    smtp_reset();
    $response[] .= "Failed to send data";
    return FALSE;
    }

  $response[] = join(': ', $SMTP_CONN->getResponse());
  return TRUE;
  }



/**
 * Reset the global SMTP connection
 * @access public
 */
function smtp_reset()
  {
  global $SMTP_CONN;

  if (is_object($SMTP_CONN))
    {
    $SMTP_CONN->rset();
    smtp_disconnect();
    }
  }



/**
 * Disconnect the global SMTP connection and destroy object
 * @access public
 */
function smtp_disconnect()
  {
  global $SMTP_CONN;

  if (is_object($SMTP_CONN))
    {
    $SMTP_CONN->disconnect();
    $SMTP_CONN = null;
    }
  }


/**
 * Take an array of mail headers and return a string containing
 * text usable in sending a message.
 *
 * @param array $headers The array of headers to prepare, in an associative
 *              array, where the array key is the header name (ie,
 *              'Subject'), and the array value is the header
 *              value (ie, 'test'). The header produced from those
 *              values would be 'Subject: test'.
 *
 * @return mixed Returns false if it encounters a bad address,
 *               otherwise returns an array containing two
 *               elements: Any From: address found in the headers,
 *               and the plain text version of the headers.
 * @access private
 */
function smtp_prepare_headers($headers)
  {
  $lines = array();
  $from = null;

  foreach ($headers as $key => $value)
    {
    if (strcasecmp($key, 'From') === 0)
      {
      $addresses = smtp_parse_rfc822($value);

      if (is_array($addresses))
        $from = $addresses[0];

      // Reject envelope From: addresses with spaces.
      if (strstr($from, ' '))
        return FALSE;


      $lines[] = $key . ': ' . $value;
      }
    else if (strcasecmp($key, 'Received') === 0)
      {
      $received = array();
      if (is_array($value))
        {
        foreach ($value as $line)
          $received[] = $key . ': ' . $line;
        }
      else
        {
        $received[] = $key . ': ' . $value;
        }

      // Put Received: headers at the top.  Spam detectors often
      // flag messages with Received: headers after the Subject:
      // as spam.
      $lines = array_merge($received, $lines);
      }

    else
      {
      // If $value is an array (i.e., a list of addresses), convert
      // it to a comma-delimited string of its elements (addresses).
      if (is_array($value))
        $value = implode(', ', $value);

      $lines[] = $key . ': ' . $value;
      }
    }

  return array($from, join(SMTP_MIME_CRLF, $lines) . SMTP_MIME_CRLF);
  }



/**
 * Take a set of recipients and parse them, returning an array of
 * bare addresses (forward paths) that can be passed to sendmail
 * or an smtp server with the rcpt to: command.
 *
 * @param mixed Either a comma-seperated list of recipients
 *              (RFC822 compliant), or an array of recipients,
 *              each RFC822 valid.
 *
 * @return array An array of forward paths (bare addresses).
 * @access private
 */
function smtp_parse_rfc822($recipients)
  {
  // if we're passed an array, assume addresses are valid and implode them before parsing.
  if (is_array($recipients))
    $recipients = implode(', ', $recipients);
    
  $addresses = array();
  $recipients = smtp_explode_quoted_str(",", $recipients);
  
  reset($recipients);
  while (list($k, $recipient) = each($recipients))
    {
  $a = explode(" ", $recipient);
  while (list($k2, $word) = each($a))
    {
      if ((strpos($word, "@") > 0) && (strpos($word, "\"")===false))
        {
        $word = ereg_replace('^<|>$', '', trim($word));
        if (in_array($word, $addresses)===false)
          array_push($addresses, $word);
        }
      }
    }
  return $addresses;
  }


/**
 * @access private
 */
function smtp_explode_quoted_str($delimiter, $string)
  {
  $quotes=explode("\"", $string);
  while ( list($key, $val) = each($quotes))
    if (($key % 2) == 1) 
      $quotes[$key] = str_replace($delimiter, "_!@!_", $quotes[$key]);
    $string=implode("\"", $quotes);
  
    $result=explode($delimiter, $string);
    while (list($key, $val) = each($result))
      $result[$key] = str_replace("_!@!_", $delimiter, $result[$key]);

  return $result;
  } 


?>
