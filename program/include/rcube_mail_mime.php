<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_mail_mime.php                                   |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2007-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Extend PEAR:Mail_mime class and override encodeHeaders method       |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: sendmail.inc 506 2007-03-14 00:39:51Z thomasb $

*/


/**
 * Replacement PEAR:Mail_mime with some additional or overloaded methods
 *
 * @package Mail
 */
class rcube_mail_mime extends Mail_mime
{

  protected $mime_content;

  /**
   * Set build parameters
   */
  function setParam($param)
  {
    if (is_array($param)) {
      $this->_build_params = array_merge($this->_build_params, $param);
    }
  }
  
  /**
   * Adds an image to the list of embedded images.
   *
   * @param  string  $file       The image file name OR image data itself
   * @param  string  $c_type     The content type
   * @param  string  $name       The filename of the image.
   *                             Only use if $file is the image data
   * @param  bool    $isfilename Whether $file is a filename or not
   *                             Defaults to true
   * @param  string  $contentid  Desired Content-ID of MIME part
   *                             Defaults to generated unique ID
   * @return mixed   true on success or PEAR_Error object
   * @access public
   */
  function addHTMLImage($file, $c_type='application/octet-stream', $name = '', $isfilename = true, $contentid = '')
  {
    $filedata = ($isfilename === true) ? $this->_file2str($file) : $file;
    if ($isfilename === true) {
      $filename = ($name == '' ? $file : $name);
    }
    else {
      $filename = $name;
    }

    if (PEAR::isError($filedata)) {
        return $filedata;
    }

    if ($contentid == '') {
       $contentid = md5(uniqid(time()));
    }

    $this->_html_images[] = array(
      'body'   => $filedata,
      'name'   => $filename,
      'c_type' => $c_type,
      'cid'    => $contentid
    );

    return true;
  }
  
  
  /**
  * returns the HTML body portion of the message
  * @return string HTML body of the message
  * @access public
  */
  function getHTMLBody()
  {
     return $this->_htmlbody;
  }
  
  
  /**
   * Creates a new mimePart object, using multipart/mixed as
   * the initial content-type and returns it during the
   * build process.
   *
   * @return object  The multipart/mixed mimePart object
   * @access private
   */
  function &_addMixedPart()
  {
    $params['content_type'] = $this->_headers['Content-Type'] ? $this->_headers['Content-Type'] : 'multipart/mixed';
    $ret = new Mail_mimePart('', $params);
    return $ret;
  }
  
  
  /**
   * Encodes a header as per RFC2047
   *
   * @param  array $input The header data to encode
   * @param  array $params Extra build parameters
   * @return array Encoded data
   * @access private
   * @override
   */
  function _encodeHeaders($input, $params = array())
  {
    $maxlen = 73;
    $params += $this->_build_params;
    
    foreach ($input as $hdr_name => $hdr_value)
    {
      // if header contains e-mail addresses
      if (preg_match('/\s<.+@[a-z0-9\-\.]+\.[a-z]+>/U', $hdr_value)) {
        $chunks = $this->_explode_quoted_string(',', $hdr_value);
      }
      else {
        $chunks = array($hdr_value);
      }

      $hdr_value = '';
      $line_len = 0;

      foreach ($chunks as $i => $value) {
        $value = trim($value);

        //This header contains non ASCII chars and should be encoded.
        if (preg_match('#[\x00-\x1F\x80-\xFF]{1}#', $value)) {
          $suffix = '';
          // Don't encode e-mail address
          if (preg_match('/(.+)\s(<.+@[a-z0-9\-\.]+>)$/Ui', $value, $matches)) {
            $value = $matches[1];
            $suffix = ' '.$matches[2];
          }

          switch ($params['head_encoding']) {
            case 'base64':
            // Base64 encoding has been selected.
            $mode = 'B';
            $encoded = base64_encode($value);
            break;

            case 'quoted-printable':
            default:
            // quoted-printable encoding has been selected
            $mode = 'Q';
            $encoded = preg_replace('/([\x3F\x00-\x1F\x80-\xFF])/e', "'='.sprintf('%02X', ord('\\1'))", $value);
            // replace spaces with _
            $encoded = str_replace(' ', '_', $encoded);
          }

          $value = '=?' . $params['head_charset'] . '?' . $mode . '?' . $encoded . '?=' . $suffix;
        }

        // add chunk to output string by regarding the header maxlen
        $len = strlen($value);
        if ($i == 0 || $line_len + $len < $maxlen) {
          $hdr_value .= ($i>0?', ':'') . $value;
          $line_len += $len + ($i>0?2:0);
        }
        else {
          $hdr_value .= ($i>0?', ':'') . "\n " . $value;
          $line_len = $len;
        }
      }

      $input[$hdr_name] = $hdr_value;
    }

    return $input;
  }


  function _explode_quoted_string($delimiter, $string)
  {
    $result = array();
    $strlen = strlen($string);
    for ($q=$p=$i=0; $i < $strlen; $i++) {
      if ($string{$i} == "\"" && $string{$i-1} != "\\") {
        $q = $q ? false : true;
      }
      else if (!$q && $string{$i} == $delimiter) {
        $result[] = substr($string, $p, $i - $p);
        $p = $i + 1;
      }
    }
    
    $result[] = substr($string, $p);
    return $result;
  }
  
  /**
   * Provides caching of body of constructed MIME Message to avoid 
   * duplicate construction of message and damage of MIME headers
   *
   * @return string The mime content
   * @access public
   * @override
   */
  public function &get($build_params = null)
  {
    if(empty($this->mime_content))
      $this->mime_content = parent::get($build_params);
    return $this->mime_content;
  }

}

