<?php

/**
 * Change SASL Password
 *
 * Plugin that adds functionality ty to change the users Cyrus/SASL password.
 * The code is derrived from the Squirrelmail "Change SASL Password" Plugin
 * by Galen Johnson.
 *
 * It only works with saslpasswd2 on the same host where RoundCube runs
 * and requires shell access and gcc in order to compile the binary.
 *
 * For installation instructions please read the README file.
 *
 * @version 1.0
 * @author Thomas Bruederli
 */
class sasl_password extends rcube_plugin
{
  public $task = 'settings';

  function init()
  {
    $rcmail = rcmail::get_instance();
    // add Tab label
    $rcmail->output->add_label('password');
    $this->register_action('plugin.saslpassword', array($this, 'password_init'));
    $this->register_action('plugin.saslpassword-save', array($this, 'password_save'));
    $this->register_handler('plugin.body', array($this, 'password_form'));
    $this->include_script('sasl_password.js');
  }

  function password_init()
  {
    $this->add_texts('locale/');
    $rcmail = rcmail::get_instance();
    $rcmail->output->set_pagetitle($this->gettext('changepasswd'));
    $rcmail->output->send('plugin');
  }
  
  function password_save()
  {
    $rcmail = rcmail::get_instance();

    $this->add_texts('locale/');

    if (!isset($_POST['_curpasswd']) || !isset($_POST['_newpasswd'])) {
      $rcmail->output->command('display_message', $this->gettext('nopassword'), 'error');
    }
    else {
      $curpwd = get_input_value('_curpasswd', RCUBE_INPUT_POST);
      $newpwd = get_input_value('_newpasswd', RCUBE_INPUT_POST);

      if ($rcmail->decrypt($_SESSION['password']) != $curpwd) {
        $rcmail->output->command('display_message', $this->gettext('passwordincorrect'), 'error');
      }
      else if ($this->_save($newpwd)) {
        $rcmail->output->command('display_message', $this->gettext('successfullysaved'), 'confirmation');
        $_SESSION['password'] = $rcmail->encrypt($newpwd);
      }
      else {
        $rcmail->output->command('display_message', $this->gettext('errorsaving'), 'error');
      }
    }

    rcmail_overwrite_action('plugin.saslpassword');
    rcmail::get_instance()->output->send('plugin');
  }

  function password_form()
  {
    $rcmail = rcmail::get_instance();

    // add some labels to client
    $rcmail->output->add_label(
        'sasl_password.nopassword',
        'sasl_password.nocurpassword',
        'sasl_password.passwordinconsistency',
        'sasl_password.changepasswd'
    );

    $table = new html_table(array('cols' => 2));

    // show current password selection
    $field_id = 'saslcurpasswd';
    $input_newpasswd = new html_passwordfield(array('name' => '_curpasswd', 'id' => $field_id, 'size' => 25));

    $table->add('title', html::label($field_id, Q($this->gettext('curpasswd'))));
    $table->add(null, $input_newpasswd->show());

    // show new password selection
    $field_id = 'saslnewpasswd';
    $input_newpasswd = new html_passwordfield(array('name' => '_newpasswd', 'id' => $field_id, 'size' => 25));

    $table->add('title', html::label($field_id, Q($this->gettext('newpasswd'))));
    $table->add(null, $input_newpasswd->show());

    // show confirm password selection
    $field_id = 'saslconfpasswd';
    $input_confpasswd = new html_passwordfield(array('name' => '_confpasswd', 'id' => $field_id, 'size' => 25));

    $table->add('title', html::label($field_id, Q($this->gettext('confpasswd'))));
    $table->add(null, $input_confpasswd->show());

    $out = html::div(array('class' => "settingsbox", 'style' => "margin:0"),
      html::div(array('id' => "userprefs-title"), $this->gettext('changepasswd')) .
      html::div(array('style' => "padding:15px"), $table->show() .
        html::p(null,
          $rcmail->output->button(array(
            'command' => 'plugin.saslpassword-save',
            'type' => 'input',
            'class' => 'button mainaction',
            'label' => 'save'
        )))
      )
    );

    $rcmail->output->add_gui_object('passform', 'password-form');

    return $rcmail->output->form_tag(array(
        'id' => 'password-form',
        'name' => 'password-form',
        'method' => 'post',
        'action' => './?_task=settings&_action=plugin.saslpassword-save',
    ), $out);
  }

  private function _save($passwd)
  {
    $curdir = realpath(dirname(__FILE__));
    $username = escapeshellcmd($_SESSION['username']);
    $code = 1;

    if ($fh = popen("$curdir/chgsaslpasswd -p $username", 'w')) {
      fwrite($fh, $passwd."\n");
      $code = pclose($fh);
    }

    return ($code == 0);
  }

}

?>
