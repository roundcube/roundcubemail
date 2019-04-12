<?php

/**
* @version 2.1
* @author Cor Bosman (cor@roundcu.be)
*/

class message_highlight extends rcube_plugin
{
  public $task = 'mail|settings';
  private $rc;
  private $prefs;
  private $config;

  public function init()
  {
    $this->rc = rcube::get_instance();

    if ($this->rc->task === 'settings') {

      $this->add_texts('localization/', array('deleteconfirm'));

      $this->add_hook('preferences_list', array($this, 'mh_preferences'));
      $this->add_hook('preferences_save', array($this, 'mh_save'));
      $this->add_hook('preferences_sections_list',array($this, 'mh_preferences_section'));

      $this->include_script('colorpicker/mColorPicker.js');

      $this->register_action('plugin.mh_add_row', array($this, 'mh_add_row'));
    } elseif ($this->rc->task === 'mail') {
      $this->add_hook('storage_init', array($this, 'storage_init'));
      $this->add_hook('messages_list', array($this, 'mh_highlight'));
    }

    $this->include_script('message_highlight.js');
    $this->load_config();

    $skin_path = $this->local_skin_path();
    $this->include_stylesheet("$skin_path/message_highlight.css");


  }

  /**
   * add the CC header to fetched headers
   *
   * @param $p
   * @return mixed
   */
  function storage_init($p)
  {
    $p['fetch_headers'] .= trim($p['fetch_headers']. ' ' . 'CC');
    return($p);
  }


  /**
   * add highlight data to messages
   *
   * @param $p
   * @return mixed
   */
  function mh_highlight($p)
  {
    $this->prefs = array_merge($this->rc->config->get('message_highlight', array()), $this->rc->config->get('message_highlight_default', array()));

    // dont loop over all messages if we dont have any highlights or no msgs
    if(!count($this->prefs) or !isset($p['messages']) or !is_array($p['messages'])) return $p;
    
    // loop over all messages and add highlight color to each message
    foreach($p['messages'] as $message) {
      if(($color = $this->mh_find_match($message)) !== false ) {
        $message->list_flags['extra_flags']['plugin_mh_color'] = $color;
      }
    }
    return($p);
  }


  /**
   * find a match
   *
   * @param $message
   * @return bool
   */
  function mh_find_match($message) {
    foreach($this->prefs as $p) {
      if(stristr(rcube_mime::decode_header($message->{$p['header']}), $p['input'])) {
        return($p['color']);
      }
    }
    return false;
  }

  // user preferences
  function mh_preferences($args) {
    if($args['section'] == 'mh_preferences') {
      $this->add_texts('localization/', false);

      $args['blocks']['mh_preferences'] =  array(
        'options' => array(),
        'name'    => rcube::Q($this->gettext('mh_title'))
        );

      $i = 1;
      $prefs = $this->rc->config->get('message_highlight', array());

      foreach($prefs as $p) {
        $args['blocks']['mh_preferences']['options'][$i++] = array(
          'content' => $this->mh_get_form_row($p['header'], $p['input'], $p['color'], true)
        );
      }

      // no rows yet, add 1 empty row
      if($i == 1) {
        $args['blocks']['mh_preferences']['options'][$i] = array(
          'content' => 	$this->mh_get_form_row()
          );
      }
    }

    return($args);
  }

  function mh_add_row() {
    $this->rc->output->command('plugin.mh_receive_row', array('row' => $this->mh_get_form_row()));
  }

  // create a form row
  function mh_get_form_row($header = 'from', $input = '', $color = '#ffffff', $delete = false) {

    // header select box
    $header_select = new html_select(array('name' => '_mh_header[]', 'class' => 'rcmfd_mh_header form-control custom-select pretty-select'));
    $header_select->add(rcube::Q($this->gettext('subject')), 'subject');
    $header_select->add(rcube::Q($this->gettext('from')), 'from');
    $header_select->add(rcube::Q($this->gettext('to')), 'to');
    $header_select->add(rcube::Q($this->gettext('cc')), 'cc');

    // input field
    $input = new html_inputfield(array('name' => '_mh_input[]', 'class' => 'rcmfd_mh_input form-control', 'type' => 'text', 'autocomplete' => 'off', 'value' => $input));

    // color box
    $color = html::tag('input', array('id' => uniqid() ,'name' => '_mh_color[]', 'class' => 'mh_color_input', 'value' => $color, 'data-text' => 'hidden', 'data-hex' => 'true'));

    // delete button
    // $button = html::a(array('href' => '#', 'class' => '  ', 'title' => $this->gettext('mh_delete')) , 'del');
    $button = html::tag('a', array('href' => '#', 'class' => 'button icon mh_delete ', 'title' => $this->gettext('mh_delete')), '');
    // $add_button = html::a(array('href' => '#', 'class' => 'button icon mh_add', 'title' => $this->gettext('mh_add')), '');
    // add button
    $button = html::tag('input', array('class' => 'button mh_delete mh_button form-control btn btn-defaut', 'type' => 'button', 'value' => $this->gettext('mh_delete'), 'title' => $this->gettext('mh_delete_description')));
    $add_button = html::tag('input', array('class' => 'button mh_add mh_button form-control btn btn-default', 'type' => 'button', 'value' => $this->gettext('mh_add'), 'title' => $this->gettext('mh_add_description')));

    $content = html::div('mh_preferences_row',
      html::div('', $header_select->show($header)) .
      html::div('ml-3 text-center', html::span('mh_matches', rcube::Q($this->gettext('mh_matches')))) .
      html::div('ml-3', $input->show()) .
      html::div('ml-5 text-center', html::span('mh_color', rcube::Q($this->gettext('mh_color')))) .
      html::div('ml-3', $color) .
      html::div('ml-3', $button) .
      html::div('ml-3', $add_button)
    );

    return($content);
  }

  // add a section to the preferences tab
  function mh_preferences_section($args) {
    $this->add_texts('localization/', false);
    $args['list']['mh_preferences'] = array(
      'id'      => 'mh_preferences',
      'section' => rcube::Q($this->gettext('mh_title'))
      );
    return($args);
  }

  // save preferences
  function mh_save($args) {
    if($args['section'] != 'mh_preferences') return;

    $rcmail = rcmail::get_instance();

    $header  = rcube_utils::get_input_value('_mh_header', rcube_utils::INPUT_POST);
    $input   = rcube_utils::get_input_value('_mh_input', rcube_utils::INPUT_POST);
    $color   = rcube_utils::get_input_value('_mh_color', rcube_utils::INPUT_POST);


    for($i=0; $i < count($header); $i++) {
      if(!in_array($header[$i], array('subject', 'from', 'to', 'cc'))) {
        $rcmail->output->show_message('message_highlight.headererror', 'error');
        return;
      }
      if(!preg_match('/^#[0-9a-fA-F]{2,6}$/', $color[$i])) {
        $rcmail->output->show_message('message_highlight.invalidcolor', 'error');
        return;
      }
      if($input[$i] == '') {
        continue;
      }
      $prefs[] = array('header' => $header[$i], 'input' => $input[$i], 'color' => $color[$i]);
    }

    $args['prefs']['message_highlight'] = $prefs;
    return($args);
  }
}
