<?php

/**
 * Archive
 *
 * Plugin that adds a new button to the mailbox toolbar
 * to move messages to a (user selectable) archive folder.
 *
 * @version 2.1
 * @license GNU GPLv3+
 * @author Andre Rodier, Thomas Bruederli, Aleksander Machniak
 */
class archive extends rcube_plugin
{
  public $task = 'mail|settings';

  function init()
  {
    $rcmail = rcmail::get_instance();
    $archive_folder = $rcmail->config->get('archive_mbox');

    // add archive folder to the list of default mailboxes
    if (($default_folders = $rcmail->config->get('default_folders')) && !in_array($archive_folder, $default_folders)) {
      $default_folders[] = $archive_folder;
      $rcmail->config->set('default_folders', $default_folders);
    }

    // There is no "Archived flags"
    // $GLOBALS['IMAP_FLAGS']['ARCHIVED'] = 'Archive';
    if ($archive_folder && $rcmail->task == 'mail' && ($rcmail->action == '' || $rcmail->action == 'show')) {
      $skin_path = $this->local_skin_path();
      if (is_file($this->home . "/$skin_path/archive.css"))
        $this->include_stylesheet("$skin_path/archive.css");

      $this->include_script('archive.js');
      $this->add_texts('localization', true);
      $this->add_button(
        array(
            'type' => 'link',
            'label' => 'buttontext',
            'command' => 'plugin.archive',
            'class' => 'button buttonPas archive disabled',
            'classact' => 'button archive',
            'width' => 32,
            'height' => 32,
            'title' => 'buttontitle',
            'domain' => $this->ID,
        ),
        'toolbar');

      // register hook to localize the archive folder
      $this->add_hook('render_mailboxlist', array($this, 'render_mailboxlist'));

      // set env variables for client
      $rcmail->output->set_env('archive_folder', $archive_folder);
      $rcmail->output->set_env('archive_type', $rcmail->config->get('archive_type',''));
    }
    else if ($rcmail->task == 'mail') {
      // handler for ajax request
      $this->register_action('plugin.move2archive', array($this, 'move_messages'));
    }
    else if ($rcmail->task == 'settings') {
      $dont_override = $rcmail->config->get('dont_override', array());
      if (!in_array('archive_mbox', $dont_override)) {
        $this->add_hook('preferences_list', array($this, 'prefs_table'));
        $this->add_hook('preferences_save', array($this, 'save_prefs'));
      }
    }
  }

  /**
   * Hook to give the archive folder a localized name in the mailbox list
   */
  function render_mailboxlist($p)
  {
    $rcmail = rcmail::get_instance();
    $archive_folder = $rcmail->config->get('archive_mbox');
    $show_real_name = $rcmail->config->get('show_real_foldernames');

    // set localized name for the configured archive folder
    if ($archive_folder && !$show_real_name) {
      if (isset($p['list'][$archive_folder]))
        $p['list'][$archive_folder]['name'] = $this->gettext('archivefolder');
      else // search in subfolders
        $this->_mod_folder_name($p['list'], $archive_folder, $this->gettext('archivefolder'));
    }

    return $p;
  }

  /**
   * Helper method to find the archive folder in the mailbox tree
   */
  private function _mod_folder_name(&$list, $folder, $new_name)
  {
    foreach ($list as $idx => $item) {
      if ($item['id'] == $folder) {
        $list[$idx]['name'] = $new_name;
        return true;
      } else if (!empty($item['folders']))
        if ($this->_mod_folder_name($list[$idx]['folders'], $folder, $new_name))
        return true;
    }
    return false;
  }

  /**
   * Plugin action to move the submitted list of messages to the archive subfolders
   * according to the user settings and their headers.
   */
  function move_messages()
  {
    $this->add_texts('localization');

    $rcmail         = rcmail::get_instance();
    $storage        = $rcmail->get_storage();
    $delimiter      = $storage->get_hierarchy_delimiter();
    $archive_folder = $rcmail->config->get('archive_mbox');
    $archive_type   = $rcmail->config->get('archive_type', '');

    $storage->set_folder(($current_mbox = rcube_utils::get_input_value('_mbox', RCUBE_INPUT_POST)));

    $result  = array('reload' => false, 'update' => false, 'errors' => array());
    $folders = array();
    $uids    = rcube_utils::get_input_value('_uid', RCUBE_INPUT_POST);
    $search_request = get_input_value('_search', RCUBE_INPUT_GPC);

    if ($uids == '*') {
      $index = $storage->index(null, rcmail_sort_column(), rcmail_sort_order());
      $uids  = $index->get();
    }
    else {
      $uids = explode(',', $uids);
    }

    foreach ($uids as $uid) {
      if (!$archive_folder || !($message = $rcmail->storage->get_message($uid))) {
        continue;
      }

      $subfolder = null;
      switch ($archive_type) {
        case 'year':
          $subfolder = $rcmail->format_date($message->timestamp, 'Y');
          break;

        case 'month':
          $subfolder = $rcmail->format_date($message->timestamp, 'Y') . $delimiter . $rcmail->format_date($message->timestamp, 'm');
          break;

        case 'folder':
          $subfolder = $current_mbox;
          break;

        case 'sender':
          $from = $message->get('from');
          if (preg_match('/[\b<](.+@.+)[\b>]/i', $from, $m)) {
            $subfolder = $m[1];
          }
          else {
            $subfolder = $this->gettext('unkownsender');
          }

          // replace reserved characters in folder name
          $repl = $delimiter == '-' ? '_' : '-';
          $replacements[$delimiter] = $repl;
          $replacements['.'] = $repl;  // some IMAP server do not allow . characters
          $subfolder = strtr($subfolder, $replacements);
          break;

        default:
          $subfolder = '';
          break;
      }

      // compose full folder path
      $folder = $archive_folder . ($subfolder ? $delimiter . $subfolder : '');

      // create archive subfolder if it doesn't yet exist
      // we'll create all folders in the path
      if (!in_array($folder, $folders)) {
        if (empty($list)) {
          $list = $storage->list_folders('', $archive_folder . '*', 'mail', null, true);
        }
        $path = explode($delimiter, $folder);

        for ($i=0; $i<count($path); $i++) {
          $_folder = implode($delimiter, array_slice($path, 0, $i+1));
          if (!in_array($_folder, $list)) {
            if ($storage->create_folder($_folder, true)) {
              $result['reload'] = true;
              $list[] = $_folder;
            }
          }
        }

        $folders[] = $folder;
      }

      // move message to target folder
      if ($storage->move_message(array($uid), $folder)) {
        $result['update'] = true;
      }
      else {
        $result['errors'][] = $uid;
      }
    }  // end for

    // send response
    if ($result['errors']) {
      $rcmail->output->show_message($this->gettext('archiveerror'), 'warning');
    }
    if ($result['reload']) {
      $rcmail->output->show_message($this->gettext('archivedreload'), 'confirmation');
    }
    else if ($result['update']) {
      $rcmail->output->show_message($this->gettext('archived'), 'confirmation');
    }

    // refresh saved search set after moving some messages
    if ($search_request && $rcmail->storage->get_search_set()) {
        $_SESSION['search'] = $rcmail->storage->refresh_search();
    }

    if ($_POST['_from'] == 'show' && !empty($result['update'])) {
      if ($next = get_input_value('_next_uid', RCUBE_INPUT_GPC)) {
        $rcmail->output->command('show_message', $next);
      }
      else {
        $rcmail->output->command('command', 'list');
      }
    }
    else {
      $rcmail->output->command('plugin.move2archive_response', $result);
    }
  }

  /**
   * Hook to inject plugin-specific user settings
   */
  function prefs_table($args)
  {
    global $CURR_SECTION;

    if ($args['section'] == 'folders') {
      $this->add_texts('localization');

      $rcmail = rcmail::get_instance();

      // load folders list when needed
      if ($CURR_SECTION)
        $select = $rcmail->folder_selector(array('noselection' => '---', 'realnames' => true,
          'maxlength' => 30, 'exceptions' => array('INBOX'), 'folder_filter' => 'mail', 'folder_rights' => 'w'));
      else
        $select = new html_select();

      $args['blocks']['main']['options']['archive_mbox'] = array(
          'title' => $this->gettext('archivefolder'),
          'content' => $select->show($rcmail->config->get('archive_mbox'), array('name' => "_archive_mbox"))
      );

      // add option for structuring the archive folder
      $archive_type = new html_select(array('name' => '_archive_type', 'id' => 'ff_archive_type'));
      $archive_type->add($this->gettext('none'), '');
      $archive_type->add($this->gettext('archivetypeyear'), 'year');
      $archive_type->add($this->gettext('archivetypemonth'), 'month');
      $archive_type->add($this->gettext('archivetypesender'), 'sender');
      $archive_type->add($this->gettext('archivetypefolder'), 'folder');

      $args['blocks']['archive'] = array(
        'name' => Q($this->gettext('settingstitle')),
        'options' => array('archive_type' => array(
            'title' => $this->gettext('archivetype'),
            'content' => $archive_type->show($rcmail->config->get('archive_type'))
          )
        )
      );
    }

    return $args;
  }

  /**
   * Hook to save plugin-specific user settings
   */
  function save_prefs($args)
  {
    if ($args['section'] == 'folders') {
      $args['prefs']['archive_mbox'] = rcube_utils::get_input_value('_archive_mbox', rcube_utils::INPUT_POST);
      $args['prefs']['archive_type'] = rcube_utils::get_input_value('_archive_type', rcube_utils::INPUT_POST);
      return $args;
    }
  }

}
