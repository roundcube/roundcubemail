<?php

/*
 +-------------------------------------------------------------------------+
 | Password Plugin for Roundcube                                           |
 | @version @package_version@                                                             |
 |                                                                         |
 | Copyright (C) 2009-2010, Roundcube Dev.                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License version 2          |
 | as published by the Free Software Foundation.                           |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License along |
 | with this program; if not, write to the Free Software Foundation, Inc., |
 | 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.             |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+

 $Id: index.php 2645 2009-06-15 07:01:36Z alec $

*/

define('PASSWORD_CRYPT_ERROR', 1);
define('PASSWORD_ERROR', 2);
define('PASSWORD_CONNECT_ERROR', 3);
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
    public $task    = 'settings';
    public $noframe = true;
    public $noajax  = true;

    function init()
    {
        $rcmail = rcmail::get_instance();
        // add Tab label
        $rcmail->output->add_label('password');
        $this->register_action('plugin.password', array($this, 'password_init'));
        $this->register_action('plugin.password-save', array($this, 'password_save'));
        $this->include_script('password.js');
    }

    function password_init()
    {
        $this->add_texts('localization/');
        $this->register_handler('plugin.body', array($this, 'password_form'));

        $rcmail = rcmail::get_instance();
        $rcmail->output->set_pagetitle($this->gettext('changepasswd'));
        $rcmail->output->send('plugin');
    }

    function password_save()
    {
        $rcmail = rcmail::get_instance();
        $this->load_config();

        $this->add_texts('localization/');
        $this->register_handler('plugin.body', array($this, 'password_form'));
        $rcmail->output->set_pagetitle($this->gettext('changepasswd'));

        $confirm = $rcmail->config->get('password_confirm_current');
        $required_length = intval($rcmail->config->get('password_minimum_length'));
        $check_strength = $rcmail->config->get('password_require_nonalpha');

        if (($confirm && !isset($_POST['_curpasswd'])) || !isset($_POST['_newpasswd'])) {
            $rcmail->output->command('display_message', $this->gettext('nopassword'), 'error');
        }
        else {

            $charset    = strtoupper($rcmail->config->get('password_charset', 'ISO-8859-1'));
            $rc_charset = strtoupper($rcmail->output->get_charset());

            $sespwd = $rcmail->decrypt($_SESSION['password']);
            $curpwd = $confirm ? get_input_value('_curpasswd', RCUBE_INPUT_POST, true, $charset) : $sespwd;
            $newpwd = get_input_value('_newpasswd', RCUBE_INPUT_POST, true);
            $conpwd = get_input_value('_confpasswd', RCUBE_INPUT_POST, true);

            // check allowed characters according to the configured 'password_charset' option
            // by converting the password entered by the user to this charset and back to UTF-8
            $orig_pwd = $newpwd;
            $chk_pwd = rcube_charset_convert($orig_pwd, $rc_charset, $charset);
            $chk_pwd = rcube_charset_convert($chk_pwd, $charset, $rc_charset);

            // WARNING: Default password_charset is ISO-8859-1, so conversion will
            // change national characters. This may disable possibility of using
            // the same password in other MUA's.
            // We're doing this for consistence with Roundcube core
            $newpwd = rcube_charset_convert($newpwd, $rc_charset, $charset);
            $conpwd = rcube_charset_convert($conpwd, $rc_charset, $charset);

            if ($chk_pwd != $orig_pwd) {
                $rcmail->output->command('display_message', $this->gettext('passwordforbidden'), 'error');
            }
            // other passwords validity checks
            else if ($conpwd != $newpwd) {
                $rcmail->output->command('display_message', $this->gettext('passwordinconsistency'), 'error');
            }
            else if ($confirm && $sespwd != $curpwd) {
                $rcmail->output->command('display_message', $this->gettext('passwordincorrect'), 'error');
            }
            else if ($required_length && strlen($newpwd) < $required_length) {
                $rcmail->output->command('display_message', $this->gettext(
	                array('name' => 'passwordshort', 'vars' => array('length' => $required_length))), 'error');
            }
            else if ($check_strength && (!preg_match("/[0-9]/", $newpwd) || !preg_match("/[^A-Za-z0-9]/", $newpwd))) {
                $rcmail->output->command('display_message', $this->gettext('passwordweak'), 'error');
            }
            // password is the same as the old one, do nothing, return success
            else if ($sespwd == $newpwd) {
                $rcmail->output->command('display_message', $this->gettext('successfullysaved'), 'confirmation');
            }
            // try to save the password
            else if (!($res = $this->_save($curpwd, $newpwd))) {
                $rcmail->output->command('display_message', $this->gettext('successfullysaved'), 'confirmation');

		// allow additional actions after password change (e.g. reset some backends)
		$plugin = $rcmail->plugins->exec_hook('password_change', array(
		    'old_pass' => $curpwd, 'new_pass' => $newpwd));

                // Reset session password
                $_SESSION['password'] = $rcmail->encrypt($plugin['new_pass']);

                // Log password change
                if ($rcmail->config->get('password_log')) {
                    write_log('password', sprintf('Password changed for user %s (ID: %d) from %s',
                        $rcmail->user->get_username(), $rcmail->user->ID, rcmail_remote_ip()));
                }
            }
            else {
                $rcmail->output->command('display_message', $res, 'error');
            }
        }

        rcmail_overwrite_action('plugin.password');
        $rcmail->output->send('plugin');
    }

    function password_form()
    {
        $rcmail = rcmail::get_instance();
        $this->load_config();

        // add some labels to client
        $rcmail->output->add_label(
            'password.nopassword',
            'password.nocurpassword',
            'password.passwordinconsistency'
        );

        $rcmail->output->set_env('product_name', $rcmail->config->get('product_name'));

        $table = new html_table(array('cols' => 2));

        if ($rcmail->config->get('password_confirm_current')) {
            // show current password selection
            $field_id = 'curpasswd';
            $input_curpasswd = new html_passwordfield(array('name' => '_curpasswd', 'id' => $field_id,
                'size' => 20, 'autocomplete' => 'off'));
  
            $table->add('title', html::label($field_id, Q($this->gettext('curpasswd'))));
            $table->add(null, $input_curpasswd->show());
        }

        // show new password selection
        $field_id = 'newpasswd';
        $input_newpasswd = new html_passwordfield(array('name' => '_newpasswd', 'id' => $field_id,
            'size' => 20, 'autocomplete' => 'off'));

        $table->add('title', html::label($field_id, Q($this->gettext('newpasswd'))));
        $table->add(null, $input_newpasswd->show());

        // show confirm password selection
        $field_id = 'confpasswd';
        $input_confpasswd = new html_passwordfield(array('name' => '_confpasswd', 'id' => $field_id,
            'size' => 20, 'autocomplete' => 'off'));

        $table->add('title', html::label($field_id, Q($this->gettext('confpasswd'))));
        $table->add(null, $input_confpasswd->show());

        $out = html::div(array('class' => 'box'),
            html::div(array('id' => 'prefs-title', 'class' => 'boxtitle'), $this->gettext('changepasswd')) .
            html::div(array('class' => 'boxcontent'), $table->show() .
            html::p(null,
                $rcmail->output->button(array(
                    'command' => 'plugin.password-save',
                    'type' => 'input',
                    'class' => 'button mainaction',
                    'label' => 'save'
            )))));

        $rcmail->output->add_gui_object('passform', 'password-form');

        return $rcmail->output->form_tag(array(
            'id' => 'password-form',
            'name' => 'password-form',
            'method' => 'post',
            'action' => './?_task=settings&_action=plugin.password-save',
        ), $out);
    }

    private function _save($curpass, $passwd)
    {
        $config = rcmail::get_instance()->config;
        $driver = $this->home.'/drivers/'.$config->get('password_driver', 'sql').'.php';

        if (!is_readable($driver)) {
            raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Unable to open driver file $driver"
            ), true, false);
            return $this->gettext('internalerror');
        }

        include($driver);

        if (!function_exists('password_save')) {
            raise_error(array(
                'code' => 600,
                'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Password plugin: Broken driver: $driver"
            ), true, false);
            return $this->gettext('internalerror');
        }

        $result = password_save($curpass, $passwd);

        if (is_array($result)) {
            $message = $result['message'];
            $result  = $result['code'];
        }

        switch ($result) {
            case PASSWORD_SUCCESS:
                return;
            case PASSWORD_CRYPT_ERROR;
                $reason = $this->gettext('crypterror');
            case PASSWORD_CONNECT_ERROR;
                $reason = $this->gettext('connecterror');
            case PASSWORD_ERROR:
            default:
                $reason = $this->gettext('internalerror');
        }

        if ($message) {
            $reason .= ' ' . $message;
        }

        return $reason;
    }
}
