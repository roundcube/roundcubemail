<?php

/**
 * Detect VCard attachments and show a button to add them to address book
 *
 * @version 1.0
 * @author Thomas Bruederli
 */
class vcard_attachments extends rcube_plugin
{
  public $task = 'mail';
  
  private $message;
  private $vcard_part;

  function init()
  {
    $rcmail = rcmail::get_instance();
    if ($rcmail->action == 'show' || $rcmail->action == 'preview') {
      $this->add_hook('message_load', array($this, 'message_load'));
      $this->add_hook('template_object_messagebody', array($this, 'html_output'));
    }
    
    $this->register_action('plugin.savevcard', array($this, 'save_vcard'));
  }
  
  /**
   * Check message attachments for vcards
   */
  function message_load($p)
  {
    $this->message = $p['object'];
    
    foreach ((array)$this->message->attachments as $attachment) {
      if (in_array($attachment->mimetype, array('text/vcard', 'text/x-vcard')))
        $this->vcard_part = $attachment->mime_id;
    }
  }
  
  /**
   * This callback function adds a box below the message content
   * if there is a vcard attachment available
   */
  function html_output($p)
  {
    if ($this->vcard_part) {
      $vcard = new rcube_vcard($this->message->get_part_content($this->vcard_part));
      
      // successfully parsed vcard
      if ($vcard->displayname) {
        $display = $vcard->displayname;
        if ($vcard->email[0])
          $display .= ' <'.$vcard->email[0].'>';
        
        // add box below messsage body
        $p['content'] .= html::p(array('style' => "margin:1em; padding:0.5em; border:1px solid #999; border-radius:4px; -moz-border-radius:4px; -webkit-border-radius:4px; width: auto;"),
          html::a(array(
              'href' => "#",
              'onclick' => "return plugin_vcard_save_contact('".JQ($this->vcard_part)."')",
              'title' => "Save contact in local address book"),  // TODO: localize this title
            html::img(array('src' => $this->url('vcard_add_contact.png'), 'align' => "middle")))
            . ' ' . html::span(null, Q($display)));
        
        $this->include_script('vcardattach.js');
      }
    }
    
    return $p;
  }
  
  /**
   * Handler for request action
   */
  function save_vcard()
  {
    $uid = get_input_value('_uid', RCUBE_INPUT_POST);
    $mbox = get_input_value('_mbox', RCUBE_INPUT_POST);
    $mime_id = get_input_value('_part', RCUBE_INPUT_POST);
    
    $rcmail = rcmail::get_instance();
    $part = $uid && $mime_id ? $rcmail->imap->get_message_part($uid, $mime_id) : null;
    
    $error_msg = 'Failed to saved vcard'; // TODO: localize this text
    
    if ($part && ($vcard = new rcube_vcard($part)) && $vcard->displayname && $vcard->email) {
      $contacts = $rcmail->get_address_book(null, true);
      
      // check for existing contacts
      $existing = $contacts->search('email', $vcard->email[0], true, false);
      if ($done = $existing->count) {
        $rcmail->output->command('display_message', $this->gettext('contactexists'), 'warning');
      }
      else {
        // add contact
        $success = $contacts->insert(array(
          'name' => $vcard->displayname,
          'firstname' => $vcard->firstname,
          'surname' => $vcard->surname,
          'email' => $vcard->email[0],
          'vcard' => $vcard->export(),
        ));
        
        if ($success)
          $rcmail->output->command('display_message', $this->gettext('addedsuccessfully'), 'confirmation');
        else
          $rcmail->output->command('display_message', $error_msg, 'error');
      }
    }
    else
      $rcmail->output->command('display_message', $error_msg, 'error');
    
    $rcmail->output->send();
  }
  
}