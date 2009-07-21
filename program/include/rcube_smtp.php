<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_smtp.php                                        |
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

 $Id: rcube_smtp.inc 2754 2009-07-14 16:34:34Z alec $

*/

// define headers delimiter
define('SMTP_MIME_CRLF', "\r\n");

class rcube_smtp {

  private $conn = null;
  private $response;
  private $error;


  /**
   * Object constructor
   *
   * @param 
   */
  function __construct()
  {
  }


  /**
   * SMTP Connection and authentication
   *
   * @return bool  Returns true on success, or false on error
   */
  public function connect()
  {
    $RCMAIL = rcmail::get_instance();
  
    // disconnect/destroy $this->conn
    $this->disconnect();
    
    // reset error/response var
    $this->error = $this->response = null;
  
    // let plugins alter smtp connection config
    $CONFIG = $RCMAIL->plugins->exec_hook('smtp_connect', array(
      'smtp_server' => $RCMAIL->config->get('smtp_server'),
      'smtp_port'   => $RCMAIL->config->get('smtp_port', 25),
      'smtp_user'   => $RCMAIL->config->get('smtp_user'),
      'smtp_pass'   => $RCMAIL->config->get('smtp_pass'),
      'smtp_auth_type' => $RCMAIL->config->get('smtp_auth_type'),
      'smtp_helo_host' => $RCMAIL->config->get('smtp_helo_host'),
      'smtp_timeout'   => $RCMAIL->config->get('smtp_timeout'),
    ));

    $smtp_host = str_replace('%h', $_SESSION['imap_host'], $CONFIG['smtp_server']);
    // when called from Installer it's possible to have empty $smtp_host here
    if (!$smtp_host) $smtp_host = 'localhost';
    $smtp_port = is_numeric($CONFIG['smtp_port']) ? $CONFIG['smtp_port'] : 25;
    $smtp_host_url = parse_url($smtp_host);

    // overwrite port
    if (isset($smtp_host_url['host']) && isset($smtp_host_url['port']))
    {
      $smtp_host = $smtp_host_url['host'];
      $smtp_port = $smtp_host_url['port'];
    }

    // re-write smtp host
    if (isset($smtp_host_url['host']) && isset($smtp_host_url['scheme']))
      $smtp_host = sprintf('%s://%s', $smtp_host_url['scheme'], $smtp_host_url['host']);

    if (!empty($CONFIG['smtp_helo_host']))
      $helo_host = $CONFIG['smtp_helo_host'];
    else if (!empty($_SERVER['SERVER_NAME']))
      $helo_host = preg_replace('/:\d+$/', '', $_SERVER['SERVER_NAME']);
    else
      $helo_host = 'localhost';

    $this->conn = new Net_SMTP($smtp_host, $smtp_port, $helo_host);

    if($RCMAIL->config->get('smtp_debug'))
      $this->conn->setDebug(true, array($this, 'debug_handler'));
    
    // try to connect to server and exit on failure
    $result = $this->conn->connect($smtp_timeout);
    if (PEAR::isError($result))
    {
      $this->response[] = "Connection failed: ".$result->getMessage();
      $this->error = array('label' => 'smtpconnerror', 'vars' => array('code' => $this->conn->_code));
      $this->conn = null;
      return false;
    }

    $smtp_user = str_replace('%u', $_SESSION['username'], $CONFIG['smtp_user']);
    $smtp_pass = str_replace('%p', $RCMAIL->decrypt($_SESSION['password']), $CONFIG['smtp_pass']);
    $smtp_auth_type = empty($CONFIG['smtp_auth_type']) ? NULL : $CONFIG['smtp_auth_type'];
      
    // attempt to authenticate to the SMTP server
    if ($smtp_user && $smtp_pass)
    {
      $result = $this->conn->auth($smtp_user, $smtp_pass, $smtp_auth_type);
      if (PEAR::isError($result))
      {
        $this->error = array('label' => 'smtpautherror', 'vars' => array('code' => $this->conn->_code));
        $this->response[] .= 'Authentication failure: ' . $result->getMessage() . ' (Code: ' . $result->getCode() . ')';
        $this->reset();
	$this->disconnect();
        return false;
      }
    }

    return true;
  }


  /**
   * Function for sending mail
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
   * @return bool  Returns true on success, or false on error
   */
  public function send_mail($from, $recipients, &$headers, &$body)
  {
    if (!is_object($this->conn))
      return false;

    // prepare message headers as string
    if (is_array($headers))
    {
      if (!($headerElements = $this->_prepare_headers($headers))) {
        $this->reset();
        return false;
      }

      list($from, $text_headers) = $headerElements;
    }
    else if (is_string($headers))
      $text_headers = $headers;
    else
    {
      $this->reset();
      $this->response[] .= "Invalid message headers";
      return false;
    }

    // exit if no from address is given
    if (!isset($from))
    {
      $this->reset();
      $this->response[] .= "No From address has been provided";
      return false;
    }

    // set From: address
    if (PEAR::isError($this->conn->mailFrom($from)))
    {
      $this->error = array('label' => 'smtpfromerror', 'vars' => array('from' => $from, 'code' => $this->conn->_code));
      $this->response[] .= "Failed to set sender '$from'";
      $this->reset();
      return false;
    }

    // prepare list of recipients
    $recipients = $this->_parse_rfc822($recipients);
    if (PEAR::isError($recipients))
    {
      $this->error = array('label' => 'smtprecipientserror');
      $this->reset();
      return false;
    }

    // set mail recipients
    foreach ($recipients as $recipient)
    {
      if (PEAR::isError($this->conn->rcptTo($recipient))) {
        $this->error = array('label' => 'smtptoerror', 'vars' => array('to' => $recipient, 'code' => $this->conn->_code));
        $this->response[] .= "Failed to add recipient '$recipient'";
        $this->reset();
        return false;
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
    if (PEAR::isError($result = $this->conn->data($data)))
    {
      $this->error = array('label' => 'smtperror', 'vars' => array('msg' => $result->getMessage()));
      $this->response[] .= "Failed to send data";
      $this->reset();
      return false;
    }

    $this->response[] = join(': ', $this->conn->getResponse());
    return true;
  }


  /**
   * Reset the global SMTP connection
   * @access public
   */
  public function reset()
  {
    if (is_object($this->conn))
      $this->conn->rset();
  }


  /**
   * Disconnect the global SMTP connection
   * @access public
   */
  public function disconnect()
  {
    if (is_object($this->conn)) {
      $this->conn->disconnect();
      $this->conn = null;
    }
  }


  /**
   * This is our own debug handler for the SMTP connection
   * @access public
   */
  public function debug_handler(&$smtp, $message)
  {
    write_log('smtp', preg_replace('/\r\n$/', '', $message));
  }


  /**
   * Get error message
   * @access public
   */
  public function get_error()
  {
    return $this->error;
  }


  /**
   * Get server response messages array
   * @access public
   */
  public function get_response()
  {
    return $this->response;
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
  private function _prepare_headers($headers)
  {
    $lines = array();
    $from = null;

    foreach ($headers as $key => $value)
    {
      if (strcasecmp($key, 'From') === 0)
      {
        $addresses = $this->_parse_rfc822($value);

        if (is_array($addresses))
          $from = $addresses[0];

        // Reject envelope From: addresses with spaces.
        if (strstr($from, ' '))
          return false;

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
  private function _parse_rfc822($recipients)
  {
    // if we're passed an array, assume addresses are valid and implode them before parsing.
    if (is_array($recipients))
      $recipients = implode(', ', $recipients);
    
    $addresses = array();
    $recipients = rcube_explode_quoted_string(',', $recipients);
  
    reset($recipients);
    while (list($k, $recipient) = each($recipients))
    {
      $a = explode(" ", $recipient);
      while (list($k2, $word) = each($a))
      {
        if ((strpos($word, "@") > 0) && (strpos($word, "\"")===false))
        {
          $word = preg_replace('/^<|>$/', '', trim($word));
          if (in_array($word, $addresses)===false)
            array_push($addresses, $word);
        }
      }
    }
    return $addresses;
  }

}

?>
