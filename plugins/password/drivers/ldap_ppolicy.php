<?php

/**
 * ldap_ppolicy driver
 *
 * Driver that adds functionality to change the user password via
 * the 'change_ldap_pass.pl' command respecting password policy (history) in LDAP.
 *
 * @version 1.0
 * @author Zbigniew Szmyd <zbigniew.szmyd@linseco.pl>
 */

class rcube_ldap_ppolicy_password
{
    protected $debug = false;

    public function save($currpass, $newpass, $username)
    {
        $rcmail = rcmail::get_instance();
        $this->debug = $rcmail->config->get('ldap_debug');

        $cmd    = $rcmail->config->get('password_ldap_ppolicy_cmd');
        $uri    = $rcmail->config->get('password_ldap_ppolicy_uri');
        $baseDN = $rcmail->config->get('password_ldap_ppolicy_basedn');
        $filter = $rcmail->config->get('password_ldap_ppolicy_search_filter');
        $bindDN = $rcmail->config->get('password_ldap_ppolicy_searchDN');
        $bindPW = $rcmail->config->get('password_ldap_ppolicy_searchPW');
        $cafile = $rcmail->config->get('password_ldap_ppolicy_cafile');

        $log_dir = $rcmail->config->get('log_dir');

        if (empty($log_dir)) {
            $log_dir = RCUBE_INSTALL_PATH . 'logs';
        }

        $descriptorspec = [
            0 => ["pipe", "r"], // stdin is a pipe that the child will read from
            1 => ["pipe", "w"], // stdout is a pipe that the child will write to
            2 => ["pipe", "w"]  // stderr is a pipe that the child will write to
        ];

        $cmd = 'plugins/password/helpers/'. $cmd;

        $this->_debug('Policy request: ' . json_encode([
            'user'   => $username,
            'cmd'    => $cmd,
            'uri'    => $uri,
            'baseDN' => $baseDN,
            'filter' => $filter,
        ]));

        $process = proc_open($cmd, $descriptorspec, $pipes);

        if ($process) {
            // $pipes now looks like this:
            // 0 => writeable handle connected to child stdin
            // 1 => readable handle connected to child stdout
            // Any error output will be appended to /tmp/error-output.txt

            fwrite($pipes[0], $uri."\n");
            fwrite($pipes[0], $baseDN."\n");
            fwrite($pipes[0], $filter."\n");
            fwrite($pipes[0], $bindDN."\n");
            fwrite($pipes[0], $bindPW."\n");
            fwrite($pipes[0], $username."\n");
            fwrite($pipes[0], $currpass."\n");
            fwrite($pipes[0], $newpass."\n");
            fwrite($pipes[0], $cafile);

            $result = trim(stream_get_contents($pipes[1]));
            $stderr = trim(stream_get_contents($pipes[2]));

            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);

            $this->_debug('Policy result: ' . $result);

            switch ($result) {
            case "OK":
                return PASSWORD_SUCCESS;
            case "Password is in history of old passwords":
                return  PASSWORD_IN_HISTORY;
            case "Cannot connect to any server":
                return PASSWORD_CONNECT_ERROR;
            default:
                rcube::raise_error([
                        'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Password plugin: Failed to execute command: $cmd. Output: $result. Error: $stderr"
                    ], true, false);
            }
        }

        return PASSWORD_ERROR;
    }

    private function _debug($str)
    {
        if ($this->debug) {
            rcube::write_log('ldap', $str);
        }
    }
}
