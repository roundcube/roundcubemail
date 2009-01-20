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
 * $washer = new washtml($config);
 * $washer->wash($html);
 * It return a sanityzed string of the $html parameter without html and head tags.
 * $html is a string containing the html code to wash.
 * $config is an array containing options:
 *   $config['allow_remote'] is a boolean to allow link to remote images.
 *   $config['blocked_src'] string with image-src to be used for blocked remote images
 *   $config['show_washed'] is a boolean to include washed out attributes as x-washed
 *   $config['cid_map'] is an array where cid urls index urls to replace them.
 *   $config['charset'] is a string containing the charset of the HTML document if it is not defined in it.
 * $washer->extlinks is a reference to a boolean that is set to true if remote images were removed. (FE: show remote images link)
 *
 * INTERNALS:
 *
 * Only tags and attributes in the static lists $html_elements and $html_attributes
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
  /* Allowed HTML elements (default) */
  static $html_elements = array('a', 'abbr', 'acronym', 'address', 'area', 'b', 'basefont', 'bdo', 'big', 'blockquote', 'br', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'fieldset', 'font', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'ins', 'label', 'legend', 'li', 'map', 'menu', 'nobr', 'ol', 'p', 'pre', 'q', 's', 'samp', 'small', 'span', 'strike', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'tt', 'u', 'ul', 'var', 'img');
  
  /* Ignore these HTML tags but process their content */
  static $ignore_elements = array('html', 'head', 'body');
  
  /* Allowed HTML attributes */
  static $html_attribs = array('name', 'class', 'title', 'alt', 'width', 'height', 'align', 'nowrap', 'col', 'row', 'id', 'rowspan', 'colspan', 'cellspacing', 'cellpadding', 'valign', 'bgcolor', 'color', 'border', 'bordercolorlight', 'bordercolordark', 'face', 'marginwidth', 'marginheight', 'axis', 'border', 'abbr', 'char', 'charoff', 'clear', 'compact', 'coords', 'vspace', 'hspace', 'cellborder', 'size', 'lang', 'dir');  
  
  /* State for linked objects in HTML */
  public $extlinks = false;

  /* Current settings */
  private $config = array();

  /* Registered callback functions for tags */
  private $handlers = array();
  
  /* Allowed HTML elements */
  private $_html_elements = array();

  /* Ignore these HTML tags but process their content */
  private $_ignore_elements = array();

  /* Allowed HTML attributes */
  private $_html_attribs = array();
  

  /* Constructor */
  public function __construct($p = array()) {
    $this->_html_elements = array_flip((array)$p['html_elements']) + array_flip(self::$html_elements) ;
    $this->_html_attribs = array_flip((array)$p['html_attribs']) + array_flip(self::$html_attribs);
    $this->_ignore_elements = array_flip((array)$p['ignore_elements']) + array_flip(self::$ignore_elements);
    unset($p['html_elements'], $p['html_attribs'], $p['ignore_elements']);
    $this->config = $p + array('show_washed'=>true, 'allow_remote'=>false, 'cid_map'=>array());
  }
  
  /* Register a callback function for a certain tag */
  public function add_callback($tagName, $callback)
  {
    $this->handlers[$tagName] = $callback;
  }
  
  /* Check CSS style */
  private function wash_style($style) {
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
            if($src = $this->config['cid_map'][$match[2]])
              $value .= ' url(\''.htmlspecialchars($src, ENT_QUOTES) . '\')';
            else if(preg_match('/^(http|https|ftp):.*$/i', $match[2], $url)) {
              if($this->config['allow_remote'])
                $value .= ' url(\''.htmlspecialchars($url[0], ENT_QUOTES).'\')';
              else
                $this->extlinks = true;
            }
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
  private function wash_attribs($node) {
    $t = '';
    $washed;

    foreach($node->attributes as $key => $plop) {
      $key = strtolower($key);
      $value = $node->getAttribute($key);
      if(isset($this->_html_attribs[$key]) ||
         ($key == 'href' && preg_match('/^(http|https|ftp|mailto):.+/i', $value)))
        $t .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
      else if($key == 'style' && ($style = $this->wash_style($value)))
        $t .= ' style="' . $style . '"';
      else if($key == 'background' || ($key == 'src' && strtolower($node->tagName) == 'img')) { //check tagName anyway
        if($src = $this->config['cid_map'][$value]) {
          $t .= ' ' . $key . '="' . htmlspecialchars($src, ENT_QUOTES) . '"';
        }
        else if(preg_match('/^(http|https|ftp):.+/i', $value)) {
          if($this->config['allow_remote'])
            $t .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
          else {
            $this->extlinks = true;
            if ($this->config['blocked_src'])
              $t .= ' ' . $key . '="' . htmlspecialchars($this->config['blocked_src'], ENT_QUOTES) . '"';
          }
        }
      } else
        $washed .= ($washed?' ':'') . $key;
    }
    return $t . ($washed && $this->config['show_washed']?' x-washed="'.$washed.'"':'');
  }

  /* The main loop that recurse on a node tree.
   * It output only allowed tags with allowed attributes
   * and allowed inline styles */
  private function dumpHtml($node) {
    if(!$node->hasChildNodes())
      return '';

    $node = $node->firstChild;
    $dump = '';

    do {
      switch($node->nodeType) {
      case XML_ELEMENT_NODE: //Check element
        $tagName = strtolower($node->tagName);
        if($callback = $this->handlers[$tagName]) {
          $dump .= call_user_func($callback, $tagName, $this->wash_attribs($node), $this->dumpHtml($node));
        } else if(isset($this->_html_elements[$tagName])) {
          $content = $this->dumpHtml($node);
          $dump .= '<' . $tagName . $this->wash_attribs($node) .
            ($content?">$content</$tagName>":' />');
        } else if(isset($this->_ignore_elements[$tagName])) {
          $dump .= '<!-- ' . htmlspecialchars($tagName, ENT_QUOTES) . ' ignored -->';
          $dump .= $this->dumpHtml($node); //Just ignored
        } else
          $dump .= '<!-- ' . htmlspecialchars($tagName, ENT_QUOTES) . ' not allowed -->';
        break;
      case XML_CDATA_SECTION_NODE:
        $dump .= $node->nodeValue;
        break;
      case XML_TEXT_NODE:
        $dump .= htmlspecialchars($node->nodeValue);
        break;
      case XML_HTML_DOCUMENT_NODE:
        $dump .= $this->dumpHtml($node);
        break;
      case XML_DOCUMENT_TYPE_NODE:
        break;
      default:
        $dump . '<!-- node type ' . $node->nodeType . ' -->';
      }
    } while($node = $node->nextSibling);

    return $dump;
  }

  /* Main function, give it untrusted HTML, tell it if you allow loading
   * remote images and give it a map to convert "cid:" urls. */
  public function wash($html) {
    //Charset seems to be ignored (probably if defined in the HTML document)
    $node = new DOMDocument('1.0', $this->config['charset']);
    $this->extlinks = false;
    @$node->loadHTML($html);
    return $this->dumpHtml($node);
  }

}

?>