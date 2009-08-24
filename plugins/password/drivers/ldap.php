<?php

/**
 * LDAP Password Driver
 *
 * Driver for passwords stored in LDAP
 * This driver use the PEAR Net_LDAP2 class (http://pear.php.net/package/Net_LDAP2).
 *
 * @version 1.0 (2009-06-24)
 * @author Edouard MOREAU <edouard.moreau@ensma.fr>
 *
 * function hashPassword based on code from the phpLDAPadmin development team (http://phpldapadmin.sourceforge.net/).
 * function randomSalt based on code from the phpLDAPadmin development team (http://phpldapadmin.sourceforge.net/).
 *
 */

function password_save($curpass, $passwd)
{
    $rcmail = rcmail::get_instance();
    require_once ('Net/LDAP2.php');
    
    // Building user DN
    $userDN = str_replace('%login', $_SESSION['username'], $rcmail->config->get('password_ldap_userDN_mask'));
    
    $parts = explode('@', $_SESSION['username']);
    if (count($parts) == 2)
    {
        $userDN = str_replace('%name', $parts[0], $userDN);
        $userDN = str_replace('%domain', $parts[1], $userDN);
    }

    if (empty($userDN)) {return PASSWORD_CONNECT_ERROR;}
    
    // Connection Method
    switch($rcmail->config->get('password_ldap_method')) {
        case 'user': $binddn = $userDN; $bindpw = $curpass; break;
        case 'admin': $binddn = $rcmail->config->get('password_ldap_adminDN'); $bindpw = $rcmail->config->get('password_ldap_adminPW'); break;
        default: $binddn = $userDN; $bindpw = $curpass; break; // default is user mode
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
    if (PEAR::isError($ldap)) {return PASSWORD_CONNECT_ERROR;}
    
    // Crypting new password
    $newCryptedPassword = hashPassword($passwd, $rcmail->config->get('password_ldap_encodage'));
    if (!$newCryptedPassword) {return PASSWORD_CRYPT_ERROR;}
    
    // Writing new crypted password to LDAP
    $userEntry = $ldap->getEntry($userDN);
    if (Net_LDAP2::isError($userEntry)) {return PASSWORD_CONNECT_ERROR;}
    if (!$userEntry->replace(array($rcmail->config->get('password_ldap_pwattr') => $newCryptedPassword),$rcmail->config->get('password_ldap_force_replace'))) {return PASSWORD_CONNECT_ERROR;}
    if (Net_LDAP2::isError($userEntry->update())) {return PASSWORD_CONNECT_ERROR;}
    
    // All done, no error
    return PASSWORD_SUCCESS;    
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
            $cryptedPassword = '{CRYPT}' . crypt($passwordClear,randomSalt(2)); 
            break;
            
        case 'ext_des':
            // extended des crypt. see OpenBSD crypt man page.
            if ( ! defined( 'CRYPT_EXT_DES' ) || CRYPT_EXT_DES == 0 ) {return FALSE;} //Your system crypt library does not support extended DES encryption.
            $cryptedPassword = '{CRYPT}' . crypt( $passwordClear, '_' . randomSalt(8) );
            break;

        case 'md5crypt':
            if( ! defined( 'CRYPT_MD5' ) || CRYPT_MD5 == 0 ) {return FALSE;} //Your system crypt library does not support md5crypt encryption.
            $cryptedPassword = '{CRYPT}' . crypt( $passwordClear , '$1$' . randomSalt(9) );
            break;

        case 'blowfish':
            if( ! defined( 'CRYPT_BLOWFISH' ) || CRYPT_BLOWFISH == 0 ) {return FALSE;} //Your system crypt library does not support blowfish encryption.
            $cryptedPassword = '{CRYPT}' . crypt( $passwordClear , '$2a$12$' . randomSalt(13) ); // hardcoded to second blowfish version and set number of rounds
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
                $salt = mhash_keygen_s2k( MHASH_SHA1, $passwordClear, substr( pack( "h*", md5( mt_rand() ) ), 0, 8 ), 4 );
                $cryptedPassword = "{SSHA}".base64_encode( mhash( MHASH_SHA1, $passwordClear.$salt ).$salt );
            } else {
                return FALSE; //Your PHP install does not have the mhash() function. Cannot do SHA hashes.
            }
            break;

        case 'smd5':
            if( function_exists( 'mhash' ) && function_exists( 'mhash_keygen_s2k' ) ) {
                mt_srand( (double) microtime() * 1000000 );
                $salt = mhash_keygen_s2k( MHASH_MD5, $passwordClear, substr( pack( "h*", md5( mt_rand() ) ), 0, 8 ), 4 );
                $cryptedPassword = "{SMD5}".base64_encode( mhash( MHASH_MD5, $passwordClear.$salt ).$salt );
            } else {
                return FALSE; //Your PHP install does not have the mhash() function. Cannot do SHA hashes.
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
    $str = "";
    mt_srand((double)microtime() * 1000000);

    while( strlen( $str ) < $length )
        $str .= substr( $possible, ( rand() % strlen( $possible ) ), 1 );

    return $str;
}

?>
