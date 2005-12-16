<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_smtp.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev. - Switzerland                      |
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
 * @access public
 */
function smtp_mail($from, $recipients, $headers, &$body)
  {
  global $SMTP_CONN, $CONFIG, $SMTP_ERROR;
  $smtp_timeout = null;
  $smtp_host = $CONFIG['smtp_server'];
  $smtp_port = is_numeric($CONFIG['smtp_port']) ? $CONFIG['smtp_port'] : 25;
  $smtp_host_url = parse_url($CONFIG['smtp_server']);
  
  // overwrite port
  if ($smtp_host_url['host'] && $smtp_host_url['port'])
    {
    $smtp_host = $smtp_host_url['host'];
    $smtp_port = $smtp_host_url['port'];
    }

  // re-write smtp host
  if ($smtp_host_url['host'] && $smtp_host_url['scheme'])
    $smtp_host = sprintf('%s://%s', $smtp_host_url['scheme'], $smtp_host_url['host']);


  // create Net_SMTP object and connect to server
  if (!is_object($smtp_conn))
    {
    $helo_host = !empty($_SERVER['server_name']) ? $_SERVER['server_name'] : 'localhost';
    $SMTP_CONN = new Net_SMTP($smtp_host, $smtp_port, $helo_host);

    // set debugging
    if ($CONFIG['debug_level'] & 8)
      $SMTP_CONN->setDebug(TRUE);


    // try to connect to server and exit on failure
    if (PEAR::isError($SMTP_CONN->connect($smtp_timeout)))
      {
      $SMTP_CONN = null;
      $SMTP_ERROR .= "Connection failed\n";
      return FALSE;
      }
      
    // attempt to authenticate to the SMTP server
    if ($CONFIG['smtp_user'] && $CONFIG['smtp_pass'])
      {
      if ($CONFIG['smtp_user'] == '%u')
		$smtp_user = $_SESSION['username'];
      else
		$smtp_user = $CONFIG['smtp_user'];
	
	  if ($CONFIG['smtp_pass'] == '%p')
		$smtp_pass = decrypt_passwd($_SESSION['password']);
      else
		$smtp_pass = $CONFIG['smtp_pass'];

	  $smtp_auth_type = empty($CONFIG['smtp_auth_type']) ? NULL : $CONFIG['smtp_auth_type'];
	  	
      if (PEAR::isError($SMTP_CONN->auth($smtp_user, $smtp_pass, $smtp_auth_type)))
        {
        smtp_reset();
        $SMTP_ERROR .= "authentication failure\n";
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
    $SMTP_ERROR .= "Invalid message headers\n";
    return FALSE;
    }

  // exit if no from address is given
  if (!isset($from))
    {
    smtp_reset();
    $SMTP_ERROR .= "No From address has been provided\n";
    return FALSE;
    }


  // set From: address
  if (PEAR::isError($SMTP_CONN->mailFrom($from)))
    {
    smtp_reset();
    $SMTP_ERROR .= "Failed to set sender '$from'\n";
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
      $SMTP_ERROR .= "Failed to add recipient '$recipient'\n";
      return FALSE;
      }
    }


  // Send the message's headers and the body as SMTP data.
  if (PEAR::isError($SMTP_CONN->data("$text_headers\r\n$body")))
    {
    smtp_reset();
    $SMTP_ERROR .= "Failed to send data\n";
    return FALSE;
    }


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
