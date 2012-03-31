<?php

/**
 * Mark as Junk
 *
 * Sample plugin that adds a new button to the mailbox toolbar
 * to mark the selected messages as Junk and move them to the Junk folder
 *
 * @version @package_version@
 * @license GNU GPLv3+
 * @author Thomas Bruederli
 */
class markasjunk extends rcube_plugin
{
  public $task = 'mail';

  function init()
  {
    $rcmail = rcmail::get_instance();

    $this->register_action('plugin.markasjunk', array($this, 'request_action'));

    if ($rcmail->action == '' || $rcmail->action == 'show') {
      $skin_path = $this->local_skin_path();
      $this->include_script('markasjunk.js');
      if (is_file($this->home . "/$skin_path/markasjunk.css"))
        $this->include_stylesheet("$skin_path/markasjunk.css");
      $this->add_texts('localization', true);

      $this->add_button(array(
        'type' => 'link',
        'label' => 'buttontext',
        'command' => 'plugin.markasjunk',
        'class' => 'button buttonPas junk disabled',
        'classact' => 'button junk',
        'title' => 'buttontitle',
        'domain' => 'markasjunk'), 'toolbar');
    }
  }

  function request_action()
  {
    $this->add_texts('localization');

    $GLOBALS['IMAP_FLAGS']['JUNK'] = 'Junk';
    $GLOBALS['IMAP_FLAGS']['NONJUNK'] = 'NonJunk';
    
    $uids = get_input_value('_uid', RCUBE_INPUT_POST);
    $mbox = get_input_value('_mbox', RCUBE_INPUT_POST);
    
    $rcmail = rcmail::get_instance();
    $rcmail->storage->unset_flag($uids, 'NONJUNK');
    $rcmail->storage->set_flag($uids, 'JUNK');
    
    if (($junk_mbox = $rcmail->config->get('junk_mbox')) && $mbox != $junk_mbox) {
      $rcmail->output->command('move_messages', $junk_mbox);
    }
    
    $rcmail->output->command('display_message', $this->gettext('reportedasjunk'), 'confirmation');
    $rcmail->output->send();
  }

}
