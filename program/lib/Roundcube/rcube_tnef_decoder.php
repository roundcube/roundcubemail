<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) 2002-2010, The Horde Project (http://www.horde.org/)    |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   MS-TNEF format decoder                                              |
 +-----------------------------------------------------------------------+
 | Author: Jan Schneider <jan@horde.org>                                 |
 | Author: Michael Slusarz <slusarz@horde.org>                           |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * MS-TNEF format decoder based on code by:
 *   Graham Norbury <gnorbury@bondcar.com>
 * Original design by:
 *   Thomas Boll <tb@boll.ch>, Mark Simpson <damned@world.std.com>
 *
 * @package    Framework
 * @subpackage Storage
 */
class rcube_tnef_decoder
{
    const SIGNATURE         = 0x223e9f78;
    const LVL_MESSAGE       = 0x01;
    const LVL_ATTACHMENT    = 0x02;

    const AFROM             = 0x08000;
    const ASUBJECT          = 0x18004;
    const AMESSAGEID        = 0x18009;
    const AFILENAME         = 0x18010;
    const APARENTID         = 0x1800a;
    const ACONVERSATIONID   = 0x1800b;
    const ABODY             = 0x2800c;
    const ADATESENT         = 0x38005;
    const ADATERECEIVED     = 0x38006;
    const ADATEMODIFIED     = 0x38020;
    const APRIORITY         = 0x4800d;
    const AOWNER            = 0x60000;
    const ASENTFOR          = 0x60001;
    const ASTATUS           = 0x68007;
    const ATTACHDATA        = 0x6800f;
    const ATTACHMETAFILE    = 0x68011;
    const ATTACHCREATEDATE  = 0x38012;
    const ARENDDATA         = 0x69002;
    const AMAPIPROPS        = 0x69003;
    const ARECIPIENTTABLE   = 0x69004;
    const AMAPIATTRS        = 0x69005;
    const AOEMCODEPAGE      = 0x69007;
    const AORIGINALMCLASS   = 0x70006;
    const AMCLASS           = 0x78008;
    const AVERSION          = 0x89006;

    const MAPI_TYPE_UNSET     = 0x0000;
    const MAPI_NULL           = 0x0001;
    const MAPI_SHORT          = 0x0002;
    const MAPI_INT            = 0x0003;
    const MAPI_FLOAT          = 0x0004;
    const MAPI_DOUBLE         = 0x0005;
    const MAPI_CURRENCY       = 0x0006;
    const MAPI_APPTIME        = 0x0007;
    const MAPI_ERROR          = 0x000a;
    const MAPI_BOOLEAN        = 0x000b;
    const MAPI_OBJECT         = 0x000d;
    const MAPI_INT8BYTE       = 0x0014;
    const MAPI_STRING         = 0x001e;
    const MAPI_UNICODE_STRING = 0x001f;
    const MAPI_SYSTIME        = 0x0040;
    const MAPI_CLSID          = 0x0048;
    const MAPI_BINARY         = 0x0102;

    const MAPI_BODY                     = 0x1000;
    const MAPI_RTF_COMPRESSED           = 0x1009;
    const MAPI_BODY_HTML                = 0x1013;
    const MAPI_NATIVE_BODY              = 0x1016;

    const MAPI_DISPLAY_NAME             = 0x3001;
    const MAPI_ADDRTYPE                 = 0x3002;
    const MAPI_EMAIL_ADDRESS            = 0x3003;
    const MAPI_COMMENT                  = 0x3004;
    const MAPI_DEPTH                    = 0x3005;
    const MAPI_PROVIDER_DISPLAY         = 0x3006;
    const MAPI_CREATION_TIME            = 0x3007;
    const MAPI_LAST_MODIFICATION_TIME   = 0x3008;
    const MAPI_RESOURCE_FLAGS           = 0x3009;
    const MAPI_PROVIDER_DLL_NAME        = 0x300A;
    const MAPI_SEARCH_KEY               = 0x300B;
    const MAPI_ATTACHMENT_X400_PARAMETERS = 0x3700;
    const MAPI_ATTACH_DATA              = 0x3701;
    const MAPI_ATTACH_ENCODING          = 0x3702;
    const MAPI_ATTACH_EXTENSION         = 0x3703;
    const MAPI_ATTACH_FILENAME          = 0x3704;
    const MAPI_ATTACH_METHOD            = 0x3705;
    const MAPI_ATTACH_LONG_FILENAME     = 0x3707;
    const MAPI_ATTACH_PATHNAME          = 0x3708;
    const MAPI_ATTACH_RENDERING         = 0x3709;
    const MAPI_ATTACH_TAG               = 0x370A;
    const MAPI_RENDERING_POSITION       = 0x370B;
    const MAPI_ATTACH_TRANSPORT_NAME    = 0x370C;
    const MAPI_ATTACH_LONG_PATHNAME     = 0x370D;
    const MAPI_ATTACH_MIME_TAG          = 0x370E;
    const MAPI_ATTACH_ADDITIONAL_INFO   = 0x370F;
    const MAPI_ATTACH_MIME_SEQUENCE     = 0x3710;
    const MAPI_ATTACH_CONTENT_ID        = 0x3712;
    const MAPI_ATTACH_CONTENT_LOCATION  = 0x3713;
    const MAPI_ATTACH_FLAGS             = 0x3714;

    const MAPI_NAMED_TYPE_ID        = 0x0000;
    const MAPI_NAMED_TYPE_STRING    = 0x0001;
    const MAPI_NAMED_TYPE_NONE      = 0xff;
    const MAPI_MV_FLAG              = 0x1000;

    const RTF_UNCOMPRESSED = 0x414c454d;
    const RTF_COMPRESSED   = 0x75465a4c;

    protected $codepage;


    /**
     * Decompress the data.
     *
     * @param string $data    The data to decompress.
     * @param bool   $as_html Return message body as HTML
     *
     * @return array The decompressed data.
     */
    public function decompress($data, $as_html = false)
    {
        $attachments = [];
        $message     = [];

        if ($this->_geti($data, 32) == self::SIGNATURE) {
            $this->_geti($data, 16);

            // Version
            $this->_geti($data, 8);     // lvl_message
            $this->_geti($data, 32);    // idTnefVersion
            $this->_getx($data, $this->_geti($data, 32));
            $this->_geti($data, 16);    // checksum

            while (strlen($data) > 0) {
                switch ($this->_geti($data, 8)) {
                case self::LVL_MESSAGE:
                    $this->_decodeMessage($data, $message);
                    break;

                case self::LVL_ATTACHMENT:
                    $this->_decodeAttachment($data, $attachments);
                    break;
                }
            }
        }

        // Return the message body as HTML
        if ($message && $as_html) {
            // HTML body
            if (!empty($message['size']) && $message['subtype'] == 'html') {
                $message = $message['stream'];
            }
            // RTF body (converted to HTML)
            // Note: RTF can contain encapsulated HTML content
            else if (!empty($message['size']) && $message['subtype'] == 'rtf'
                && function_exists('iconv')
                && class_exists('RtfHtmlPhp\Document')
            ) {
                try {
                    $document  = new RtfHtmlPhp\Document($message['stream']);
                    $formatter = new RtfHtmlPhp\Html\HtmlFormatter(RCUBE_CHARSET);
                    $message   = $formatter->format($document);
                }
                catch (Exception $e) {
                    // ignore the body
                    rcube::raise_error([
                            'file' => __FILE__,
                            'line' => __LINE__,
                            'message' => "Failed to extract RTF/HTML content from TNEF attachment"
                        ], true, false
                    );
                }
            }
            else {
                $message = null;
            }
        }

        return [
            'message'     => $message,
            'attachments' => array_reverse($attachments),
        ];
    }

    /**
     * Pop specified number of bytes from the buffer.
     *
     * @param string &$data The data string.
     * @param int    $bytes How many bytes to retrieve.
     *
     * @return string Extracted data
     */
    protected function _getx(&$data, $bytes)
    {
        $value = null;

        if (strlen($data) >= $bytes) {
            $value = substr($data, 0, $bytes);
            $data  = substr($data, $bytes);
        }

        return $value;
    }

    /**
     * Pop specified number of bits from the buffer
     *
     * @param string &$data The data string.
     * @param int    $bits  How many bits to retrieve.
     *
     * @return int|null
     */
    protected function _geti(&$data, $bits)
    {
        $bytes = $bits / 8;
        $value = null;

        if (strlen($data) >= $bytes) {
            $value = ord($data[0]);
            if ($bytes >= 2) {
                $value += (ord($data[1]) << 8);
            }
            if ($bytes >= 4) {
                $value += (ord($data[2]) << 16) + (ord($data[3]) << 24);
            }

            $data = substr($data, $bytes);
        }

        return $value;
    }

    /**
     * Decode a single attribute
     *
     * @param string &$data The data string.
     *
     * @return string Extracted data
     */
    protected function _decodeAttribute(&$data)
    {
        // Data.
        $value = $this->_getx($data, $this->_geti($data, 32));

        // Checksum.
        $this->_geti($data, 16);

        return $value;
    }

    /**
     * TODO
     *
     * @param string $data   The data string.
     * @param array  &result TODO
     */
    protected function _extractMapiAttributes($data, &$result)
    {
        // Number of attributes.
        $number = $this->_geti($data, 32);

        while ((strlen($data) > 0) && $number--) {
            $have_mval = false;
            $num_mval  = 1;
            $value     = null;
            $attr_type = $this->_geti($data, 16);
            $attr_name = $this->_geti($data, 16);

            if (($attr_type & self::MAPI_MV_FLAG) != 0) {
                $have_mval = true;
                $attr_type = $attr_type & ~self::MAPI_MV_FLAG;
            }

            if (($attr_name >= 0x8000) && ($attr_name < 0xFFFE)) {
                $this->_getx($data, 16);
                $named_type = $this->_geti($data, 32);

                switch ($named_type) {
                case self::MAPI_NAMED_TYPE_ID:
                    $attr_name = $this->_geti($data, 32);
                    break;

                case self::MAPI_NAMED_TYPE_STRING:
                    $attr_name = 0x9999;
                    $idlen     = $this->_geti($data, 32);
                    $name      = $this->_getx($data, $idlen + ((4 - ($idlen % 4)) % 4));
                    // $name      = $this->convertString(substr($name, 0, $idlen));
                    break;

                case self::MAPI_NAMED_TYPE_NONE:
                default:
                    continue 2;
                }
            }

            if ($have_mval) {
                $num_mval = $this->_geti($data, 32);
            }

            switch ($attr_type) {
            case self::MAPI_NULL:
            case self::MAPI_TYPE_UNSET:
                break;

            case self::MAPI_SHORT:
                $value = $this->_geti($data, 16);
                $this->_geti($data, 16);
                break;

            case self::MAPI_INT:
            case self::MAPI_BOOLEAN:
                for ($i = 0; $i < $num_mval; $i++) {
                    $value = $this->_geti($data, 32);
                }
                break;

            case self::MAPI_FLOAT:
            case self::MAPI_ERROR:
                $value = $this->_getx($data, 4);
                break;

            case self::MAPI_DOUBLE:
            case self::MAPI_APPTIME:
            case self::MAPI_CURRENCY:
            case self::MAPI_INT8BYTE:
            case self::MAPI_SYSTIME:
                $value = $this->_getx($data, 8);
                break;

            case self::MAPI_STRING:
            case self::MAPI_UNICODE_STRING:
            case self::MAPI_BINARY:
            case self::MAPI_OBJECT:
                $num_vals = $have_mval ? $num_mval : $this->_geti($data, 32);
                for ($i = 0; $i < $num_vals; $i++) {
                    $length = $this->_geti($data, 32);

                    // Pad to next 4 byte boundary.
                    $datalen = $length + ((4 - ($length % 4)) % 4);

                    // Read and truncate to length.
                    $value = $this->_getx($data, $datalen);
                }

                if ($attr_type == self::MAPI_UNICODE_STRING) {
                    $value = $this->convertString($value);
                }

                break;
            }

            // Store any interesting attributes.
            switch ($attr_name) {
            case self::MAPI_RTF_COMPRESSED:
                $result['type']    = 'application';
                $result['subtype'] = 'rtf';
                $result['name']    = (!empty($result['name']) ? $result['name'] : 'Untitled') . '.rtf';
                $result['stream']  = $this->_decodeRTF($value);
                $result['size']    = strlen($result['stream']);
                break;

            case self::MAPI_BODY:
            case self::MAPI_BODY_HTML:
                $result['type']    = 'text';
                $result['subtype'] = $attr_name == self::MAPI_BODY ? 'plain' : 'html';
                $result['name']    = (!empty($result['name']) ? $result['name'] : 'Untitled')
                    . ($attr_name == self::MAPI_BODY ? '.txt' : '.html');
                $result['stream']  = $value;
                $result['size']    = strlen($value);
                break;

            case self::MAPI_ATTACH_LONG_FILENAME:
                // Used in preference to AFILENAME value.
                $result['name'] = trim(preg_replace('/.*[\/](.*)$/', '\1', $value));
                break;

            case self::MAPI_ATTACH_MIME_TAG:
                // Is this ever set, and what is format?
                $value = explode('/', trim($value));
                $result['type']    = $value[0];
                $result['subtype'] = $value[1];
                break;

            case self::MAPI_ATTACH_CONTENT_ID:
                $result['content-id'] = $value;
                break;

            case self::MAPI_ATTACH_DATA:
                $this->_getx($value, 16);
                $att = new rcube_tnef_decoder;
                $res = $att->decompress($value);
                $result = array_merge($result, $res['message']);
                break;
            }
        }
    }

    /**
     * Decodes TNEF message attributes
     *
     * @param string &$data    The data string.
     * @param array  &$message Message data
     */
    protected function _decodeMessage(&$data, &$message)
    {
        $attribute = $this->_geti($data, 32);
        $value     = $this->_decodeAttribute($data);

        switch ($attribute) {
        case self::AOEMCODEPAGE:
            // Find codepage of the message
            $value = unpack('V', $value);
            $this->codepage = $value[1];
            break;

        case self::AMCLASS:
            $value = trim(str_replace('Microsoft Mail v3.0 ', '', $value));
            // Normal message will be that with prefix 'IPM.Microsoft Mail.
            break;

        case self::ASUBJECT:
            $message['name'] = $value;
            break;

        case self::AMAPIPROPS:
            $this->_extractMapiAttributes($value, $message);
            break;
        }
    }

    /**
     * Decodes TNEF attachment attributes
     *
     * @param string &$data       The data string.
     * @param array  &$attachment Attachments data
     */
    protected function _decodeAttachment(&$data, &$attachment)
    {
        $attribute = $this->_geti($data, 32);
        $size      = $this->_geti($data, 32);
        $value     = $this->_getx($data, $size);

        $this->_geti($data, 16); // checksum

        switch ($attribute) {
        case self::ARENDDATA:
            // Add a new default data block to hold details of this
            // attachment. Reverse order is easier to handle later!
            array_unshift($attachment, [
                    'type'    => 'application',
                    'subtype' => 'octet-stream',
                    'name'    => 'unknown',
                    'stream'  => ''
            ]);

            break;

        case self::AFILENAME:
            $value = $this->convertString($value, true);
            // Strip path
            $attachment[0]['name'] = trim(preg_replace('/.*[\/](.*)$/', '\1', $value));
            break;

        case self::ATTACHDATA:
            // The attachment itself
            $attachment[0]['size']   = $size;
            $attachment[0]['stream'] = $value;
            break;

        case self::AMAPIATTRS:
            $this->_extractMapiAttributes($value, $attachment[0]);
            break;
        }
    }

    /**
     * Convert string value to system charset according to defined codepage
     */
    protected function convertString($str, $use_codepage = false)
    {
        if ($use_codepage && $this->codepage
            && ($charset = rcube_charset::$windows_codepages[$this->codepage])
        ) {
            $str = rcube_charset::convert($str, $charset, RCUBE_CHARSET);
        }
        else if (($pos = strpos($str, "\0")) !== false && $pos != strlen($str)-1) {
            $str = rcube_charset::convert($str, 'UTF-16LE', RCUBE_CHARSET);
        }

        return trim($str);
    }

    /**
     * Decodes TNEF RTF
     */
    protected function _decodeRTF($data)
    {
        $c_size = $this->_geti($data, 32);
        $size   = $this->_geti($data, 32);
        $magic  = $this->_geti($data, 32);
        $crc    = $this->_geti($data, 32);

        if ($magic == self::RTF_COMPRESSED) {
            $data = $this->_decompressRTF($data, $size);
        }

        return $data;
    }

    /**
     * Decompress compressed RTF. Logic taken from Horde.
     */
    protected function _decompressRTF($data, $size)
    {
        $in = $out = $flags = $flag_count = 0;
        $uncomp    = '';
        $preload   = "{\\rtf1\\ansi\\mac\\deff0\\deftab720{\\fonttbl;}{\\f0\\fnil \\froman \\fswiss \\fmodern \\fscript \\fdecor MS Sans SerifSymbolArialTimes New RomanCourier{\\colortbl\\red0\\green0\\blue0\n\r\\par \\pard\\plain\\f0\\fs20\\b\\i\\u\\tab\\tx";
        $length_preload = strlen($preload);

        for ($cnt = 0; $cnt < $length_preload; $cnt++) {
            $uncomp .= $preload[$cnt];
            ++$out;
        }

        while ($out < ($size + $length_preload)) {
            if (($flag_count++ % 8) == 0) {
                $flags = ord($data[$in++]);
            }
            else {
                $flags = $flags >> 1;
            }

            if (($flags & 1) != 0) {
                $offset = ord($data[$in++]);
                $length = ord($data[$in++]);
                $offset = ($offset << 4) | ($length >> 4);
                $length = ($length & 0xF) + 2;
                $offset = ((int)($out / 4096)) * 4096 + $offset;

                if ($offset >= $out) {
                    $offset -= 4096;
                }

                $end = $offset + $length;

                while ($offset < $end) {
                    $uncomp.= $uncomp[$offset++];
                    ++$out;
                }
            }
            else {
                $uncomp .= $data[$in++];
                ++$out;
            }
        }

        return substr($uncomp, $length_preload);
    }

    /**
     * Parse RTF data and return the best plaintext representation we can.
     * Adapted from: http://webcheatsheet.com/php/reading_the_clean_text_from_rtf.php
     *
     * @param string $text The RTF (uncompressed) text.
     *
     * @return string The plain text.
     */
    public static function rtf2text($text)
    {
        $document = '';
        $stack    = [];
        $j        = -1;

        // Read the data character-by- character…
        for ($i = 0, $len = strlen($text); $i < $len; $i++) {
            $c = $text[$i];
            switch ($c) {
            case "\\":
                // Key Word
                $nextChar = $text[$i + 1];
                // If it is another backslash or nonbreaking space or hyphen,
                // then the character is plain text and add it to the output stream.
                if ($nextChar == "\\" && self::_rtfIsPlain($stack[$j])) {
                    $document .= "\\";
                }
                elseif ($nextChar == '~' && self::_rtfIsPlain($stack[$j])) {
                    $document .= ' ';
                }
                elseif ($nextChar == '_' && self::_rtfIsPlain($stack[$j])) {
                    $document .= '-';
                }
                elseif ($nextChar == '*') {
                    // Add to the stack.
                    $stack[$j]['*'] = true;
                }
                elseif ($nextChar == "'") {
                    // If it is a single quote, read next two characters that
                    // are the hexadecimal notation of a character we should add
                    // to the output stream.
                    $hex = substr($text, $i + 2, 2);

                    if (self::_rtfIsPlain($stack[$j])) {
                        $document .= html_entity_decode('&#' . hexdec($hex) .';');
                    }

                    //Shift the pointer.
                    $i += 2;
                }
                elseif ($nextChar >= 'a' && $nextChar <= 'z' || $nextChar >= 'A' && $nextChar <= 'Z') {
                    // Since, we’ve found the alphabetic character, the next
                    // characters are control words and, possibly, some digit
                    // parameter.
                    $word  = '';
                    $param = null;

                    // Start reading characters after the backslash.
                    for ($k = $i + 1, $m = 0; $k < strlen($text); $k++, $m++) {
                        $nextChar = $text[$k];
                        // If the current character is a letter and there were
                        // no digits before it, then we’re still reading the
                        // control word. If there were digits, we should stop
                        // since we reach the end of the control word.
                        if ($nextChar >= 'a' && $nextChar <= 'z'
                            || $nextChar >= 'A' && $nextChar <= 'Z') {
                            if (!empty($param)) {
                                break;
                            }
                            $word .= $nextChar;
                        }
                        elseif ($nextChar >= '0' && $nextChar <= '9') {
                            // If it is a digit, store the parameter.
                            $param .= $nextChar;
                        }
                        elseif ($nextChar == '-') {
                            // Since minus sign may occur only before a digit
                            // parameter, check whether $param is empty.
                            // Otherwise, we reach the end of the control word.
                            if (!empty($param)) {
                                break;
                            }
                            $param .= $nextChar;
                        }
                        else {
                            break;
                        }
                    }

                    // Shift the pointer on the number of read characters.
                    $i += $m - 1;

                    // Start analyzing.We are interested mostly in control words
                    $toText = '';

                    switch (strtolower($word)) {
                    // If the control word is "u", then its parameter is
                    // the decimal notation of the Unicode character that
                    // should be added to the output stream. We need to
                    // check whether the stack contains \ucN control word.
                    // If it does, we should remove the N characters from
                    // the output stream.
                    case 'u':
                        $toText .= html_entity_decode('&#x' . dechex($param) .';');
                        $ucDelta = @$stack[$j]['uc'];
                        if ($ucDelta > 0) {
                            $i += $ucDelta;
                        }
                        break;
                    case 'par':
                    case 'page':
                    case 'column':
                    case 'line':
                    case 'lbr':
                        $toText .= "\n";
                        break;
                    case 'emspace':
                    case 'enspace':
                    case 'qmspace':
                        $toText .= ' ';
                        break;
                    case 'tab':
                        $toText .= "\t";
                        break;
                    case 'chdate':
                        $toText .= date('m.d.Y');
                        break;
                    case 'chdpl':
                        $toText .= date('l, j F Y');
                        break;
                    case 'chdpa':
                        $toText .= date('D, j M Y');
                        break;
                    case 'chtime':
                        $toText .= date('H:i:s');
                        break;
                    case 'emdash':
                        $toText .= html_entity_decode('&mdash;');
                        break;
                    case 'endash':
                        $toText .= html_entity_decode('&ndash;');
                        break;
                    case 'bullet':
                        $toText .= html_entity_decode('&#149;');
                        break;
                    case 'lquote':
                        $toText .= html_entity_decode('&lsquo;');
                        break;
                    case 'rquote':
                        $toText .= html_entity_decode('&rsquo;');
                        break;
                    case 'ldblquote':
                        $toText .= html_entity_decode('&laquo;');
                        break;
                    case 'rdblquote':
                        $toText .= html_entity_decode('&raquo;');
                        break;
                    default:
                        $stack[$j][strtolower($word)] = empty($param) ? true : $param;
                        break;
                    }

                    // Add data to the output stream if required.
                    if (self::_rtfIsPlain($stack[$j])) {
                        $document .= $toText;
                    }
                }

                $i++;
                break;

            case '{':
                // New subgroup starts, add new stack element and write the data
                // from previous stack element to it.
                if (!empty($stack[$j])) {
                    array_push($stack, $stack[$j++]);
                }
                else {
                    $j++;
                }
                break;

            case '}':
                array_pop($stack);
                $j--;
                break;

            case '\0':
            case '\r':
            case '\f':
            case '\n':
                // Junk
                break;

            default:
                // Add other data to the output stream if required.
                if (!empty($stack[$j]) && self::_rtfIsPlain($stack[$j])) {
                    $document .= $c;
                }
                break;
            }
        }

        return $document;
    }

    /**
     * Checks if an RTF element is plain text
     */
    protected static function _rtfIsPlain($s)
    {
        $notPlain = ['*', 'fonttbl', 'colortbl', 'datastore', 'themedata', 'stylesheet'];

        for ($i = 0; $i < count($notPlain); $i++) {
            if (!empty($s[$notPlain[$i]])) {
                return false;
            }
        }

        return true;
    }
}
