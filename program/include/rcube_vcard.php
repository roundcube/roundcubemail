<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_vcard.php                                       |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2008, RoundCube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Logical representation of a vcard address record                    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: $

*/


/**
 * Logical representation of a vcard-based address record
 * Provides functions to parse and export vCard data format
 *
 * @package    Addressbook
 * @author     Thomas Bruederli <roundcube@gmail.com>
 */
class rcube_vcard
{
  private $raw = array(
    'FN' => array(),
    'N' => array(array('','','','','')),
  );

  public $business = false;
  public $displayname;
  public $surname;
  public $firstname;
  public $middlename;
  public $nickname;
  public $organization;
  public $notes;
  public $email = array();


  /**
   * Constructor
   */
  public function __construct($vcard = null)
  {
    if (!empty($vcard))
      $this->load($vcard);
  }


  /**
   * Load record from (internal, unfolded) vcard 3.0 format
   *
   * @param string vCard string to parse
   */
  public function load($vcard)
  {
    $this->raw = self::vcard_decode($vcard);

    // find well-known address fields
    $this->displayname = $this->raw['FN'][0];
    $this->surname = $this->raw['N'][0][0];
    $this->firstname = $this->raw['N'][0][1];
    $this->middlename = $this->raw['N'][0][2];
    $this->nickname = $this->raw['NICKNAME'][0];
    $this->organization = $this->raw['ORG'][0];
    $this->business = ($this->raw['X-ABShowAs'][0] == 'COMPANY') || (join('', (array)$this->raw['N'][0]) == '' && !empty($this->organization));
    
    foreach ((array)$this->raw['EMAIL'] as $i => $raw_email)
      $this->email[$i] = is_array($raw_email) ? $raw_email[0] : $raw_email;
    
    // make the pref e-mail address the first entry in $this->email
    $pref_index = $this->get_type_index('EMAIL', 'pref');
    if ($pref_index > 0) {
      $tmp = $this->email[0];
      $this->email[0] = $this->email[$pref_index];
      $this->email[$pref_index] = $tmp;
    }
  }


  /**
   * Convert the data structure into a vcard 3.0 string
   */
  public function export()
  {
    return self::rfc2425_fold(self::vcard_encode($this->raw));
  }


  /**
   * Setter for address record fields
   *
   * @param string Field name
   * @param string Field value
   * @param string Section name
   */
  public function set($field, $value, $section = 'HOME')
  {
    switch ($field) {
      case 'name':
      case 'displayname':
        $this->raw['FN'][0] = $value;
        break;
        
      case 'firstname':
        $this->raw['N'][0][1] = $value;
        break;
        
      case 'surname':
        $this->raw['N'][0][0] = $value;
        break;
      
      case 'nickname':
        $this->raw['NICKNAME'][0] = $value;
        break;
        
      case 'organization':
        $this->raw['ORG'][0] = $value;
        break;
        
      case 'email':
        $index = $this->get_type_index('EMAIL', $section);
        if (!is_array($this->raw['EMAIL'][$index])) {
          $this->raw['EMAIL'][$index] = array(0 => $value, 'type' => array('INTERNET', $section, 'pref'));
        }
        else {
          $this->raw['EMAIL'][$index][0] = $value;
        }
        break;
    }
  }


  /**
   * Find index with the '$type' attribute
   *
   * @param string Field name
   * @return int Field index having $type set
   */
  private function get_type_index($field, $type = 'pref')
  {
    $result = 0;
    if ($this->raw[$field]) {
      foreach ($this->raw[$field] as $i => $data) {
        if (is_array($data['type']) && in_array_nocase('pref', $data['type']))
          $result = $i;
      }
    }
    
    return $result;
  }


  /**
   * Factory method to import a vcard file
   *
   * @param string vCard file content
   * @return array List of rcube_vcard objects
   */
  public static function import($data)
  {
    $out = array();

    // detect charset and convert to utf-8
    $encoding = self::detect_encoding($data);
    if ($encoding && $encoding != RCMAIL_CHARSET) {
      $data = rcube_charset_convert($data, $encoding);
    }

    $vcard_block = '';
    $in_vcard_block = false;

    foreach (preg_split("/[\r\n]+/", $data) as $i => $line) {
      if ($in_vcard_block && !empty($line))
        $vcard_block .= $line . "\n";

      if (trim($line) == 'END:VCARD') {
        // parse vcard
        $obj = new rcube_vcard(self::cleanup($vcard_block));
        if (!empty($obj->displayname))
          $out[] = $obj;

        $in_vcard_block = false;
      }
      else if (trim($line) == 'BEGIN:VCARD') {
        $vcard_block = $line . "\n";
        $in_vcard_block = true;
      }
    }

    return $out;
  }


  /**
   * Normalize vcard data for better parsing
   *
   * @param string vCard block
   * @return string Cleaned vcard block
   */
  private static function cleanup($vcard)
  {
    // Convert special types (like Skype) to normal type='skype' classes with this simple regex ;)
    $vcard = preg_replace(
      '/item(\d+)\.(TEL|URL)([^:]*?):(.*?)item\1.X-ABLabel:(?:_\$!<)?([\w-() ]*)(?:>!\$_)?./s',
      '\2;type=\5\3:\4',
      $vcard);

    // Remove cruft like item1.X-AB*, item1.ADR instead of ADR, and empty lines
    $vcard = preg_replace(array('/^item\d*\.X-AB.*$/m', '/^item\d*\./m', "/\n+/"), array('', '', "\n"), $vcard);

    // remove vcard 2.1 charset definitions
    $vcard = preg_replace('/;CHARSET=[^:;]+/', '', $vcard);
    
    // if N doesn't have any semicolons, add some 
    $vcard = preg_replace('/^(N:[^;\R]*)$/m', '\1;;;;', $vcard);

    return $vcard;
  }


  private static function rfc2425_fold($val)
  {
    return preg_replace('/:([^\n]{72,})/e', '":\n  ".rtrim(chunk_split("\\1", 72, "\n  "))', $val) . "\n";
  }


  /**
   * Decodes a vcard block (vcard 3.0 format, unfolded)
   * into an array structure
   *
   * @param string vCard block to parse
   * @return array Raw data structure
   */
  private static function vcard_decode($vcard)
  {
    // Perform RFC2425 line unfolding
    $vcard = preg_replace(array("/\r/", "/\n\s+/"), '', $vcard);
    
    $lines = preg_split('/\r?\n/', $vcard);
    $data = array();
    
    for ($i=0; $i < count($lines); $i++) {
      if (!preg_match('/^([^\\:]*):(.+)$/', $lines[$i], $line))
          continue;

      // convert 2.1-style "EMAIL;internet;home:" to 3.0-style "EMAIL;TYPE=internet;TYPE=home:"
      if (($data['VERSION'][0] == "2.1") && preg_match('/^([^;]+);([^:]+)/', $line[1], $regs2) && !preg_match('/^TYPE=/i', $regs2[2])) {
        $line[1] = $regs2[1];
        foreach (explode(';', $regs2[2]) as $prop)
          $line[1] .= ';' . (strpos($prop, '=') ? $prop : 'TYPE='.$prop);
      }

      if (!preg_match('/^(BEGIN|END)$/', $line[1]) && preg_match_all('/([^\\;]+);?/', $line[1], $regs2)) {
        $entry = array('');
        $field = $regs2[1][0];

        foreach($regs2[1] as $attrid => $attr) {
          if ((list($key, $value) = explode('=', $attr)) && $value) {
            if ($key == 'ENCODING') {
              # add next line(s) to value string if QP line end detected                                                                                                               
              while ($value == 'QUOTED-PRINTABLE' && ereg('=$', $lines[$i]))
                  $line[2] .= "\n" . $lines[++$i];
              
              $line[2] = self::decode_value($line[2], $value);
            }
            else
              $entry[strtolower($key)] = array_merge((array)$entry[strtolower($key)], (array)self::vcard_unquote($value, ','));
          }
          else if ($attrid > 0) {
            $entry[$key] = true;  # true means attr without =value
          }
        }

        $entry[0] = self::vcard_unquote($line[2]);
        $data[$field][] = count($entry) > 1 ? $entry : $entry[0];
      }
    }

    unset($data['VERSION']);

    return $data;
  }


  /**
   * Split quoted string
   *
   * @param string vCard string to split
   * @param string Separator char/string
   * @return array List with splitted values
   */
  private static function vcard_unquote($s, $sep = ';')
  {
    // break string into parts separated by $sep, but leave escaped $sep alone
    if (count($parts = explode($sep, strtr($s, array("\\$sep" => "\007")))) > 1) {
      foreach($parts as $s) {
        $result[] = self::vcard_unquote(strtr($s, array("\007" => "\\$sep")), $sep);
      }
      return $result;
    }
    else {
      return strtr($s, array("\r" => '', '\\\\' => '\\', '\n' => "\n", '\,' => ',', '\;' => ';', '\:' => ':'));
    }
  }


  /**
   * Decode a given string with the encoding rule from ENCODING attributes
   *
   * @param string String to decode
   * @param string Encoding type (quoted-printable and base64 supported)
   * @return string Decoded 8bit value
   */
  private static function decode_value($value, $encoding)
  {
    switch (strtolower($encoding)) {
      case 'quoted-printable':
        return quoted_printable_decode($value);

      case 'base64':
        return base64_decode($value);

      default:
        return $value;
    }
  }


  /**
   * Encodes an entry for storage in our database (vcard 3.0 format, unfolded)
   *
   * @param array Raw data structure to encode
   * @return string vCard encoded string
   */
  static function vcard_encode($data)
  {
    foreach((array)$data as $type => $entries) {
      /* valid N has 5 properties */
      while ($type == "N" && is_array($entries[0]) && count($entries[0]) < 5)
        $entries[0][] = "";

      foreach((array)$entries as $entry) {
        $attr = '';
        if (is_array($entry)) {
          $value = array();
          foreach($entry as $attrname => $attrvalues) {
            if (is_int($attrname))
              $value[] = $attrvalues;
            elseif ($attrvalues === true)
              $attr .= ";$attrname";    # true means just tag, not tag=value, as in PHOTO;BASE64:...
            else {
              foreach((array)$attrvalues as $attrvalue)
                $attr .= ";$attrname=" . self::vcard_quote($attrvalue, ',');
            }
          }
        }
        else {
          $value = $entry;
        }

        $vcard .= self::vcard_quote($type) . $attr . ':' . self::vcard_quote($value) . "\n";
      }
    }

    return "BEGIN:VCARD\nVERSION:3.0\n{$vcard}END:VCARD";
  }


  /**
   * Join indexed data array to a vcard quoted string
   *
   * @param array Field data
   * @param string Separator
   * @return string Joined and quoted string
   */
  private static function vcard_quote($s, $sep = ';')
  {
    if (is_array($s)) {
      foreach($s as $part) {
        $r[] = self::vcard_quote($part, $sep);
      }
      return(implode($sep, (array)$r));
    }
    else {
      return strtr($s, array('\\' => '\\\\', "\r" => '', "\n" => '\n', ';' => '\;', ':' => '\:'));
    }
  }


  /**
   * Returns UNICODE type based on BOM (Byte Order Mark)
   *
   * @param string Input string to test
   * @return string Detected encoding
   */
  private static function detect_encoding($string)
  {
    if (substr($string, 0, 4) == "\0\0\xFE\xFF") return 'UTF-32BE';  // Big Endian
    if (substr($string, 0, 4) == "\xFF\xFE\0\0") return 'UTF-32LE';  // Little Endian
    if (substr($string, 0, 2) == "\xFE\xFF")     return 'UTF-16BE';  // Big Endian
    if (substr($string, 0, 2) == "\xFF\xFE")     return 'UTF-16LE';  // Little Endian
    if (substr($string, 0, 3) == "\xEF\xBB\xBF") return 'UTF-8';

    if ($enc = rc_detect_encoding($string))
      return $enc;

    // No match, check for UTF-8
    // from http://w3.org/International/questions/qa-forms-utf-8.html
    if (preg_match('/\A(
        [\x09\x0A\x0D\x20-\x7E]
        | [\xC2-\xDF][\x80-\xBF]
        | \xE0[\xA0-\xBF][\x80-\xBF]
        | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}
        | \xED[\x80-\x9F][\x80-\xBF]
        | \xF0[\x90-\xBF][\x80-\xBF]{2}
        | [\xF1-\xF3][\x80-\xBF]{3}
        | \xF4[\x80-\x8F][\x80-\xBF]{2}
        )*\z/xs', substr($string, 0, 2048)))
      return 'UTF-8';

    return 'ISO-8859-1'; # fallback to Latin-1
  }

}


