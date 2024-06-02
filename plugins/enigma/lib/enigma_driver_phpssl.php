<?php

/*
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
    private $homedir; // @phpstan-ignore-line
    private $user;

    #[Override]
    public function __construct($user)
    {
        $rcmail = rcmail::get_instance();
        $this->rc = $rcmail;
        $this->user = $user;
    }

    /**
     * Driver initialization and environment checking.
     * Should only return critical errors.
     *
     * @return enigma_error|null NULL on success, enigma_error on failure
     */
    #[Override]
    public function init()
    {
        $homedir = $this->rc->config->get('enigma_smime_homedir', INSTALL_PATH . '/plugins/enigma/home');

        if (!$homedir) {
            return new enigma_error(enigma_error::INTERNAL,
                "Option 'enigma_smime_homedir' not specified");
        }

        // check if homedir exists (create it if not) and is readable
        if (!file_exists($homedir)) {
            return new enigma_error(enigma_error::INTERNAL,
                "Keys directory doesn't exists: {$homedir}");
        }
        if (!is_writable($homedir)) {
            return new enigma_error(enigma_error::INTERNAL,
                "Keys directory isn't writeable: {$homedir}");
        }

        $homedir = $homedir . '/' . $this->user;

        // check if user's homedir exists (create it if not) and is readable
        if (!file_exists($homedir)) {
            mkdir($homedir, 0700);
        }

        if (!file_exists($homedir)) {
            return new enigma_error(enigma_error::INTERNAL,
                "Unable to create keys directory: {$homedir}");
        }
        if (!is_writable($homedir)) {
            return new enigma_error(enigma_error::INTERNAL,
                "Unable to write to keys directory: {$homedir}");
        }

        $this->homedir = $homedir;

        return null;
    }

    #[Override]
    public function encrypt($text, $keys, $sign_key = null)
    {
        return new enigma_error(enigma_error::INTERNAL, 'Not implemented');
    }

    #[Override]
    public function decrypt($text, $keys = [], &$signature = null)
    {
        return new enigma_error(enigma_error::INTERNAL, 'Not implemented');
    }

    #[Override]
    public function sign($text, $key, $mode = null)
    {
        return new enigma_error(enigma_error::INTERNAL, 'Not implemented');
    }

    #[Override]
    public function verify($struct, $message)
    {
        /*
        // use common temp dir
        $msg_file  = rcube_utils::temp_filename('enigmsg');
        $cert_file = rcube_utils::temp_filename('enigcrt');

        $fh = fopen($msg_file, 'w');
        if ($struct->mime_id) {
            $message->get_part_body($struct->mime_id, false, 0, $fh);
        } else {
            $this->rc->storage->get_raw_body($message->uid, $fh);
        }
        fclose($fh);

        // @TODO: use stored certificates

        // try with certificate verification
        $sig      = openssl_pkcs7_verify($msg_file, 0, $cert_file);
        $validity = true;

        if ($sig !== true) {
            // try without certificate verification
            $sig      = openssl_pkcs7_verify($msg_file, \PKCS7_NOVERIFY, $cert_file);
            $validity = enigma_error::UNVERIFIED;
        }

        if ($sig === true) {
            $sig = $this->parse_sig_cert($cert_file, $validity);
        } else {
            $errorstr = $this->get_openssl_error();
            $sig = new enigma_error(enigma_error::INTERNAL, $errorstr);
        }

        // remove temp files
        @unlink($msg_file);
        @unlink($cert_file);

        return $sig;
        */
        return new enigma_error(enigma_error::INTERNAL, 'Not implemented');
    }

    #[Override]
    public function import($content, $isfile = false, $passwords = [])
    {
        return new enigma_error(enigma_error::INTERNAL, 'Not implemented');
    }

    #[Override]
    public function export($key, $with_private = false, $passwords = [])
    {
        return new enigma_error(enigma_error::INTERNAL, 'Not implemented');
    }

    #[Override]
    public function list_keys($pattern = '')
    {
        return new enigma_error(enigma_error::INTERNAL, 'Not implemented');
    }

    #[Override]
    public function get_key($keyid)
    {
        return new enigma_error(enigma_error::INTERNAL, 'Not implemented');
    }

    #[Override]
    public function gen_key($data)
    {
        return new enigma_error(enigma_error::INTERNAL, 'Not implemented');
    }

    #[Override]
    public function delete_key($keyid)
    {
        return new enigma_error(enigma_error::INTERNAL, 'Not implemented');
    }

    /**
     * Returns a name of the hash algorithm used for the last
     * signing operation.
     *
     * @return string Hash algorithm name e.g. sha1
     */
    #[Override]
    public function signature_algorithm()
    {
        return ''; // TODO
    }

    private function get_openssl_error()
    {
        $tmp = [];
        while ($errorstr = openssl_error_string()) {
            $tmp[] = $errorstr;
        }

        return implode("\n", array_values($tmp));
    }

    // @phpstan-ignore-next-line
    private function parse_sig_cert($file, $validity)
    {
        $cert = openssl_x509_parse(file_get_contents($file));

        if (empty($cert) || empty($cert['subject'])) {
            $errorstr = $this->get_openssl_error();
            return new enigma_error(enigma_error::INTERNAL, $errorstr);
        }

        $data = new enigma_signature();

        $data->id = $cert['hash']; // ?
        $data->valid = $validity;
        $data->fingerprint = $cert['serialNumber'];
        $data->created = $cert['validFrom_time_t'];
        $data->expires = $cert['validTo_time_t'];
        $data->name = $cert['subject']['CN'];
        // $data->comment     = '';
        $data->email = $cert['subject']['emailAddress'];

        return $data;
    }
}
