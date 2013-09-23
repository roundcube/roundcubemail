<?php

/**
 * Simple LDAP Password Driver
 *
 * Driver for passwords stored in LDAP
 * This driver is based on Edouard's LDAP Password Driver, but does not
 * require PEAR's Net_LDAP2 to be installed
 *
 * @version 2.0
 * @author Wout Decre <wout@canodus.be>
 */

class rcube_ldap_simple_password
{
    function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();

        // Connect
        if (!$ds = ldap_connect($rcmail->config->get('password_ldap_host'), $rcmail->config->get('password_ldap_port'))) {
            ldap_unbind($ds);
            return PASSWORD_CONNECT_ERROR;
        }

        // Set protocol version
        if (!ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $rcmail->config->get('password_ldap_version'))) {
            ldap_unbind($ds);
            return PASSWORD_CONNECT_ERROR;
        }

        // Start TLS
        if ($rcmail->config->get('password_ldap_starttls')) {
            if (!ldap_start_tls($ds)) {
                ldap_unbind($ds);
                return PASSWORD_CONNECT_ERROR;
            }
        }

        // include 'ldap' driver, we share some static methods with it
        require_once INSTALL_PATH . 'plugins/password/drivers/ldap.php';

        // Build user DN
        if ($user_dn = $rcmail->config->get('password_ldap_userDN_mask')) {
            $user_dn = rcube_ldap_password::substitute_vars($user_dn);
        }
        else {
            $user_dn = $this->search_userdn($rcmail, $ds);
        }

        if (empty($user_dn)) {
            ldap_unbind($ds);
            return PASSWORD_CONNECT_ERROR;
        }

        // Connection method
        switch ($rcmail->config->get('password_ldap_method')) {
        case 'admin':
            $binddn = $rcmail->config->get('password_ldap_adminDN');
            $bindpw = $rcmail->config->get('password_ldap_adminPW');
            break;
        case 'user':
        default:
            $binddn = $user_dn;
            $bindpw = $curpass;
            break;
        }

        $lchattr      = $rcmail->config->get('password_ldap_lchattr');
        $pwattr       = $rcmail->config->get('password_ldap_pwattr');
        $smbpwattr    = $rcmail->config->get('password_ldap_samba_pwattr');
        $smblchattr   = $rcmail->config->get('password_ldap_samba_lchattr');
        $samba        = $rcmail->config->get('password_ldap_samba');
        $pass_mode    = $rcmail->config->get('password_ldap_encodage');
        $crypted_pass = rcube_ldap_password::hash_password($passwd, $pass_mode);

        // Support password_ldap_samba option for backward compat.
        if ($samba && !$smbpwattr) {
            $smbpwattr  = 'sambaNTPassword';
            $smblchattr = 'sambaPwdLastSet';
        }

        // Crypt new password
        if (!$crypted_pass) {
            return PASSWORD_CRYPT_ERROR;
        }

        // Crypt new Samba password
        if ($smbpwattr && !($samba_pass = rcube_ldap_password::hash_password($passwd, 'samba'))) {
            return PASSWORD_CRYPT_ERROR;
        }

        // Bind
        if (!ldap_bind($ds, $binddn, $bindpw)) {
            ldap_unbind($ds);
            return PASSWORD_CONNECT_ERROR;
        }

        $entree[$pwattr] = $crypted_pass;

        // Update PasswordLastChange Attribute if desired
        if ($lchattr) {
            $entree[$lchattr] = (int)(time() / 86400);
        }

        // Update Samba password
        if ($smbpwattr) {
            $entree[$smbpwattr] = $samba_pass;
        }

        // Update Samba password last change
        if ($smblchattr) {
            $entree[$smblchattr] = time();
        }

        if (!ldap_modify($ds, $user_dn, $entree)) {
            ldap_unbind($ds);
            return PASSWORD_CONNECT_ERROR;
        }

        // All done, no error
        ldap_unbind($ds);
        return PASSWORD_SUCCESS;
    }

    /**
     * Bind with searchDN and searchPW and search for the user's DN
     * Use search_base and search_filter defined in config file
     * Return the found DN
     */
    function search_userdn($rcmail, $ds)
    {
        $search_user = $rcmail->config->get('password_ldap_searchDN');
        $search_pass = $rcmail->config->get('password_ldap_searchPW');

        // Bind
        if (!ldap_bind($ds, $search_user, $search_pass)) {
            return false;
        }

        $search_base   = $rcmail->config->get('password_ldap_search_base');
        $search_filter = $rcmail->config->get('password_ldap_search_filter');
        $search_filter = rcube_ldap_password::substitute_vars($search_filter);

        // Search for the DN
        if (!$sr = ldap_search($ds, $search_base, $search_filter)) {
            return false;
        }

        // If no or more entries were found, return false
        if (ldap_count_entries($ds, $sr) != 1) {
            return false;
        }

        return ldap_get_dn($ds, ldap_first_entry($ds, $sr));
    }
}
