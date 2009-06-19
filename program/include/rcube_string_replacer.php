<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_string_replacer.php                             |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2009, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Handle string replacements based on preg_replace_callback           |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id:  $

*/


/**
 * Helper class for string replacements based on preg_replace_callback
 *
 * @package Core
 */
class rcube_string_replacer
{
  public static $pattern = '/##str_replacement\[([0-9]+)\]##/';
  public $mailto_pattern;
  public $link_pattern;
  private $values = array();


  function __construct()
  {
    $url_chars = 'a-z0-9_\-\+\*\$\/&%=@#:;';
    $url_chars_within = '\?\.~,!';

    $this->link_pattern = "/([\w]+:\/\/|\Wwww\.)([a-z0-9\-\.]+[a-z]{2,4}([$url_chars$url_chars_within]*[$url_chars])?)/i";
    $this->mailto_pattern = "/([a-z0-9][a-z0-9\-\.\+\_]*@[a-z0-9]([a-z0-9\-][.]?)*[a-z0-9]\\.[a-z]{2,5})/i";
  }

  /**
   * Add a string to the internal list
   *
   * @param string String value 
   * @return int Index of value for retrieval
   */
  public function add($str)
  {
    $i = count($this->values);
    $this->values[$i] = $str;
    return $i;
  }

  /**
   * Build replacement string
   */
  public function get_replacement($i)
  {
    return '##str_replacement['.$i.']##';
  }

  /**
   * Callback function used to build HTML links around URL strings
   *
   * @param array Matches result from preg_replace_callback
   * @return int Index of saved string value
   */
  public function link_callback($matches)
  {
    $i = -1;
    $scheme = strtolower($matches[1]);

    if (preg_match('!^(http|ftp|file)s?://!', $scheme)) {
      $url = $matches[1] . $matches[2];
      $i = $this->add(html::a(array('href' => $url, 'target' => '_blank'), Q($url)));
    }
    else if (preg_match('/^(\W)www\.$/', $matches[1], $m)) {
      $url = 'www.' . $matches[2];
      $i = $this->add($m[1] . html::a(array('href' => 'http://' . $url, 'target' => '_blank'), Q($url)));
    }

    // Return valid link for recognized schemes, otherwise, return the unmodified string for unrecognized schemes.
    return $i >= 0 ? $this->get_replacement($i) : $matches[0];
  }

  /**
   * Callback function used to build mailto: links around e-mail strings
   *
   * @param array Matches result from preg_replace_callback
   * @return int Index of saved string value
   */
  public function mailto_callback($matches)
  {
    $i = $this->add(html::a(array(
        'href' => 'mailto:' . $matches[1],
        'onclick' => "return ".JS_OBJECT_NAME.".command('compose','".JQ($matches[1])."',this)",
      ),
      Q($matches[1])));

    return $i >= 0 ? $this->get_replacement($i) : '';
  }

  /**
   * Look up the index from the preg_replace matches array
   * and return the substitution value.
   *
   * @param array Matches result from preg_replace_callback
   * @return string Value at index $matches[1]
   */
  public function replace_callback($matches)
  {
    return $this->values[$matches[1]];
  }

  /**
   * Replace substituted strings with original values
   */
  public function resolve($str)
  {
    return preg_replace_callback(self::$pattern, array($this, 'replace_callback'), $str);
  }

}