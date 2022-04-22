<?php

/**
 +-------------------------------------------------------------------------+
 | S/MIME driver for the Enigma Plugin                                     |
 |                                                                         |
 | Copyright (C) The Roundcube Dev Team                                    |
 |                                                                         |
 | Licensed under the GNU General Public License version 3 or              |
 | any later version with exceptions for skins & plugins.                  |
 | See the README file for a full license statement.                       |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+
*/

class enigma_driver_phpssl extends enigma_driver
{
    private $rc;
    private $homedir;
    private $user;
    private $cainfo;

    function __construct($user)
    {
        $rcmail = rcmail::get_instance();
        $this->rc   = $rcmail;
        $this->user = $user;
    }

    /**
     * Driver initialization and environment checking.
     * Should only return critical errors.
     *
     * @return mixed NULL on success, enigma_error on failure
     */
    function init()
    {
        $homedir = $this->rc->config->get('enigma_smime_homedir', INSTALL_PATH . '/plugins/enigma/home');
        $this->cainfo = $this->rc->config->get('enigma_smime_ca', []);

        if (!$homedir)
            return new enigma_error(enigma_error::INTERNAL,
                "Option 'enigma_smime_homedir' not specified");

        // check if homedir exists (create it if not) and is readable
        if (!file_exists($homedir))
            return new enigma_error(enigma_error::INTERNAL,
                "Keys directory doesn't exists: $homedir");
        if (!is_writable($homedir))
            return new enigma_error(enigma_error::INTERNAL,
                "Keys directory isn't writeable: $homedir");

        $homedir = $homedir . '/' . $this->user;

        // check if user's homedir exists (create it if not) and is readable
        if (!file_exists($homedir))
            mkdir($homedir, 0700);

        if (!file_exists($homedir))
            return new enigma_error(enigma_error::INTERNAL,
                "Unable to create keys directory: $homedir");
        if (!is_writable($homedir))
            return new enigma_error(enigma_error::INTERNAL,
                "Unable to write to keys directory: $homedir");

        $this->homedir = $homedir;

        // XXX: Workaround for https://bugs.php.net/bug.php?id=75494
	    if (!is_array($this->cainfo) || count($this->cainfo) > 0) {
            $dummy_cert_dir = $this->homedir . '/' . 'cert_dummy';
            if (!file_exists($dummy_cert_dir)) {
                mkdir($dummy_cert_dir, 0700);
            }
            $this->cainfo[] = $dummy_cert_dir;
        }
    }

    function encrypt($text, $keys, $sign_key = null)
    {
    }

    function decrypt($text, $keys = [], &$signature = null)
    {
    }

    function sign($text, $key, $mode = null)
    {
    }

    /**
     * Verifying S/MIME signed message.
     *
     * @param array  $struct to verify
     * @param string $message message body
     * 
     * @return mixed enigma_signature or enigma_signature
     */
    function verify($struct, $message)
    {
        // use common temp dir
        $msg_file  = rcube_utils::temp_filename('enigmsg');
        $cert_file = rcube_utils::temp_filename('enigcrt');
        $content_file = rcube_utils::temp_filename('enigcnt');

        $fh = fopen($msg_file, "w");
        if ($struct->mime_id) {
            if ($struct->ctype_parameters['smime-type'] == 'signed-data' ) {
                // The following statement gives the whole body.
                // openssl can verify this (openssl pkcs7 -verify -noverify)
                $this->rc->storage->get_raw_body($message->uid, $fh);
            }
            else {
                // this will only give the *one part*.
                $message->get_part_body($struct->mime_id, false, 0, $fh);
            }
        }
        else {
            $this->rc->storage->get_raw_body($message->uid, $fh);
        }
        fclose($fh);

        // @TODO: use stored certificates
        // try with global config'd certificates
        $sig      = openssl_pkcs7_verify($msg_file, 0, $cert_file, $this->cainfo, null, $content_file);
        $validity = true;

        if ($sig !== true) {
            // try with server trusted certificate verification
            $sig      = openssl_pkcs7_verify($msg_file, 0, $cert_file, [], null, $content_file);
            $validity = enigma_error::SERVER_VERIFIED;
        }

        if ($sig !== true) {
            // try without certificate verification
            $sig      = openssl_pkcs7_verify($msg_file, PKCS7_NOVERIFY, $cert_file, [], null, $content_file);
            $validity = enigma_error::UNVERIFIED;
        }

        if ($sig === true) {
            $sig = $this->parse_sig_cert($cert_file, $validity);
            $content = file_get_contents($content_file);
            $sig->comment = $content;
        }
        else {
            $errorstr = $this->get_openssl_error();
            $sig = new enigma_error(enigma_error::INTERNAL, $errorstr);
        }

        // remove temp files
        @unlink($msg_file);
        @unlink($cert_file);
        @unlink($content_file);

        return $sig;
    }

    public function import($content, $isfile = false, $passwords = [])
    {
    }

    public function export($key, $with_private = false, $passwords = [])
    {
    }

    public function list_keys($pattern='')
    {
    }

    public function get_key($keyid)
    {
    }

    public function gen_key($data)
    {
    }

    public function delete_key($keyid)
    {
    }

    /**
     * Returns a name of the hash algorithm used for the last
     * signing operation.
     *
     * @return string Hash algorithm name e.g. sha1
     */
    public function signature_algorithm()
    {
    }

    /**
     * Converts Crypt_GPG_Key object into Enigma's key object
     *
     * @param Crypt_GPG_Key Key object
     *
     * @return enigma_key Key object
     */
    private function parse_key($key)
    {
    }

    private function get_openssl_error()
    {
        $tmp = [];
        while ($errorstr = openssl_error_string()) {
            $tmp[] = $errorstr;
        }

        return join("\n", array_values($tmp));
    }

    private function parse_sig_cert($file, $validity)
    {
        $cert = openssl_x509_parse(file_get_contents($file));

        if (empty($cert) || empty($cert['subject'])) {
            $errorstr = $this->get_openssl_error();
            return new enigma_error(enigma_error::INTERNAL, $errorstr);
        }

        $data = new enigma_signature();
        $data->id          = $cert['hash']; //?
        $data->valid       = $validity;
        $data->fingerprint = $cert['serialNumber'];
        $data->created     = $cert['validFrom_time_t'];
        $data->expires     = $cert['validTo_time_t'];
        $data->name        = $cert['subject']['CN'];
//        $data->comment     = '';
        if (array_key_exists('emailAddress', $cert['subject'])) {
            $data->email       = $cert['subject']['emailAddress'];
        }
        else {
            if (array_key_exists('extensions', $cert)) {
                if (array_key_exists('subjectAltName', $cert['extensions'])) {
                    if (substr($cert['extensions']['subjectAltName'], 0, 6) == "email:") {
                        $data->email = substr($cert['extensions']['subjectAltName'], 6);
                    }
                }
            }
        }

        return $data;
    }
}
