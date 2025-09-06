<?php

/**
 * Check box plugin
 *
 *
 * @version 0.2.4
 * @author Denis Sobolev
 */

class chbox extends rcube_plugin {
  public $task = 'mail';

  function init() {
    $rcmail = rcmail::get_instance();
    if (($rcmail->task == 'mail') && ($rcmail->action == '')) {
      $this->add_hook('render_page', array($this, 'select_menu'));
      $this->add_hook('render_page', array($this, 'startup_chbox'));
      $this->include_script('chbox.js');
    }
    $this->add_hook('messages_list', array($this, 'message_list'));
  }

  function startup_chbox($args){
    $this->add_texts('localization');
    $rcmail = rcmail::get_instance();
    $icon = 'plugins/chbox/' .$this->local_skin_path(). '/columncheck.png';
    $chboxicon = html::img(array('src' => $icon, 'id' => 'selectmenulink', 'title' => $this->gettext('chbox'), 'alt' => $this->gettext('chbox')));
    $rcmail->output->add_label('chbox.chbox');
    $rcmail->output->set_env('chboxicon', $chboxicon);
    $this->include_stylesheet($this->local_skin_path(). '/chbox.css');
    $this->include_script($this->local_skin_path(). '/chbox.js');

    return $args;
  }

  function message_list($args){
    if (is_array($args['messages'])) {
      $count = count($args['messages']);
    } else {
      $count = 0;
    }

    for ($i=0;$i<$count;$i++) {
      $uid = $args['messages'][$i]->uid;
    if(!empty($uid))
      $tmp = $args['messages'][$i]->list_cols['chbox'];
      $args['messages'][$i]->list_cols['chbox'] = '<span id="msgicnrcmrowMTExOTg" class="msgicon" title=""></span>'
         . '<input type="checkbox" name="rcmselect'.$uid.'" id="rcmselect'.$uid.'" />';
    }
    return $args;
  }

  function select_menu() {
    $rcmail = rcmail::get_instance();
    $skin = $rcmail->config->get('skin');
    if ($skin == 'classic' or $skin == 'default') {
      $out .= "<div id=\"selectmenu\" class=\"popupmenu\">
        <ul>
          <li><a title=\"".rcmail::get_instance()->gettext('all')."\" href=\"#\" onclick=\"return rcmail.command('select-all','',this)\" class=\"active\">".rcmail::get_instance()->gettext('all')."</a></li>
          <li><a title=\"".rcmail::get_instance()->gettext('currpage')."\" href=\"#\" onclick=\"return rcmail.command('select-all','page',this)\" class=\"active\">".rcmail::get_instance()->gettext('currpage')."</a></li>
          <li><a title=\"".rcmail::get_instance()->gettext('unread')."\" href=\"#\" onclick=\"return rcmail.command('select-all','unread',this)\" class=\"active\">".rcmail::get_instance()->gettext('unread')."</a></li>
          <li><a title=\"".rcmail::get_instance()->gettext('invert')."\" href=\"#\" onclick=\"return rcmail.command('select-all','invert',this)\" class=\"active\">".rcmail::get_instance()->gettext('invert')."</a></li>
          <li><a title=\"".rcmail::get_instance()->gettext('none')."\" href=\"#\" onclick=\"return rcmail.command('select-none','',this)\" class=\"active\">".rcmail::get_instance()->gettext('none')."</a></li>
        </ul>";
    } else {
      $out .="<div id=\"selectmenu\" class=\"popupmenu dropdown\">
        <ul>
          <li title=\"".rcmail::get_instance()->gettext('all')."\" onclick=\"return rcmail.command('select-all','',this)\" class=\"active\">".rcmail::get_instance()->gettext('all')."</li>
          <li title=\"".rcmail::get_instance()->gettext('currpage')."\" onclick=\"return rcmail.command('select-all','page',this)\" class=\"active\">".rcmail::get_instance()->gettext('currpage')."</li>
          <li title=\"".rcmail::get_instance()->gettext('unread')."\" onclick=\"return rcmail.command('select-all','unread',this)\" class=\"active\">".rcmail::get_instance()->gettext('unread')."</li>
          <li title=\"".rcmail::get_instance()->gettext('invert')."\" onclick=\"return rcmail.command('select-all','invert',this)\" class=\"active\">".rcmail::get_instance()->gettext('invert')."</li>
          <li title=\"".rcmail::get_instance()->gettext('none')."\" onclick=\"return rcmail.command('select-none','',this)\" class=\"active\">".rcmail::get_instance()->gettext('none')."</li>
        </ul>";
    }
    $out .= "</div>";
    $rcmail->output->add_gui_object('selectmenu', 'selectmenu');
    $rcmail->output->add_footer($out);
  }
}
