<?php

/**
 * Change Password
 *
 * Plugin that adds a possibility to change password using a database
 * (Settings -> Password tab)
 *
 * @version 1.1
 * @author Aleksander 'A.L.E.C' Machniak <alec@alec.pl>
 * @editor Daniel Black
 *
 * Configuration Items (config/main.inc.php):
 *   password_confirm_current - boolean to determine whether current password
 *     is required to change password. Defaults to FALSE.
 *   password_db_dsn - is the PEAR database DSN for performing the query. Defaults
 *     to the default databse setting in config/db.inc.php
 *   password_query - the SQL query used to change the password.
 *     If the SQL query is a SELECT it will return an error message in a row if unsuccessful
 *     If the SQL query is a UPDATE it will update a single row only. 
 *     An UPDATE where zero rows changed will be inteperated to be a wrong username/password
 *     More than one row changed will be inteperated as an internal error
 *     The query can contain the following macros that will be expanded as follows:
 *       %p is replaced with the plaintext new password
 *       %c is replaced with the crypt version of the new password, MD5 if available
 *         otherwise DES.
 *       %u is replaced with the username (from the session info)
 *       %o is replaced with the password before the change
 *       %h is replaced with the imap host (from the session info)
 *     Escaping of macros is handled by this module.
 *     Defaults to "SELECT update_passwd(%c, %u)" 
 *     To use this you need to define the update_passwd function in your
 *     database.
 *
 * Example SQL queries:
 * These will typically need to define a function to change the password:
 * 
 * Example implementations of an update_passwd function:
 *
 * This is for use with LMS (http://lms.org.pl) database and postgres:
 * CREATE OR REPLACE FUNCTION update_passwd(hash text, account text) RETURNS integer AS $$
 * DECLARE
 *         res integer;
 * BEGIN
 *      UPDATE passwd SET password = hash
 *	WHERE login = split_part(account, '@', 1)
 *		AND domainid = (SELECT id FROM domains WHERE name = split_part(account, '@', 2))
 *	RETURNING id INTO res;
 *	RETURN res;
 * END;
 * $$ LANGUAGE plpgsql SECURITY DEFINER;
 *
 * This is for use with a SELECT update_passwd(%o,%c,%u) query
 * Uupdates the password only when the old password matches the MD5 password in the database
 * CREATE FUNCTION update_password (oldpass text, cryptpass text, user text) RETURNS text
 *        MODIFIES SQL DATA
 * BEGIN
 *   DECLARE currentsalt varchar(20);
 *   DECLARE error text;
 *   SET error = 'incorrect current password';
 *   SELECT substring_index(substr(user.password,4),_latin1'$',1) INTO currentsalt FROM users WHERE username=user;
 *   SELECT '' INTO error FROM users WHERE username=user AND password=ENCRYPT(oldpass,currentsalt);
 *   UPDATE users SET password=cryptpass WHERE username=user AND password=ENCRYPT(oldpass,currentsalt);
 *   RETURN error;
 * END
 *
 * Example SQL UPDATEs:
 * 
 *   Plain text passwords:
 *   UPDATE users SET password=%p WHERE username=%u AND password=%o AND domain=%h LIMIT 1
 * 
 *   Crypt text passwords:
 *   UPDATE users SET password=%c WHERE username=%u LIMIT 1
 *
 *   Use a MYSQL crypt function (*nix only) with random 8 character salt
 *   UPDATE users SET password=ENCRYPT(%p,concat(_utf8'$1$',right(md5(rand()),8),_utf8'$')) WHERE username=%u LIMIT 1
 * 
 *   MD5 stored passwords:
 *   UPDATE users SET password=MD5(%p) WHERE username=%u AND password=MD5(%o) LIMIT 1
 * 
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

    $confirm = $rcmail->config->get('password_confirm_current');
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

    if ($confirm) {
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

  private function _save($curpass,$passwd)
  {
    $cfg = rcmail::get_instance()->config;

    if (!($sql = $cfg->get('password_query')))
      $sql = "SELECT update_passwd(%c, %u)";

    if ($dsn = $cfg->get('password_db_dsn')) {
      $db = new rcube_mdb2($dsn, '', FALSE);
      $db->set_debug((bool)$cfg->get('sql_debug'));
      $db->db_connect('w');
    } else {
      $db = rcmail::get_instance()->get_dbh();
    }

    if ($err = $db->is_error())
      return $err;
    
    if (strpos($sql,'%c') !== FALSE) {
      $salt = '';
      if (CRYPT_MD5) { 
        $len = rand(3,CRYPT_SALT_LENGTH);
      } else if (CRYPT_STD_DES) {
        $len = 2;
      } else {
        return $this->gettext('nocryptfunction');
      }
      for ($i = 0; $i < $len ; $i++) {
        $salt .= chr(rand(ord('.'),ord('z')));
      }
      $sql = str_replace('%c',  $db->quote(crypt($passwd, CRYPT_MD5 ? '$1$'.$salt.'$' : $salt)), $sql);
    }
    $sql = str_replace('%u', $db->quote($_SESSION['username'],'text'), $sql);
    $sql = str_replace('%p', $db->quote($passwd,'text'), $sql);
    $sql = str_replace('%o', $db->quote($curpass,'text'), $sql);
    $sql = str_replace('%h', $db->quote($_SESSION['imap_host'],'text'), $sql);

    $res = $db->query($sql);
    if ($err = $db->is_error())
      return $err;
    if (strtolower(substr(trim($query),0,6))=='select') {
      return $db->fetch_array($res);
    } else { 
      $res = $db->affected_rows($res);
      if ($res == 0) return $this->gettext('errorsaving');
      if ($res == 1) return FALSE; // THis is the good case - 1 row updated
      return $this->gettext('internalerror');
    }

  }

}

?>
