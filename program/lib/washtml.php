<?php
/*                Washtml, a HTML sanityzer.
 *
 * Copyright (c) 2007 Frederic Motte <fmotte@ubixis.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/* Please send me your comments about this code if you have some, thanks, Fred. */

/* OVERVIEW:
 *
 * Wahstml take an untrusted HTML and return a safe html string.
 *
 * SYNOPSIS:
 *
 * washtml::wash($html, $config, $full);
 * It return a sanityzed string of the $html parameter without html and head tags.
 * $html is a string containing the html code to wash.
 * $config is an array containing options:
 *   $config['allow_remote'] is a boolean to allow link to remote images.
 *   $config['blocked_src'] string with image-src to be used for blocked remote images
 *   $config['show_washed'] is a boolean to include washed out attributes as x-washed
 *   $config['cid_map'] is an array where cid urls index urls to replace them.
 *   $config['charset'] is a string containing the charset of the HTML document if it is not defined in it.
 * $full is a reference to a boolean that is set to true if no remote images are removed. (FE: show remote images link)
 *
 * INTERNALS:
 *
 * Only tags and attributes in the globals $html_elements and $html_attributes
 * are kept, inline styles are also filtered: all style identifiers matching
 * /[a-z\-]/i are allowed. Values matching colors, sizes, /[a-z\-]/i and safe
 * urls if allowed and cid urls if mapped are kept.
 *
 * BUGS: It MUST be safe !
 *  - Check regexp
 *  - urlencode URLs instead of htmlspecials
 *  - Check is a 3 bytes utf8 first char can eat '">'
 *  - Update PCRE: CVE-2007-1659 - CVE-2007-1660 - CVE-2007-1661 - CVE-2007-1662 
 *                 CVE-2007-4766 - CVE-2007-4767 - CVE-2007-4768  
 *    http://lists.debian.org/debian-security-announce/debian-security-announce-2007/msg00177.html 
 *  - ...
 *
 * MISSING:
 *  - relative links, can be implemented by prefixing an absolute path, ask me
 *    if you need it...
 *  - ...
 *
 * Dont be a fool:
 *  - Dont alter data on a GET: '<img src="http://yourhost/mail?action=delete&uid=3267" />'
 *  - ...
 */

class washtml
{

  /* Allowed HTML elements */
  static $html_elements = array('a', 'abbr', 'acronym', 'address', 'area', 'b', 'basefont', 'bdo', 'big', 'blockquote', 'br', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'fieldset', 'font', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'ins', 'label', 'legend', 'li', 'map', 'menu', 'ol', 'p', 'pre', 'q', 's', 'samp', 'small', 'span', 'strike', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'u', 'ul', 'var', 'img');

  /* Allowed HTML attributes */
  static $html_attribs = array('name', 'class', 'title', 'alt', 'width', 'height', 'align', 'nowrap', 'col', 'row', 'id', 'rowspan', 'colspan', 'cellspacing', 'cellpadding', 'valign', 'bgcolor', 'color', 'border', 'bordercolorlight', 'bordercolordark', 'face', 'marginwidth', 'marginheight', 'axis', 'border', 'abbr', 'char', 'charoff', 'clear', 'compact', 'coords', 'vspace', 'hspace', 'cellborder', 'size', 'lang', 'dir');

  /* Check CSS style */
  static function wash_style($style, $config, &$full) {
    $s = '';

    foreach(explode(';', $style) as $declaration) {
      if(preg_match('/^\s*([a-z\-]+)\s*:\s*(.*)\s*$/i', $declaration, $match)) {
        $cssid = $match[1];
        $str = $match[2];
        $value = '';
        while(sizeof($str) > 0 &&
          preg_match('/^(url\(\s*[\'"]?([^\'"\)]*)[\'"]?\s*\)'./*1,2*/
                 '|rgb\(\s*[0-9]+\s*,\s*[0-9]+\s*,\s*[0-9]+\s*\)'.
                 '|-?[0-9.]+\s*(em|ex|px|cm|mm|in|pt|pc|deg|rad|grad|ms|s|hz|khz|%)?'.
                 '|#[0-9a-f]{3,6}|[a-z0-9\-]+'.
                 ')\s*/i', $str, $match)) {
          if($match[2]) {
            if(preg_match('/^(http|https|ftp):.*$/i', $match[2], $url)) {
              if($config['allow_remote'])
                $value .= ' url(\''.htmlspecialchars($url[0], ENT_QUOTES).'\')';
              else
                $full = false;
            } else if(preg_match('/^cid:(.*)$/i', $match[2], $cid))
              $value .= ' url(\''.htmlspecialchars($config['cid_map']['cid:'.$cid[1]], ENT_QUOTES) . '\')';
          } else if($match[0] != 'url' && $match[0] != 'rbg')//whitelist ?
            $value .= ' ' . $match[0];
          $str = substr($str, strlen($match[0]));
        }
        if($value)
          $s .= ($s?' ':'') . $cssid . ':' . $value . ';';
      }
    }
    return $s;
  }

  /* Take a node and return allowed attributes and check values */
  static function wash_attribs($node, $config, &$full) {
    $t = '';
    $washed;

    foreach($node->attributes as $key => $plop) {
      $key = strtolower($key);
      $value = $node->getAttribute($key);
      if((in_array($key, self::$html_attribs)) ||
         ($key == 'href' && preg_match('/^(http|https|ftp|mailto):.*/i', $value)))
        $t .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
      else if($key == 'style' && ($style = self::wash_style($value, $config, $full)))
        $t .= ' style="' . $style . '"';
      else if($key == 'src' && strtolower($node->tagName) == 'img') { //check tagName anyway
        if(preg_match('/^(http|https|ftp):.*/i', $value)) {
          if($config['allow_remote'])
            $t .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
          else {
            $full = false;
            if ($config['blocked_src'])
              $t .= ' src="' . htmlspecialchars($config['blocked_src'], ENT_QUOTES) . '"';
          }
        } else if(preg_match('/^cid:(.*)$/i', $value, $cid))
          $t .= ' ' . $key . '="' . htmlspecialchars($config['cid_map']['cid:'.$cid[1]], ENT_QUOTES) . '"';
      } else
        $washed .= ($washed?' ':'') . $key;
    }
    return $t . ($washed && $config['show_washed']?' x-washed="'.$washed.'"':'');
  }

  /* The main loop that recurse on a node tree.
   * It output only allowed tags with allowed attributes
   * and allowed inline styles */
  static function dumpHtml($node, $config, &$full) {
    if(!$node->hasChildNodes())
      return '';

    $node = $node->firstChild;
    $dump = '';

    do {
      switch($node->nodeType) {
      case XML_ELEMENT_NODE: //Check element
        $tagName = strtolower($node->tagName);
        if(in_array($tagName, self::$html_elements)) {
          $content = self::dumpHtml($node, $config, $full);
          $dump .= '<' . $tagName . self::wash_attribs($node, $config, $full) .
            ($content?">$content</$tagName>":' />');
        } else if($tagName == 'html' || $tagName == 'body') {
          $dump .= self::dumpHtml($node, $config, $full); //Just ignored
        } else
          $dump .= '<!-- ' . htmlspecialchars($tagName, ENT_QUOTES) . ' not allowed -->';
        break;
      case XML_TEXT_NODE:
        $dump .= htmlspecialchars($node->nodeValue);
        break;
      case XML_HTML_DOCUMENT_NODE:
        $dump .= self::dumpHtml($node, $config, $full);
        break;
      case XML_DOCUMENT_TYPE_NODE: break;
      default:
      }
    } while($node = $node->nextSibling);

    return $dump;
  }

  /* Main function, give it untrusted HTML, tell it if you allow loading
   * remote images and give it a map to convert "cid:" urls. */
  static function wash($html, $config=array(), &$full=true) {
    $config += array('show_washed'=>true, 'allow_remote'=>false, 'cid_map'=>array());
    //Charset seems to be ignored (probably if defined in the HTML document)
    $node = new DOMDocument('1.0', $config['charset']);
    $full = true;
    @$node->loadHTML($html);
    return self::dumpHtml($node, $config, $full);
  }

}

?>