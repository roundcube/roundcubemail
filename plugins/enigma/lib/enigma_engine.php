<?php

/**
 +-------------------------------------------------------------------------+
 | Engine of the Enigma Plugin                                             |
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

/**
 * Enigma plugin engine.
 *
 * RFC2440: OpenPGP Message Format
 * RFC3156: MIME Security with OpenPGP
 * RFC3851: S/MIME
 */
class enigma_engine
{
    private $rc;
    private $enigma;
    private $pgp_driver;
    private $smime_driver;
    private $password_time;

    public $decryptions     = array();
    public $signatures      = array();
    public $encrypted_parts = array();

    const SIGN_MODE_BODY     = 1;
    const SIGN_MODE_SEPARATE = 2;
    const SIGN_MODE_MIME     = 3;

    const ENCRYPT_MODE_BODY = 1;
    const ENCRYPT_MODE_MIME = 2;


    /**
     * Plugin initialization.
     */
    function __construct($enigma)
    {
        $this->rc     = rcmail::get_instance();
        $this->enigma = $enigma;

        $this->password_time = $this->rc->config->get('enigma_password_time') * 60;

        // this will remove passwords from session after some time
        if ($this->password_time) {
            $this->get_passwords();
        }
    }

    /**
     * PGP driver initialization.
     */
    function load_pgp_driver()
    {
        if ($this->pgp_driver) {
            return;
        }

        $driver   = 'enigma_driver_' . $this->rc->config->get('enigma_pgp_driver', 'gnupg');
        $username = $this->rc->user->get_username();

        // Load driver
        $this->pgp_driver = new $driver($username);

        if (!$this->pgp_driver) {
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: Unable to load PGP driver: $driver"
            ), true, true);
        }

        // Initialise driver
        $result = $this->pgp_driver->init();

        if ($result instanceof enigma_error) {
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: ".$result->getMessage()
            ), true, true);
        }
    }

    /**
     * S/MIME driver initialization.
     */
    function load_smime_driver()
    {
        if ($this->smime_driver) {
            return;
        }

        $driver   = 'enigma_driver_' . $this->rc->config->get('enigma_smime_driver', 'phpssl');
        $username = $this->rc->user->get_username();

        // Load driver
        $this->smime_driver = new $driver($username);

        if (!$this->smime_driver) {
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: Unable to load S/MIME driver: $driver"
            ), true, true);
        }

        // Initialise driver
        $result = $this->smime_driver->init();

        if ($result instanceof enigma_error) {
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: ".$result->getMessage()
            ), true, true);
        }
    }

    /**
     * Handler for message signing
     *
     * @param Mail_mime Original message
     * @param int       Encryption mode
     *
     * @return enigma_error On error returns error object
     */
    function sign_message(&$message, $mode = null)
    {
        $mime = new enigma_mime_message($message, enigma_mime_message::PGP_SIGNED);
        $from = $mime->getFromAddress();

        // find private key
        $key = $this->find_key($from, true);

        if (empty($key)) {
            return new enigma_error(enigma_error::KEYNOTFOUND);
        }

        // check if we have password for this key
        $passwords = $this->get_passwords();
        $pass      = $passwords[$key->id];

        if ($pass === null) {
            // ask for password
            $error = array('missing' => array($key->id => $key->name));
            return new enigma_error(enigma_error::BADPASS, '', $error);
        }

        // select mode
        switch ($mode) {
        case self::SIGN_MODE_BODY:
            $pgp_mode = Crypt_GPG::SIGN_MODE_CLEAR;
            break;

        case self::SIGN_MODE_MIME:
            $pgp_mode = Crypt_GPG::SIGN_MODE_DETACHED;
            break;
/*
        case self::SIGN_MODE_SEPARATE:
            $pgp_mode = Crypt_GPG::SIGN_MODE_NORMAL;
            break;
*/
        default:
            if ($mime->isMultipart()) {
                $pgp_mode = Crypt_GPG::SIGN_MODE_DETACHED;
            }
            else {
                $pgp_mode = Crypt_GPG::SIGN_MODE_CLEAR;
            }
        }

        // get message body
        if ($pgp_mode == Crypt_GPG::SIGN_MODE_CLEAR) {
            // in this mode we'll replace text part
            // with the one containing signature
            $body = $message->getTXTBody();

            $text_charset = $message->getParam('text_charset');
            $line_length  = $this->rc->config->get('line_length', 72);

            // We can't use format=flowed for signed messages
            if (strpos($text_charset, 'format=flowed')) {
                list($charset, $params) = explode(';', $text_charset);
                $body = rcube_mime::unfold_flowed($body);
                $body = rcube_mime::wordwrap($body, $line_length, "\r\n", false, $charset);

                $text_charset = str_replace(";\r\n format=flowed", '', $text_charset);
            }
        }
        else {
            // here we'll build PGP/MIME message
            $body = $mime->getOrigBody();
        }

        // sign the body
        $result = $this->pgp_sign($body, $key->id, $pass, $pgp_mode);

        if ($result !== true) {
            if ($result->getCode() == enigma_error::BADPASS) {
                // ask for password
                $error = array('bad' => array($key->id => $key->name));
                return new enigma_error(enigma_error::BADPASS, '', $error);
            }

            return $result;
        }

        // replace message body
        if ($pgp_mode == Crypt_GPG::SIGN_MODE_CLEAR) {
            $message->setTXTBody($body);
            $message->setParam('text_charset', $text_charset);
        }
        else {
            $mime->addPGPSignature($body);
            $message = $mime;
        }
    }

    /**
     * Handler for message encryption
     *
     * @param Mail_mime Original message
     * @param int       Encryption mode
     * @param bool      Is draft-save action - use only sender's key for encryption
     *
     * @return enigma_error On error returns error object
     */
    function encrypt_message(&$message, $mode = null, $is_draft = false)
    {
        $mime = new enigma_mime_message($message, enigma_mime_message::PGP_ENCRYPTED);

        // always use sender's key
        $recipients = array($mime->getFromAddress());

        // if it's not a draft we add all recipients' keys
        if (!$is_draft) {
            $recipients = array_merge($recipients, $mime->getRecipients());
        }

        if (empty($recipients)) {
            return new enigma_error(enigma_error::KEYNOTFOUND);
        }

        $recipients = array_unique($recipients);

        // find recipient public keys
        foreach ((array) $recipients as $email) {
            $key = $this->find_key($email);

            if (empty($key)) {
                return new enigma_error(enigma_error::KEYNOTFOUND, '', array(
                    'missing' => $email
                ));
            }

            $keys[] = $key->id;
        }

        // select mode
        switch ($mode) {
        case self::ENCRYPT_MODE_BODY:
            $encrypt_mode = $mode;
            break;

        case self::ENCRYPT_MODE_MIME:
            $encrypt_mode = $mode;
            break;

        default:
            $encrypt_mode = $mime->isMultipart() ? self::ENCRYPT_MODE_MIME : self::ENCRYPT_MODE_BODY;
        }

        // get message body
        if ($encrypt_mode == self::ENCRYPT_MODE_BODY) {
            // in this mode we'll replace text part
            // with the one containing encrypted message
            $body = $message->getTXTBody();
        }
        else {
            // here we'll build PGP/MIME message
            $body = $mime->getOrigBody();
        }

        // sign the body
        $result = $this->pgp_encrypt($body, $keys);

        if ($result !== true) {
            return $result;
        }

        // replace message body
        if ($encrypt_mode == self::ENCRYPT_MODE_BODY) {
            $message->setTXTBody($body);
        }
        else {
            $mime->setPGPEncryptedBody($body);
            $message = $mime;
        }
    }

    /**
     * Handler for message_part_structure hook.
     * Called for every part of the message.
     *
     * @param array  Original parameters
     * @param string Part body (will be set if used internally)
     *
     * @return array Modified parameters
     */
    function part_structure($p, $body = null)
    {
        if ($p['mimetype'] == 'text/plain' || $p['mimetype'] == 'application/pgp') {
            $this->parse_plain($p, $body);
        }
        else if ($p['mimetype'] == 'multipart/signed') {
            $this->parse_signed($p, $body);
        }
        else if ($p['mimetype'] == 'multipart/encrypted') {
            $this->parse_encrypted($p);
        }
        else if ($p['mimetype'] == 'application/pkcs7-mime') {
            $this->parse_encrypted($p);
        }

        return $p;
    }

    /**
     * Handler for message_part_body hook.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function part_body($p)
    {
        // encrypted attachment, see parse_plain_encrypted()
        if ($p['part']->need_decryption && $p['part']->body === null) {
            $this->load_pgp_driver();

            $storage = $this->rc->get_storage();
            $body    = $storage->get_message_part($p['object']->uid, $p['part']->mime_id, $p['part'], null, null, true, 0, false);
            $result  = $this->pgp_decrypt($body);

            // @TODO: what to do on error?
            if ($result === true) {
                $p['part']->body = $body;
                $p['part']->size = strlen($body);
                $p['part']->body_modified = true;
            }
        }

        return $p;
    }

    /**
     * Handler for plain/text message.
     *
     * @param array  Reference to hook's parameters
     * @param string Part body (will be set if used internally)
     */
    function parse_plain(&$p, $body = null)
    {
        $part = $p['structure'];

        // exit, if we're already inside a decrypted message
        if (in_array($part->mime_id, $this->encrypted_parts)) {
            return;
        }

        // Get message body from IMAP server
        if ($body === null) {
            $body = $this->get_part_body($p['object'], $part);
        }

        // @TODO: big message body could be a file resource
        // PGP signed message
        if (preg_match('/-----BEGIN PGP SIGNED MESSAGE-----/', $body)) {
            $this->parse_plain_signed($p, $body);
        }
        // PGP encrypted message
        else if (preg_match('/-----BEGIN PGP MESSAGE-----/', $body)) {
            $this->parse_plain_encrypted($p, $body);
        }
    }

    /**
     * Handler for multipart/signed message.
     *
     * @param array  Reference to hook's parameters
     * @param string Part body (will be set if used internally)
     */
    function parse_signed(&$p, $body = null)
    {
        $struct = $p['structure'];

        // S/MIME
        if ($struct->parts[1] && $struct->parts[1]->mimetype == 'application/pkcs7-signature') {
            $this->parse_smime_signed($p, $body);
        }
        // PGP/MIME: RFC3156
        // The multipart/signed body MUST consist of exactly two parts.
        // The first part contains the signed data in MIME canonical format,
        // including a set of appropriate content headers describing the data.
        // The second body MUST contain the PGP digital signature.  It MUST be
        // labeled with a content type of "application/pgp-signature".
        else if (count($struct->parts) == 2
            && $struct->parts[1] && $struct->parts[1]->mimetype == 'application/pgp-signature'
        ) {
            $this->parse_pgp_signed($p, $body);
        }
    }

    /**
     * Handler for multipart/encrypted message.
     *
     * @param array Reference to hook's parameters
     */
    function parse_encrypted(&$p)
    {
        $struct = $p['structure'];

        // S/MIME
        if ($p['mimetype'] == 'application/pkcs7-mime') {
            $this->parse_smime_encrypted($p);
        }
        // PGP/MIME: RFC3156
        // The multipart/encrypted MUST consist of exactly two parts. The first
        // MIME body part must have a content type of "application/pgp-encrypted".
        // This body contains the control information.
        // The second MIME body part MUST contain the actual encrypted data.  It
        // must be labeled with a content type of "application/octet-stream".
        else if (count($struct->parts) == 2
            && $struct->parts[0] && $struct->parts[0]->mimetype == 'application/pgp-encrypted'
            && $struct->parts[1] && $struct->parts[1]->mimetype == 'application/octet-stream'
        ) {
            $this->parse_pgp_encrypted($p);
        }
    }

    /**
     * Handler for plain signed message.
     * Excludes message and signature bodies and verifies signature.
     *
     * @param array  Reference to hook's parameters
     * @param string Message (part) body
     */
    private function parse_plain_signed(&$p, $body)
    {
        if (!$this->rc->config->get('enigma_signatures', true)) {
            return;
        }

        $this->load_pgp_driver();
        $part = $p['structure'];

        // Verify signature
        if ($this->rc->action == 'show' || $this->rc->action == 'preview' || $this->rc->action == 'print') {
            $sig = $this->pgp_verify($body);
        }

        // @TODO: Handle big bodies using (temp) files

        // In this way we can use fgets on string as on file handle
        $fh = fopen('php://memory', 'br+');
        // @TODO: fopen/fwrite errors handling
        if ($fh) {
            fwrite($fh, $body);
            rewind($fh);
        }

        $body = $part->body = null;
        $part->body_modified = true;

        // Extract body (and signature?)
        while (!feof($fh)) {
            $line = fgets($fh, 1024);

            if ($part->body === null)
                $part->body = '';
            else if (preg_match('/-----BEGIN PGP SIGNATURE-----/', $line))
                break;
            else
                $part->body .= $line;
        }

        // Remove "Hash" Armor Headers
        $part->body = preg_replace('/^.*\r*\n\r*\n/', '', $part->body);
        // de-Dash-Escape (RFC2440)
        $part->body = preg_replace('/(^|\n)- -/', '\\1-', $part->body);

        // Store signature data for display
        if (!empty($sig)) {
            $this->signatures[$part->mime_id] = $sig;
        }

        fclose($fh);
    }

    /**
     * Handler for PGP/MIME signed message.
     * Verifies signature.
     *
     * @param array  Reference to hook's parameters
     * @param string Part body (will be set if used internally)
     */
    private function parse_pgp_signed(&$p, $body = null)
    {
        if (!$this->rc->config->get('enigma_signatures', true)) {
            return;
        }

        if ($this->rc->action != 'show' && $this->rc->action != 'preview' && $this->rc->action != 'print') {
            return;
        }

        $this->load_pgp_driver();
        $struct = $p['structure'];

        $msg_part = $struct->parts[0];
        $sig_part = $struct->parts[1];

        // Get bodies
        // Note: The first part body need to be full part body with headers
        //       it also cannot be decoded
        if ($body !== null) {
            // set signed part body
            list($msg_body, $sig_body) = $this->explode_signed_body($body, $struct->ctype_parameters['boundary']);
        }
        else {
            $msg_body = $this->get_part_body($p['object'], $msg_part, true);
            $sig_body = $this->get_part_body($p['object'], $sig_part);
        }

        // Verify
        $sig = $this->pgp_verify($msg_body, $sig_body);

        // Store signature data for display
        $this->signatures[$struct->mime_id] = $sig;
        $this->signatures[$msg_part->mime_id] = $sig;
    }

    /**
     * Handler for S/MIME signed message.
     * Verifies signature.
     *
     * @param array  Reference to hook's parameters
     * @param string Part body (will be set if used internally)
     */
    private function parse_smime_signed(&$p, $body = null)
    {
        if (!$this->rc->config->get('enigma_signatures', true)) {
            return;
        }

        // @TODO
    }

    /**
     * Handler for plain encrypted message.
     *
     * @param array  Reference to hook's parameters
     * @param string Message (part) body
     */
    private function parse_plain_encrypted(&$p, $body)
    {
        if (!$this->rc->config->get('enigma_decryption', true)) {
            return;
        }

        $this->load_pgp_driver();
        $part = $p['structure'];

        // Decrypt
        $result = $this->pgp_decrypt($body);

        // Store decryption status
        $this->decryptions[$part->mime_id] = $result;

        // find parent part ID
        if (strpos($part->mime_id, '.')) {
            $items = explode('.', $part->mime_id);
            array_pop($items);
            $parent = implode('.', $items);
        }
        else {
            $parent = 0;
        }

        // Parse decrypted message
        if ($result === true) {
            $part->body          = $body;
            $part->body_modified = true;

            // Remember it was decrypted
            $this->encrypted_parts[] = $part->mime_id;

            // PGP signed inside? verify signature
            if (preg_match('/-----BEGIN PGP SIGNED MESSAGE-----/', $body)) {
                $this->parse_plain_signed($p, $body);
            }

            // Encrypted plain message may contain encrypted attachments
            // in such case attachments have .pgp extension and type application/octet-stream.
            // This is what happens when you select "Encrypt each attachment separately
            // and send the message using inline PGP" in Thunderbird's Enigmail.

            if ($p['object']->mime_parts[$parent]) {
                foreach ((array)$p['object']->mime_parts[$parent]->parts as $p) {
                    if ($p->disposition == 'attachment' && $p->mimetype == 'application/octet-stream'
                        && preg_match('/^(.*)\.pgp$/i', $p->filename, $m)
                    ) {
                        // modify filename
                        $p->filename = $m[1];
                        // flag the part, it will be decrypted when needed
                        $p->need_decryption = true;
                        // disable caching
                        $p->body_modified = true;
                    }
                }
            }
        }
        // decryption failed, but the message may have already
        // been cached with the modified parts (see above),
        // let's bring the original state back
        else if ($p['object']->mime_parts[$parent]) {
            foreach ((array)$p['object']->mime_parts[$parent]->parts as $p) {
                if ($p->need_decryption && !preg_match('/^(.*)\.pgp$/i', $p->filename, $m)) {
                    // modify filename
                    $p->filename .= '.pgp';
                    // flag the part, it will be decrypted when needed
                    unset($p->need_decryption);
                }
            }
        }
    }

    /**
     * Handler for PGP/MIME encrypted message.
     *
     * @param array Reference to hook's parameters
     */
    private function parse_pgp_encrypted(&$p)
    {
        if (!$this->rc->config->get('enigma_decryption', true)) {
            return;
        }

        $this->load_pgp_driver();

        $struct = $p['structure'];
        $part   = $struct->parts[1];

        // Get body
        $body = $this->get_part_body($p['object'], $part);

        // Decrypt
        $result = $this->pgp_decrypt($body);

        // Stripping out the signature part because it is not managed yet
        $body = preg_replace("/Content-Type: multipart\/signed;(.+?)Content-Type: multipart\//s","Content-Type: multipart/",$body);
        $body = preg_replace("/^(.+)--=_(.+?)Content-Type: application\/pgp-signature;(.+)$/s","$1",$body);

        if ($result === true) {
            // Parse decrypted message
            $struct = $this->parse_body($body);

            // Modify original message structure
            $this->modify_structure($p, $struct, strlen($body));

            // Parse the structure (there may be encrypted/signed parts inside
            $this->part_structure(array(
                    'object'    => $p['object'],
                    'structure' => $struct,
                    'mimetype'  => $struct->mimetype
                ), $body);

            // Attach the decryption message to all parts
            $this->decryptions[$struct->mime_id] = $result;
            foreach ((array) $struct->parts as $sp) {
                $this->decryptions[$sp->mime_id] = $result;
                if  ($sp->mime_id == 2) { $this->decryptions['1.2'] = $result; } // We need to display the message even in case of attachments
            }
        }
        else {
            $this->decryptions[$part->mime_id] = $result;

            // Make sure decryption status message will be displayed
            $part->type = 'content';
            $p['object']->parts[] = $part;

            // don't show encrypted part on attachments list
            // don't show "cannot display encrypted message" text
            $p['abort'] = true;
        }
    }

    /**
     * Handler for S/MIME encrypted message.
     *
     * @param array Reference to hook's parameters
     */
    private function parse_smime_encrypted(&$p)
    {
        if (!$this->rc->config->get('enigma_decryption', true)) {
            return;
        }

        // @TODO
    }

    /**
     * PGP signature verification.
     *
     * @param mixed Message body
     * @param mixed Signature body (for MIME messages)
     *
     * @return mixed enigma_signature or enigma_error
     */
    private function pgp_verify(&$msg_body, $sig_body=null)
    {
        // @TODO: Handle big bodies using (temp) files
        $sig = $this->pgp_driver->verify($msg_body, $sig_body);

        if (($sig instanceof enigma_error) && $sig->getCode() != enigma_error::KEYNOTFOUND)
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: " . $sig->getMessage()
                ), true, false);

        return $sig;
    }

    /**
     * PGP message decryption.
     *
     * @param mixed Message body
     *
     * @return mixed True or enigma_error
     */
    private function pgp_decrypt(&$msg_body)
    {
        // @TODO: Handle big bodies using (temp) files
        $keys   = $this->get_passwords();
        $result = $this->pgp_driver->decrypt($msg_body, $keys);

        if ($result instanceof enigma_error) {
            $err_code = $result->getCode();
            if (!in_array($err_code, array(enigma_error::KEYNOTFOUND, enigma_error::BADPASS)))
                rcube::raise_error(array(
                    'code' => 600, 'type' => 'php',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Enigma plugin: " . $result->getMessage()
                    ), true, false);
            return $result;
        }

        $msg_body = $result;

        return true;
    }

    /**
     * PGP message signing
     *
     * @param mixed  Message body
     * @param string Key ID
     * @param string Key passphrase
     * @param int    Signing mode
     *
     * @return mixed True or enigma_error
     */
    private function pgp_sign(&$msg_body, $keyid, $password, $mode = null)
    {
        // @TODO: Handle big bodies using (temp) files
        $result = $this->pgp_driver->sign($msg_body, $keyid, $password, $mode);

        if ($result instanceof enigma_error) {
            $err_code = $result->getCode();
            if (!in_array($err_code, array(enigma_error::KEYNOTFOUND, enigma_error::BADPASS)))
                rcube::raise_error(array(
                    'code' => 600, 'type' => 'php',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Enigma plugin: " . $result->getMessage()
                    ), true, false);
            return $result;
        }

        $msg_body = $result;

        return true;
    }

    /**
     * PGP message encrypting
     *
     * @param mixed Message body
     * @param array Keys
     *
     * @return mixed True or enigma_error
     */
    private function pgp_encrypt(&$msg_body, $keys)
    {
        // @TODO: Handle big bodies using (temp) files
        $result = $this->pgp_driver->encrypt($msg_body, $keys);

        if ($result instanceof enigma_error) {
            $err_code = $result->getCode();
            if (!in_array($err_code, array(enigma_error::KEYNOTFOUND, enigma_error::BADPASS)))
                rcube::raise_error(array(
                    'code' => 600, 'type' => 'php',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Enigma plugin: " . $result->getMessage()
                    ), true, false);
            return $result;
        }

        $msg_body = $result;

        return true;
    }

    /**
     * PGP keys listing.
     *
     * @param mixed Key ID/Name pattern
     *
     * @return mixed Array of keys or enigma_error
     */
    function list_keys($pattern = '')
    {
        $this->load_pgp_driver();
        $result = $this->pgp_driver->list_keys($pattern);

        if ($result instanceof enigma_error) {
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: " . $result->getMessage()
                ), true, false);
        }

        return $result;
    }

    /**
     * Find PGP private/public key
     *
     * @param string E-mail address
     * @param bool   Need a key for signing?
     *
     * @return enigma_key The key
     */
    function find_key($email, $can_sign = false)
    {
        $this->load_pgp_driver();
        $result = $this->pgp_driver->list_keys($email);

        if ($result instanceof enigma_error) {
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: " . $result->getMessage()
                ), true, false);

            return;
        }

        $mode = $can_sign ? enigma_key::CAN_SIGN : enigma_key::CAN_ENCRYPT;

        // check key validity and type
        foreach ($result as $key) {
            if ($keyid = $key->find_subkey($email, $mode)) {
                return $key;
            }
        }
    }

    /**
     * PGP key details.
     *
     * @param mixed Key ID
     *
     * @return mixed enigma_key or enigma_error
     */
    function get_key($keyid)
    {
        $this->load_pgp_driver();
        $result = $this->pgp_driver->get_key($keyid);

        if ($result instanceof enigma_error) {
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: " . $result->getMessage()
                ), true, false);
        }

        return $result;
    }

    /**
     * PGP key delete.
     *
     * @param string Key ID
     *
     * @return enigma_error|bool True on success
     */
    function delete_key($keyid)
    {
        $this->load_pgp_driver();
        $result = $this->pgp_driver->delete_key($keyid);

        if ($result instanceof enigma_error) {
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: " . $result->getMessage()
                ), true, false);
        }

        return $result;
    }

    /**
     * PGP keys pair generation.
     *
     * @param array Key pair parameters
     *
     * @return mixed enigma_key or enigma_error
     */
    function generate_key($data)
    {
        $this->load_pgp_driver();
        $result = $this->pgp_driver->gen_key($data);

        if ($result instanceof enigma_error) {
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: " . $result->getMessage()
                ), true, false);
        }

        return $result;
    }

    /**
     * PGP keys/certs importing.
     *
     * @param mixed   Import file name or content
     * @param boolean True if first argument is a filename
     *
     * @return mixed Import status data array or enigma_error
     */
    function import_key($content, $isfile=false)
    {
        $this->load_pgp_driver();
        $result = $this->pgp_driver->import($content, $isfile);

        if ($result instanceof enigma_error) {
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: " . $result->getMessage()
                ), true, false);
        }
        else {
            $result['imported'] = $result['public_imported'] + $result['private_imported'];
            $result['unchanged'] = $result['public_unchanged'] + $result['private_unchanged'];
        }

        return $result;
    }

    /**
     * Handler for keys/certs import request action
     */
    function import_file()
    {
        $uid     = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST);
        $mbox    = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_POST);
        $mime_id = rcube_utils::get_input_value('_part', rcube_utils::INPUT_POST);
        $storage = $this->rc->get_storage();

        if ($uid && $mime_id) {
            $storage->set_folder($mbox);
            $part = $storage->get_message_part($uid, $mime_id);
        }

        if ($part && is_array($result = $this->import_key($part))) {
            $this->rc->output->show_message('enigma.keysimportsuccess', 'confirmation',
                array('new' => $result['imported'], 'old' => $result['unchanged']));
        }
        else
            $this->rc->output->show_message('enigma.keysimportfailed', 'error');

        $this->rc->output->send();
    }

    /**
     * PGP keys/certs export..
     *
     * @param string   Key ID
     * @param resource Optional output stream
     *
     * @return mixed Key content or enigma_error
     */
    function export_key($key, $fp = null)
    {
        $this->load_pgp_driver();
        $result = $this->pgp_driver->export($key, $fp);

        if ($result instanceof enigma_error) {
            rcube::raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: " . $result->getMessage()
                ), true, false);

            return $result;
        }

        if ($fp) {
            fwrite($fp, $result);
        }
        else {
            return $result;
        }
    }

    /**
     * Registers password for specified key/cert sent by the password prompt.
     */
    function password_handler()
    {
        $keyid  = rcube_utils::get_input_value('_keyid', rcube_utils::INPUT_POST);
        $passwd = rcube_utils::get_input_value('_passwd', rcube_utils::INPUT_POST, true);

        if ($keyid && $passwd !== null && strlen($passwd)) {
            $this->save_password($keyid, $passwd);
        }
    }

    /**
     * Saves key/cert password in user session
     */
    function save_password($keyid, $password)
    {
        // we store passwords in session for specified time
        if ($config = $_SESSION['enigma_pass']) {
            $config = $this->rc->decrypt($config);
            $config = @unserialize($config);
        }

        $config[$keyid] = array($password, time());

        $_SESSION['enigma_pass'] = $this->rc->encrypt(serialize($config));
    }

    /**
     * Returns currently stored passwords
     */
    function get_passwords()
    {
        if ($config = $_SESSION['enigma_pass']) {
            $config = $this->rc->decrypt($config);
            $config = @unserialize($config);
        }

        $threshold = $this->password_time ? time() - $this->password_time : 0;
        $keys      = array();

        // delete expired passwords
        foreach ((array) $config as $key => $value) {
            if ($threshold && $value[1] < $threshold) {
                unset($config[$key]);
                $modified = true;
            }
            else {
                $keys[$key] = $value[0];
            }
        }

        if ($modified) {
            $_SESSION['enigma_pass'] = $this->rc->encrypt(serialize($config));
        }

        return $keys;
    }

    /**
     * Get message part body.
     *
     * @param rcube_message      Message object
     * @param rcube_message_part Message part
     * @param bool               Return raw body with headers
     */
    private function get_part_body($msg, $part, $full = false)
    {
        // @TODO: Handle big bodies using file handles

        if ($full) {
            $storage = $this->rc->get_storage();
            $body    = $storage->get_raw_headers($msg->uid, $part->mime_id);
            $body   .= $storage->get_raw_body($msg->uid, null, $part->mime_id);
        }
        else {
            $body = $msg->get_part_body($part->mime_id, false);
        }

        return $body;
    }

    /**
     * Parse decrypted message body into structure
     *
     * @param string Message body
     *
     * @return array Message structure
     */
    private function parse_body(&$body)
    {
        // Mail_mimeDecode need \r\n end-line, but gpg may return \n
        $body = preg_replace('/\r?\n/', "\r\n", $body);

        // parse the body into structure
        $struct = rcube_mime::parse_message($body);

        return $struct;
    }

    /**
     * Replace message encrypted structure with decrypted message structure
     *
     * @param array              Hook arguments
     * @param rcube_message_part Part structure
     * @param int                Part size
     */
    private function modify_structure(&$p, $struct, $size = 0)
    {
        // modify mime_parts property of the message object
        $old_id = $p['structure']->mime_id;

        foreach (array_keys($p['object']->mime_parts) as $idx) {
            if (!$old_id || $idx == $old_id || strpos($idx, $old_id . '.') === 0) {
                unset($p['object']->mime_parts[$idx]);
            }
        }

        // set some part params used by Roundcube core
        $struct->headers  = array_merge($p['structure']->headers, $struct->headers);
        $struct->size     = $size;
        $struct->filename = $p['structure']->filename;

        // modify the new structure to be correctly handled by Roundcube
        $this->modify_structure_part($struct, $p['object'], $old_id);

        // replace old structure with the new one
        $p['structure'] = $struct;
        $p['mimetype']  = $struct->mimetype;
    }

    /**
     * Modify decrypted message part
     *
     * @param rcube_message_part
     * @param rcube_message
     */
    private function modify_structure_part($part, $msg, $old_id)
    {
        // never cache the body
        $part->body_modified = true;
        $part->encoding      = 'stream';

        // modify part identifier
        if ($old_id) {
            $part->mime_id = !$part->mime_id ? $old_id : ($old_id . '.' . $part->mime_id);
        }

        // Cache the fact it was decrypted
        $this->encrypted_parts[] = $part->mime_id;
        $msg->mime_parts[$part->mime_id] = $part;

        // modify sub-parts
        foreach ((array) $part->parts as $p) {
            $this->modify_structure_part($p, $msg, $old_id);
        }
    }

    /**
     * Extracts body and signature of multipart/signed message body
     */
    private function explode_signed_body($body, $boundary)
    {
        if (!$body) {
            return array();
        }

        $boundary     = '--' . $boundary;
        $boundary_len = strlen($boundary) + 2;

        // Find boundaries
        $start = strpos($body, $boundary) + $boundary_len;
        $end   = strpos($body, $boundary, $start);

        // Get signed body and signature
        $sig  = substr($body, $end + $boundary_len);
        $body = substr($body, $start, $end - $start - 2);

        // Cleanup signature
        $sig = substr($sig, strpos($sig, "\r\n\r\n") + 4);
        $sig = substr($sig, 0, strpos($sig, $boundary));

        return array($body, $sig);
    }

    /**
     * Checks if specified message part is a PGP-key or S/MIME cert data
     *
     * @param rcube_message_part Part object
     *
     * @return boolean True if part is a key/cert
     */
    public function is_keys_part($part)
    {
        // @TODO: S/MIME
        return (
            // Content-Type: application/pgp-keys
            $part->mimetype == 'application/pgp-keys'
        );
    }
}
