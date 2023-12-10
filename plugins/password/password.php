<?php

/**
 * Password Plugin for Roundcube
 *
 * @author Aleksander Machniak <alec@alec.pl>
 *
 * Copyright (C) The Roundcube Dev Team
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

define('PASSWORD_CRYPT_ERROR', 1);
define('PASSWORD_ERROR', 2);
define('PASSWORD_CONNECT_ERROR', 3);
define('PASSWORD_IN_HISTORY', 4);
define('PASSWORD_CONSTRAINT_VIOLATION', 5);
define('PASSWORD_COMPARE_CURRENT', 6);
define('PASSWORD_COMPARE_NEW', 7);
define('PASSWORD_SUCCESS', 0);

/**
 * Change password plugin
 *
 * Plugin that adds functionality to change a users password.
 * It provides common functionality and user interface and supports
 * several backends to finally update the password.
 *
 * For installation and configuration instructions please read the README file.
 *
 * @author Aleksander Machniak
 */
class password extends rcube_plugin
{
    public $task    = '?(?!logout).*';
    public $noframe = true;
    public $noajax  = true;

    private $newuser = false;
    private $drivers = [];
    private $rc;


    function init()
    {
        $this->rc = rcmail::get_instance();

        $this->load_config();

        // update deprecated password_require_nonalpha option removed 20181007
        if ($this->rc->config->get('password_minimum_score') === null && $this->rc->config->get('password_require_nonalpha')) {
            $this->rc->config->set('password_minimum_score', 2);
        }

        if ($this->rc->task == 'settings') {
            if (!$this->check_host_login_exceptions()) {
                return;
            }

            $this->add_texts('localization/');

            $this->add_hook('settings_actions', [$this, 'settings_actions']);

            $this->register_action('plugin.password', [$this, 'password_init']);
            $this->register_action('plugin.password-save', [$this, 'password_save']);
        }

        if ($this->rc->config->get('password_force_new_user')) {
            if ($this->rc->config->get('newuserpassword') && $this->check_host_login_exceptions()) {
                if (!($this->rc->task == 'settings' && strpos($this->rc->action, 'plugin.password') === 0)) {
                    $this->rc->output->command('redirect', '?_task=settings&_action=plugin.password&_first=1', false);
                }
            }

            $this->add_hook('user_create', [$this, 'user_create']);
            $this->add_hook('login_after', [$this, 'login_after']);
        }
    }

    function settings_actions($args)
    {
        // register as settings action
        $args['actions'][] = [
            'action' => 'plugin.password',
            'class'  => 'password',
            'label'  => 'password',
            'title'  => 'changepasswd',
            'domain' => 'password',
        ];

        return $args;
    }

    function password_init()
    {
        $this->register_handler('plugin.body', [$this, 'password_form']);

        $this->rc->output->set_pagetitle($this->gettext('changepasswd'));

        if (rcube_utils::get_input_value('_first', rcube_utils::INPUT_GET)) {
            $this->rc->output->command('display_message', $this->gettext('firstloginchange'), 'notice');
        }
        else if (!empty($_SESSION['password_expires'])) {
            if ($_SESSION['password_expires'] == 1) {
                $this->rc->output->command('display_message', $this->gettext('passwdexpired'), 'error');
            }
            else {
                $this->rc->output->command('display_message', $this->gettext([
                        'name' => 'passwdexpirewarning',
                        'vars' => ['expirationdatetime' => $_SESSION['password_expires']]
                    ]), 'warning');
            }
        }

        $this->rc->output->send('plugin');
    }

    function password_save()
    {
        $this->register_handler('plugin.body', [$this, 'password_form']);

        $this->rc->output->set_pagetitle($this->gettext('changepasswd'));

        $confirm         = $this->rc->config->get('password_confirm_current');
        $required_length = (int) $this->rc->config->get('password_minimum_length', 8);
        $force_save      = $this->rc->config->get('password_force_save');

        if (($confirm && !isset($_POST['_curpasswd'])) || !isset($_POST['_newpasswd']) || !strlen($_POST['_newpasswd'])) {
            $this->rc->output->command('display_message', $this->gettext('nopassword'), 'error');
        }
        else {
            $charset    = strtoupper($this->rc->config->get('password_charset', 'UTF-8'));
            $rc_charset = strtoupper($this->rc->output->get_charset());

            $sesspwd = $this->rc->decrypt($_SESSION['password']);
            $curpwd = $confirm ? rcube_utils::get_input_string('_curpasswd', rcube_utils::INPUT_POST, true, $charset) : $sesspwd;
            $newpwd = rcube_utils::get_input_string('_newpasswd', rcube_utils::INPUT_POST, true);
            $conpwd = rcube_utils::get_input_string('_confpasswd', rcube_utils::INPUT_POST, true);

            // check allowed characters according to the configured 'password_charset' option
            // by converting the password entered by the user to this charset and back to UTF-8
            $orig_pwd = $newpwd;
            $chk_pwd  = rcube_charset::convert($orig_pwd, $rc_charset, $charset);
            $chk_pwd  = rcube_charset::convert($chk_pwd, $charset, $rc_charset);

            // We're doing this for consistence with Roundcube core
            $newpwd = rcube_charset::convert($newpwd, $rc_charset, $charset);
            $conpwd = rcube_charset::convert($conpwd, $rc_charset, $charset);

            if ($chk_pwd != $orig_pwd || preg_match('/[\x00-\x1F\x7F]/', $newpwd)) {
                $this->rc->output->command('display_message', $this->gettext('passwordforbidden'), 'error');
            }
            // other passwords validity checks
            else if ($conpwd != $newpwd) {
                $this->rc->output->command('display_message', $this->gettext('passwordinconsistency'), 'error');
            }
            else if ($confirm && ($res = $this->_compare($sesspwd, $curpwd, PASSWORD_COMPARE_CURRENT))) {
                $this->rc->output->command('display_message', $res, 'error');
            }
            else if ($required_length && strlen($newpwd) < $required_length) {
                $this->rc->output->command('display_message', $this->gettext(
                    ['name' => 'passwordshort', 'vars' => ['length' => $required_length]]), 'error');
            }
            else if ($res = $this->_check_strength($newpwd)) {
                $this->rc->output->command('display_message', $res, 'error');
            }
            // password is the same as the old one, warn user, return error
            else if (!$force_save && ($res = $this->_compare($sesspwd, $newpwd, PASSWORD_COMPARE_NEW))) {
                $this->rc->output->command('display_message', $res, 'error');
            }
            // try to save the password
            else if (!($res = $this->_save($curpwd, $newpwd))) {
                $this->rc->output->command('display_message', $this->gettext('successfullysaved'), 'confirmation');

                // allow additional actions after password change (e.g. reset some backends)
                $plugin = $this->rc->plugins->exec_hook('password_change', [
                        'old_pass' => $curpwd,
                        'new_pass' => $newpwd
                ]);

                // Reset session password
                $_SESSION['password'] = $this->rc->encrypt($plugin['new_pass']);

                if ($this->rc->config->get('newuserpassword')) {
                    $this->rc->user->save_prefs(['newuserpassword' => false]);
                }

                // Log password change
                if ($this->rc->config->get('password_log')) {
                    rcube::write_log('password', sprintf('Password changed for user %s (ID: %d) from %s',
                        $this->rc->get_user_name(), $this->rc->user->ID, rcube_utils::remote_ip()));
                }

                // Remove expiration date/time
                $this->rc->session->remove('password_expires');
            }
            else {
                $this->rc->output->command('display_message', $res, 'error');
            }
        }

        $this->rc->overwrite_action('plugin.password');
        $this->rc->output->send('plugin');
    }

    function password_form()
    {
        // add some labels to client
        $this->rc->output->add_label(
            'password.nopassword',
            'password.nocurpassword',
            'password.passwordinconsistency'
        );

        $form_disabled = $this->rc->config->get('password_disabled');

        $this->rc->output->set_env('product_name', $this->rc->config->get('product_name'));
        $this->rc->output->set_env('password_disabled', !empty($form_disabled));

        $table = new html_table(['cols' => 2, 'class' => 'propform']);

        if ($this->rc->config->get('password_confirm_current')) {
            // show current password selection
            $field_id = 'curpasswd';
            $input_curpasswd = new html_passwordfield([
                    'name'         => '_curpasswd',
                    'id'           => $field_id,
                    'size'         => 20,
                    'autocomplete' => 'off',
            ]);

            $table->add('title', html::label($field_id, rcube::Q($this->gettext('curpasswd'))));
            $table->add(null, $input_curpasswd->show());
        }

        // show new password selection
        $field_id = 'newpasswd';
        $input_newpasswd = new html_passwordfield([
                'name'         => '_newpasswd',
                'id'           => $field_id,
                'size'         => 20,
                'autocomplete' => 'off',
        ]);

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('newpasswd'))));
        $table->add(null, $input_newpasswd->show());

        // show confirm password selection
        $field_id = 'confpasswd';
        $input_confpasswd = new html_passwordfield([
                'name'         => '_confpasswd',
                'id'           => $field_id,
                'size'         => 20,
                'autocomplete' => 'off',
        ]);

        $table->add('title', html::label($field_id, rcube::Q($this->gettext('confpasswd'))));
        $table->add(null, $input_confpasswd->show());

        $rules = '';

        $required_length = (int) $this->rc->config->get('password_minimum_length', 8);
        if ($required_length > 0) {
            $rules .= html::tag('li', ['class' => 'required-length d-block'], $this->gettext([
                'name' => 'passwordshort',
                'vars' => ['length' => $required_length]
            ]));
        }

        if ($msgs = $this->_strength_rules()) {
            foreach ($msgs as $msg) {
                $rules .= html::tag('li', ['class' => 'strength-rule d-block'], $msg);
            }
        }

        if (!empty($rules)) {
            $rules = html::tag('ul', ['id' => 'ruleslist', 'class' => 'hint proplist'], $rules);
        }

        $disabled_msg = '';
        if ($form_disabled) {
            $disabled_msg = is_string($form_disabled) ? $form_disabled : $this->gettext('disablednotice');
            $disabled_msg = html::div(['class' => 'boxwarning', 'id' => 'password-notice'], $disabled_msg);
        }

        $submit_button = $this->rc->output->button([
                'command' => 'plugin.password-save',
                'class'   => 'button mainaction submit',
                'label'   => 'save',
        ]);
        $form_buttons = html::p(['class' => 'formbuttons footerleft'], $submit_button);

        $this->rc->output->add_gui_object('passform', 'password-form');

        $this->include_script('password.js');

        $form = $this->rc->output->form_tag([
                'id'     => 'password-form',
                'name'   => 'password-form',
                'method' => 'post',
                'action' => './?_task=settings&_action=plugin.password-save',
            ],
            $disabled_msg . $table->show() . $rules
        );

        return html::div(['id' => 'prefs-title', 'class' => 'boxtitle'], $this->gettext('changepasswd'))
            . html::div(['class' => 'box formcontainer scroller'],
                html::div(['class' => 'boxcontent formcontent'], $form) . $form_buttons
            );
    }

    private function _compare($curpwd, $newpwd, $type)
    {
        $driver = $this->_load_driver();

        if (!$driver) {
            $result = $this->gettext('internalerror');
        }
        else if (method_exists($driver, 'compare')) {
            $result = $driver->compare($curpwd, $newpwd, $type);
        }
        else {
            switch ($type) {
            case PASSWORD_COMPARE_CURRENT:
                $result = $curpwd != $newpwd ? $this->gettext('passwordincorrect') : null;
                break;
            case PASSWORD_COMPARE_NEW:
                $result = $curpwd == $newpwd ? $this->gettext('samepasswd') : null;
                break;
            default:
                $result = $this->gettext('internalerror');
            }
        }

        return $result;
    }

    private function _strength_rules()
    {
        $result = null;

        if (($driver = $this->_load_driver('strength')) && method_exists($driver, 'strength_rules')) {
            $result = $driver->strength_rules();
        }
        else if ($this->rc->config->get('password_minimum_score') > 1) {
            $result = $this->gettext('passwordweak');
        }

        if (!is_array($result)) {
            $result = [$result];
        }

        return $result;
    }

    private function _check_strength($passwd)
    {
        $min_score = $this->rc->config->get('password_minimum_score');

        if (!$min_score) {
            return;
        }

        if (($driver = $this->_load_driver('strength')) && method_exists($driver, 'check_strength')) {
            list($score, $reason) = $driver->check_strength($passwd);
        }
        else {
            $score = (!preg_match("/[0-9]/", $passwd) || !preg_match("/[^A-Za-z0-9]/", $passwd)) ? 1 : 5;
        }

        if ($score < $min_score) {
            return $this->gettext('passwordtooweak') . (!empty($reason) ? " $reason" : '');
        }
    }

    private function _save($curpass, $passwd)
    {
        if (!($driver = $this->_load_driver())) {
            return $this->gettext('internalerror');
        }

        // Make sure the current user password is still valid
        // by logging in to the IMAP server (#8142)
        if (!$this->rc->get_storage()->check_connection()) {
            return $this->gettext('internalerror');
        }

        $result  = $driver->save($curpass, $passwd, self::username());
        $message = '';

        if (is_array($result)) {
            $message = $result['message'];
            $result  = $result['code'];
        }

        switch ($result) {
            case PASSWORD_SUCCESS:
                return;
            case PASSWORD_CRYPT_ERROR:
                $reason = $this->gettext('crypterror');
                break;
            case PASSWORD_CONNECT_ERROR:
                $reason = $this->gettext('connecterror');
                break;
            case PASSWORD_IN_HISTORY:
                $reason = $this->gettext('passwdinhistory');
                break;
            case PASSWORD_CONSTRAINT_VIOLATION:
                $reason = $this->gettext('passwdconstraintviolation');
                break;
            case PASSWORD_ERROR:
            default:
                $reason = $this->gettext('internalerror');
        }

        if ($message) {
            $reason .= ' ' . $message;
        }

        return $reason;
    }

    private function _load_driver($type = 'password')
    {
        if (!($type && $driver = $this->rc->config->get('password_' . $type . '_driver'))) {
            $driver = $this->rc->config->get('password_driver', 'sql');
        }

        if (empty($this->drivers[$type])) {
            $class  = "rcube_{$driver}_password";
            $file = $this->home . "/drivers/$driver.php";

            if (!file_exists($file)) {
                rcube::raise_error([
                        'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Password plugin: Driver file does not exist ($file)"
                    ], true, false
                );
                return false;
            }

            include_once $file;

            if (!class_exists($class, false) || (!method_exists($class, 'save') && !method_exists($class, 'check_strength'))) {
                rcube::raise_error([
                        'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Password plugin: Broken driver $driver"
                    ], true, false
                );
                return false;
            }

            $this->drivers[$type] = new $class;
        }

        return $this->drivers[$type];
    }

    function user_create($args)
    {
        $this->newuser = true;
        return $args;
    }

    function login_after($args)
    {
        if ($this->newuser && $this->check_host_login_exceptions()) {
            $this->rc->user->save_prefs(['newuserpassword' => true]);

            $args['_task']   = 'settings';
            $args['_action'] = 'plugin.password';
            $args['_first']  = 'true';
        }

        return $args;
    }

    // Check if host and login is allowed to change the password, false = not allowed, true = not allowed
    private function check_host_login_exceptions()
    {
        // Host exceptions
        $hosts = $this->rc->config->get('password_hosts');
        if (!empty($hosts) && !in_array($_SESSION['storage_host'], (array) $hosts)) {
            return false;
        }

        // Login exceptions
        if ($exceptions = $this->rc->config->get('password_login_exceptions')) {
            $exceptions = array_map('trim', (array) $exceptions);
            $exceptions = array_filter($exceptions);
            $username   = $_SESSION['username'];

            foreach ($exceptions as $ec) {
                if ($username === $ec) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Hashes a password and returns the hash based on the specified method
     *
     * Parts of the code originally from the phpLDAPadmin development team
     * http://phpldapadmin.sourceforge.net/
     *
     * @param string      $password Clear password
     * @param string      $method   Hashing method
     * @param bool|string $prefixed Prefix string or TRUE to add a default prefix
     *
     * @return string Hashed password
     */
    public static function hash_password($password, $method = '', $prefixed = true)
    {
        $method  = strtolower($method);
        $rcmail  = rcmail::get_instance();
        $prefix  = '';
        $crypted = null;
        $options = (array) $rcmail->config->get('password_algorithm_options', []);

        if (empty($method) || $method == 'default') {
            $method   = $rcmail->config->get('password_algorithm');
            $prefixed = $rcmail->config->get('password_algorithm_prefix');
        }

        switch ($method) {
        case 'des':
        case 'des-crypt':
            $crypted = crypt($password, rcube_utils::random_bytes(2));
            $prefix  = '{CRYPT}';
            break;

        case 'ext_des': // for BC
        case 'ext-des-crypt':
            $crypted = crypt($password, '_' . rcube_utils::random_bytes(8));
            $prefix  = '{CRYPT}';
            break;

        case 'md5crypt': // for BC
        case 'md5-crypt':
            $crypted = crypt($password, '$1$' . rcube_utils::random_bytes(9));
            $prefix  = '{MD5-CRYPT}';
            break;

        case 'sha256-crypt':
            $rounds = (int) $rcmail->config->get('password_crypt_rounds');
            $prefix = '$5$';

            if ($rounds > 1000) {
                $prefix .= 'rounds=' . $rounds . '$';
            }

            $crypted = crypt($password, $prefix . rcube_utils::random_bytes(16));
            $prefix  = '{SHA256-CRYPT}';
            break;

        case 'sha512-crypt':
            $rounds = (int) $rcmail->config->get('password_crypt_rounds');
            $prefix = '$6$';

            if ($rounds > 1000) {
                $prefix .= 'rounds=' . $rounds . '$';
            }

            $crypted = crypt($password, $prefix . rcube_utils::random_bytes(16));
            $prefix  = '{SHA512-CRYPT}';
            break;

        case 'blowfish': // for BC
        case 'blowfish-crypt':
            $cost   = (int) ($options['cost'] ?? $rcmail->config->get('password_blowfish_cost'));
            $cost   = $cost < 4 || $cost > 31 ? 12 : $cost;
            $prefix = sprintf('$2y$%02d$', $cost);

            $crypted = crypt($password, $prefix . rcube_utils::random_bytes(22));
            $prefix  = '{BLF-CRYPT}';
            break;

        case 'md5':
            $crypted = base64_encode(pack('H*', md5($password)));
            $prefix  = '{MD5}';
            break;

        case 'sha':
            if (function_exists('sha1')) {
                $crypted = pack('H*', sha1($password));
            }
            else if (function_exists('hash')) {
                $crypted = hash('sha1', $password, true);
            }
            else {
                rcube::raise_error([
                        'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Password plugin: Your PHP installation does not have the hash() nor sha1() function"
                    ], true, true
                );
            }

            $crypted = base64_encode($crypted);
            $prefix = '{SHA}';
            break;

        case 'ssha':
            $salt = rcube_utils::random_bytes(8);

            if (function_exists('sha1')) {
                $salt    = substr(pack("H*", sha1($salt . $password)), 0, 4);
                $crypted = sha1($password . $salt, true);
            }
            else if (function_exists('hash')) {
                $salt    = substr(pack("H*", hash('sha1', $salt . $password)), 0, 4);
                $crypted = hash('sha1', $password . $salt, true);
            }
            else {
                rcube::raise_error([
                        'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Password plugin: Your PHP installation does not have the hash() nor sha1() function"
                   ], true, true
               );
            }

            $crypted = base64_encode($crypted . $salt);
            $prefix  = '{SSHA}';
            break;

        // base64 encoded ssha256 for mailcow
        case 'ssha256':
            $salt = rcube_utils::random_bytes(8);

            if (function_exists('hash')) {
                $salt    = substr(pack("H*", hash('sha256', $salt . $password)), 0, 4);
                $crypted = hash('sha256', $password . $salt, true);
            }
            else {
                rcube::raise_error([
                        'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Password plugin: Your PHP installation does not have the hash() function"
                   ], true, true
               );
            }

            $crypted = base64_encode($crypted . $salt);
            $prefix  = '{SSHA256}';
            break;

        case 'ssha512':
            $salt = rcube_utils::random_bytes(8);

            if (function_exists('hash')) {
                $salt    = substr(pack("H*", hash('sha512', $salt . $password)), 0, 4);
                $crypted = hash('sha512', $password . $salt, true);
            }
            else {
                rcube::raise_error([
                        'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Password plugin: Your PHP installation does not have the hash() function"
                    ], true, true
                );
            }

            $crypted = base64_encode($crypted . $salt);
            $prefix  = '{SSHA512}';
            break;

        case 'smd5':
            $salt = rcube_utils::random_bytes(8);

            if (function_exists('hash')) {
                $salt    = substr(pack("H*", hash('md5', $salt . $password)), 0, 4);
                $crypted = hash('md5', $password . $salt, true);
            }
            else {
                $salt    = substr(pack("H*", md5($salt . $password)), 0, 4);
                $crypted = md5($password . $salt, true);
            }

            $crypted = base64_encode($crypted . $salt);
            $prefix  = '{SMD5}';
            break;

        case 'samba':
            if (function_exists('hash')) {
                $crypted = hash('md4', rcube_charset::convert($password, RCUBE_CHARSET, 'UTF-16LE'));
                $crypted = strtoupper($crypted);
            }
            else {
                rcube::raise_error([
                        'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Password plugin: Your PHP installation does not have hash() function"
                    ], true, true
                );
            }
            break;

        case 'ad':
            $crypted = rcube_charset::convert('"' . $password . '"', RCUBE_CHARSET, 'UTF-16LE');
            break;

        case 'dovecot':
            if (!($dovecotpw = $rcmail->config->get('password_dovecotpw'))) {
                $dovecotpw = 'dovecotpw';
            }
            if (!($method = $rcmail->config->get('password_dovecotpw_method'))) {
                $method = 'CRAM-MD5';
            }

            $command = "$dovecotpw -s '$method'";

            $spec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $pipe = proc_open($command, $spec, $pipes);

            if (!$pipe) {
                rcube::raise_error([
                        'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Password plugin: Failed to execute command: $command"
                    ], true, false);
                return false;
            }

            fwrite($pipes[0], $password . "\n", 1+strlen($password));
            usleep(1000);
            fwrite($pipes[0], $password . "\n", 1+strlen($password));

            $crypted = trim(stream_get_contents($pipes[1]), "\n");
            $stderr = trim(stream_get_contents($pipes[2]));

            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($pipe);

            if (!preg_match('/^\{' . $method . '\}/', $crypted)) {
                rcube::raise_error([
                        'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                        'message' => "Password plugin: Failed to execute command: $command. Error: $stderr"
                    ], true, false);

                return false;
            }

            if (!$prefixed) {
                $prefixed = (bool) $rcmail->config->get('password_dovecotpw_with_method');
            }

            if (!$prefixed) {
                $crypted = trim(str_replace('{' . $method . '}', '', $crypted));
            }

            $prefixed = false;

            break;

        case 'hash-bcrypt':
            $crypted = password_hash($password, PASSWORD_BCRYPT, $options);
            $prefix  = '{BLF-CRYPT}';
            break;

        case 'hash-argon2i':
            $crypted = password_hash($password, PASSWORD_ARGON2I, $options);
            $prefix  = '{ARGON2I}';
            break;

        case 'hash-argon2id':
            $crypted = password_hash($password, PASSWORD_ARGON2ID, $options);
            $prefix  = '{ARGON2ID}';
            break;

        case 'clear':
            $crypted = $password;
            break;

        default:
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Hash method not supported."
                ], true, true
            );
        }

        if ($crypted === null || $crypted === false) {
            rcube::raise_error([
                    'code' => 600, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Password plugin: Failed to hash password ($method). Check for configuration issues."
                ],
                true, true
            );
        }

        if ($prefixed && $prefixed !== true) {
            $prefix   = $prefixed;
            $prefixed = true;
        }

        if ($prefixed === true && $prefix) {
            $crypted = $prefix . $crypted;
        }

        return $crypted;
    }

    /**
     * Returns username in a configured form appropriate for the driver
     *
     * @param string $format Username format
     *
     * @return string Username
     */
    public static function username($format = null)
    {
        $rcmail = rcmail::get_instance();

        if (!$format) {
            $format = $rcmail->config->get('password_username_format');
        }

        if (!$format) {
            return $_SESSION['username'];
        }

        return strtr($format, [
                '%l' => $rcmail->user->get_username('local'),
                '%d' => $rcmail->user->get_username('domain'),
                '%u' => $_SESSION['username'],
        ]);
    }

    /**
     * Returns Guzzle HTTP client instance configured for use in a password driver.
     *
     * @return \GuzzleHttp\Client HTTP client
     */
    public static function get_http_client()
    {
        $rcube  = rcube::get_instance();
        $config = (array) $rcube->config->get('password_http_client');

        return $rcube->get_http_client($config);
    }
}
