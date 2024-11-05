<?php

/**
 * File based User-to-Email and Email-to-User lookup
 *
 * Add it to the plugins list in config.inc.php and set
 * path to a virtuser table file to resolve user names and e-mail
 * addresses
 * $rcmail['virtuser_file'] = '';
 *
 * @license GNU GPLv3+
 * @author Aleksander Machniak
 */
class virtuser_file extends rcube_plugin
{
    private $file;
    private $app;

    /**
     * Plugin initialization
     */
    function init()
    {
        $this->app  = rcmail::get_instance();
        $this->file = $this->app->config->get('virtuser_file');

        if ($this->file) {
            $this->add_hook('user2email', [$this, 'user2email']);
            $this->add_hook('email2user', [$this, 'email2user']);
        }
    }

    /**
     * User > Email
     */
    function user2email($p)
    {
        $r = $this->findinvirtual('/\s' . preg_quote($p['user'], '/') . '\s*$/');
        $result = [];

        for ($i=0; $i<count($r); $i++) {
            $arr = preg_split('/\s+/', $r[$i]);

            if (count($arr) > 0 && strpos($arr[0], '@')) {
                $result[] = rcube_utils::idn_to_ascii(trim(str_replace('\\@', '@', $arr[0])));

                if (!empty($p['first'])) {
                    $p['email'] = $result[0];
                    break;
                }
            }
        }

        $p['email'] = empty($result) ? null : $result;

        return $p;
    }

    /**
     * Email > User
     */
    function email2user($p)
    {
        $r = $this->findinvirtual('/^' . preg_quote($p['email'], '/') . '\s/');

        for ($i=0; $i<count($r); $i++) {
            $arr = preg_split('/\s+/', trim($r[$i]));

            if (count($arr) > 0) {
                // Replace '\@' with '@' to handle cases where internal usernames include an '@' character.
                // Sometimes usernames with '@' are saved with a leading '\' to avoid conflicts.
                $p['user'] = trim(str_replace('\@', '@', $arr[count($arr) - 1]));
                break;
            }
        }

        return $p;
    }

    /**
     * Find matches of the given pattern in virtuser file
     *
     * @param string $pattern Regular expression to search for
     *
     * @return array Matching entries
     */
    private function findinvirtual($pattern)
    {
        $result  = [];
        $virtual = null;

        if ($this->file) {
            $virtual = file($this->file);
        }

        if (empty($virtual)) {
            return $result;
        }

        // check each line for matches
        foreach ($virtual as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] == '#') {
                continue;
            }

            if (preg_match($pattern, $line)) {
                $result[] = $line;
            }
        }

        return $result;
    }
}
