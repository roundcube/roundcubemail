<?php
/*
 +-------------------------------------------------------------------------+
 | S/MIME driver for the Enigma Plugin                                |
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

class enigma_driver_phpssl extends enigma_driver
{
    private $rc;
    //private $gpg;
    private $homedir;
    private $user;

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

        if (!$homedir)
            return new enigma_error(enigma_error::E_INTERNAL,
                "Option 'enigma_smime_homedir' not specified");

        // check if homedir exists (create it if not) and is readable
        if (!file_exists($homedir))
            return new enigma_error(enigma_error::E_INTERNAL,
                "Keys directory doesn't exists: $homedir");
        if (!is_writable($homedir))
            return new enigma_error(enigma_error::E_INTERNAL,
                "Keys directory isn't writeable: $homedir");

        $homedir = $homedir . '/' . $this->user;

        // check if user's homedir exists (create it if not) and is readable
        if (!file_exists($homedir))
            mkdir($homedir, 0700);

        if (!file_exists($homedir))
            return new enigma_error(enigma_error::E_INTERNAL,
                "Unable to create keys directory: $homedir");
        if (!is_writable($homedir))
            return new enigma_error(enigma_error::E_INTERNAL,
                "Unable to write to keys directory: $homedir");

        $this->homedir = $homedir;

    }

    function encrypt($text, $keys)
    {
    }

    function decrypt($text, $key, $passwd)
    {
    }

    function sign($text, $key, $passwd)
    {
    }

    function verify($struct, $message)
    {
        // use common temp dir
        $temp_dir  = $this->rc->config->get('temp_dir');
        $msg_file  = tempnam($temp_dir, 'rcmMsg');
        $cert_file = tempnam($temp_dir, 'rcmCert');

        $fh = fopen($msg_file, "w");
        if ($struct->mime_id) {
            $message->get_part_body($struct->mime_id, false, 0, $fh);
        }
        else {
            $this->rc->storage->get_raw_body($message->uid, $fh);
        }
        fclose($fh);

        // @TODO: use stored certificates

        // try with certificate verification
        $sig      = openssl_pkcs7_verify($msg_file, 0, $cert_file);
        $validity = true;

        if ($sig !== true) {
            // try without certificate verification
            $sig      = openssl_pkcs7_verify($msg_file, PKCS7_NOVERIFY, $cert_file);
            $validity = enigma_error::E_UNVERIFIED;
        }

        if ($sig === true) {
            $sig = $this->parse_sig_cert($cert_file, $validity);
        }
        else {
            $errorstr = $this->get_openssl_error();
            $sig = new enigma_error(enigma_error::E_INTERNAL, $errorstr);
        }

        // remove temp files
        @unlink($msg_file);
        @unlink($cert_file);

        return $sig;
    }

    public function import($content, $isfile=false)
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

    public function del_key($keyid)
    {
    }

    public function del_privkey($keyid)
    {
    }

    public function del_pubkey($keyid)
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
/*
        $ekey = new enigma_key();

        foreach ($key->getUserIds() as $idx => $user) {
            $id = new enigma_userid();
            $id->name    = $user->getName();
            $id->comment = $user->getComment();
            $id->email   = $user->getEmail();
            $id->valid   = $user->isValid();
            $id->revoked = $user->isRevoked();

            $ekey->users[$idx] = $id;
        }
        
        $ekey->name = trim($ekey->users[0]->name . ' <' . $ekey->users[0]->email . '>');

        foreach ($key->getSubKeys() as $idx => $subkey) {
                $skey = new enigma_subkey();
                $skey->id          = $subkey->getId();
                $skey->revoked     = $subkey->isRevoked();
                $skey->created     = $subkey->getCreationDate();
                $skey->expires     = $subkey->getExpirationDate();
                $skey->fingerprint = $subkey->getFingerprint();
                $skey->has_private = $subkey->hasPrivate();
                $skey->can_sign    = $subkey->canSign();
                $skey->can_encrypt = $subkey->canEncrypt();

                $ekey->subkeys[$idx] = $skey;
        };
        
        $ekey->id = $ekey->subkeys[0]->id;
        
        return $ekey;
*/
    }

    private function get_openssl_error()
    {
        $tmp = array();
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
            return new enigma_error(enigm_error::E_INTERNAL, $errorstr);
        }

        $data = new enigma_signature();

        $data->id          = $cert['hash']; //?
        $data->valid       = $validity;
        $data->fingerprint = $cert['serialNumber'];
        $data->created     = $cert['validFrom_time_t'];
        $data->expires     = $cert['validTo_time_t'];
        $data->name        = $cert['subject']['CN'];
//        $data->comment     = '';
        $data->email       = $cert['subject']['emailAddress'];

        return $data;
    }

}
