<?php
/*
 +-------------------------------------------------------------------------+
 | Engine of the Enigma Plugin                                             |
 |                                                                         |
 | This program is free software; you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License version 2          |
 | as published by the Free Software Foundation.                           |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License along |
 | with this program; if not, write to the Free Software Foundation, Inc., |
 | 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.             |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+

*/

/*
    RFC2440: OpenPGP Message Format
    RFC3156: MIME Security with OpenPGP
    RFC3851: S/MIME
*/

class enigma_engine
{
    private $rc;
    private $enigma;
    private $pgp_driver;
    private $smime_driver;

    public $decryptions = array();
    public $signatures = array();
    public $signed_parts = array();


    /**
     * Plugin initialization.
     */
    function __construct($enigma)
    {
        $rcmail = rcmail::get_instance();
        $this->rc = $rcmail;    
        $this->enigma = $enigma;
    }

    /**
     * PGP driver initialization.
     */
    function load_pgp_driver()
    {
        if ($this->pgp_driver)
            return;

        $driver = 'enigma_driver_' . $this->rc->config->get('enigma_pgp_driver', 'gnupg');
        $username = $this->rc->user->get_username();

        // Load driver
        $this->pgp_driver = new $driver($username);

        if (!$this->pgp_driver) {
            raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: Unable to load PGP driver: $driver"
            ), true, true);
        }

        // Initialise driver
        $result = $this->pgp_driver->init();

        if ($result instanceof enigma_error) {
            raise_error(array(
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
        if ($this->smime_driver)
            return;

        // NOT IMPLEMENTED!
        return;

        $driver = 'enigma_driver_' . $this->rc->config->get('enigma_smime_driver', 'phpssl');
        $username = $this->rc->user->get_username();

        // Load driver
        $this->smime_driver = new $driver($username);

        if (!$this->smime_driver) {
            raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: Unable to load S/MIME driver: $driver"
            ), true, true);
        }

        // Initialise driver
        $result = $this->smime_driver->init();

        if ($result instanceof enigma_error) {
            raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: ".$result->getMessage()
            ), true, true);
        }
    }

    /**
     * Handler for plain/text message.
     *
     * @param array Reference to hook's parameters
     */
    function parse_plain(&$p)
    {
        $part = $p['structure'];

        // Get message body from IMAP server
        $this->set_part_body($part, $p['object']->uid);

        // @TODO: big message body can be a file resource
        // PGP signed message
        if (preg_match('/^-----BEGIN PGP SIGNED MESSAGE-----/', $part->body)) {
            $this->parse_plain_signed($p);
        }
        // PGP encrypted message
        else if (preg_match('/^-----BEGIN PGP MESSAGE-----/', $part->body)) {
            $this->parse_plain_encrypted($p);
        }
    }

    /**
     * Handler for multipart/signed message.
     *
     * @param array Reference to hook's parameters
     */
    function parse_signed(&$p)
    {
        $struct = $p['structure'];

        // S/MIME
        if ($struct->parts[1] && $struct->parts[1]->mimetype == 'application/pkcs7-signature') {
            $this->parse_smime_signed($p);
        }
        // PGP/MIME:
        // The multipart/signed body MUST consist of exactly two parts.
        // The first part contains the signed data in MIME canonical format,
        // including a set of appropriate content headers describing the data.
        // The second body MUST contain the PGP digital signature.  It MUST be
        // labeled with a content type of "application/pgp-signature".
        else if ($struct->parts[1] && $struct->parts[1]->mimetype == 'application/pgp-signature') {
            $this->parse_pgp_signed($p);
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
        if ($struct->mimetype == 'application/pkcs7-mime') {
            $this->parse_smime_encrypted($p);
        }
        // PGP/MIME:
        // The multipart/encrypted MUST consist of exactly two parts.  The first
        // MIME body part must have a content type of "application/pgp-encrypted".
        // This body contains the control information.
        // The second MIME body part MUST contain the actual encrypted data.  It
        // must be labeled with a content type of "application/octet-stream".
        else if ($struct->parts[0] && $struct->parts[0]->mimetype == 'application/pgp-encrypted' &&
            $struct->parts[1] && $struct->parts[1]->mimetype == 'application/octet-stream'
        ) {
            $this->parse_pgp_encrypted($p);
        }
    }

    /**
     * Handler for plain signed message.
     * Excludes message and signature bodies and verifies signature.
     *
     * @param array Reference to hook's parameters
     */
    private function parse_plain_signed(&$p)
    {
        $this->load_pgp_driver();
        $part = $p['structure'];

        // Verify signature
        if ($this->rc->action == 'show' || $this->rc->action == 'preview') {
            $sig = $this->pgp_verify($part->body);
        }

        // @TODO: Handle big bodies using (temp) files

        // In this way we can use fgets on string as on file handle
        $fh = fopen('php://memory', 'br+');
        // @TODO: fopen/fwrite errors handling
        if ($fh) {
            fwrite($fh, $part->body);
            rewind($fh);
        }
        $part->body = null;

        // Extract body (and signature?)
        while (!feof($fh)) {
            $line = fgets($fh, 1024);

            if ($part->body === null)
                $part->body = '';
            else if (preg_match('/^-----BEGIN PGP SIGNATURE-----/', $line))
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
            $this->signed_parts[$part->mime_id] = $part->mime_id;
            $this->signatures[$part->mime_id] = $sig;
        }

        fclose($fh);
    }
    
    /**
     * Handler for PGP/MIME signed message.
     * Verifies signature.
     *
     * @param array Reference to hook's parameters
     */
    private function parse_pgp_signed(&$p)
    {
        $this->load_pgp_driver();
        $struct = $p['structure'];
        
        // Verify signature
        if ($this->rc->action == 'show' || $this->rc->action == 'preview') {
            $msg_part = $struct->parts[0];
            $sig_part = $struct->parts[1];
        
            // Get bodies
            $this->set_part_body($msg_part, $p['object']->uid);
            $this->set_part_body($sig_part, $p['object']->uid);

            // Verify
            $sig = $this->pgp_verify($msg_part->body, $sig_part->body);

            // Store signature data for display
            $this->signatures[$struct->mime_id] = $sig;

            // Message can be multipart (assign signature to each subpart)
            if (!empty($msg_part->parts)) {
                foreach ($msg_part->parts as $part)
                    $this->signed_parts[$part->mime_id] = $struct->mime_id;
            }
            else
                $this->signed_parts[$msg_part->mime_id] = $struct->mime_id;

            // Remove signature file from attachments list
            unset($struct->parts[1]);
        }
    }

    /**
     * Handler for S/MIME signed message.
     * Verifies signature.
     *
     * @param array Reference to hook's parameters
     */
    private function parse_smime_signed(&$p)
    {
        $this->load_smime_driver();
    }

    /**
     * Handler for plain encrypted message.
     *
     * @param array Reference to hook's parameters
     */
    private function parse_plain_encrypted(&$p)
    {
        $this->load_pgp_driver();
        $part = $p['structure'];
        
        // Get body
        $this->set_part_body($part, $p['object']->uid);

        // Decrypt 
        $result = $this->pgp_decrypt($part->body);
        
        // Store decryption status
        $this->decryptions[$part->mime_id] = $result;
        
        // Parse decrypted message
        if ($result === true) {
            // @TODO
        }
    }
    
    /**
     * Handler for PGP/MIME encrypted message.
     *
     * @param array Reference to hook's parameters
     */
    private function parse_pgp_encrypted(&$p)
    {
        $this->load_pgp_driver();
        $struct = $p['structure'];
        $part = $struct->parts[1];
        
        // Get body
        $this->set_part_body($part, $p['object']->uid);

        // Decrypt
        $result = $this->pgp_decrypt($part->body);

        $this->decryptions[$part->mime_id] = $result;
//print_r($part);
        // Parse decrypted message
        if ($result === true) {
            // @TODO
        }
        else {
            // Make sure decryption status message will be displayed
            $part->type = 'content';
            $p['object']->parts[] = $part;
        }
    }

    /**
     * Handler for S/MIME encrypted message.
     *
     * @param array Reference to hook's parameters
     */
    private function parse_smime_encrypted(&$p)
    {
        $this->load_smime_driver();
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
        // @TODO: caching of verification result
        
         $sig = $this->pgp_driver->verify($msg_body, $sig_body);

         if (($sig instanceof enigma_error) && $sig->getCode() != enigma_error::E_KEYNOTFOUND)
             raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: " . $error->getMessage()
                ), true, false);

//print_r($sig);
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
        // @TODO: caching of verification result
        
        $result = $this->pgp_driver->decrypt($msg_body, $key, $pass);

//print_r($result);

        if ($result instanceof enigma_error) {
            $err_code = $result->getCode();
            if (!in_array($err_code, array(enigma_error::E_KEYNOTFOUND, enigma_error::E_BADPASS)))
                raise_error(array(
                    'code' => 600, 'type' => 'php',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Enigma plugin: " . $result->getMessage()
                    ), true, false);
            return $result;
        }

//        $msg_body = $result;
        return true;
    }

    /**
     * PGP keys listing.
     *
     * @param mixed Key ID/Name pattern
     *
     * @return mixed Array of keys or enigma_error
     */
    function list_keys($pattern='')
    {
        $this->load_pgp_driver();
        $result = $this->pgp_driver->list_keys($pattern);
    
        if ($result instanceof enigma_error) {
            raise_error(array(
                'code' => 600, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Enigma plugin: " . $result->getMessage()
                ), true, false);
        }
        
        return $result;
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
            raise_error(array(
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
            raise_error(array(
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
        $uid = get_input_value('_uid', RCUBE_INPUT_POST);
        $mbox = get_input_value('_mbox', RCUBE_INPUT_POST);
        $mime_id = get_input_value('_part', RCUBE_INPUT_POST);

        if ($uid && $mime_id) {
            $part = $this->rc->imap->get_message_part($uid, $mime_id);
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
     * Checks if specified message part contains body data.
     * If body is not set it will be fetched from IMAP server.
     *
     * @param rcube_message_part Message part object
     * @param integer            Message UID
     */
    private function set_part_body($part, $uid)
    {
        // @TODO: Create such function in core
        // @TODO: Handle big bodies using file handles
        if (!isset($part->body)) {
            $part->body = $this->rc->imap->get_message_part(
                $uid, $part->mime_id, $part);
        }
    }

    /**
     * Adds CSS style file to the page header.
     */
    private function add_css()
    {
        $skin = $this->rc->config->get('skin');
        if (!file_exists($this->home . "/skins/$skin/enigma.css"))
            $skin = 'default';

        $this->include_stylesheet("skins/$skin/enigma.css");                                                
    }
}
