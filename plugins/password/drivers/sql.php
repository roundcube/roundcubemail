<?php

/**
 * SQL Password Driver
 *
 * Driver for passwords stored in SQL database
 *
 * @version 2.0
 * @author Aleksander 'A.L.E.C' Machniak <alec@alec.pl>
 *
 */

class rcube_sql_password
{
    function save($curpass, $passwd)
    {
        $rcmail = rcmail::get_instance();

        if (!($sql = $rcmail->config->get('password_query')))
            $sql = 'SELECT update_passwd(%c, %u)';

        if ($dsn = $rcmail->config->get('password_db_dsn')) {
            // #1486067: enable new_link option
            if (is_array($dsn) && empty($dsn['new_link']))
                $dsn['new_link'] = true;
            else if (!is_array($dsn) && !preg_match('/\?new_link=true/', $dsn))
                $dsn .= '?new_link=true';

            $db = rcube_db::factory($dsn, '', false);
            $db->set_debug((bool)$rcmail->config->get('sql_debug'));
            $db->db_connect('w');
        }
        else {
            $db = $rcmail->get_dbh();
        }

        if ($db->is_error()) {
            return PASSWORD_ERROR;
        }

        // crypted password
        if (strpos($sql, '%c') !== FALSE) {
            $salt = '';

            if (!($crypt_hash = $rcmail->config->get('password_crypt_hash')))
            {
                if (CRYPT_MD5)
                    $crypt_hash = 'md5';
                else if (CRYPT_STD_DES)
                    $crypt_hash = 'des';
            }

            switch ($crypt_hash)
            {
            case 'md5':
                $len = 8;
                $salt_hashindicator = '$1$';
                break;
            case 'des':
                $len = 2;
                break;
            case 'blowfish':
                $len = 22;
                $salt_hashindicator = '$2a$';
                break;
            case 'sha256':
                $len = 16;
                $salt_hashindicator = '$5$';
                break;
            case 'sha512':
                $len = 16;
                $salt_hashindicator = '$6$';
                break;
            default:
                return PASSWORD_CRYPT_ERROR;
            }

            //Restrict the character set used as salt (#1488136)
            $seedchars = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            for ($i = 0; $i < $len ; $i++) {
                $salt .= $seedchars[rand(0, 63)];
            }

            $sql = str_replace('%c',  $db->quote(crypt($passwd, $salt_hashindicator ? $salt_hashindicator .$salt.'$' : $salt)), $sql);
        }

        // dovecotpw
        if (strpos($sql, '%D') !== FALSE) {
            if (!($dovecotpw = $rcmail->config->get('password_dovecotpw')))
                $dovecotpw = 'dovecotpw';
            if (!($method = $rcmail->config->get('password_dovecotpw_method')))
                $method = 'CRAM-MD5';

            // use common temp dir
            $tmp_dir = $rcmail->config->get('temp_dir');
            $tmpfile = tempnam($tmp_dir, 'roundcube-');

            $pipe = popen("$dovecotpw -s '$method' > '$tmpfile'", "w");
            if (!$pipe) {
                unlink($tmpfile);
                return PASSWORD_CRYPT_ERROR;
            }
            else {
                fwrite($pipe, $passwd . "\n", 1+strlen($passwd)); usleep(1000);
                fwrite($pipe, $passwd . "\n", 1+strlen($passwd));
                pclose($pipe);
                $newpass = trim(file_get_contents($tmpfile), "\n");
                if (!preg_match('/^\{' . $method . '\}/', $newpass)) {
                    return PASSWORD_CRYPT_ERROR;
                }
                if (!$rcmail->config->get('password_dovecotpw_with_method'))
                    $newpass = trim(str_replace('{' . $method . '}', '', $newpass));
                unlink($tmpfile);
            }
            $sql = str_replace('%D', $db->quote($newpass), $sql);
        }

        // hashed passwords
        if (preg_match('/%[n|q]/', $sql)) {
            if (!extension_loaded('hash')) {
                rcube::raise_error(array(
                    'code' => 600,
                    'type' => 'php',
                    'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: 'hash' extension not loaded!"
                ), true, false);

                return PASSWORD_ERROR;
            }

            if (!($hash_algo = strtolower($rcmail->config->get('password_hash_algorithm'))))
                $hash_algo = 'sha1';

            $hash_passwd = hash($hash_algo, $passwd);
            $hash_curpass = hash($hash_algo, $curpass);

            if ($rcmail->config->get('password_hash_base64')) {
                $hash_passwd = base64_encode(pack('H*', $hash_passwd));
                $hash_curpass = base64_encode(pack('H*', $hash_curpass));
            }

            $sql = str_replace('%n', $db->quote($hash_passwd, 'text'), $sql);
            $sql = str_replace('%q', $db->quote($hash_curpass, 'text'), $sql);
        }

        // Handle clear text passwords securely (#1487034)
        $sql_vars = array();
        if (preg_match_all('/%[p|o]/', $sql, $m)) {
            foreach ($m[0] as $var) {
                if ($var == '%p') {
                    $sql = preg_replace('/%p/', '?', $sql, 1);
                    $sql_vars[] = (string) $passwd;
                }
                else { // %o
                    $sql = preg_replace('/%o/', '?', $sql, 1);
                    $sql_vars[] = (string) $curpass;
                }
            }
        }

        $local_part  = $rcmail->user->get_username('local');
        $domain_part = $rcmail->user->get_username('domain');
        $username    = $_SESSION['username'];
        $host        = $_SESSION['imap_host'];

        // convert domains to/from punnycode
        if ($rcmail->config->get('password_idn_ascii')) {
            $domain_part = rcube_utils::idn_to_ascii($domain_part);
            $username    = rcube_utils::idn_to_ascii($username);
            $host        = rcube_utils::idn_to_ascii($host);
        }
        else {
            $domain_part = rcube_utils::idn_to_utf8($domain_part);
            $username    = rcube_utils::idn_to_utf8($username);
            $host        = rcube_utils::idn_to_utf8($host);
        }

        // at least we should always have the local part
        $sql = str_replace('%l', $db->quote($local_part, 'text'), $sql);
        $sql = str_replace('%d', $db->quote($domain_part, 'text'), $sql);
        $sql = str_replace('%u', $db->quote($username, 'text'), $sql);
        $sql = str_replace('%h', $db->quote($host, 'text'), $sql);

        $res = $db->query($sql, $sql_vars);

        if (!$db->is_error()) {
            if (strtolower(substr(trim($sql),0,6)) == 'select') {
                if ($db->fetch_array($res))
                    return PASSWORD_SUCCESS;
            } else {
                // This is the good case: 1 row updated
                if ($db->affected_rows($res) == 1)
                    return PASSWORD_SUCCESS;
                // @TODO: Some queries don't affect any rows
                // Should we assume a success if there was no error?
            }
        }

        return PASSWORD_ERROR;
    }
}
