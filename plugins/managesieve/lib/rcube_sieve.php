<?php

/**
  Classes for managesieve operations (using PEAR::Net_Sieve)

  Author: Aleksander Machniak <alec@alec.pl>

  $Id$

*/

//  Sieve Language Basics: http://www.ietf.org/rfc/rfc5228.txt

define('SIEVE_ERROR_CONNECTION', 1);
define('SIEVE_ERROR_LOGIN', 2);
define('SIEVE_ERROR_NOT_EXISTS', 3);    // script not exists
define('SIEVE_ERROR_INSTALL', 4);       // script installation
define('SIEVE_ERROR_ACTIVATE', 5);      // script activation
define('SIEVE_ERROR_DELETE', 6);        // script deletion
define('SIEVE_ERROR_INTERNAL', 7);      // internal error
define('SIEVE_ERROR_DEACTIVATE', 8);    // script activation
define('SIEVE_ERROR_OTHER', 255);       // other/unknown error


class rcube_sieve
{
    private $sieve;                 // Net_Sieve object
    private $error = false;         // error flag
    private $list = array();        // scripts list

    public $script;                 // rcube_sieve_script object
    public $current;                // name of currently loaded script
    private $disabled;              // array of disabled extensions


    /**
     * Object constructor
     *
     * @param string  Username (for managesieve login)
     * @param string  Password (for managesieve login)
     * @param string  Managesieve server hostname/address
     * @param string  Managesieve server port number
     * @param string  Managesieve authentication method 
     * @param boolean Enable/disable TLS use
     * @param array   Disabled extensions
     * @param boolean Enable/disable debugging
     * @param string  Proxy authentication identifier
     * @param string  Proxy authentication password
     */
    public function __construct($username, $password='', $host='localhost', $port=2000,
        $auth_type=null, $usetls=true, $disabled=array(), $debug=false,
        $auth_cid=null, $auth_pw=null)
    {
        $this->sieve = new Net_Sieve();

        if ($debug) {
            $this->sieve->setDebug(true, array($this, 'debug_handler'));
        }

        if (PEAR::isError($this->sieve->connect($host, $port, null, $usetls))) {
            return $this->_set_error(SIEVE_ERROR_CONNECTION);
        }

        if (!empty($auth_cid)) {
            $authz    = $username;
            $username = $auth_cid;
            $password = $auth_pw;
        }

        if (PEAR::isError($this->sieve->login($username, $password,
            $auth_type ? strtoupper($auth_type) : null, $authz))
        ) {
            return $this->_set_error(SIEVE_ERROR_LOGIN);
        }

        $this->disabled = $disabled;
    }

    public function __destruct() {
        $this->sieve->disconnect();
    }

    /**
     * Getter for error code
     */
    public function error()
    {
        return $this->error ? $this->error : false;
    }

    /**
     * Saves current script into server
     */
    public function save($name = null)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if (!$this->script)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if (!$name)
            $name = $this->current;

        $script = $this->script->as_text();

        if (!$script)
            $script = '/* empty script */';

        if (PEAR::isError($this->sieve->installScript($name, $script)))
            return $this->_set_error(SIEVE_ERROR_INSTALL);

        return true;
    }

    /**
     * Saves text script into server
     */
    public function save_script($name, $content = null)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if (!$content)
            $content = '/* empty script */';

        if (PEAR::isError($this->sieve->installScript($name, $content)))
            return $this->_set_error(SIEVE_ERROR_INSTALL);

        return true;
    }

    /**
     * Activates specified script
     */
    public function activate($name = null)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if (!$name)
            $name = $this->current;

        if (PEAR::isError($this->sieve->setActive($name)))
            return $this->_set_error(SIEVE_ERROR_ACTIVATE);

        return true;
    }

    /**
     * De-activates specified script
     */
    public function deactivate()
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if (PEAR::isError($this->sieve->setActive('')))
            return $this->_set_error(SIEVE_ERROR_DEACTIVATE);

        return true;
    }

    /**
     * Removes specified script
     */
    public function remove($name = null)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if (!$name)
            $name = $this->current;

        // script must be deactivated first
        if ($name == $this->sieve->getActive())
            if (PEAR::isError($this->sieve->setActive('')))
                return $this->_set_error(SIEVE_ERROR_DELETE);

        if (PEAR::isError($this->sieve->removeScript($name)))
            return $this->_set_error(SIEVE_ERROR_DELETE);

        if ($name == $this->current)
            $this->current = null;

        return true;
    }

    /**
     * Gets list of supported by server Sieve extensions
     */
    public function get_extensions()
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        $ext = $this->sieve->getExtensions();
        // we're working on lower-cased names
        $ext = array_map('strtolower', (array) $ext);

        if ($this->script) {
            $supported = $this->script->get_extensions();
            foreach ($ext as $idx => $ext_name)
                if (!in_array($ext_name, $supported))
                    unset($ext[$idx]);
        }

        return array_values($ext);
    }

    /**
     * Gets list of scripts from server
     */
    public function get_scripts()
    {
        if (!$this->list) {

            if (!$this->sieve)
                return $this->_set_error(SIEVE_ERROR_INTERNAL);

            $this->list = $this->sieve->listScripts();

            if (PEAR::isError($this->list))
                return $this->_set_error(SIEVE_ERROR_OTHER);
        }

        return $this->list;
    }

    /**
     * Returns active script name
     */
    public function get_active()
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        return $this->sieve->getActive();
    }

    /**
     * Loads script by name
     */
    public function load($name)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if ($this->current == $name)
            return true;

        $script = $this->sieve->getScript($name);

        if (PEAR::isError($script))
            return $this->_set_error(SIEVE_ERROR_OTHER);

        // try to parse from Roundcube format
        $this->script = $this->_parse($script);

        $this->current = $name;

        return true;
    }

    /**
     * Loads script from text content
     */
    public function load_script($script)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        // try to parse from Roundcube format
        $this->script = $this->_parse($script);
    }

    /**
     * Creates rcube_sieve_script object from text script
     */
    private function _parse($txt)
    {
        // try to parse from Roundcube format
        $script = new rcube_sieve_script($txt, $this->disabled);

        // ... else try to import from different formats
        if (empty($script->content)) {
            $script = $this->_import_rules($txt);
            $script = new rcube_sieve_script($script, $this->disabled);
        }

        // replace all elsif with if+stop, we support only ifs
        foreach ($script->content as $idx => $rule) {
            if (!isset($script->content[$idx+1])
                || preg_match('/^else|elsif$/', $script->content[$idx+1]['type'])) {
                // 'stop' not found?
                if (!preg_match('/^(stop|vacation)$/', $rule['actions'][count($rule['actions'])-1]['type'])) {
                    $script->content[$idx]['actions'][] = array(
                        'type' => 'stop'
                    );
                }
            }
        }

        return $script;
    }

    /**
     * Gets specified script as text
     */
    public function get_script($name)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        $content = $this->sieve->getScript($name);

        if (PEAR::isError($content))
            return $this->_set_error(SIEVE_ERROR_OTHER);

        return $content;
    }

    /**
     * Creates empty script or copy of other script
     */
    public function copy($name, $copy)
    {
        if (!$this->sieve)
            return $this->_set_error(SIEVE_ERROR_INTERNAL);

        if ($copy) {
            $content = $this->sieve->getScript($copy);

            if (PEAR::isError($content))
                return $this->_set_error(SIEVE_ERROR_OTHER);
        }

        return $this->save_script($name, $content);
    }

    private function _import_rules($script)
    {
        $i = 0;
        $name = array();

        // Squirrelmail (Avelsieve)
        if ($tokens = preg_split('/(#START_SIEVE_RULE.*END_SIEVE_RULE)\r?\n/', $script, -1, PREG_SPLIT_DELIM_CAPTURE)) {
            foreach($tokens as $token) {
                if (preg_match('/^#START_SIEVE_RULE.*/', $token, $matches)) {
                    $name[$i] = "unnamed rule ".($i+1);
                    $content .= "# rule:[".$name[$i]."]\n";
                }
                elseif (isset($name[$i])) {
                    // This preg_replace is added because I've found some Avelsieve scripts
                    // with rules containing "if" here. I'm not sure it was working
                    // before without this or not.
                    $token = preg_replace('/^if\s+/', '', trim($token));
                    $content .= "if $token\n";
                    $i++;
                }
            }
        }
        // Horde (INGO)
        else if ($tokens = preg_split('/(# .+)\r?\n/i', $script, -1, PREG_SPLIT_DELIM_CAPTURE)) {
            foreach($tokens as $token) {
                if (preg_match('/^# (.+)/i', $token, $matches)) {
                    $name[$i] = $matches[1];
                    $content .= "# rule:[" . $name[$i] . "]\n";
                }
                elseif (isset($name[$i])) {
                    $token = str_replace(":comparator \"i;ascii-casemap\" ", "", $token);
                    $content .= $token . "\n";
                    $i++;
                }
            }
        }

        return $content;
    }

    private function _set_error($error)
    {
        $this->error = $error;
        return false;
    }

    /**
     * This is our own debug handler for connection
     */
    public function debug_handler(&$sieve, $message)
    {
        write_log('sieve', preg_replace('/\r\n$/', '', $message));
    }
}


class rcube_sieve_script
{
    public $content = array();      // script rules array

    private $supported = array(     // extensions supported by class
        'fileinto',
        'reject',
        'ereject',
        'copy',                     // RFC3894
        'vacation',                 // RFC5230
        'relational',               // RFC3431
    // TODO: (most wanted first) body, imapflags, notify, regex
    );

    /**
     * Object constructor
     *
     * @param  string  Script's text content
     * @param  array   Disabled extensions
     */
    public function __construct($script, $disabled=null)
    {
        if (!empty($disabled)) {
            // we're working on lower-cased names
            $disabled = array_map('strtolower', (array) $disabled);
            foreach ($disabled as $ext) {
                if (($idx = array_search($ext, $this->supported)) !== false) {
                    unset($this->supported[$idx]);
                }
            }
        }

        $this->content = $this->_parse_text($script);
    }

    /**
     * Adds script contents as text to the script array (at the end)
     *
     * @param    string    Text script contents
     */
    public function add_text($script)
    {
        $content = $this->_parse_text($script);
        $result = false;

        // check existsing script rules names
        foreach ($this->content as $idx => $elem) {
            $names[$elem['name']] = $idx;
        }

        foreach ($content as $elem) {
            if (!isset($names[$elem['name']])) {
                array_push($this->content, $elem);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Adds rule to the script (at the end)
     *
     * @param string Rule name
     * @param array  Rule content (as array)
     */
    public function add_rule($content)
    {
        // TODO: check this->supported
        array_push($this->content, $content);
        return sizeof($this->content)-1;
    }

    public function delete_rule($index)
    {
        if(isset($this->content[$index])) {
            unset($this->content[$index]);
            return true;
        }
        return false;
    }

    public function size()
    {
        return sizeof($this->content);
    }

    public function update_rule($index, $content)
    {
        // TODO: check this->supported
        if ($this->content[$index]) {
            $this->content[$index] = $content;
            return $index;
        }
        return false;
    }

    /**
     * Returns script as text
     */
    public function as_text()
    {
        $script = '';
        $exts = array();
        $idx = 0;

        // rules
        foreach ($this->content as $rule) {
            $extension = '';
            $tests = array();
            $i = 0;

            // header
            $script .= '# rule:[' . $rule['name'] . "]\n";

            // constraints expressions
            foreach ($rule['tests'] as $test) {
                $tests[$i] = '';
                switch ($test['test']) {
                case 'size':
                    $tests[$i] .= ($test['not'] ? 'not ' : '');
                    $tests[$i] .= 'size :' . ($test['type']=='under' ? 'under ' : 'over ') . $test['arg'];
                    break;
                case 'true':
                    $tests[$i] .= ($test['not'] ? 'false' : 'true');
                    break;
                case 'exists':
                    $tests[$i] .= ($test['not'] ? 'not ' : '');
                    $tests[$i] .= 'exists ' . self::escape_string($test['arg']);
                    break;
                case 'header':
                    $tests[$i] .= ($test['not'] ? 'not ' : '');

                    // relational operator + comparator
					if (preg_match('/^(value|count)-([gteqnl]{2})/', $test['type'], $m)) {
						array_push($exts, 'relational');
						array_push($exts, 'comparator-i;ascii-numeric');
                        $tests[$i] .= 'header :' . $m[1] . ' "' . $m[2] . '" :comparator "i;ascii-numeric"';
                    }
                    else
                        $tests[$i] .= 'header :' . $test['type'];

                    $tests[$i] .= ' ' . self::escape_string($test['arg1']);
                    $tests[$i] .= ' ' . self::escape_string($test['arg2']);
                    break;
                }
                $i++;
            }

            // disabled rule: if false #....
            $script .= 'if ' . ($rule['disabled'] ? 'false # ' : '');

            if (empty($tests)) {
                $tests_str = 'true';
            }
            else if (count($tests) > 1) {
                $tests_str = implode(', ', $tests);
            }
            else {
                $tests_str = $tests[0];
            }

            if ($rule['join'] || count($tests) > 1) {
                $script .= sprintf('%s (%s)', $rule['join'] ? 'allof' : 'anyof', $tests_str);
            }
            else {
                $script .= $tests_str;
            }
            $script .= "\n{\n";

            // action(s)
            foreach ($rule['actions'] as $action) {
                switch ($action['type']) {
                case 'fileinto':
                    array_push($exts, 'fileinto');
                    $script .= "\tfileinto ";
                    if ($action['copy']) {
                        $script .= ':copy ';
                        array_push($exts, 'copy');
                    }
                    $script .= self::escape_string($action['target']) . ";\n";
                    break;
                case 'redirect':
                    $script .= "\tredirect ";
                    if ($action['copy']) {
                        $script .= ':copy ';
                        array_push($exts, 'copy');
                    }
                    $script .= self::escape_string($action['target']) . ";\n";
                    break;
                case 'reject':
                case 'ereject':
                    array_push($exts, $action['type']);
                    $script .= "\t".$action['type']." "
                        . self::escape_string($action['target']) . ";\n";
                    break;
                case 'keep':
                case 'discard':
                case 'stop':
                    $script .= "\t" . $action['type'] .";\n";
                    break;
                case 'vacation':
                    array_push($exts, 'vacation');
                    $script .= "\tvacation";
                    if (!empty($action['days']))
                        $script .= " :days " . $action['days'];
                    if (!empty($action['addresses']))
                        $script .= " :addresses " . self::escape_string($action['addresses']);
                    if (!empty($action['subject']))
                        $script .= " :subject " . self::escape_string($action['subject']);
                    if (!empty($action['handle']))
                        $script .= " :handle " . self::escape_string($action['handle']);
                    if (!empty($action['from']))
                        $script .= " :from " . self::escape_string($action['from']);
                    if (!empty($action['mime']))
                        $script .= " :mime";
                    $script .= " " . self::escape_string($action['reason']) . ";\n";
                    break;
                }
            }

            $script .= "}\n";
            $idx++;
        }

        // requires
        if (!empty($exts))
            $script = 'require ["' . implode('","', array_unique($exts)) . "\"];\n" . $script;

        return $script;
    }

    /**
     * Returns script object
     *
     */
    public function as_array()
    {
        return $this->content;
    }

    /**
     * Returns array of supported extensions
     *
     */
    public function get_extensions()
    {
        return array_values($this->supported);
    }

    /**
     * Converts text script to rules array
     *
     * @param string Text script
     */
    private function _parse_text($script)
    {
        $i = 0;
        $content = array();

        // tokenize rules
        if ($tokens = preg_split('/(# rule:\[.*\])\r?\n/', $script, -1, PREG_SPLIT_DELIM_CAPTURE)) {
            foreach($tokens as $token) {
                if (preg_match('/^# rule:\[(.*)\]/', $token, $matches)) {
                    $content[$i]['name'] = $matches[1];
                }
                else if (isset($content[$i]['name']) && sizeof($content[$i]) == 1) {
                    if ($rule = $this->_tokenize_rule($token)) {
                        $content[$i] = array_merge($content[$i], $rule);
                        $i++;
                    }
                    else // unknown rule format
                        unset($content[$i]);
                }
            }
        }

        return $content;
    }

    /**
     * Convert text script fragment to rule object
     *
     * @param string Text rule
     */
    private function _tokenize_rule($content)
    {
        $cond = strtolower(self::tokenize($content, 1));

        if ($cond != 'if' && $cond != 'elsif' && $cond != 'else') {
            return null;
        }

        $disabled = false;
        $join     = false;

        // disabled rule (false + comment): if false # .....
        if (preg_match('/^\s*false\s+#/i', $content)) {
            $content = preg_replace('/^\s*false\s+#\s*/i', '', $content);
            $disabled = true;
        }

        while (strlen($content)) {
            $tokens = self::tokenize($content, true);
            $separator = array_pop($tokens);

            if (!empty($tokens)) {
                $token = array_shift($tokens);
            }
            else {
                $token = $separator;
            }

            $token = strtolower($token);

            if ($token == 'not') {
                $not = true;
                $token = strtolower(array_shift($tokens));
            }
            else {
                $not = false;
            }

            switch ($token) {
            case 'allof':
                $join = true;
                break;
            case 'anyof':
                break;

            case 'size':
                $size = array('test' => 'size', 'not'  => $not);
                for ($i=0, $len=count($tokens); $i<$len; $i++) {
                    if (!is_array($tokens[$i])
                        && preg_match('/^:(under|over)$/i', $tokens[$i])
                    ) {
                        $size['type'] = strtolower(substr($tokens[$i], 1));
                    }
                    else {
                        $size['arg'] = $tokens[$i];
                    }
                }

                $tests[] = $size;
                break;

            case 'header':
                $header = array('test' => 'header', 'not' => $not, 'arg1' => '', 'arg2' => '');
                for ($i=0, $len=count($tokens); $i<$len; $i++) {
                    if (!is_array($tokens[$i]) && preg_match('/^:comparator$/i', $tokens[$i])) {
                        $i++;
                    }
                    else if (!is_array($tokens[$i]) && preg_match('/^:(count|value)$/i', $tokens[$i])) {
                        $header['type'] = strtolower(substr($tokens[$i], 1)) . '-' . $tokens[++$i];
                    }
                    else if (!is_array($tokens[$i]) && preg_match('/^:(is|contains|matches)$/i', $tokens[$i])) {
                        $header['type'] = strtolower(substr($tokens[$i], 1));
                    }
                    else {
                        $header['arg1'] = $header['arg2'];
                        $header['arg2'] = $tokens[$i];
                    }
                }

                $tests[] = $header;
                break;

            case 'exists':
                $tests[] = array('test' => 'exists', 'not'  => $not,
                    'arg'  => array_pop($tokens));
                break;

            case 'true':
                $tests[] = array('test' => 'true', 'not'  => $not);
                break;

            case 'false':
                $tests[] = array('test' => 'true', 'not'  => !$not);
                break;
            }

            // goto actions...
            if ($separator == '{') {
                break;
            }
        }

        // ...and actions block
        if ($tests) {
            $actions = $this->_parse_actions($content);
        }

        if ($tests && $actions) {
            $result = array(
                'type'     => $cond,
                'tests'    => $tests,
                'actions'  => $actions,
                'join'     => $join,
                'disabled' => $disabled,
            );
        }

        return $result;
    }

    /**
     * Parse body of actions section
     *
     * @param string Text body
     * @return array Array of parsed action type/target pairs
     */
    private function _parse_actions($content)
    {
        $result = null;

        while (strlen($content)) {
            $tokens = self::tokenize($content, true);
            $separator = array_pop($tokens);

            if (!empty($tokens)) {
                $token = array_shift($tokens);
            }
            else {
                $token = $separator;
            }

            switch ($token) {
            case 'discard':
            case 'keep':
            case 'stop':
                $result[] = array('type' => $token);
                break;

            case 'fileinto':
            case 'redirect':
                $copy   = false;
                $target = '';

                for ($i=0, $len=count($tokens); $i<$len; $i++) {
                    if (strtolower($tokens[$i]) == ':copy') {
                        $copy = true;
                    }
                    else {
                        $target = $tokens[$i];
                    }
                }

                $result[] = array('type' => $token, 'copy' => $copy,
                    'target' => $target);
                break;

            case 'reject':
            case 'ereject':
                $result[] = array('type' => $token, 'target' => array_pop($tokens));
                break;

            case 'vacation':
                $vacation = array('type' => 'vacation', 'reason' => array_pop($tokens));

                for ($i=0, $len=count($tokens); $i<$len; $i++) {
                    $tok = strtolower($tokens[$i]);
                    if ($tok == ':days') {
                        $vacation['days'] = $tokens[++$i];
                    }
                    else if ($tok == ':subject') {
                        $vacation['subject'] = $tokens[++$i];
                    }
                    else if ($tok == ':addresses') {
                        $vacation['addresses'] = $tokens[++$i];
                    }
                    else if ($tok == ':handle') {
                        $vacation['handle'] = $tokens[++$i];
                    }
                    else if ($tok == ':from') {
                        $vacation['from'] = $tokens[++$i];
                    }
                    else if ($tok == ':mime') {
                        $vacation['mime'] = true;
                    }
                }

                $result[] = $vacation;
                break;
            }
        }

        return $result;
    }

    /**
     * Escape special chars into quoted string value or multi-line string
     * or list of strings
     *
     * @param string $str Text or array (list) of strings
     *
     * @return string Result text
     */
    static function escape_string($str)
    {
        if (is_array($str) && count($str) > 1) {
            foreach($str as $idx => $val)
                $str[$idx] = self::escape_string($val);

            return '[' . implode(',', $str) . ']';
        }
        else if (is_array($str)) {
            $str = array_pop($str);
        }

        // multi-line string
        if (preg_match('/[\r\n\0]/', $str) || strlen($str) > 1024) {
            return sprintf("text:\n%s\n.\n", self::escape_multiline_string($str));
        }
        // quoted-string
        else {
            $replace = array('\\' => '\\\\', '"' => '\\"');
            $str = str_replace(array_keys($replace), array_values($replace), $str);
            return '"' . $str . '"';
        }
    }

    /**
     * Escape special chars in multi-line string value
     *
     * @param string $str Text
     *
     * @return string Text
     */
    static function escape_multiline_string($str)
    {
        $str = preg_split('/(\r?\n)/', $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($str as $idx => $line) {
            // dot-stuffing
            if (isset($line[0]) && $line[0] == '.') {
                $str[$idx] = '.' . $line;
            }
        }

        return implode($str);
    }

    /**
     * Splits script into string tokens
     *
     * @param string &$str    The script
     * @param mixed  $num     Number of tokens to return, 0 for all
     *                        or True for all tokens until separator is found.
     *                        Separator will be returned as last token.
     * @param int    $in_list Enable to called recursively inside a list
     *
     * @return mixed Tokens array or string if $num=1
     */
    static function tokenize(&$str, $num=0, $in_list=false)
    {
        $result = array();

        // remove spaces from the beginning of the string
        while (($str = ltrim($str)) !== ''
            && (!$num || $num === true || count($result) < $num)
        ) {
            switch ($str[0]) {

            // Quoted string
            case '"':
                $len = strlen($str);

                for ($pos=1; $pos<$len; $pos++) {
                    if ($str[$pos] == '"') {
                        break;
                    }
                    if ($str[$pos] == "\\") {
                        if ($str[$pos + 1] == '"' || $str[$pos + 1] == "\\") {
                            $pos++;
                        }
                    }
                }
                if ($str[$pos] != '"') {
                    // error
                }
                // we need to strip slashes for a quoted string
                $result[] = stripslashes(substr($str, 1, $pos - 1));
                $str      = substr($str, $pos + 1);
                break;

            // Parenthesized list
            case '[':
                $str = substr($str, 1);
                $result[] = self::tokenize($str, 0, true);
                break;
            case ']':
                $str = substr($str, 1);
                return $result;
                break;

            // list/test separator
            case ',':
            // command separator
            case ';':
            // block/tests-list
            case '(':
            case ')':
            case '{':
            case '}':
                $sep = $str[0];
                $str = substr($str, 1);
                if ($num === true) {
                    $result[] = $sep;
                    break 2; 
                }
                break;

            // bracket-comment
            case '/':
                if ($str[1] == '*') {
                    if ($end_pos = strpos($str, '*/')) {
                        $str = substr($str, $end_pos + 2);
                    }
                    else {
                        // error
                        $str = '';
                    }
                }
                break;

            // hash-comment
            case '#':
                if ($lf_pos = strpos($str, "\n")) {
                    $str = substr($str, $lf_pos);
                    break;
                }
                else {
                    $str = '';
                }

            // String atom
            default:
                // empty or one character
                if ($str === '') {
                    break 2;
                }
                if (strlen($str) < 2) {
                    $result[] = $str;
                    $str = '';
                    break;
                }

                // tag/identifier/number
                if (preg_match('/^([a-z0-9:_]+)/i', $str, $m)) {
                    $str = substr($str, strlen($m[1]));

                    if ($m[1] != 'text:') {
                        $result[] = $m[1];
                    }
                    // multiline string
                    else {
                        // possible hash-comment after "text:"
                        if (preg_match('/^( |\t)*(#[^\n]+)?\n/', $str, $m)) {
                            $str = substr($str, strlen($m[0]));
                        }
                        // get text until alone dot in a line
                        if (preg_match('/^(.*)\r?\n\.\r?\n/sU', $str, $m)) {
                            $text = $m[1];
                            // remove dot-stuffing
                            $text = str_replace("\n..", "\n.", $text);
                            $str = substr($str, strlen($m[0]));
                        }
                        else {
                            $text = '';
                        }

                        $result[] = $text;
                    }
                }

                break;
            }
        }

        return $num === 1 ? (isset($result[0]) ? $result[0] : null) : $result;
    }

}
