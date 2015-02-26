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
 * Copyright (C) 2005-2014, The Roundcube Dev Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

class rcube_ldap_password
{
    public function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();
        require_once 'Net/LDAP2.php';

        // Building user DN
        if ($userDN = $rcmail->config->get('password_ldap_userDN_mask')) {
            $userDN = self::substitute_vars($userDN);
        }
        else {
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
        if (is_a($ldap, 'PEAR_Error')) {
            return PASSWORD_CONNECT_ERROR;
        }

        $force        = $rcmail->config->get('password_ldap_force_replace');
        $pwattr       = $rcmail->config->get('password_ldap_pwattr');
        $lchattr      = $rcmail->config->get('password_ldap_lchattr');
        $smbpwattr    = $rcmail->config->get('password_ldap_samba_pwattr');
        $smblchattr   = $rcmail->config->get('password_ldap_samba_lchattr');
        $samba        = $rcmail->config->get('password_ldap_samba');
        $encodage     = $rcmail->config->get('password_ldap_encodage');

        // Support multiple userPassword values where desired.
        // multiple encodings can be specified separated by '+' (e.g. "cram-md5+ssha")
        $encodages    = explode('+', $encodage);
        $crypted_pass = array();

        foreach ($encodages as $enc) {
            $cpw = self::hash_password($passwd, $enc);
            if (!empty($cpw)) {
                $crypted_pass[] = $cpw;
            }
        }

        // Support password_ldap_samba option for backward compat.
        if ($samba && !$smbpwattr) {
            $smbpwattr  = 'sambaNTPassword';
            $smblchattr = 'sambaPwdLastSet';
        }

        // Crypt new password
        if (empty($crypted_pass)) {
            return PASSWORD_CRYPT_ERROR;
        }

        // Crypt new samba password
        if ($smbpwattr && !($samba_pass = self::hash_password($passwd, 'samba'))) {
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
        $binddn = $rcmail->config->get('password_ldap_searchDN');
        $bindpw = $rcmail->config->get('password_ldap_searchPW');

        $ldapConfig = array (
            'basedn'    => $rcmail->config->get('password_ldap_basedn'),
            'host'      => $rcmail->config->get('password_ldap_host'),
            'port'      => $rcmail->config->get('password_ldap_port'),
            'starttls'  => $rcmail->config->get('password_ldap_starttls'),
            'version'   => $rcmail->config->get('password_ldap_version'),
        );

        // allow anonymous searches
        if (!empty($binddn)) {
            $ldapConfig['binddn'] = $binddn;
            $ldapConfig['bindpw'] = $bindpw;
        }

        $ldap = Net_LDAP2::connect($ldapConfig);

        if (is_a($ldap, 'PEAR_Error')) {
            return '';
        }

        $base   = self::substitute_vars($rcmail->config->get('password_ldap_search_base'));
        $filter = self::substitute_vars($rcmail->config->get('password_ldap_search_filter'));
        $options = array (
            'scope' => 'sub',
            'attributes' => array(),
        );

        $result = $ldap->search($base, $filter, $options);
        $ldap->done();
        if (is_a($result, 'PEAR_Error') || ($result->count() != 1)) {
            return '';
        }

        return $result->current()->dn();
    }

    /**
     * Substitute %login, %name, %domain, %dc in $str
     * See plugin config for details
     */
    static function substitute_vars($str)
    {
        $str = str_replace('%login', $_SESSION['username'], $str);
        $str = str_replace('%l', $_SESSION['username'], $str);

        $parts = explode('@', $_SESSION['username']);

        if (count($parts) == 2) {
            $dc = 'dc='.strtr($parts[1], array('.' => ',dc=')); // hierarchal domain string

            $str = str_replace('%name', $parts[0], $str);
            $str = str_replace('%n', $parts[0], $str);
            $str = str_replace('%dc', $dc, $str);
            $str = str_replace('%domain', $parts[1], $str);
            $str = str_replace('%d', $parts[1], $str);
        }

        return $str;
    }

    /**
     * Code originaly from the phpLDAPadmin development team
     * http://phpldapadmin.sourceforge.net/
     *
     * Hashes a password and returns the hash based on the specified enc_type
     */
    static function hash_password($password_clear, $encodage_type)
    {
        $encodage_type = strtolower($encodage_type);
        switch ($encodage_type) {
        case 'crypt':
            $crypted_password = '{CRYPT}' . crypt($password_clear, self::random_salt(2));
            break;

        case 'ext_des':
            /* Extended DES crypt. see OpenBSD crypt man page */
            if (!defined('CRYPT_EXT_DES') || CRYPT_EXT_DES == 0) {
                /* Your system crypt library does not support extended DES encryption */
                return false;
            }

            $crypted_password = '{CRYPT}' . crypt($password_clear, '_' . self::random_salt(8));
            break;

        case 'md5crypt':
            if (!defined('CRYPT_MD5') || CRYPT_MD5 == 0) {
                /* Your system crypt library does not support md5crypt encryption */
                return false;
            }

            $crypted_password = '{CRYPT}' . crypt($password_clear, '$1$' . self::random_salt(9));
            break;

        case 'blowfish':
            if (!defined('CRYPT_BLOWFISH') || CRYPT_BLOWFISH == 0) {
                /* Your system crypt library does not support blowfish encryption */
                return false;
            }

            $rcmail = rcmail::get_instance();
            $cost   = (int) $rcmail->config->get('password_blowfish_cost');
            $cost   = $cost < 4 || $cost > 31 ? 12 : $cost;
            $prefix = sprintf('$2a$%02d$', $cost);

            $crypted_password = '{CRYPT}' . crypt($password_clear, $prefix . self::random_salt(22));
            break;

        case 'md5':
            $crypted_password = '{MD5}' . base64_encode(pack('H*', md5($password_clear)));
            break;

        case 'sha':
            if (function_exists('sha1')) {
                /* Use PHP 4.3.0+ sha1 function, if it is available */
                $crypted_password = '{SHA}' . base64_encode(pack('H*', sha1($password_clear)));
            }
            else if (function_exists('hash')) {
                $crypted_password = '{SHA}' . base64_encode(hash('sha1', $password_clear, true));
            }
            else if (function_exists('mhash')) {
                $crypted_password = '{SHA}' . base64_encode(mhash(MHASH_SHA1, $password_clear));
            }
            else {
                /* Your PHP install does not have the mhash()/hash() nor sha1() function */
                return false;
            }
            break;

        case 'ssha':
            mt_srand((double) microtime() * 1000000);
            $salt = substr(pack('h*', md5(mt_rand())), 0, 8);

            if (function_exists('mhash') && function_exists('mhash_keygen_s2k')) {
                $salt     = mhash_keygen_s2k(MHASH_SHA1, $password_clear, $salt, 4);
                $password = mhash(MHASH_SHA1, $password_clear . $salt);
            }
            else if (function_exists('sha1')) {
                $salt     = substr(pack("H*", sha1($salt . $password_clear)), 0, 4);
                $password = sha1($password_clear . $salt, true);
            }
            else if (function_exists('hash')) {
                $salt     = substr(pack("H*", hash('sha1', $salt . $password_clear)), 0, 4);
                $password = hash('sha1', $password_clear . $salt, true);
            }

            if ($password) {
                $crypted_password = '{SSHA}' . base64_encode($password . $salt);
            }
            else {
                /* Your PHP install does not have the mhash()/hash() nor sha1() function */
                return false;
            }
            break;


        case 'smd5':
            mt_srand((double) microtime() * 1000000);
            $salt = substr(pack('h*', md5(mt_rand())), 0, 8);

            if (function_exists('mhash') && function_exists('mhash_keygen_s2k')) {
                $salt     = mhash_keygen_s2k(MHASH_MD5, $password_clear, $salt, 4);
                $password = mhash(MHASH_MD5, $password_clear . $salt);
            }
            else if (function_exists('hash')) {
                $salt     = substr(pack("H*", hash('md5', $salt . $password_clear)), 0, 4);
                $password = hash('md5', $password_clear . $salt, true);
            }
            else {
                $salt     = substr(pack("H*", md5($salt . $password_clear)), 0, 4);
                $password = md5($password_clear . $salt, true);
            }

            $crypted_password = '{SMD5}' . base64_encode($password . $salt);
            break;

        case 'samba':
            if (function_exists('hash')) {
                $crypted_password = hash('md4', rcube_charset::convert($password_clear, RCUBE_CHARSET, 'UTF-16LE'));
                $crypted_password = strtoupper($crypted_password);
            }
            else {
                /* Your PHP install does not have the hash() function */
                return false;
            }
            break;

        case 'ad':
            $crypted_password = rcube_charset::convert('"' . $password_clear . '"', RCUBE_CHARSET, 'UTF-16LE');
            break;

        case 'cram-md5':
            require_once __DIR__ . '/../helpers/dovecot_hmacmd5.php';
            return dovecot_hmacmd5($password_clear);
            break;

        case 'clear':
        default:
            $crypted_password = $password_clear;
        }

        return $crypted_password;
    }

    /**
     * Code originaly from the phpLDAPadmin development team
     * http://phpldapadmin.sourceforge.net/
     *
     * Used to generate a random salt for crypt-style passwords
     */
    static function random_salt($length)
    {
        $possible = '0123456789' . 'abcdefghijklmnopqrstuvwxyz' . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . './';
        $str = '';
        // mt_srand((double)microtime() * 1000000);

        while (strlen($str) < $length) {
            $str .= substr($possible, (rand() % strlen($possible)), 1);
        }

        return $str;
    }
}
