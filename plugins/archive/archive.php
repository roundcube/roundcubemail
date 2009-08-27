<?php

/**
 * Archive
 *
 * Plugin that adds a new button to the mailbox toolbar
 * to move messages to a (user selectable) archive folder.
 *
 * @version 1.4
 * @author Andre Rodier, Thomas Bruederli
 */
class archive extends rcube_plugin
{
  public $task = 'mail|settings';

  function init()
  {
    $this->register_action('plugin.archive', array($this, 'request_action'));

    # There is no "Archived flags"
    # $GLOBALS['IMAP_FLAGS']['ARCHIVED'] = 'Archive';
    
    $rcmail = rcmail::get_instance();
    if ($rcmail->task == 'mail' && ($rcmail->action == '' || $rcmail->action == 'show') && ($archive_folder = $rcmail->config->get('archive_mbox'))) {
      $this->include_script('archive.js');
      $this->add_texts('localization', true);
      $this->add_button(
        array(
            'command' => 'plugin.archive',
            'imagepas' => 'archive_pas.png',
            'imageact' => 'archive_act.png',
            'title' => 'buttontitle',
            'domain' => $this->ID,
        ),
        'toolbar');
      
      // register hook to localize the archive folder
      $this->add_hook('render_mailboxlist', array($this, 'render_mailboxlist'));

      // set env variable for client
      $rcmail->output->set_env('archive_folder', $archive_folder);

      // add archive folder to the list of defailt mailboxes
      if (($default_folders = $rcmail->config->get('default_imap_folders')) && !in_array($archive_folder, $default_folders)) {
        $default_folders[] = $archive_folder;
        $rcmail->config->set('default_imap_folders', $default_folders);
      }
      
    }
    else if ($rcmail->task == 'settings') {
      $dont_override = $rcmail->config->get('dont_override', array());
      if (!in_array('archive_mbox', $dont_override)) {
        $this->add_hook('user_preferences', array($this, 'prefs_table'));
        $this->add_hook('save_preferences', array($this, 'save_prefs'));
      }
    }
  }
  
  function render_mailboxlist($p)
  {
    $rcmail = rcmail::get_instance();
    $archive_folder = $rcmail->config->get('archive_mbox');
    
    // set localized name for the configured arcive folder
    if ($archive_folder && $p['list'][$archive_folder])
      $p['list'][$archive_folder]['name'] = $this->gettext('archivefolder');
      
    return $p;
  }

  function request_action()
  {
    $this->add_texts('localization');
    
    $uids = get_input_value('_uid', RCUBE_INPUT_POST);
    $mbox = get_input_value('_mbox', RCUBE_INPUT_POST);
    
    $rcmail = rcmail::get_instance();
    
    # There is no "Archive flags", but I left this line in case it may be useful
    # $rcmail->imap->set_flag($uids, 'ARCHIVE');
    
    if (($archive_mbox = $rcmail->config->get('archive_mbox')) && $mbox != $archive_mbox) {
      $rcmail->output->command('move_messages', $archive_mbox);
      $rcmail->output->command('display_message', $this->gettext('archived'), 'confirmation');
    }
    
    $rcmail->output->send();
  }

  function prefs_table($args)
  {
    if ($args['section'] == 'folders') {
      $this->add_texts('localization');
      
      $rcmail = rcmail::get_instance();
      $select = rcmail_mailbox_select(array('noselection' => '---', 'realnames' => true, 'maxlength' => 30));

      $args['blocks']['main']['options']['archive_mbox'] = array(
          'title' => $this->gettext('archivefolder'),
          'content' => $select->show($rcmail->config->get('archive_mbox'), array('name' => "_archive_mbox"))
      );
    }

    return $args;
  }

  function save_prefs($args)
  {
    $args['prefs']['archive_mbox'] = get_input_value('_archive_mbox', RCUBE_INPUT_POST);
    return $args;
  }

}
