<?php

/**
 * Sample plugin that adds a new tab to the settings section
 * to display some information about the current user
 */
class userinfo extends rcube_plugin
{
  public $task = 'settings';

  function init()
  {
    $this->add_texts('localization/', array('userinfo'));
    $this->register_action('plugin.userinfo', array($this, 'infostep'));
    $this->include_script('userinfo.js');
  }

  function infostep()
  {
    $this->register_handler('plugin.body', array($this, 'infohtml'));
    rcmail::get_instance()->output->send('plugin');
  }
  
  function infohtml()
  {
    $rcmail = rcmail::get_instance();
    $user = $rcmail->user;
    
    $table = new html_table(array('cols' => 2, 'cellpadding' => 3));

    $table->add('title', 'ID');
    $table->add('', Q($user->ID));
    
    $table->add('title', Q($this->gettext('username')));
    $table->add('', Q($user->data['username']));
    
    $table->add('title', Q($this->gettext('server')));
    $table->add('', Q($user->data['mail_host']));

    $table->add('title', Q($this->gettext('created')));
    $table->add('', Q($user->data['created']));

    $table->add('title', Q($this->gettext('lastlogin')));
    $table->add('', Q($user->data['last_login']));
    
    $identity = $user->get_identity();
    $table->add('title', Q($this->gettext('defaultidentity')));
    $table->add('', Q($identity['name'] . ' <' . $identity['email'] . '>'));
    
    return html::tag('h4', null, Q('Infos for ' . $user->get_username())) . $table->show();
  }

}