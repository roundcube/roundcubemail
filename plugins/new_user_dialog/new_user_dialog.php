<?php

/**
 * Present identities settings dialog to new users
 *
 * When a new user is created, this plugin checks the default identity
 * and sets a session flag in case it is incomplete. An overlay box will appear
 * on the screen until the user has reviewed/completed his identity.
 *
 * @version @package_version@
 * @author Thomas Bruederli
 */
class new_user_dialog extends rcube_plugin
{
  public $task = 'login|mail';

  function init()
  {
    $this->add_hook('identity_create', array($this, 'create_identity'));
    $this->register_action('plugin.newusersave', array($this, 'save_data'));

    // register additional hooks if session flag is set
    if ($_SESSION['plugin.newuserdialog']) {
      $this->add_hook('render_page', array($this, 'render_page'));
    }
  }

  /**
   * Check newly created identity at first login
   */
  function create_identity($p)
  {
    // set session flag when a new user was created and the default identity seems to be incomplete
    if ($p['login'] && !$p['complete'])
      $_SESSION['plugin.newuserdialog'] = true;
  }

  /**
   * Callback function when HTML page is rendered
   * We'll add an overlay box here.
   */
  function render_page($p)
  {
    if ($_SESSION['plugin.newuserdialog'] && $p['template'] == 'mail') {
      $this->add_texts('localization');

      $rcmail = rcmail::get_instance();
      $identity = $rcmail->user->get_identity();
      $identities_level = intval($rcmail->config->get('identities_level', 0));

      // compose user-identity dialog
      $table = new html_table(array('cols' => 2));

      $table->add('title', $this->gettext('name'));
      $table->add(null, html::tag('input', array(
        'type' => 'text',
        'name' => '_name',
        'value' => $identity['name']
      )));

      $table->add('title', $this->gettext('email'));
      $table->add(null, html::tag('input', array(
        'type' => 'text',
        'name' => '_email',
        'value' => idn_to_utf8($identity['email']),
        'disabled' => ($identities_level == 1 || $identities_level == 3)
      )));

      // add overlay input box to html page
      $rcmail->output->add_footer(html::div(array('id' => 'newuseroverlay'),
        html::tag('form', array(
            'action' => $rcmail->url('plugin.newusersave'),
            'method' => 'post'),
          html::tag('h3', null, Q($this->gettext('identitydialogtitle'))) .
          html::p('hint', Q($this->gettext('identitydialoghint'))) .
          $table->show() .
          html::p(array('class' => 'formbuttons'),
            html::tag('input', array('type' => 'submit',
              'class' => 'button mainaction', 'value' => $this->gettext('save'))))
        )
      ));

      // disable keyboard events for messages list (#1486726)
      $rcmail->output->add_script(
        "$(document).ready(function () {
          rcmail.message_list.key_press = function(){};
          rcmail.message_list.key_down = function(){};
          $('input[name=_name]').focus();
          });", 'foot');

      $this->include_stylesheet('newuserdialog.css');
    }
  }

  /**
   * Handler for submitted form
   *
   * Check fields and save to default identity if valid.
   * Afterwards the session flag is removed and we're done.
   */
  function save_data()
  {
    $rcmail = rcmail::get_instance();
    $identity = $rcmail->user->get_identity();
    $identities_level = intval($rcmail->config->get('identities_level', 0));

    $save_data = array(
      'name' => get_input_value('_name', RCUBE_INPUT_POST),
      'email' => get_input_value('_email', RCUBE_INPUT_POST),
    );

    // don't let the user alter the e-mail address if disabled by config
    if ($identities_level == 1 || $identities_level == 3)
      $save_data['email'] = $identity['email'];
    else
      $save_data['email'] = idn_to_ascii($save_data['email']);

    // save data if not empty
    if (!empty($save_data['name']) && !empty($save_data['email'])) {
      $rcmail->user->update_identity($identity['identity_id'], $save_data);
      $rcmail->session->remove('plugin.newuserdialog');
    }

    $rcmail->output->redirect('');
  }

}

?>