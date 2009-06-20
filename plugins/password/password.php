<?php

/*
 +-------------------------------------------------------------------------+
 | Password Plugin for Roundcube                                           |
 | Version 1.3                                                             |
 |                                                                         |
 | Copyright (C) 2009, RoundCube Dev. - Switzerland                        |
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

class password extends rcube_plugin
{
  public $task = 'settings';

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

    if (($confirm && !isset($_POST['_curpasswd'])) || !isset($_POST['_newpasswd']))
      $rcmail->output->command('display_message', $this->gettext('nopassword'), 'error');
    else {
      $curpwd = get_input_value('_curpasswd', RCUBE_INPUT_POST);
      $newpwd = get_input_value('_newpasswd', RCUBE_INPUT_POST);

      if ($confirm && $rcmail->decrypt($_SESSION['password']) != $curpwd)
        $rcmail->output->command('display_message', $this->gettext('passwordincorrect'), 'error');
      else if (!($res = $this->_save($curpwd,$newpwd))) {
        $rcmail->output->command('display_message', $this->gettext('successfullysaved'), 'confirmation');
        $_SESSION['password'] = $rcmail->encrypt($newpwd);
      } else
        $rcmail->output->command('display_message', $res, 'error');
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

    // allow the following attributes to be added to the <table> tag
    $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border', 'summary'));

    // return the complete edit form as table
    $out = '<table' . $attrib_str . ">\n\n";

    if ($rcmail->config->get('password_confirm_current')) {
      // show current password selection
      $field_id = 'curpasswd';
      $input_newpasswd = new html_passwordfield(array('name' => '_curpasswd', 'id' => $field_id,
    	    'size' => 20, 'autocomplete' => 'off'));
  
      $out .= sprintf("<tr><td class=\"title\"><label for=\"%s\">%s</label></td><td>%s</td></tr>\n",
                  $field_id,
                  rep_specialchars_output($this->gettext('curpasswd')),
                  $input_newpasswd->show($rcmail->config->get('curpasswd')));
    }

    // show new password selection
    $field_id = 'newpasswd';
    $input_newpasswd = new html_passwordfield(array('name' => '_newpasswd', 'id' => $field_id,
	    'size' => 20, 'autocomplete' => 'off'));

    $out .= sprintf("<tr><td class=\"title\"><label for=\"%s\">%s</label></td><td>%s</td></tr>\n",
                $field_id,
                rep_specialchars_output($this->gettext('newpasswd')),
                $input_newpasswd->show($rcmail->config->get('newpasswd')));

    // show confirm password selection
    $field_id = 'confpasswd';
    $input_confpasswd = new html_passwordfield(array('name' => '_confpasswd', 'id' => $field_id,
	    'size' => 20, 'autocomplete' => 'off'));

    $out .= sprintf("<tr><td class=\"title\"><label for=\"%s\">%s</label></td><td>%s</td></tr>\n",
                $field_id,
                rep_specialchars_output($this->gettext('confpasswd')),
                $input_confpasswd->show($rcmail->config->get('confpasswd')));

    $out .= "\n</table>";

    $out .= '<br />';
    
    $out .= $rcmail->output->button(array(
	    'command' => 'plugin.password-save',
	    'type' => 'input',
	    'class' => 'button mainaction',
	    'label' => 'save'
    ));

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
	'file' => __FILE__,
	'message' => "Password plugin: Unable to open driver file $driver"
	), true, false);
      return $this->gettext('internalerror');
    }
    
    include($driver);

    if (!function_exists('password_save')) {
      raise_error(array(
        'code' => 600,
	'type' => 'php',
	'file' => __FILE__,
	'message' => "Password plugin: Broken driver: $driver"
	), true, false);
      return $this->gettext('internalerror');
    }

    $result = password_save($curpass, $passwd);

    switch ($result) {
      case PASSWORD_SUCCESS:
        return;
      case PASSWORD_CRYPT_ERROR;
        return $this->gettext('crypterror');
      case PASSWORD_CONNECT_ERROR;
        return $this->gettext('connecterror');
      case PASSWORD_ERROR:
      default:
        return $this->gettext('internalerror');
    }
  }

}

?>
