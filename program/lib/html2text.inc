<?php

/*************************************************************************
*                                                                       *
* class.html2text.inc                                                   *
*                                                                       *
*************************************************************************
*                                                                       *
* Converts HTML to formatted plain text                                 *
*                                                                       *
* Copyright (c) 2005 Jon Abernathy <jon@chuggnutt.com>                  *
* All rights reserved.                                                  *
*                                                                       *
* This script is free software; you can redistribute it and/or modify   *
* it under the terms of the GNU General Public License as published by  *
* the Free Software Foundation; either version 2 of the License, or     *
* (at your option) any later version.                                   *
*                                                                       *
* The GNU General Public License can be found at                        *
* http://www.gnu.org/copyleft/gpl.html.                                 *
*                                                                       *
* This script is distributed in the hope that it will be useful,        *
* but WITHOUT ANY WARRANTY; without even the implied warranty of        *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the          *
* GNU General Public License for more details.                          *
*                                                                       *
* Author(s): Jon Abernathy <jon@chuggnutt.com>                          *
*                                                                       *
* Last modified: 04/06/05                                               *
* Modified: 2004/05/19 (tbr)                                            *
*                                                                       *
*************************************************************************/


/**
*  Takes HTML and converts it to formatted, plain text.
*
*  Thanks to Alexander Krug (http://www.krugar.de/) to pointing out and
*  correcting an error in the regexp search array. Fixed 7/30/03.
*
*  Updated set_html() function's file reading mechanism, 9/25/03.
*
*  Thanks to Joss Sanglier (http://www.dancingbear.co.uk/) for adding
*  several more HTML entity codes to the $search and $replace arrays.
*  Updated 11/7/03.
*
*  Thanks to Darius Kasperavicius (http://www.dar.dar.lt/) for
*  suggesting the addition of $allowed_tags and its supporting function
*  (which I slightly modified). Updated 3/12/04.
*
*  Thanks to Justin Dearing for pointing out that a replacement for the
*  <TH> tag was missing, and suggesting an appropriate fix.
*  Updated 8/25/04.
*
*  Thanks to Mathieu Collas (http://www.myefarm.com/) for finding a
*  display/formatting bug in the _build_link_list() function: email
*  readers would show the left bracket and number ("[1") as part of the
*  rendered email address.
*  Updated 12/16/04.
*
*  Thanks to Wojciech Bajon (http://histeria.pl/) for submitting code
*  to handle relative links, which I hadn't considered. I modified his
*  code a bit to handle normal HTTP links and MAILTO links. Also for
*  suggesting three additional HTML entity codes to search for.
*  Updated 03/02/05.
*
*  Thanks to Jacob Chandler for pointing out another link condition
*  for the _build_link_list() function: "https".
*  Updated 04/06/05.
*
*  @author Jon Abernathy <jon@chuggnutt.com>
*  @version 0.6.1
*  @since PHP 4.0.2
*/
class html2text
{

    /**
     *  Contains the HTML content to convert.
     *
     *  @var string $html
     *  @access public
     */
    var $html;

    /**
     *  Contains the converted, formatted text.
     *
     *  @var string $text
     *  @access public
     */
    var $text;

    /**
     *  Maximum width of the formatted text, in columns.
     *
     *  @var integer $width
     *  @access public
     */
    var $width = 70;

    /**
     *  List of preg* regular expression patterns to search for,
     *  used in conjunction with $replace.
     *
     *  @var array $search
     *  @access public
     *  @see $replace
     */
    var $search = array(
        "/\r/",                                  // Non-legal carriage return
        "/[\n\t]+/",                             // Newlines and tabs
        '/<script[^>]*>.*?<\/script>/i',         // <script>s -- which strip_tags supposedly has problems with
        //'/<!-- .* -->/',                         // Comments -- which strip_tags might have problem a with
        '/<a href="([^"]+)"[^>]*>(.+?)<\/a>/ie', // <a href="">
        '/<h[123][^>]*>(.+?)<\/h[123]>/ie',      // H1 - H3
        '/<h[456][^>]*>(.+?)<\/h[456]>/ie',      // H4 - H6
        '/<p[^>]*>/i',                           // <P>
        '/<br[^>]*>/i',                          // <br>
        '/<b[^>]*>(.+?)<\/b>/ie',                // <b>
        '/<i[^>]*>(.+?)<\/i>/i',                 // <i>
        '/(<ul[^>]*>|<\/ul>)/i',                 // <ul> and </ul>
        '/(<ol[^>]*>|<\/ol>)/i',                 // <ol> and </ol>
        '/<li[^>]*>/i',                          // <li>
        '/<hr[^>]*>/i',                          // <hr>
        '/(<table[^>]*>|<\/table>)/i',           // <table> and </table>
        '/(<tr[^>]*>|<\/tr>)/i',                 // <tr> and </tr>
        '/<td[^>]*>(.+?)<\/td>/i',               // <td> and </td>
        '/<th[^>]*>(.+?)<\/th>/ie',              // <th> and </th>
        '/&nbsp;/i',
        '/&quot;/i',
        '/&gt;/i',
        '/&lt;/i',
        '/&(amp|#38);/i',
        '/&copy;/i',
        '/&trade;/i',
        '/&#8220;/',
        '/&#8221;/',
        '/&#8211;/',
        '/&#(8217|39);/',
        '/&#169;/',
        '/&#8482;/',
        '/&#151;/',
        '/&#147;/',
        '/&#148;/',
        '/&#149;/',
        '/&reg;/i',
        '/&bull;/i',
        '/&[&;]+;/i'
    );

    /**
     *  List of pattern replacements corresponding to patterns searched.
     *
     *  @var array $replace
     *  @access public
     *  @see $search
     */
    var $replace = array(
        '',                                     // Non-legal carriage return
        ' ',                                    // Newlines and tabs
        '',                                     // <script>s -- which strip_tags supposedly has problems with
        //'',                                     // Comments -- which strip_tags might have problem a with
        '$this->_build_link_list("\\1", "\\2")', // <a href="">
        "strtoupper(\"\n\n\\1\n\n\")",          // H1 - H3
        "ucwords(\"\n\n\\1\n\n\")",             // H4 - H6
        "\n\n\t",                               // <P>
        "\n",                                   // <br>
        'strtoupper("\\1")',                    // <b>
        '_\\1_',                                // <i>
        "\n\n",                                 // <ul> and </ul>
        "\n\n",                                 // <ol> and </ol>
        "\t*",                                  // <li>
        "\n-------------------------\n",        // <hr>
        "\n\n",                                 // <table> and </table>
        "\n",                                   // <tr> and </tr>
        "\t\t\\1\n",                            // <td> and </td>
        "strtoupper(\"\t\t\\1\n\")",            // <th> and </th>
        ' ',
        '"',
        '>',
        '<',
        '&',
        '(c)',
        '(tm)',
        '"',
        '"',
        '-',
        "'",
        '(c)',
        '(tm)',
        '--',
        '"',
        '"',
        '*',
        '(R)',
        '*',
        ''
    );

    /**
     *  Contains a list of HTML tags to allow in the resulting text.
     *
     *  @var string $allowed_tags
     *  @access public
     *  @see set_allowed_tags()
     */
    var $allowed_tags = '';

    /**
     *  Contains the base URL that relative links should resolve to.
     *
     *  @var string $url
     *  @access public
     */
    var $url;

    /**
     *  Indicates whether content in the $html variable has been converted yet.
     *
     *  @var boolean $converted
     *  @access private
     *  @see $html, $text
     */
    var $_converted = false;

    /**
     *  Contains URL addresses from links to be rendered in plain text.
     *
     *  @var string $link_list
     *  @access private
     *  @see _build_link_list()
     */
    var $_link_list = array();

    /**
     *  Constructor.
     *
     *  If the HTML source string (or file) is supplied, the class
     *  will instantiate with that source propagated, all that has
     *  to be done it to call get_text().
     *
     *  @param string $source HTML content
     *  @param boolean $from_file Indicates $source is a file to pull content from
     *  @access public
     *  @return void
     */
    function html2text( $source = '', $from_file = false )
    {
        if ( !empty($source) ) {
            $this->set_html($source, $from_file);
        }
        $this->set_base_url();
    }

    /**
     *  Loads source HTML into memory, either from $source string or a file.
     *
     *  @param string $source HTML content
     *  @param boolean $from_file Indicates $source is a file to pull content from
     *  @access public
     *  @return void
     */
    function set_html( $source, $from_file = false )
    {
        $this->html = $source;

        if ( $from_file && file_exists($source) ) {
            $fp = fopen($source, 'r');
            $this->html = fread($fp, filesize($source));
            fclose($fp);
        }

        $this->_converted = false;
    }

    /**
     *  Returns the text, converted from HTML.
     *
     *  @access public
     *  @return string
     */
    function get_text()
    {
        if ( !$this->_converted ) {
            $this->_convert();
        }

        return $this->text;
    }

    /**
     *  Prints the text, converted from HTML.
     *
     *  @access public
     *  @return void
     */
    function print_text()
    {
        print $this->get_text();
    }

    /**
     *  Alias to print_text(), operates identically.
     *
     *  @access public
     *  @return void
     *  @see print_text()
     */
    function p()
    {
        print $this->get_text();
    }

    /**
     *  Sets the allowed HTML tags to pass through to the resulting text.
     *
     *  Tags should be in the form "<p>", with no corresponding closing tag.
     *
     *  @access public
     *  @return void
     */
    function set_allowed_tags( $allowed_tags = '' )
    {
        if ( !empty($allowed_tags) ) {
            $this->allowed_tags = $allowed_tags;
        }
    }

    /**
     *  Sets a base URL to handle relative links.
     *
     *  @access public
     *  @return void
     */
    function set_base_url( $url = '' )
    {
        if ( empty($url) ) {
            $this->url = 'http://' . $_SERVER['HTTP_HOST'];
        } else {
            // Strip any trailing slashes for consistency (relative
            // URLs may already start with a slash like "/file.html")
            if ( substr($url, -1) == '/' ) {
                $url = substr($url, 0, -1);
            }
            $this->url = $url;
        }
    }

    /**
     *  Workhorse function that does actual conversion.
     *
     *  First performs custom tag replacement specified by $search and
     *  $replace arrays. Then strips any remaining HTML tags, reduces whitespace
     *  and newlines to a readable format, and word wraps the text to
     *  $width characters.
     *
     *  @access private
     *  @return void
     */
    function _convert()
    {
        // Variables used for building the link list
        //$link_count = 1;
        //$this->_link_list = '';

        $text = trim(stripslashes($this->html));

        // Run our defined search-and-replace
        $text = preg_replace($this->search, $this->replace, $text);

        // Strip any other HTML tags
        $text = strip_tags($text, $this->allowed_tags);

        // Bring down number of empty lines to 2 max
        $text = preg_replace("/\n\s+\n/", "\n", $text);
        $text = preg_replace("/[\n]{3,}/", "\n\n", $text);

        // Add link list
        if ( sizeof($this->_link_list) ) {
            $text .= "\n\nLinks:\n------\n";
            foreach ($this->_link_list as $id => $link) {
                $text .= '[' . ($id+1) . '] ' . $link . "\n";
            }
        }

        // Wrap the text to a readable format
        // for PHP versions >= 4.0.2. Default width is 75
        $text = wordwrap($text, $this->width);

        $this->text = $text;

        $this->_converted = true;
    }

    /**
     *  Helper function called by preg_replace() on link replacement.
     *
     *  Maintains an internal list of links to be displayed at the end of the
     *  text, with numeric indices to the original point in the text they
     *  appeared. Also makes an effort at identifying and handling absolute
     *  and relative links.
     *
     *  @param integer $link_count Counter tracking current link number
     *  @param string $link URL of the link
     *  @param string $display Part of the text to associate number with
     *  @access private
     *  @return string
    */
    function _build_link_list($link, $display)
      {
      $link_lc = strtolower($link);
      
      if (substr($link_lc, 0, 7) == 'http://' || substr($link_lc, 0, 8) == 'https://' || substr($link_lc, 0, 7) == 'mailto:')
        {
        $url = $link;
        }
      else
        {
        $url = $this->url;
        if ($link{0} != '/') {
             $url .= '/';
            }
        $url .= $link;
        }

      $index = array_search($url, $this->_link_list);
      if ($index===FALSE)
        {
        $index = sizeof($this->_link_list);
        $this->_link_list[$index] = $url;
        }
              
      return $display . ' [' . ($index+1) . ']';
      }
}

?>