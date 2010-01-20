<?php
/**
 * The Mail_mimePart class is used to create MIME E-mail messages
 *
 * This class enables you to manipulate and build a mime email
 * from the ground up. The Mail_Mime class is a userfriendly api
 * to this class for people who aren't interested in the internals
 * of mime mail.
 * This class however allows full control over the email.
 *
 * Compatible with PHP versions 4 and 5
 *
 * LICENSE: This LICENSE is in the BSD license style.
 * Copyright (c) 2002-2003, Richard Heyes <richard@phpguru.org>
 * Copyright (c) 2003-2006, PEAR <pear-group@php.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or
 * without modification, are permitted provided that the following
 * conditions are met:
 *
 * - Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * - Neither the name of the authors, nor the names of its contributors 
 *   may be used to endorse or promote products derived from this 
 *   software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Mail
 * @package   Mail_Mime
 * @author    Richard Heyes  <richard@phpguru.org>
 * @author    Cipriano Groenendal <cipri@php.net>
 * @author    Sean Coates <sean@php.net>
 * @author    Aleksander Machniak <alec@php.net>
 * @copyright 2003-2006 PEAR <pear-group@php.net>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Mail_mime
 */


/**
 * The Mail_mimePart class is used to create MIME E-mail messages
 *
 * This class enables you to manipulate and build a mime email
 * from the ground up. The Mail_Mime class is a userfriendly api
 * to this class for people who aren't interested in the internals
 * of mime mail.
 * This class however allows full control over the email.
 *
 * @category  Mail
 * @package   Mail_Mime
 * @author    Richard Heyes  <richard@phpguru.org>
 * @author    Cipriano Groenendal <cipri@php.net>
 * @author    Sean Coates <sean@php.net>
 * @author    Aleksander Machniak <alec@php.net>
 * @copyright 2003-2006 PEAR <pear-group@php.net>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Mail_mime
 */
class Mail_mimePart
{
    /**
    * The encoding type of this part
    *
    * @var string
    * @access private
    */
    var $_encoding;

    /**
    * An array of subparts
    *
    * @var array
    * @access private
    */
    var $_subparts;

    /**
    * The output of this part after being built
    *
    * @var string
    * @access private
    */
    var $_encoded;

    /**
    * Headers for this part
    *
    * @var array
    * @access private
    */
    var $_headers;

    /**
    * The body of this part (not encoded)
    *
    * @var string
    * @access private
    */
    var $_body;

    /**
    * The location of file with body of this part (not encoded)
    *
    * @var string
    * @access private
    */
    var $_body_file;

    /**
    * The end-of-line sequence
    *
    * @var string
    * @access private
    */
    var $_eol = "\r\n";

    /**
    * Constructor.
    *
    * Sets up the object.
    *
    * @param string $body   The body of the mime part if any.
    * @param array  $params An associative array of optional parameters:
    *     content_type      - The content type for this part eg multipart/mixed
    *     encoding          - The encoding to use, 7bit, 8bit,
    *                         base64, or quoted-printable
    *     cid               - Content ID to apply
    *     disposition       - Content disposition, inline or attachment
    *     dfilename         - Filename parameter for content disposition
    *     description       - Content description
    *     charset           - Character set to use
    *     name_encoding     - Encoding for attachment name (Content-Type)
    *                         By default filenames are encoded using RFC2231
    *                         Here you can set RFC2047 encoding (quoted-printable
    *                         or base64) instead
    *     filename_encoding - Encoding for attachment filename (Content-Disposition)
    *                         See 'name_encoding'
    *     eol               - End of line sequence. Default: "\r\n"
    *     body_file         - Location of file with part's body (instead of $body)
    *
    * @access public
    */
    function Mail_mimePart($body = '', $params = array())
    {
        if (!empty($params['eol'])) {
            $this->_eol = $params['eol'];
        } else if (defined('MAIL_MIMEPART_CRLF')) { // backward-copat.
            $this->_eol = MAIL_MIMEPART_CRLF;
        }

        $c_type = array();
        $c_disp = array();
        foreach ($params as $key => $value) {
            switch ($key) {
            case 'content_type':
                $c_type['type'] = $value;
                break;

            case 'encoding':
                $this->_encoding = $value;
                $headers['Content-Transfer-Encoding'] = $value;
                break;

            case 'cid':
                $headers['Content-ID'] = '<' . $value . '>';
                break;

            case 'disposition':
                $c_disp['disp'] = $value;
                break;

            case 'dfilename':
                $c_disp['filename'] = $value;
                $c_type['name'] = $value;
                break;

            case 'description':
                $headers['Content-Description'] = $value;
                break;

            case 'charset':
                $c_type['charset'] = $value;
                $c_disp['charset'] = $value;
                break;

            case 'language':
                $c_type['language'] = $value;
                $c_disp['language'] = $value;
                break;

            case 'location':
                $headers['Content-Location'] = $value;
                break;

            case 'body_file':
                $this->_body_file = $value;
                break;
            }
        }

        // Content-Type
        if (isset($c_type['type'])) {
            $headers['Content-Type'] = $c_type['type'];
            if (isset($c_type['name'])) {
                $headers['Content-Type'] .= ';' . $this->_eol;
                $headers['Content-Type'] .= $this->_buildHeaderParam(
                    'name', $c_type['name'], 
                    isset($c_type['charset']) ? $c_type['charset'] : 'US-ASCII', 
                    isset($c_type['language']) ? $c_type['language'] : null,
                    isset($params['name_encoding']) ?  $params['name_encoding'] : null
                );
            }
            if (isset($c_type['charset'])) {
                $headers['Content-Type']
                    .= ';' . $this->_eol . " charset={$c_type['charset']}";
            }
        }

        // Content-Disposition
        if (isset($c_disp['disp'])) {
            $headers['Content-Disposition'] = $c_disp['disp'];
            if (isset($c_disp['filename'])) {
                $headers['Content-Disposition'] .= ';' . $this->_eol;
                $headers['Content-Disposition'] .= $this->_buildHeaderParam(
                    'filename', $c_disp['filename'], 
                    isset($c_disp['charset']) ? $c_disp['charset'] : 'US-ASCII', 
                    isset($c_disp['language']) ? $c_disp['language'] : null,
                    isset($params['filename_encoding']) ?  $params['filename_encoding'] : null
                );
            }
        }

        // Default content-type
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'text/plain';
        }

        // Default encoding
        if (!isset($this->_encoding)) {
            $this->_encoding = '7bit';
        }

        // Assign stuff to member variables
        $this->_encoded  = array();
        $this->_headers  = $headers;
        $this->_body     = $body;
    }

    /**
     * Encodes and returns the email. Also stores
     * it in the encoded member variable
     *
     * @param string $boundary Pre-defined boundary string
     *
     * @return An associative array containing two elements,
     *         body and headers. The headers element is itself
     *         an indexed array. On error returns PEAR error object.
     * @access public
     */
    function encode($boundary=null)
    {
        $encoded =& $this->_encoded;

        if (count($this->_subparts)) {
            $boundary = $boundary ? $boundary : '=_' . md5(rand() . microtime());
            $eol = $this->_eol;

            $this->_headers['Content-Type'] .= ";$eol boundary=\"$boundary\"";

            $encoded['body'] = ''; 

            for ($i = 0; $i < count($this->_subparts); $i++) {
                $encoded['body'] .= '--' . $boundary . $eol;
                $tmp = $this->_subparts[$i]->encode();
                if (PEAR::isError($tmp)) {
                    return $tmp;
                }
                foreach ($tmp['headers'] as $key => $value) {
                    $encoded['body'] .= $key . ': ' . $value . $eol;
                }
                $encoded['body'] .= $eol . $tmp['body'] . $eol;
            }

            $encoded['body'] .= '--' . $boundary . '--' . $eol;

        } else if ($this->_body) {
            $encoded['body'] = $this->_getEncodedData($this->_body, $this->_encoding);
        } else if ($this->_body_file) {
            // Temporarily reset magic_quotes_runtime for file reads and writes
            if ($magic_quote_setting = get_magic_quotes_runtime()) {
                @ini_set('magic_quotes_runtime', 0);
            }
            $body = $this->_getEncodedDataFromFile($this->_body_file, $this->_encoding);
            if ($magic_quote_setting) {
                @ini_set('magic_quotes_runtime', $magic_quote_setting);
            }

            if (PEAR::isError($body)) {
                return $body;
            }
            $encoded['body'] = $body;
        } else {
            $encoded['body'] = '';
        }

        // Add headers to $encoded
        $encoded['headers'] =& $this->_headers;

        return $encoded;
    }

    /**
     * Encodes and saves the email into file. File must exist.
     * Data will be appended to the file.
     *
     * @param string $filename Output file location
     * @param string $boundary Pre-defined boundary string
     *
     * @return array An associative array containing message headers
     *               or PEAR error object
     * @access public
     * @since 1.6.0
     */
    function encodeToFile($filename, $boundary=null)
    {
        if (file_exists($filename) && !is_writable($filename)) {
            $err = PEAR::raiseError('File is not writeable: ' . $filename);
            return $err;
        }

        if (!($fh = fopen($filename, 'ab'))) {
            $err = PEAR::raiseError('Unable to open file: ' . $filename);
            return $err;
        }

        // Temporarily reset magic_quotes_runtime for file reads and writes
        if ($magic_quote_setting = get_magic_quotes_runtime()) {
            @ini_set('magic_quotes_runtime', 0);
        }

        $res = $this->_encodePartToFile($fh, $boundary);

        fclose($fh);

        if ($magic_quote_setting) {
            @ini_set('magic_quotes_runtime', $magic_quote_setting);
        }

        return PEAR::isError($res) ? $res : $this->_headers;
    }

    /**
     * Encodes given email part into file
     *
     * @param string $fh       Output file handle
     * @param string $boundary Pre-defined boundary string
     *
     * @return array True on sucess or PEAR error object
     * @access private
     */
    function _encodePartToFile($fh, $boundary=null)
    {
        $eol = $this->_eol;

        if (count($this->_subparts)) {
            $boundary = $boundary ? $boundary : '=_' . md5(rand() . microtime());
            $this->_headers['Content-Type'] .= ";$eol boundary=\"$boundary\"";
        }

        foreach ($this->_headers as $key => $value) {
            fwrite($fh, $key . ': ' . $value . $eol);
        }

        if (count($this->_subparts)) {
            for ($i = 0; $i < count($this->_subparts); $i++) {
                fwrite($fh, $eol . '--' . $boundary . $eol);
                $res = $this->_subparts[$i]->_encodePartToFile($fh);
                if (PEAR::isError($res)) {
                    return $res;
                }
            }

            fwrite($fh, $eol . '--' . $boundary . '--' . $eol);

        } else if ($this->_body) {
            fwrite($fh, $eol . $this->_getEncodedData($this->_body, $this->_encoding));
        } else if ($this->_body_file) {
            fwrite($fh, $eol);
            $res = $this->_getEncodedDataFromFile(
                $this->_body_file, $this->_encoding, $fh
            );
            if (PEAR::isError($res)) {
                return $res;
            }
        }

        return true;
    }

    /**
     * Adds a subpart to current mime part and returns
     * a reference to it
     *
     * @param string $body   The body of the subpart, if any.
     * @param array  $params The parameters for the subpart, same
     *                       as the $params argument for constructor.
     *
     * @return Mail_mimePart A reference to the part you just added. It is
     *                       crucial if using multipart/* in your subparts that
     *                       you use =& in your script when calling this function,
     *                       otherwise you will not be able to add further subparts.
     * @access public
     */
    function &addSubPart($body, $params)
    {
        $this->_subparts[] = new Mail_mimePart($body, $params);
        return $this->_subparts[count($this->_subparts) - 1];
    }

    /**
     * Returns encoded data based upon encoding passed to it
     *
     * @param string $data     The data to encode.
     * @param string $encoding The encoding type to use, 7bit, base64,
     *                         or quoted-printable.
     *
     * @return string
     * @access private
     */
    function _getEncodedData($data, $encoding)
    {
        switch ($encoding) {
        case 'quoted-printable':
            return $this->_quotedPrintableEncode($data);
            break;

        case 'base64':
            return rtrim(chunk_split(base64_encode($data), 76, $this->_eol));
            break;

        case '8bit':
        case '7bit':
        default:
            return $data;
        }
    }

    /**
     * Returns encoded data based upon encoding passed to it
     *
     * @param string   $filename Data file location
     * @param string   $encoding The encoding type to use, 7bit, base64,
     *                           or quoted-printable.
     * @param resource $fh       Output file handle. If set, data will be
     *                           stored into it instead of returning it
     *
     * @return string Encoded data or PEAR error object
     * @access private
     */
    function _getEncodedDataFromFile($filename, $encoding, $fh=null)
    {
        if (!is_readable($filename)) {
            $err = PEAR::raiseError('Unable to read file: ' . $filename);
            return $err;
        }

        if (!($fd = fopen($filename, 'rb'))) {
            $err = PEAR::raiseError('Could not open file: ' . $filename);
            return $err;
        }

        $data = '';

        switch ($encoding) {
        case 'quoted-printable':
            while (!feof($fd)) {
                $buffer = $this->_quotedPrintableEncode(fgets($fd));
                if ($fh) {
                    fwrite($fh, $buffer);
                } else {
                    $data .= $buffer;
                }
            }
            break;

        case 'base64':
            while (!feof($fd)) {
                // Should read in a multiple of 57 bytes so that
                // the output is 76 bytes per line. Don't use big chunks
                // because base64 encoding is memory expensive
                $buffer = fread($fd, 57 * 9198); // ca. 0.5 MB
                $buffer = base64_encode($buffer);
                $buffer = chunk_split($buffer, 76, $this->_eol);
                if (feof($fd)) {
                    $buffer = rtrim($buffer);
                }

                if ($fh) {
                    fwrite($fh, $buffer);
                } else {
                    $data .= $buffer;
                }
            }
            break;

        case '8bit':
        case '7bit':
        default:
            while (!feof($fd)) {
                $buffer = fread($fd, 1048576); // 1 MB
                if ($fh) {
                    fwrite($fh, $buffer);
                } else {
                    $data .= $buffer;
                }
            }
        }

        fclose($fd);

        if (!$fh) {
            return $data;
        }
    }

    /**
     * Encodes data to quoted-printable standard.
     *
     * @param string $input    The data to encode
     * @param int    $line_max Optional max line length. Should
     *                         not be more than 76 chars
     *
     * @return string Encoded data
     *
     * @access private
     */
    function _quotedPrintableEncode($input , $line_max = 76)
    {
        $eol = $this->_eol;
        /*
        // imap_8bit() is extremely fast, but doesn't handle properly some characters
        if (function_exists('imap_8bit') && $line_max == 76) {
            $input = preg_replace('/\r?\n/', "\r\n", $input);
            $input = imap_8bit($input);
            if ($eol != "\r\n") {
                $input = str_replace("\r\n", $eol, $input);
            }
            return $input;
        }
        */
        $lines  = preg_split("/\r?\n/", $input);
        $escape = '=';
        $output = '';

        while (list($idx, $line) = each($lines)) {
            $newline = '';
            $i = 0;

            while (isset($line[$i])) {
                $char = $line[$i];
                $dec  = ord($char);
                $i++;

                if (($dec == 32) && (!isset($line[$i]))) {
                    // convert space at eol only
                    $char = '=20';
                } elseif ($dec == 9 && isset($line[$i])) {
                    ; // Do nothing if a TAB is not on eol
                } elseif (($dec == 61) || ($dec < 32) || ($dec > 126)) {
                    $char = $escape . sprintf('%02X', $dec);
                } elseif (($dec == 46) && (($newline == '')
                    || ((strlen($newline) + strlen("=2E")) >= $line_max))
                ) {
                    // Bug #9722: convert full-stop at bol,
                    // some Windows servers need this, won't break anything (cipri)
                    // Bug #11731: full-stop at bol also needs to be encoded
                    // if this line would push us over the line_max limit.
                    $char = '=2E';
                }

                // Note, when changing this line, also change the ($dec == 46)
                // check line, as it mimics this line due to Bug #11731
                // EOL is not counted
                if ((strlen($newline) + strlen($char)) >= $line_max) {
                    // soft line break; " =\r\n" is okay
                    $output  .= $newline . $escape . $eol;
                    $newline  = '';
                }
                $newline .= $char;
            } // end of for
            $output .= $newline . $eol;
            unset($lines[$idx]);
        }
        // Don't want last crlf
        $output = substr($output, 0, -1 * strlen($eol));
        return $output;
    }

    /**
     * Encodes the paramater of a header.
     *
     * @param string $name      The name of the header-parameter
     * @param string $value     The value of the paramter
     * @param string $charset   The characterset of $value
     * @param string $language  The language used in $value
     * @param string $encoding  Parameter encoding. If not set, parameter value
     *                          is encoded according to RFC2231
     * @param int    $maxLength The maximum length of a line. Defauls to 75
     *
     * @return string
     *
     * @access private
     */
    function _buildHeaderParam($name, $value, $charset=null, $language=null,
        $encoding=null, $maxLength=75
    ) {
        // RFC 2045:
        // value needs encoding if contains non-ASCII chars or is longer than 78 chars
        if (!preg_match('#[^\x20-\x7E]#', $value)) {
            $token_regexp = '#([^\x21,\x23-\x27,\x2A,\x2B,\x2D'
                . ',\x2E,\x30-\x39,\x41-\x5A,\x5E-\x7E])#';
            if (!preg_match($token_regexp, $value)) {
                // token
                if (strlen($name) + strlen($value) + 3 <= $maxLength) {
                    return " {$name}={$value}";
                }
            } else {
                // quoted-string
                $quoted = addcslashes($value, '\\"');
                if (strlen($name) + strlen($quoted) + 5 <= $maxLength) {
                    return " {$name}=\"{$quoted}\"";
                }
            }
        }

        // RFC2047: use quoted-printable/base64 encoding
        if ($encoding == 'quoted-printable' || $encoding == 'base64') {
            return $this->_buildRFC2047Param($name, $value, $charset, $encoding);
        }

        // RFC2231:
        $encValue = preg_replace_callback(
            '/([^\x21,\x23,\x24,\x26,\x2B,\x2D,\x2E,\x30-\x39,\x41-\x5A,\x5E-\x7E])/',
            array($this, '_encodeReplaceCallback'), $value
        );
        $value = "$charset'$language'$encValue";

        $header = " {$name}*={$value}";
        if (strlen($header) <= $maxLength) {
            return $header;
        }

        $preLength = strlen(" {$name}*0*=");
        $maxLength = max(16, $maxLength - $preLength - 3);
        $maxLengthReg = "|(.{0,$maxLength}[^\%][^\%])|";

        $headers = array();
        $headCount = 0;
        while ($value) {
            $matches = array();
            $found = preg_match($maxLengthReg, $value, $matches);
            if ($found) {
                $headers[] = " {$name}*{$headCount}*={$matches[0]}";
                $value = substr($value, strlen($matches[0]));
            } else {
                $headers[] = " {$name}*{$headCount}*={$value}";
                $value = '';
            }
            $headCount++;
        }

        $headers = implode(';' . $this->_eol, $headers);
        return $headers;
    }

    /**
     * Encodes header parameter as per RFC2047 if needed
     *
     * @param string $name      The parameter name
     * @param string $value     The parameter value
     * @param string $charset   The parameter charset
     * @param string $encoding  Encoding type (quoted-printable or base64)
     * @param int    $maxLength Encoded parameter max length. Default: 76
     *
     * @return string Parameter line
     * @access private
     */
    function _buildRFC2047Param($name, $value, $charset,
        $encoding='quoted-printable', $maxLength=76
    ) {
        // WARNING: RFC 2047 says: "An 'encoded-word' MUST NOT be used in
        // parameter of a MIME Content-Type or Content-Disposition field",
        // but... it's supported by many clients/servers
        $quoted = '';

        if ($encoding == 'base64') {
            $value = base64_encode($value);
            $prefix = '=?' . $charset . '?B?';
            $suffix = '?=';

            // 2 x SPACE, 2 x '"', '=', ';'
            $add_len = strlen($prefix . $suffix) + strlen($name) + 6;
            $len = $add_len + strlen($value);

            while ($len > $maxLength) { 
                // We can cut base64-encoded string every 4 characters
                $real_len = floor(($maxLength - $add_len) / 4) * 4;
                $_quote = substr($value, 0, $real_len);
                $value = substr($value, $real_len);

                $quoted .= $prefix . $_quote . $suffix . $this->_eol . ' ';
                $add_len = strlen($prefix . $suffix) + 4; // 2 x SPACE, '"', ';'
                $len = strlen($value) + $add_len;
            }
            $quoted .= $prefix . $value . $suffix;

        } else {
            // quoted-printable
            $value = $this->encodeQP($value);
            $prefix = '=?' . $charset . '?Q?';
            $suffix = '?=';

            // 2 x SPACE, 2 x '"', '=', ';'
            $add_len = strlen($prefix . $suffix) + strlen($name) + 6;
            $len = $add_len + strlen($value);

            while ($len > $maxLength) {
                $length = $maxLength - $add_len;
                // don't break any encoded letters
                if (preg_match("/^(.{0,$length}[^\=][^\=])/", $value, $matches)) {
                    $_quote = $matches[1];
                }

                $quoted .= $prefix . $_quote . $suffix . $this->_eol . ' ';
                $value = substr($value, strlen($_quote));
                $add_len = strlen($prefix . $suffix) + 4; // 2 x SPACE, '"', ';'
                $len = strlen($value) + $add_len;
            }

            $quoted .= $prefix . $value . $suffix;
        }

        return " {$name}=\"{$quoted}\"";
    }

    /**
     * Callback function to replace extended characters (\x80-xFF) with their
     * ASCII values (RFC2231)
     *
     * @param array $matches Preg_replace's matches array
     *
     * @return string        Encoded character string
     * @access private
     */
    function _encodeReplaceCallback($matches)
    {
        return sprintf('%%%02X', ord($matches[1]));
    }

    /**
     * Encodes the given string using quoted-printable
     *
     * @param string $str String to encode
     *
     * @return string     Encoded string
     * @access public
     * @since 1.6.0
     */
    function encodeQP($str)
    {
        // Replace all special characters used by the encoder
        $search  = array('=',   '_',   '?',   ' ');
        $replace = array('=3D', '=5F', '=3F', '_');
        $str = str_replace($search, $replace, $str);

        // Replace all extended characters (\x80-xFF) with their
        // ASCII values.
        return preg_replace_callback(
            '/([\x80-\xFF])/', array('Mail_mimePart', '_qpReplaceCallback'), $str
        );
    }

    /**
     * Callback function to replace extended characters (\x80-xFF) with their
     * ASCII values (RFC2047: quoted-printable)
     *
     * @param array $matches Preg_replace's matches array
     *
     * @return string        Encoded character string
     * @access private
     */
    function _qpReplaceCallback($matches)
    {
        return sprintf('=%02X', ord($matches[1]));
    }

} // End of class
