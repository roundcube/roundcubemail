<?php

/**
 +-------------------------------------------------------------------------+
 | GnuPG (PGP) driver for the Enigma Plugin                                |
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

require_once 'Crypt/GPG.php';

class enigma_driver_gnupg extends enigma_driver
{
    protected $rc;
    protected $gpg;
    protected $homedir;
    protected $user;
    protected $last_sig_algorithm;
    protected $debug    = false;
    protected $db_files = ['pubring.gpg', 'secring.gpg', 'pubring.kbx'];


    /**
     * Class constructor
     *
     * @param rcube_user $user User object
     */
    function __construct($user)
    {
        $this->rc   = rcmail::get_instance();
        $this->user = $user;
    }

    /**
     * Driver initialization and environment checking.
     * Should only return critical errors.
     *
     * @return enigma_error|null NULL on success, enigma_error on failure
     */
    function init()
    {
        $homedir = $this->rc->config->get('enigma_pgp_homedir');
        $debug   = $this->rc->config->get('enigma_debug');
        $binary  = $this->rc->config->get('enigma_pgp_binary');
        $agent   = $this->rc->config->get('enigma_pgp_agent');
        $gpgconf = $this->rc->config->get('enigma_pgp_gpgconf');

        if (!$homedir) {
            return new enigma_error(enigma_error::INTERNAL,
                "Option 'enigma_pgp_homedir' not specified");
        }

        // check if homedir exists (create it if not) and is readable
        if (!file_exists($homedir)) {
            return new enigma_error(enigma_error::INTERNAL,
                "Keys directory doesn't exists: $homedir");
        }
        if (!is_writable($homedir)) {
            return new enigma_error(enigma_error::INTERNAL,
                "Keys directory isn't writeable: $homedir");
        }

        $homedir = $homedir . '/' . $this->user;

        // check if user's homedir exists (create it if not) and is readable
        if (!file_exists($homedir)) {
            mkdir($homedir, 0700);
        }

        if (!file_exists($homedir)) {
            return new enigma_error(enigma_error::INTERNAL,
                "Unable to create keys directory: $homedir");
        }
        if (!is_writable($homedir)) {
            return new enigma_error(enigma_error::INTERNAL,
                "Unable to write to keys directory: $homedir");
        }

        $this->debug   = $debug;
        $this->homedir = $homedir;

        $options = ['homedir' => $this->homedir];

        if ($debug) {
            $options['debug'] = [$this, 'debug'];
        }
        if ($binary) {
            $options['binary'] = $binary;
        }
        if ($agent) {
            $options['agent'] = $agent;
        }
        if ($gpgconf) {
            $options['gpgconf'] = $gpgconf;
        }

        $options['cipher-algo'] = $this->rc->config->get('enigma_pgp_cipher_algo');
        $options['digest-algo'] = $this->rc->config->get('enigma_pgp_digest_algo');

        // Create Crypt_GPG object
        try {
            $this->gpg = new Crypt_GPG($options);
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }

        $this->db_sync();
    }

    /**
     * Encryption (and optional signing).
     *
     * @param string     $text     Message body
     * @param array      $keys     List of keys (enigma_key objects)
     * @param enigma_key $sign_key Optional signing Key ID
     *
     * @return string|enigma_error Encrypted message or enigma_error on failure
     */
    function encrypt($text, $keys, $sign_key = null)
    {
        try {
            foreach ($keys as $key) {
                $this->gpg->addEncryptKey($key->reference);
            }

            if ($sign_key) {
                $this->gpg->addSignKey($sign_key->reference, $sign_key->password);

                $res     = $this->gpg->encryptAndSign($text, true);
                $sigInfo = $this->gpg->getLastSignatureInfo();

                $this->last_sig_algorithm = $sigInfo->getHashAlgorithmName();

                return $res;
            }

            return $this->gpg->encrypt($text, true);
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }

    /**
     * Decrypt a message (and verify if signature found)
     *
     * @param string           $text       Encrypted message
     * @param array            $keys       List of key-password mapping
     * @param enigma_signature &$signature Signature information (if available)
     *
     * @return mixed Decrypted message or enigma_error on failure
     */
    function decrypt($text, $keys = [], &$signature = null)
    {
        try {
            foreach ($keys as $key => $password) {
                $this->gpg->addDecryptKey($key, $password);
            }

            $result = $this->gpg->decryptAndVerify($text, true);

            if (!empty($result['signatures'])) {
                $signature = $this->parse_signature($result['signatures'][0]);
            }

            // EFAIL vulnerability mitigation (#6289)
            // Handle MDC warning as an exception, this is the default for gpg 2.3.
            if (method_exists($this->gpg, 'getWarnings')) {
                foreach ($this->gpg->getWarnings() as $warning_msg) {
                    if (strpos($warning_msg, 'not integrity protected') !== false) {
                        return new enigma_error(enigma_error::NOMDC, ucfirst($warning_msg));
                    }
                }
            }

            return $result['data'];
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }

    /**
     * Signing.
     *
     * @param string     $text Message body
     * @param enigma_key $key  The key
     * @param int        $mode Signing mode (enigma_engine::SIGN_*)
     *
     * @return mixed True on success or enigma_error on failure
     */
    function sign($text, $key, $mode = null)
    {
        try {
            $this->gpg->addSignKey($key->reference, $key->password);

            $res     = $this->gpg->sign($text, $mode, Crypt_GPG::ARMOR_ASCII, true);
            $sigInfo = $this->gpg->getLastSignatureInfo();

            $this->last_sig_algorithm = $sigInfo->getHashAlgorithmName();

            return $res;
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }

    /**
     * Signature verification.
     *
     * @param string $text      Message body
     * @param string $signature Signature, if message is of type PGP/MIME and body doesn't contain it
     *
     * @return enigma_signature|enigma_error Signature information or enigma_error
     */
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

    /**
     * Key file import.
     *
     * @param string $content  File name or file content
     * @param bool   $isfile   True if first argument is a filename
     * @param array  $password Optional key => password map
     *
     * @return mixed Import status array or enigma_error
     */
    public function import($content, $isfile = false, $passwords = [])
    {
        try {
            // GnuPG 2.1 requires secret key passphrases on import
            foreach ($passwords as $keyid => $pass) {
                $this->gpg->addPassphrase($keyid, $pass);
            }

            if ($isfile) {
                $result = $this->gpg->importKeyFile($content);
            }
            else {
                $result = $this->gpg->importKey($content);
            }

            $this->db_save();

            return $result;
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }

    /**
     * Key export.
     *
     * @param string $keyid        Key ID
     * @param bool   $with_private Include private key
     * @param array  $passwords    Optional key => password map
     *
     * @return string|enigma_error Key content or enigma_error
     */
    public function export($keyid, $with_private = false, $passwords = [])
    {
        try {
            $key = $this->gpg->exportPublicKey($keyid, true);

            if ($with_private) {
                // GnuPG 2.1 requires secret key passphrases on export
                foreach ($passwords as $_keyid => $pass) {
                    $this->gpg->addPassphrase($_keyid, $pass);
                }

                $priv = $this->gpg->exportPrivateKey($keyid, true);
                $key .= $priv;
            }

            return $key;
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }

    /**
     * Keys listing.
     *
     * @param string $patter Optional pattern for key ID, user ID or fingerprint
     *
     * @return enigma_key[]|enigma_error Array of keys or enigma_error
     */
    public function list_keys($pattern = '')
    {
        try {
            $keys   = $this->gpg->getKeys($pattern);
            $result = [];

            foreach ($keys as $idx => $key) {
                $result[] = $this->parse_key($key);
            }

            return $result;
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }

    /**
     * Single key information.
     *
     * @param string $keyid Key ID, user ID or fingerprint
     *
     * @return enigma_key|enigma_error Key object or enigma_error
     */
    public function get_key($keyid)
    {
        $list = $this->list_keys($keyid);

        if (is_array($list)) {
            return $list[key($list)];
        }

        // error
        return $list;
    }

    /**
     * Key pair generation.
     *
     * @param array $data Key/User data (user, email, password, size)
     *
     * @return mixed Key (enigma_key) object or enigma_error
     */
    public function gen_key($data)
    {
        try {
            $debug  = $this->rc->config->get('enigma_debug');
            $keygen = new Crypt_GPG_KeyGenerator([
                    'homedir' => $this->homedir,
                    // 'binary'  => '/usr/bin/gpg2',
                    'debug'   => $debug ? [$this, 'debug'] : false,
            ]);

            $key = $keygen
                ->setExpirationDate(0)
                ->setPassphrase($data['password'])
                ->generateKey($data['user'], $data['email']);

            return $this->parse_key($key);
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }

    /**
     * Key deletion.
     *
     * @param string $keyid Key ID
     *
     * @return mixed True on success or enigma_error
     */
    public function delete_key($keyid)
    {
        // delete public key
        $result = $this->delete_pubkey($keyid);

        // error handling
        if ($result !== true) {
            $code = $result->getCode();

            // if not found, delete private key
            if ($code == enigma_error::KEYNOTFOUND) {
                $result = $this->delete_privkey($keyid);
            }
            // need to delete private key first
            else if ($code == enigma_error::DELKEY) {
                $result = $this->delete_privkey($keyid);

                if ($result === true) {
                    $result = $this->delete_pubkey($keyid);
                }
            }
        }

        $this->db_save();

        return $result;
    }

    /**
     * Returns a name of the hash algorithm used for the last
     * signing operation.
     *
     * @return string Hash algorithm name e.g. sha1
     */
    public function signature_algorithm()
    {
        return $this->last_sig_algorithm;
    }

    /**
     * Returns a list of supported features.
     *
     * @return array Capabilities list
     */
    public function capabilities()
    {
        $caps = [enigma_driver::SUPPORT_RSA];
        $version = $this->gpg->getVersion();

        if (version_compare($version, '2.1.7', 'ge')) {
            $caps[] = enigma_driver::SUPPORT_ECC;
        }

        return $caps;
    }

    /**
     * Private key deletion.
     */
    protected function delete_privkey($keyid)
    {
        try {
            $this->gpg->deletePrivateKey($keyid);
            return true;
        }
        catch (Exception $e) {
            return $this->get_error_from_exception($e);
        }
    }

    /**
     * Public key deletion.
     */
    protected function delete_pubkey($keyid)
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
     * @param mixed $e Exception object
     *
     * @return enigma_error Error object
     */
    protected function get_error_from_exception($e)
    {
        $data = [];

        if ($e instanceof Crypt_GPG_KeyNotFoundException) {
            $error = enigma_error::KEYNOTFOUND;
            $data['id'] = $e->getKeyId();
        }
        else if ($e instanceof Crypt_GPG_BadPassphraseException) {
            $error = enigma_error::BADPASS;
            $data['bad']     = $e->getBadPassphrases();
            $data['missing'] = $e->getMissingPassphrases();
        }
        else if ($e instanceof Crypt_GPG_NoDataException) {
            $error = enigma_error::NODATA;
        }
        else if ($e instanceof Crypt_GPG_DeletePrivateKeyException) {
            $error = enigma_error::DELKEY;
        }
        else {
            $error = enigma_error::INTERNAL;
        }

        $msg = $e->getMessage();

        return new enigma_error($error, $msg, $data);
    }

    /**
     * Converts Crypt_GPG_Signature object into Enigma's signature object
     *
     * @param Crypt_GPG_Signature $sig Signature object
     *
     * @return enigma_signature Signature object
     */
    protected function parse_signature($sig)
    {
        $data = new enigma_signature();

        $data->id          = $sig->getId() ?: $sig->getKeyId();
        $data->valid       = $sig->isValid();
        $data->fingerprint = $sig->getKeyFingerprint();
        $data->created     = $sig->getCreationDate();
        $data->expires     = $sig->getExpirationDate();

        // In case of ERRSIG user may not be set
        if ($user = $sig->getUserId()) {
            $data->name    = $user->getName();
            $data->comment = $user->getComment();
            $data->email   = $user->getEmail();
        }

        return $data;
    }

    /**
     * Converts Crypt_GPG_Key object into Enigma's key object
     *
     * @param Crypt_GPG_Key $key Key object
     *
     * @return enigma_key Key object
     */
    protected function parse_key($key)
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

        // keep reference to Crypt_GPG's key for performance reasons
        $ekey->reference = $key;

        foreach ($key->getSubKeys() as $idx => $subkey) {
            $skey = new enigma_subkey();
            $skey->id          = $subkey->getId();
            $skey->revoked     = $subkey->isRevoked();
            $skey->fingerprint = $subkey->getFingerprint();
            $skey->has_private = $subkey->hasPrivate();
            $skey->algorithm   = $subkey->getAlgorithm();
            $skey->length      = $subkey->getLength();
            $skey->usage       = $subkey->usage();

            if (method_exists($subkey, 'getCreationDateTime')) {
                $skey->created = $subkey->getCreationDateTime();
                $skey->expires = $subkey->getExpirationDateTime();
            }
            else {
                $skey->created = $subkey->getCreationDate();
                $skey->expires = $subkey->getExpirationDate();

                if ($skey->created) {
                    $skey->created = new DateTime("@{$skey->created}");
                }

                if ($skey->expires) {
                    $skey->expires = new DateTime("@{$skey->expires}");
                }
            }

            $ekey->subkeys[$idx] = $skey;
        };

        $ekey->id = $ekey->subkeys[0]->id;

        return $ekey;
    }

    /**
     * Synchronize keys database on multi-host setups
     */
    protected function db_sync()
    {
        if (!$this->rc->config->get('enigma_multihost')) {
            return;
        }

        $db    = $this->rc->get_dbh();
        $table = $db->table_name('filestore', true);
        $files = [];

        $result = $db->query(
            "SELECT `file_id`, `filename`, `mtime` FROM $table WHERE `user_id` = ? AND `context` = ?",
            $this->rc->user->ID, 'enigma'
        );

        while ($record = $db->fetch_assoc($result)) {
            $file  = $this->homedir . '/' . $record['filename'];
            $mtime = @filemtime($file);
            $files[] = $record['filename'];

            if ($mtime < $record['mtime']) {
                $data_result = $db->query("SELECT `data`, `mtime` FROM $table"
                    . " WHERE `file_id` = ?", $record['file_id']
                );

                $record = $db->fetch_assoc($data_result);
                $data   = $record ? base64_decode($record['data']) : null;

                if ($data === null || $data === false) {
                    rcube::raise_error([
                            'code' => 605, 'line' => __LINE__, 'file' => __FILE__,
                            'message' => "Enigma: Failed to sync $file ({$record['file_id']}). Decode error."
                        ], true, false);

                    continue;
                }

                // Private keys might be located in 'private-keys-v1.d' subdirectory. Make sure it exists.
                if (strpos($file, '/private-keys-v1.d/')) {
                    if (!file_exists($this->homedir . '/private-keys-v1.d')) {
                        mkdir($this->homedir . '/private-keys-v1.d', 0700);
                    }
                }

                $tmpfile = $file . '.tmp';

                if (file_put_contents($tmpfile, $data, LOCK_EX) === strlen($data)) {
                    rename($tmpfile, $file);
                    touch($file, $record['mtime']);

                    if ($this->debug) {
                        $this->debug("SYNC: Fetched file: $file");
                    }
                }
                else {
                    // error
                    @unlink($tmpfile);

                    rcube::raise_error([
                            'code' => 605, 'line' => __LINE__, 'file' => __FILE__,
                            'message' => "Enigma: Failed to sync $file."
                        ], true, false);
                }
            }
        }

        // Remove files not in database
        if (!$db->is_error($result)) {
            foreach (array_diff($this->db_files_list(), $files) as $file) {
                $file = $this->homedir . '/' . $file;

                if (unlink($file)) {
                    if ($this->debug) {
                        $this->debug("SYNC: Removed file: $file");
                    }
                }
            }
        }

        // No records found, do initial sync if already have the keyring
        if (!$db->is_error($result) && empty($file)) {
            $this->db_save(true);
        }
    }

    /**
     * Save keys database for multi-host setups
     */
    protected function db_save($is_empty = false)
    {
        if (!$this->rc->config->get('enigma_multihost')) {
            return true;
        }

        $db      = $this->rc->get_dbh();
        $table   = $db->table_name('filestore', true);
        $records = [];

        if (!$is_empty) {
            $result = $db->query(
                "SELECT `file_id`, `filename`, `mtime` FROM $table WHERE `user_id` = ? AND `context` = ?",
                $this->rc->user->ID, 'enigma'
            );

            while ($record = $db->fetch_assoc($result)) {
                $records[$record['filename']] = $record;
            }
        }

        foreach ($this->db_files_list() as $filename) {
            $file  = $this->homedir . '/' . $filename;
            $mtime = @filemtime($file);

            $existing = !empty($records[$filename]) ? $records[$filename] : null;
            unset($records[$filename]);

            if ($mtime && (empty($existing) || $mtime > $existing['mtime'])) {
                $data     = file_get_contents($file);
                $data     = base64_encode($data);
                $datasize = strlen($data);

                if (empty($maxsize)) {
                    $maxsize = min($db->get_variable('max_allowed_packet', 1048500), 4*1024*1024) - 2000;
                }

                if ($datasize > $maxsize) {
                    rcube::raise_error([
                            'code' => 605, 'line' => __LINE__, 'file' => __FILE__,
                            'message' => "Enigma: Failed to save $file. Size exceeds max_allowed_packet."
                        ], true, false);

                    continue;
                }

                $unique = ['user_id' => $this->rc->user->ID, 'context' => 'enigma', 'filename' => $filename];
                $result = $db->insert_or_update($table, $unique, ['mtime', 'data'], [$mtime, $data]);

                if ($db->is_error($result)) {
                    rcube::raise_error([
                            'code' => 605, 'line' => __LINE__, 'file' => __FILE__,
                            'message' => "Enigma: Failed to save $file into database."
                        ], true, false);

                    break;
                }

                if ($this->debug) {
                    $this->debug("SYNC: Pushed file: $file");
                }
            }
        }

        // Delete removed files from database
        foreach (array_keys($records) as $filename) {
            $file   = $this->homedir . '/' . $filename;
            $result = $db->query("DELETE FROM $table WHERE `user_id` = ? AND `context` = ? AND `filename` = ?",
                $this->rc->user->ID, 'enigma', $filename
            );

            if ($db->is_error($result)) {
                rcube::raise_error([
                        'code' => 605, 'line' => __LINE__, 'file' => __FILE__,
                        'message' => "Enigma: Failed to delete $file from database."
                    ], true, false);

                break;
            }

            if ($this->debug) {
                $this->debug("SYNC: Removed file: $file");
            }
        }
    }

    /**
     * Returns list of homedir files to backup
     */
    protected function db_files_list()
    {
        $files = [];

        foreach ($this->db_files as $file) {
            if (file_exists($this->homedir . '/' . $file)) {
                $files[] = $file;
            }
        }

        foreach (glob($this->homedir . '/private-keys-v1.d/*.key') as $file) {
            $files[] = ltrim(substr($file, strlen($this->homedir)), '/');
        }

        return $files;
    }

    /**
     * Write debug info from Crypt_GPG to logs/enigma
     */
    public function debug($line)
    {
        rcube::write_log('enigma', 'GPG: ' . $line);
    }
}
