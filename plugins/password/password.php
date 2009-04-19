<?php

/**
 * Change Password
 *
 * Sample plugin that adds a possibility to change password
 * (Settings -> Password tab)
 *
 * @version 1.0
 * @author Aleksander 'A.L.E.C' Machniak
 */
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
    $this->register_handler('plugin.body', array($this, 'password_form'));
    $this->include_script('password.js');
  }

  function password_init()
  {
    $this->add_texts('localization/');
    rcmail::get_instance()->output->send('plugin');
  }
  
  function password_save()
  {
    $rcmail = rcmail::get_instance();

    $this->add_texts('localization/');

    if (!isset($_POST['_curpasswd']) || !isset($_POST['_newpasswd']))
      $rcmail->output->command('display_message', $this->gettext('nopassword'), 'error');
    else {
      $curpwd = get_input_value('_curpasswd', RCUBE_INPUT_POST);
      $newpwd = get_input_value('_newpasswd', RCUBE_INPUT_POST);

      if ($_SESSION['password'] != $rcmail->encrypt_passwd($curpwd))
        $rcmail->output->command('display_message', $this->gettext('passwordincorrect'), 'error');
      else if ($res = $this->_save($newpwd)) {
        $rcmail->output->command('display_message', $this->gettext('successfullysaved'), 'confirmation');
        $_SESSION['password'] = $rcmail->encrypt_passwd($newpwd);
      } else
        $rcmail->output->command('display_message', $this->gettext('errorsaving'), 'error');
    }

    rcmail_overwrite_action('plugin.password');
    rcmail::get_instance()->output->send('plugin');
  }

  function password_form()
  {
    $rcmail = rcmail::get_instance();

    // add some labels to client
    $rcmail->output->add_label(
	'password.nopassword',
	'password.nocurpassword',
        'password.passwordinconsistency',
	'password.changepasswd'
    );
//    $rcmail->output->set_pagetitle($this->gettext('changepasswd'));
    $rcmail->output->set_env('product_name', $rcmail->config->get('product_name'));

    // allow the following attributes to be added to the <table> tag
    $attrib_str = create_attrib_string($attrib, array('style', 'class', 'id', 'cellpadding', 'cellspacing', 'border', 'summary'));

    // return the complete edit form as table
    $out = '<table' . $attrib_str . ">\n\n";

    $a_show_cols = array('curpasswd'   => array('type' => 'text'),
                'newpasswd'   => array('type' => 'text'),
                'confpasswd'   => array('type' => 'text'));

    // show current password selection
    $field_id = 'curpasswd';
    $input_newpasswd = new html_passwordfield(array('name' => '_curpasswd', 'id' => $field_id, 'size' => 20));

    $out .= sprintf("<tr><td class=\"title\"><label for=\"%s\">%s</label></td><td>%s</td></tr>\n",
                $field_id,
                rep_specialchars_output($this->gettext('curpasswd')),
                $input_newpasswd->show($rcmail->config->get('curpasswd')));

    // show new password selection
    $field_id = 'newpasswd';
    $input_newpasswd = new html_passwordfield(array('name' => '_newpasswd', 'id' => $field_id, 'size' => 20));

    $out .= sprintf("<tr><td class=\"title\"><label for=\"%s\">%s</label></td><td>%s</td></tr>\n",
                $field_id,
                rep_specialchars_output($this->gettext('newpasswd')),
                $input_newpasswd->show($rcmail->config->get('newpasswd')));

    // show confirm password selection
    $field_id = 'confpasswd';
    $input_confpasswd = new html_passwordfield(array('name' => '_confpasswd', 'id' => $field_id, 'size' => 20));

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


  private function _save($passwd)
  {
    $cfg = rcmail::get_instance()->config;

    if (!($sql = $cfg->get('password_query')))
      $sql = "SELECT update_passwd('%p', '%u')";
        
    $sql = str_replace('%u', $_SESSION['username'], $sql);
    $sql = str_replace('%p', crypt($passwd), $sql);

    if ($dsn = $cfg->get('db_passwd_dsn')) {
      $db = new rcube_mdb2($dsn, '', FALSE);
      $db->set_debug((bool)$cfg->get('sql_debug'));
      $db->db_connect('w');
    } else {
      $db = rcmail::get_instance()->get_dbh();
    }
    
    if (!$db->db_connected)
      return false;
    
    $res = $db->query($sql);
    $res = $db->fetch_array($res);

    return $res;
  }

}

?>
