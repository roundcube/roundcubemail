<?php
/**
 * keyboard_shortcuts
 *
 * Enables some common tasks to be executed with keyboard shortcuts
 *
 * @version 1.4 - 07.07.2010
 * @author Patrik Kullman / Roland 'rosali' Liebl / Cor Bosman <roundcube@wa.ter.net>
 * @licence GNU GPL
 *
 **/
 /** *
 **/

/**
 * Shortcuts, list view:
 * ?:	Show shortcut help
 * a:	Select all visible messages
 * A:	Mark all as read (as Google Reader)
 * c:	Compose new message
 * d:	Delete message
 * f:	Forward message
 * j:	Go to previous page of messages (as Gmail)
 * k:	Go to next page of messages (as Gmail)
 * p:	Print message
 * r:	Reply to message
 * R:	Reply to all of message
 * s:	Jump to quicksearch
 * u:	Check for new mail (update)
 * z:	Move message to archive
 *
 * Shortcuts, threads view:
 * E:   Expand all
 * C:   Collapse all
 * U:   Expand Unread
 *
 * Shortcuts, mail view:
 * d:	Delete message
 * f:	Forward message
 * i:	Go to back to message list (as Gmail)
 * j:	Go to previous message (as Gmail)
 * k:	Go to next message (as Gmail)
 * p:	Print message
 * r:	Reply to message
 * R:	Reply to all of message
 * z:	Move message to archive
 */

class keyboard_shortcuts extends rcube_plugin
{
    public $task = 'mail|compose';

    function init()
    {
      // only init in authenticated state and if newuserdialog is finished
      // do not init on compose (css incompatibility with compose_addressbook plugin
      $rcmail = rcmail::get_instance();
      $this->require_plugin('jqueryui');

      if($_SESSION['username'] && empty($_SESSION['plugin.newuserdialog'])){
        $this->include_stylesheet('keyboard_shortcuts.css');
        $this->include_script('keyboard_shortcuts.js');
        $this->add_hook('template_container', array($this, 'html_output'));
        $this->add_texts('localization', true);
      }
    }

    function html_output($p) {
      if ($p['name'] == "listcontrols") {
        $rcmail = rcmail::get_instance();
        $skin  = $rcmail->config->get('skin');

        if(!file_exists('plugins/keyboard_shortcuts/skins/' . $skin . '/images/keyboard.png')){
          $skin = "default";
        }

        $this->load_config();
        $keyboard_shortcuts = $rcmail->config->get('keyboard_shortcuts_extras', array());
        $archive_supported = $rcmail->config->get('archive_mbox');

        $c = "";
        $c .= '<span id="keyboard_shortcuts_title">' . $this->gettext("title") . ":&nbsp;</span><a id='keyboard_shortcuts_link' href='#' class='button' title='".$this->gettext("keyboard_shortcuts")." ".$this->gettext("show")."' onclick='return keyboard_shortcuts_show_help()'><img align='top' src='plugins/keyboard_shortcuts/skins/".$skin."/images/keyboard.png' alt='".$this->gettext("keyboard_shortcuts")." ".$this->gettext("show")."' /></a>\n";
        $c .= "<div id='keyboard_shortcuts_help'>";
        $c .= "<div><h4>".$this->gettext("mailboxview")."</h4>";
        $c .= "<div class='shortcut_key'>?</div> ".$this->gettext('help')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>a</div> ".$this->gettext('selectallvisiblemessages')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>A</div> ".$this->gettext('markallvisiblemessagesasread')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>c</div> ".$this->gettext('compose')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>d</div> ".$this->gettext('deletemessage')."<br class='clear' />";
        if (!empty($archive_supported)) {
          $c .= "<div class='shortcut_key'>z</div> ".$this->gettext('archive.buttontitle')."<br class='clear' />";
        }
        $c .= "<div class='shortcut_key'>f</div> ".$this->gettext('forwardmessage')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>j</div> ".$this->gettext('previouspage')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>k</div> ".$this->gettext('nextpage')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>p</div> ".$this->gettext('printmessage')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>r</div> ".$this->gettext('replytomessage')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>R</div> ".$this->gettext('replytoallmessage')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>s</div> ".$this->gettext('quicksearch')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>u</div> ".$this->gettext('checkmail')."<br class='clear' />";
        $c .= "<div class='shortcut_key'> </div> <br class='clear' />";
        $c .= "</div>";

        if(!is_object($rcmail->imap)){
          $rcmail->imap_connect();
        }
        $threading_supported = $rcmail->imap->get_capability('thread=references')
          || $rcmail->imap->get_capability('thread=orderedsubject')
          || $rcmail->imap->get_capability('thread=refs');

        if ($threading_supported) {
          $c .= "<div><h4>".$this->gettext("threads")."</h4>";
          $c .= "<div class='shortcut_key'>E</div> ".$this->gettext('expand-all')."<br class='clear' />";
          $c .= "<div class='shortcut_key'>C</div> ".$this->gettext('collapse-all')."<br class='clear' />";
          $c .= "<div class='shortcut_key'>U</div> ".$this->gettext('expand-unread')."<br class='clear' />";
          $c .= "<div class='shortcut_key'> </div> <br class='clear' />";
          $c .= "</div>";
        }
        $c .= "<div><h4>".$this->gettext("messagesdisplaying")."</h4>";
        $c .= "<div class='shortcut_key'>d</div> ".$this->gettext('deletemessage')."<br class='clear' />";
        if (!empty($archive_supported)) {
          $c .= "<div class='shortcut_key'>z</div> ".$this->gettext('archive.buttontitle')."<br class='clear' />";
        }
        $c .= "<div class='shortcut_key'>c</div> ".$this->gettext('compose')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>f</div> ".$this->gettext('forwardmessage')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>i</div> ".$this->gettext('backtolist')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>j</div> ".$this->gettext('previousmessage')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>k</div> ".$this->gettext('nextmessage')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>p</div> ".$this->gettext('printmessage')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>r</div> ".$this->gettext('replytomessage')."<br class='clear' />";
        $c .= "<div class='shortcut_key'>R</div> ".$this->gettext('replytoallmessage')."<br class='clear' />";
        $c .= "<div class='shortcut_key'> </div> <br class='clear' />";
        $c .= "</div></div>";

        $rcmail->output->set_env('ks_functions', array('63' => 'ks_help'));

        $p['content'] = $c . $p['content'];
      }
      return $p;
    }
}
