<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Class representing a message part                                   |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * Class representing a message part
 */
class rcube_message_part
{
    /**
     * Part MIME identifier
     *
     * @var string
     */
    public $mime_id = '';

    /**
     * Content main type
     *
     * @var string
     */
    public $ctype_primary = 'text';

    /**
     * Content subtype
     *
     * @var string
     */
    public $ctype_secondary = 'plain';

    /**
     * Full content type
     *
     * @var string
     */
    public $mimetype = 'text/plain';

    /**
     * Real content type (for fake parts)
     *
     * @var string|null
     */
    public $realtype;

    /**
     * Real content type of a message/rfc822 part
     *
     * @var string
     */
    public $real_mimetype = '';

    /**
     * Part size in bytes
     *
     * @var int
     */
    public $size = 0;

    /**
     * Is the $size exact or approximate
     *
     * @var bool
     */
    public $exact_size = false;

    /**
     * Part body
     *
     * @var string|null
     */
    public $body;

    /**
     * Part headers
     *
     * @var array
     */
    public $headers = [];

    /**
     * Sub-Parts
     *
     * @var array<rcube_message_part>
     */
    public $parts = [];

    /**
     * Part Content-Id
     *
     * @var string|null
     */
    public $content_id;

    /**
     * Part Content-Location
     *
     * @var string|null
     */
    public $content_location;

    public $type;
    public $replaces = [];
    public $disposition = '';
    public $filename = '';
    public $encoding = '8bit';
    public $charset = '';
    public $d_parameters = [];
    public $ctype_parameters = [];
    public $body_modified = false;
    public $need_decryption = false;

    /**
     * Clone handler.
     */
    public function __clone()
    {
        foreach ($this->parts as $idx => $part) {
            $this->parts[$idx] = clone $part;
        }
    }

    /**
     * Normalize and set some part properties from the structure or raw headers
     *
     * @param string|rcube_message_header|null $headers Part's raw headers
     *
     * @return string Attachment file name
     */
    public function normalize($headers = null)
    {
        // Some IMAP servers do not support RFC2231, if we have
        // part headers we'll get attachment name from them, not the BODYSTRUCTURE
        $rfc2231_params = [];
        if (!empty($headers) || !empty($this->headers)) {
            if (is_object($headers)) {
                $headers = get_object_vars($headers);
            } else {
                $headers = !empty($headers) ? rcube_mime::parse_headers($headers) : $this->headers;
            }

            $ctype = $headers['content-type'] ?? '';
            $disposition = $headers['content-disposition'] ?? '';
            $tokens = preg_split('/;[\s\r\n\t]*/', $ctype . ';' . $disposition);

            foreach ($tokens as $token) {
                // TODO: Use order defined by the parameter name not order of occurrence in the header
                if (preg_match('/^(name|filename)\*([0-9]*)\*?="*([^"]+)"*/i', $token, $matches)) {
                    $key = strtolower($matches[1]);
                    $rfc2231_params[$key] = ($rfc2231_params[$key] ?? '') . $matches[3];
                }
            }
        }

        // Why the order below?
        // 1. 'name' maybe truncated, but 'filename' not (seen in an email from Thunderbird)
        // 2. RFC2231 is most-reliable

        if (isset($rfc2231_params['filename'])) {
            $filename_encoded = $rfc2231_params['filename'];
        } elseif (isset($rfc2231_params['name'])) {
            $filename_encoded = $rfc2231_params['name'];
        } elseif (isset($this->d_parameters['filename*'])) {
            $filename_encoded = $this->d_parameters['filename*'];
        } elseif (isset($this->ctype_parameters['name*'])) {
            $filename_encoded = $this->ctype_parameters['name*'];
        } elseif (!empty($this->d_parameters['filename'])) {
            $filename_mime = $this->d_parameters['filename'];
        } elseif (!empty($this->ctype_parameters['name'])) {
            $filename_mime = $this->ctype_parameters['name'];
        } elseif (!empty($this->headers['content-description'])) {
            $filename_mime = $this->headers['content-description'];
        }

        // decode filename
        if (isset($filename_encoded)) {
            // decode filename according to RFC 2231, Section 4
            if (preg_match("/^([^']*)'[^']*'(.*)$/", $filename_encoded, $fmatches)) {
                $filename_charset = $fmatches[1];
                $filename_encoded = $fmatches[2];
            }

            $this->filename = rawurldecode($filename_encoded);

            if (!empty($filename_charset)) {
                $this->filename = rcube_charset::convert($this->filename, $filename_charset);
            }
        } elseif (isset($filename_mime)) {
            // Note: Do not use charset of part/message nor the default charset (#9376)
            $this->filename = rcube_mime::decode_mime_string($filename_mime, false);
        }

        // Workaround for invalid Content-Type (#6816)
        // Some servers for "Content-Type: PDF; name=test.pdf" may return text/plain and ignore name argument
        if ($this->mimetype == 'text/plain' && !empty($headers['content-type'])) {
            $tokens = preg_split('/;[\s\r\n\t]*/', $headers['content-type']);
            $type = rcube_mime::fix_mimetype($tokens[0]);

            if ($type != $this->mimetype) {
                $this->mimetype = $type;
                [$this->ctype_primary, $this->ctype_secondary] = explode('/', $this->mimetype);
            }
        }

        return $this->filename;
    }
}
