<?php

/**
 * LDAP Password Driver
 *
 * Driver for passwords stored in LDAP
 * This driver use the PEAR Net_LDAP2 class (http://pear.php.net/package/Net_LDAP2).
 *
 * @version 2.0
 * @author Edouard MOREAU <edouard.moreau@ensma.fr>
 *
 * method hashPassword based on code from the phpLDAPadmin development team (http://phpldapadmin.sourceforge.net/).
 * method randomSalt based on code from the phpLDAPadmin development team (http://phpldapadmin.sourceforge.net/).
 *
 */

class rcube_ldap_password
{
    public function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();
        require_once 'Net/LDAP2.php';

        // Building user DN
        if ($userDN = $rcmail->config->get('password_ldap_userDN_mask')) {
            $userDN = $this->substitute_vars($userDN);
        } else {
            $userDN = $this->search_userdn($rcmail);
        }

        if (empty($userDN)) {
            return PASSWORD_CONNECT_ERROR;
        }

        // Connection Method
        switch($rcmail->config->get('password_ldap_method')) {
            case 'admin':
                $binddn = $rcmail->config->get('password_ldap_adminDN');
                $bindpw = $rcmail->config->get('password_ldap_adminPW');
                break;
            case 'user':
            default:
                $binddn = $userDN;
                $bindpw = $curpass;
                break;
        }

        // Configuration array
        $ldapConfig = array (
            'binddn'    => $binddn,
            'bindpw'    => $bindpw,
            'basedn'    => $rcmail->config->get('password_ldap_basedn'),
            'host'      => $rcmail->config->get('password_ldap_host'),
            'port'      => $rcmail->config->get('password_ldap_port'),
            'starttls'  => $rcmail->config->get('password_ldap_starttls'),
            'version'   => $rcmail->config->get('password_ldap_version'),
        );

        // Connecting using the configuration array
        $ldap = Net_LDAP2::connect($ldapConfig);

        // Checking for connection error
        if (PEAR::isError($ldap)) {
            return PASSWORD_CONNECT_ERROR;
        }

        $crypted_pass = $this->hashPassword($passwd, $rcmail->config->get('password_ldap_encodage'));
        $force        = $rcmail->config->get('password_ldap_force_replace');
        $pwattr       = $rcmail->config->get('password_ldap_pwattr');
        $lchattr      = $rcmail->config->get('password_ldap_lchattr');
        $smbpwattr    = $rcmail->config->get('password_ldap_samba_pwattr');
        $smblchattr   = $rcmail->config->get('password_ldap_samba_lchattr');
        $samba        = $rcmail->config->get('password_ldap_samba');

        // Support password_ldap_samba option for backward compat.
        if ($samba && !$smbpwattr) {
            $smbpwattr  = 'sambaNTPassword';
            $smblchattr = 'sambaPwdLastSet';
        }

        // Crypt new password
        if (!$crypted_pass) {
            return PASSWORD_CRYPT_ERROR;
        }

        // Crypt new samba password
        if ($smbpwattr && !($samba_pass = $this->hashPassword($passwd, 'samba'))) {
            return PASSWORD_CRYPT_ERROR;
        }

        // Writing new crypted password to LDAP
        $userEntry = $ldap->getEntry($userDN);
        if (Net_LDAP2::isError($userEntry)) {
            return PASSWORD_CONNECT_ERROR;
        }

        if (!$userEntry->replace(array($pwattr => $crypted_pass), $force)) {
            return PASSWORD_CONNECT_ERROR;
        }

        // Updating PasswordLastChange Attribute if desired
        if ($lchattr) {
            $current_day = (int)(time() / 86400);
            if (!$userEntry->replace(array($lchattr => $current_day), $force)) {
                return PASSWORD_CONNECT_ERROR;
            }
        }

        // Update Samba password and last change fields
        if ($smbpwattr) {
            $userEntry->replace(array($smbpwattr => $samba_pass), $force);
        }
        // Update Samba password last change field
        if ($smblchattr) {
            $userEntry->replace(array($smblchattr => time()), $force);
        }

        if (Net_LDAP2::isError($userEntry->update())) {
            return PASSWORD_CONNECT_ERROR;
        }

        // All done, no error
        return PASSWORD_SUCCESS;
    }

    /**
     * Bind with searchDN and searchPW and search for the user's DN.
     * Use search_base and search_filter defined in config file.
     * Return the found DN.
     */
    function search_userdn($rcmail)
    {
        $ldapConfig = array (
            'binddn'    => $rcmail->config->get('password_ldap_searchDN'),
            'bindpw'    => $rcmail->config->get('password_ldap_searchPW'),
            'basedn'    => $rcmail->config->get('password_ldap_basedn'),
            'host'      => $rcmail->config->get('password_ldap_host'),
            'port'      => $rcmail->config->get('password_ldap_port'),
            'starttls'  => $rcmail->config->get('password_ldap_starttls'),
            'version'   => $rcmail->config->get('password_ldap_version'),
        );

        $ldap = Net_LDAP2::connect($ldapConfig);

        if (PEAR::isError($ldap)) {
            return '';
        }

        $base = $rcmail->config->get('password_ldap_search_base');
        $filter = $this->substitute_vars($rcmail->config->get('password_ldap_search_filter'));
        $options = array (
            'scope' => 'sub',
            'attributes' => array(),
        );

        $result = $ldap->search($base, $filter, $options);
        $ldap->done();
        if (PEAR::isError($result) || ($result->count() != 1)) {
            return '';
        }

        return $result->current()->dn();
    }

    /**
     * Substitute %login, %name, %domain, %dc in $str.
     * See plugin config for details.
     */
    function substitute_vars($str)
    {
        $rcmail = rcmail::get_instance();
        $domain = $rcmail->user->get_username('domain');
        $dc     = 'dc='.strtr($domain, array('.' => ',dc=')); // hierarchal domain string

        $str = str_replace(array(
                '%login',
                '%name',
                '%domain',
                '%dc',
            ), array(
                $_SESSION['username'],
                $rcmail->user->get_username('local'),
                $domain,
                $dc,
            ), $str
        );

        return $str;
    }

    /**
     * Code originaly from the phpLDAPadmin development team
     * http://phpldapadmin.sourceforge.net/
     *
     * Hashes a password and returns the hash based on the specified enc_type.
     *
     * @param string $passwordClear The password to hash in clear text.
     * @param string $encodageType Standard LDAP encryption type which must be one of
     *        crypt, ext_des, md5crypt, blowfish, md5, sha, smd5, ssha, or clear.
     * @return string The hashed password.
     *
     */
    function hashPassword( $passwordClear, $encodageType )
    {
        $encodageType = strtolower( $encodageType );
        switch( $encodageType ) {
        case 'crypt':
            $cryptedPassword = '{CRYPT}' . crypt($passwordClear, $this->randomSalt(2));
            break;

        case 'ext_des':
            // extended des crypt. see OpenBSD crypt man page.
            if ( ! defined( 'CRYPT_EXT_DES' ) || CRYPT_EXT_DES == 0 ) {
                // Your system crypt library does not support extended DES encryption.
                return FALSE;
            }
            $cryptedPassword = '{CRYPT}' . crypt( $passwordClear, '_' . $this->randomSalt(8) );
            break;

        case 'md5crypt':
            if( ! defined( 'CRYPT_MD5' ) || CRYPT_MD5 == 0 ) {
                // Your system crypt library does not support md5crypt encryption.
                return FALSE;
            }
            $cryptedPassword = '{CRYPT}' . crypt( $passwordClear , '$1$' . $this->randomSalt(9) );
            break;

        case 'blowfish':
            if( ! defined( 'CRYPT_BLOWFISH' ) || CRYPT_BLOWFISH == 0 ) {
                // Your system crypt library does not support blowfish encryption.
                return FALSE;
            }
            // hardcoded to second blowfish version and set number of rounds
            $cryptedPassword = '{CRYPT}' . crypt( $passwordClear , '$2a$12$' . $this->randomSalt(13) );
            break;

        case 'md5':
            $cryptedPassword = '{MD5}' . base64_encode( pack( 'H*' , md5( $passwordClear) ) );
            break;

        case 'sha':
            if( function_exists('sha1') ) {
                // use php 4.3.0+ sha1 function, if it is available.
                $cryptedPassword = '{SHA}' . base64_encode( pack( 'H*' , sha1( $passwordClear) ) );
            } elseif( function_exists( 'mhash' ) ) {
                $cryptedPassword = '{SHA}' . base64_encode( mhash( MHASH_SHA1, $passwordClear) );
            } else {
                return FALSE; //Your PHP install does not have the mhash() function. Cannot do SHA hashes.
            }
            break;

        case 'ssha':
            if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
                mt_srand( (double) microtime() * 1000000 );
                $salt = mhash_keygen_s2k( MHASH_SHA1, $passwordClear, substr( pack( 'h*', md5( mt_rand() ) ), 0, 8 ), 4 );
                $cryptedPassword = '{SSHA}'.base64_encode( mhash( MHASH_SHA1, $passwordClear.$salt ).$salt );
            } else {
                return FALSE; //Your PHP install does not have the mhash() function. Cannot do SHA hashes.
            }
            break;

        case 'smd5':
            if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
                mt_srand( (double) microtime() * 1000000 );
                $salt = mhash_keygen_s2k( MHASH_MD5, $passwordClear, substr( pack( 'h*', md5( mt_rand() ) ), 0, 8 ), 4 );
                $cryptedPassword = '{SMD5}'.base64_encode( mhash( MHASH_MD5, $passwordClear.$salt ).$salt );
            } else {
                return FALSE; //Your PHP install does not have the mhash() function. Cannot do SHA hashes.
            }
            break;

        case 'samba':
            if (function_exists('hash')) {
                $cryptedPassword = hash('md4', rcube_charset::convert($passwordClear, RCUBE_CHARSET, 'UTF-16LE'));
                $cryptedPassword = strtoupper($cryptedPassword);
            } else {
                /* Your PHP install does not have the hash() function */
                return false;
            }
            break;

        case 'clear':
        default:
            $cryptedPassword = $passwordClear;
        }

        return $cryptedPassword;
    }

    /**
     * Code originaly from the phpLDAPadmin development team
     * http://phpldapadmin.sourceforge.net/
     *
     * Used to generate a random salt for crypt-style passwords. Salt strings are used
     * to make pre-built hash cracking dictionaries difficult to use as the hash algorithm uses
     * not only the user's password but also a randomly generated string. The string is
     * stored as the first N characters of the hash for reference of hashing algorithms later.
     *
     * --- added 20021125 by bayu irawan <bayuir@divnet.telkom.co.id> ---
     * --- ammended 20030625 by S C Rigler <srigler@houston.rr.com> ---
     *
     * @param int $length The length of the salt string to generate.
     * @return string The generated salt string.
     */
    function randomSalt( $length )
    {
        $possible = '0123456789'.
            'abcdefghijklmnopqrstuvwxyz'.
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
            './';
        $str = '';
        // mt_srand((double)microtime() * 1000000);

        while (strlen($str) < $length)
            $str .= substr($possible, (rand() % strlen($possible)), 1);

        return $str;
    }
}
