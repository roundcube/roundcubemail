<?php

/*
 *  Copyright (C) 2000 Edmund Grimley Evans <edmundo@rano.org>
 * 
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  Translated from C to PHP by Thomas Bruederli <roundcube@gmail.com>
 */ 


/**
 * Convert the data ($str) from RFC 2060's UTF-7 to UTF-8.
 * If input data is invalid, return the original input string.
 * RFC 2060 obviously intends the encoding to be unique (see
 * point 5 in section 5.1.3), so we reject any non-canonical
 * form, such as &ACY- (instead of &-) or &AMA-&AMA- (instead
 * of &AMAAwA-).
 */
function utf7_to_utf8($str)
{
  $Index_64 = array(
      -1,-1,-1,-1, -1,-1,-1,-1, -1,-1,-1,-1, -1,-1,-1,-1,
      -1,-1,-1,-1, -1,-1,-1,-1, -1,-1,-1,-1, -1,-1,-1,-1,
      -1,-1,-1,-1, -1,-1,-1,-1, -1,-1,-1,62, 63,-1,-1,-1,
      52,53,54,55, 56,57,58,59, 60,61,-1,-1, -1,-1,-1,-1,
      -1, 0, 1, 2,  3, 4, 5, 6,  7, 8, 9,10, 11,12,13,14,
      15,16,17,18, 19,20,21,22, 23,24,25,-1, -1,-1,-1,-1,
      -1,26,27,28, 29,30,31,32, 33,34,35,36, 37,38,39,40,
      41,42,43,44, 45,46,47,48, 49,50,51,-1, -1,-1,-1,-1
  );

  $u7len = strlen($str);
  $p = $err = '';

  for ($i=0; $u7len > 0; $i++, $u7len--)
  {
    $u7 = $str{$i};
    if ($u7 == '&')
    {
      $i++;
      $u7len--;
      $u7 = $str{$i};
      
      if ($u7len && $u7 == '-')
      {
        $p .= '&';
        continue;
      }

      $ch = 0;
      $k = 10;
      for (; $u7len > 0; $i++, $u7len--)
      {
        $u7 = $str{$i};

        if ((ord($u7) & 0x80) || ($b = $Index_64[ord($u7)]) == -1)
          break;

        if ($k > 0)
        {
          $ch |= $b << $k;
          $k -= 6;
        }
        else
        {
          $ch |= $b >> (-$k);
          if ($ch < 0x80)
          {
            /* Printable US-ASCII */
            if (0x20 <= $ch && $ch < 0x7f)
              return $err;
           $p .= chr($ch);
          }
          else if ($ch < 0x800)
          {
            $p .= chr(0xc0 | ($ch >> 6));
            $p .= chr(0x80 | ($ch & 0x3f));
          }
          else
          {
            $p .= chr(0xe0 | ($ch >> 12));
            $p .= chr(0x80 | (($ch >> 6) & 0x3f));
            $p .= chr(0x80 | ($ch & 0x3f));
          }

          $ch = ($b << (16 + $k)) & 0xffff;
          $k += 10;
        }
      }

      /* Non-zero or too many extra bits */
      if ($ch || $k < 6)
        return $err;
        
      /* BASE64 not properly terminated */
      if (!$u7len || $u7 != '-')
        return $err;
        
      /* Adjacent BASE64 sections */
      if ($u7len > 2 && $str{$i+1} == '&' && $str{$i+2} != '-')
        return $err;
    }
    /* Not printable US-ASCII */
    else if (ord($u7) < 0x20 || ord($u7) >= 0x7f)
      return $err;
    else
      $p .= $u7;
  }

  return $p;
}


/**
 * Convert the data ($str) from UTF-8 to RFC 2060's UTF-7.
 * Unicode characters above U+FFFF are replaced by U+FFFE.
 * If input data is invalid, return an empty string.
 */
function utf8_to_utf7($str)
{
  $B64Chars = array(
    'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
    'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd',
    'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
    't', 'u', 'v', 'w', 'x', 'y', 'z', '0', '1', '2', '3', '4', '5', '6', '7',
    '8', '9', '+', ','
  );

  $u8len = strlen($str);
  $base64 = $i = 0;
  $p = $err = '';

  while ($u8len)
  {
    $u8 = $str{$i};
    $c = ord($u8);
    
    if ($c < 0x80)
    {
      $ch = $c;
      $n = 0;
    }
    else if ($c < 0xc2)
      return $err;
    else if ($c < 0xe0)
    {
      $ch = $c & 0x1f;
      $n = 1;
    }
    else if ($c < 0xf0)
    {
      $ch = $c & 0x0f;
      $n = 2;
    }
    else if ($c < 0xf8)
    {
      $ch = $c & 0x07;
      $n = 3;
    }
    else if ($c < 0xfc)
    {
      $ch = $c & 0x03;
      $n = 4;
    }
    else if ($c < 0xfe)
    {
      $ch = $c & 0x01;
      $n = 5;
    }
    else
      return $err;

    $i++;
    $u8len--;

    if ($n > $u8len)
      return $err;

    for ($j=0; $j < $n; $j++)
    {
      $o = ord($str{$i+$j});
      if (($o & 0xc0) != 0x80)
        return $err;
      $ch = ($ch << 6) | ($o & 0x3f);
    }
    
    if ($n > 1 && !($ch >> ($n * 5 + 1)))
      return $err;
    
    $i += $n;
    $u8len -= $n;

    if ($ch < 0x20 || $ch >= 0x7f)
    {
      if (!$base64)
      {
        $p .= '&';
        $base64 = 1;
        $b = 0;
        $k = 10;
      }
      if ($ch & ~0xffff)
        $ch = 0xfffe;
      
      $p .= $B64Chars[($b | $ch >> $k)];
      $k -= 6;
      for (; $k >= 0; $k -= 6)
        $p .= $B64Chars[(($ch >> $k) & 0x3f)];

      $b = ($ch << (-$k)) & 0x3f;
      $k += 16;
    }
    else
    {
      if ($base64)
      {
        if ($k > 10)
          $p .= $B64Chars[$b];
        $p .= '-';
        $base64 = 0;
      }
      
      $p .= chr($ch);
      if (chr($ch) == '&')
        $p .= '-';
    }
  }

  if ($base64)
  {
    if ($k > 10)
      $p .= $B64Chars[$b];
    $p .= '-';
  }

  return $p;
}

?>
