<?php
/*
 +-------------------------------------------------------------------------+
 | GnuPG (PGP) driver for the Enigma Plugin                                |
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

require_once 'Crypt/GPG.php';

class enigma_driver_gnupg extends enigma_driver
{
    private $rc;
    private $gpg;
    private $homedir;
    private $user;

    function __construct($user)
    {
        $rcmail = rcmail::get_instance();
        $this->rc = $rcmail;
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
        $homedir = $this->rc->config->get('enigma_pgp_homedir', INSTALL_PATH . '/plugins/enigma/home');

        if (!$homedir)
            return new enigma_error(enigma_error::E_INTERNAL,
                "Option 'enigma_pgp_homedir' not specified");

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

        // Create Crypt_GPG object
        try {
	        $this->gpg = new Crypt_GPG(array(
                'homedir'   => $this->homedir,
//                'debug'     => true,
          ));
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }

    function encrypt($text, $keys)
    {
/*
	    foreach ($keys as $key) {
		    $this->gpg->addEncryptKey($key);
	    }
	    $enc = $this->gpg->encrypt($text);
	    return $enc;
*/
    }

    function decrypt($text, $key, $passwd)
    {
//	    $this->gpg->addDecryptKey($key, $passwd);
        try {
    	    $dec = $this->gpg->decrypt($text);
    	    return $dec;
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }

    function sign($text, $key, $passwd)
    {
/*
	    $this->gpg->addSignKey($key, $passwd);
	    $signed = $this->gpg->sign($text, Crypt_GPG::SIGN_MODE_DETACHED);
	    return $signed;
*/
    }

    function verify($text, $signature)
    {
        try {
    	    $verified = $this->gpg->verify($text, $signature);
      	    return $this->parse_signature($verified[0]);
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }

    public function import($content, $isfile=false)
    {
        try {
            if ($isfile)
                return $this->gpg->importKeyFile($content);
            else
                return $this->gpg->importKey($content);
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }
    
    public function list_keys($pattern='')
    {
        try {
    	    $keys = $this->gpg->getKeys($pattern);
            $result = array();
//print_r($keys);
            foreach ($keys as $idx => $key) {
                $result[] = $this->parse_key($key);
                unset($keys[$idx]);
            }
//print_r($result);
      	    return $result;
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }
    
    public function get_key($keyid)
    {
        $list = $this->list_keys($keyid);

        if (is_array($list))
            return array_shift($list);

        // error        
        return $list;
    }

    public function gen_key($data)
    {
    }

    public function del_key($keyid)
    {
//        $this->get_key($keyid);
        
        
    }
    
    public function del_privkey($keyid)
    {
        try {
    	    $this->gpg->deletePrivateKey($keyid);
            return true;
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }

    public function del_pubkey($keyid)
    {
        try {
    	    $this->gpg->deletePublicKey($keyid);
            return true;
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }
    
    /**
     * Converts Crypt_GPG exception into Enigma's error object
     *
     * @param mixed Exception object
     *
     * @return enigma_error Error object
     */
    private function get_error_from_exception($e)
    {
        $data = array();

        if ($e instanceof Crypt_GPG_KeyNotFoundException) {
            $error = enigma_error::E_KEYNOTFOUND;
            $data['id'] = $e->getKeyId();
        }
        else if ($e instanceof Crypt_GPG_BadPassphraseException) {
            $error = enigma_error::E_BADPASS;
            $data['bad']     = $e->getBadPassphrases();
            $data['missing'] = $e->getMissingPassphrases();
        }
        else if ($e instanceof Crypt_GPG_NoDataException)
            $error = enigma_error::E_NODATA;
        else if ($e instanceof Crypt_GPG_DeletePrivateKeyException)
            $error = enigma_error::E_DELKEY;
        else
            $error = enigma_error::E_INTERNAL;

        $msg = $e->getMessage();

        return new enigma_error($error, $msg, $data);
    }

    /**
     * Converts Crypt_GPG_Signature object into Enigma's signature object
     *
     * @param Crypt_GPG_Signature Signature object
     *
     * @return enigma_signature Signature object
     */
    private function parse_signature($sig)
    {
        $user = $sig->getUserId();

        $data = new enigma_signature();
        $data->id          = $sig->getId();
        $data->valid       = $sig->isValid();
        $data->fingerprint = $sig->getKeyFingerprint();
        $data->created     = $sig->getCreationDate();
        $data->expires     = $sig->getExpirationDate();
        $data->name        = $user->getName();
        $data->comment     = $user->getComment();
        $data->email       = $user->getEmail();

        return $data;
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
    }
}
