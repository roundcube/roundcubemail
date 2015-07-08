<?php

/**
 +-------------------------------------------------------------------------+
 | Mail_mime wrapper for the Enigma Plugin                                 |
 |                                                                         |
 | Copyright (C) 2010-2015 The Roundcube Dev Team                          |
 |                                                                         |
 | Licensed under the GNU General Public License version 3 or              |
 | any later version with exceptions for skins & plugins.                  |
 | See the README file for a full license statement.                       |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+
*/

class enigma_mime_message extends Mail_mime
{
    const PGP_SIGNED    = 1;
    const PGP_ENCRYPTED = 2;

    protected $_type;
    protected $_message;
    protected $_body;
    protected $_signature;
    protected $_encrypted;


    /**
     * Object constructor
     *
     * @param Mail_mime Original message
     * @param int       Output message type
     */
    function __construct($message, $type)
    {
        $this->_message = $message;
        $this->_type    = $type;

        // clone parameters
        foreach (array_keys($this->_build_params) as $param) {
            $this->_build_params[$param] = $message->getParam($param);
        }

        // clone headers
        $this->_headers = $message->_headers;

/*
        if ($message->getParam('delay_file_io')) {
            // use common temp dir
            $temp_dir    = $this->config->get('temp_dir');
            $body_file   = tempnam($temp_dir, 'rcmMsg');
            $mime_result = $message->saveMessageBody($body_file);

            if (is_a($mime_result, 'PEAR_Error')) {
                self::raise_error(array('code' => 650, 'type' => 'php',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Could not create message: ".$mime_result->getMessage()),
                    true, false);
                return false;
            }

            $msg_body = fopen($body_file, 'r');
        }
        else {
*/
            // \r\n is must-have here
            $this->_body = $message->get() . "\r\n";
/*
        }
*/
    }

    /**
     * Check if the message is multipart (requires PGP/MIME)
     *
     * @return bool True if it is multipart, otherwise False
     */
    function isMultipart()
    {
        return $this->_message instanceof enigma_mime_message
            || !empty($this->_message->_parts) || $this->_message->getHTMLBody();
    }

    /**
     * Get e-mail address of message sender
     *
     * @return string Sender address
     */
    function getFromAddress()
    {
        // get sender address
        $headers = $this->_message->headers();
        $from    = rcube_mime::decode_address_list($headers['From'], 1, false, null, true);
        $from    = $from[1];

        return $from;
    }

    /**
     * Get recipients' e-mail addresses
     *
     * @return array Recipients' addresses
     */
    function getRecipients()
    {
        // get sender address
        $headers = $this->_message->headers();
        $to      = rcube_mime::decode_address_list($headers['To'], null, false, null, true);
        $cc      = rcube_mime::decode_address_list($headers['Cc'], null, false, null, true);
        $bcc     = rcube_mime::decode_address_list($headers['Bcc'], null, false, null, true);

        $recipients = array_unique(array_merge($to, $cc, $bcc));
        $recipients = array_diff($recipients, array('undisclosed-recipients:'));

        return $recipients;
    }

    /**
     * Get original message body, to be encrypted/signed
     *
     * @return string Message body
     */
    function getOrigBody()
    {
        $_headers = $this->_message->headers();
        $headers  = array();

        if ($_headers['Content-Transfer-Encoding']) {
            $headers[] = 'Content-Transfer-Encoding: ' . $_headers['Content-Transfer-Encoding'];
        }
        $headers[] = 'Content-Type: ' . $_headers['Content-Type'];

        return implode("\r\n", $headers) . "\r\n\r\n" . $this->_body;
    }

    /**
     * Register signature attachment
     *
     * @param string Signature body
     */
    function addPGPSignature($body)
    {
        $this->_signature = $body;
    }

    /**
     * Register encrypted body
     *
     * @param string Encrypted body
     */
    function setPGPEncryptedBody($body)
    {
        $this->_encrypted = $body;
    }

    /**
     * Builds the multipart message.
     *
     * @param array    $params    Build parameters that change the way the email
     *                            is built. Should be associative. See $_build_params.
     * @param resource $filename  Output file where to save the message instead of
     *                            returning it
     * @param boolean  $skip_head True if you want to return/save only the message
     *                            without headers
     *
     * @return mixed The MIME message content string, null or PEAR error object
     * @access public
     */
    function get($params = null, $filename = null, $skip_head = false)
    {
        if (isset($params)) {
            while (list($key, $value) = each($params)) {
                $this->_build_params[$key] = $value;
            }
        }

        $this->_checkParams();

        if ($this->_type == self::PGP_SIGNED) {
            $body   = "This is an OpenPGP/MIME signed message (RFC 4880 and 3156)";
            $params = array(
                'content_type' => "multipart/signed; micalg=pgp-sha1; protocol=\"application/pgp-signature\"",
                'eol'          => $this->_build_params['eol'],
            );

            $message = new Mail_mimePart($body, $params);

            if (!empty($this->_body)) {
                $headers = $this->_message->headers();
                $params  = array('content_type' => $headers['Content-Type']);

                if ($headers['Content-Transfer-Encoding']) {
                    $params['encoding'] = $headers['Content-Transfer-Encoding'];
                }

                $message->addSubpart($this->_body, $params);
            }

            if (!empty($this->_signature)) {
                $message->addSubpart($this->_signature, array(
                    'filename'     => 'signature.asc',
                    'content_type' => 'application/pgp-signature',
                    'disposition'  => 'attachment',
                    'description'  => 'OpenPGP digital signature',
                ));
            }
        }
        else if ($this->_type == self::PGP_ENCRYPTED) {
            $body   = "This is an OpenPGP/MIME encrypted message (RFC 4880 and 3156)";
            $params = array(
                'content_type' => "multipart/encrypted; protocol=\"application/pgp-encrypted\"",
                'eol'          => $this->_build_params['eol'],
            );

            $message = new Mail_mimePart($body, $params);

            $message->addSubpart('Version: 1', array(
                    'content_type' => 'application/pgp-encrypted',
                    'description'  => 'PGP/MIME version identification',
            ));

            $message->addSubpart($this->_encrypted, array(
                    'content_type' => 'application/octet-stream',
                    'description'  => 'PGP/MIME encrypted message',
                    'disposition'  => 'inline',
                    'filename'     => 'encrypted.asc',
            ));
        }

        // Use saved boundary
        if (!empty($this->_build_params['boundary'])) {
            $boundary = $this->_build_params['boundary'];
        }
        else {
            $boundary = null;
        }

        // Write output to file
        if ($filename) {
            // Append mimePart message headers and body into file
            $headers = $message->encodeToFile($filename, $boundary, $skip_head);
            if ($this->_isError($headers)) {
                return $headers;
            }
            $this->_headers = array_merge($this->_headers, $headers);
            return null;
        }
        else {
            $output = $message->encode($boundary, $skip_head);
            if ($this->_isError($output)) {
                return $output;
            }
            $this->_headers = array_merge($this->_headers, $output['headers']);
            return $output['body'];
        }
    }

    /**
     * Get Content-Type and Content-Transfer-Encoding headers of the message
     *
     * @return array Headers array
     * @access private
     */
    function _contentHeaders()
    {
        $this->_checkParams();

        $eol = !empty($this->_build_params['eol']) ? $this->_build_params['eol'] : "\r\n";

        // multipart message: and boundary
        if (!empty($this->_build_params['boundary'])) {
            $boundary = $this->_build_params['boundary'];
        }
        else if (!empty($this->_headers['Content-Type'])
            && preg_match('/boundary="([^"]+)"/', $this->_headers['Content-Type'], $m)
        ) {
            $boundary = $m[1];
        }
        else {
            $boundary = '=_' . md5(rand() . microtime());
        }

        $this->_build_params['boundary'] = $boundary;

        if ($this->_type == self::PGP_SIGNED) {
            $headers['Content-Type'] = "multipart/signed; micalg=pgp-sha1;$eol"
                ." protocol=\"application/pgp-signature\";$eol"
                ." boundary=\"$boundary\"";
        }
        else if ($this->_type == self::PGP_ENCRYPTED) {
            $headers['Content-Type'] = "multipart/encrypted;$eol"
                ." protocol=\"application/pgp-encrypted\";$eol"
                ." boundary=\"$boundary\"";
        }

        return $headers;
    }
}
