<?php

/**
 * Help Plugin
 *
 * @author Aleksander 'A.L.E.C' Machniak
 * @licence GNU GPL
 *
 * Configuration (see config.inc.php.dist)
 * 
 **/

class help extends rcube_plugin
{
    function init()
    {
      $rcmail = rcmail::get_instance();
      
      if (!$rcmail->user->ID)
        return;

      $this->add_texts('localization/', false);
      
      // register actions
      $this->register_action('plugin.help', array($this, 'action'));
      $this->register_action('plugin.helpabout', array($this, 'action'));
      $this->register_action('plugin.helplicense', array($this, 'action'));

      // add taskbar button
      $this->add_button(array(
	'name' 	=> 'helptask',
	'class'	=> 'button-help',
	'label'	=> 'help.help',
	'href'	=> './?_task=dummy&_action=plugin.help',
        ), 'taskbar');

      $skin = $rcmail->config->get('skin');
      if (!file_exists($this->home."/skins/$skin/help.css"))
	$skin = 'default';

      // add style for taskbar button (must be here) and Help UI    
      $this->include_stylesheet("skins/$skin/help.css");
    }

    function action()
    {
      $rcmail = rcmail::get_instance();

      $this->load_config();

      // register UI objects
      $rcmail->output->add_handlers(array(
	    'helpcontent' => array($this, 'content'),
      ));

      if ($rcmail->action == 'plugin.helpabout')
	$rcmail->output->set_pagetitle($this->gettext('about'));
      else if ($rcmail->action == 'plugin.helplicense')
        $rcmail->output->set_pagetitle($this->gettext('license'));
      else
        $rcmail->output->set_pagetitle($this->gettext('help'));

      $rcmail->output->send('help.help');
    }
    
    function content($attrib)
    {
      $rcmail = rcmail::get_instance();

      if ($rcmail->action == 'plugin.helpabout') {
	return @file_get_contents($this->home.'/content/about.html');
      }
      else if ($rcmail->action == 'plugin.helplicense') {
	return @file_get_contents($this->home.'/content/license.html');
      }

      // default content: iframe

      if ($src = $rcmail->config->get('help_source'))
	$attrib['src'] = $src;

      if (empty($attrib['id']))
        $attrib['id'] = 'rcmailhelpcontent';
    
      // allow the following attributes to be added to the <iframe> tag
      $attrib_str = create_attrib_string($attrib, array('id', 'class', 'style', 'src', 'width', 'height', 'frameborder'));
      $framename = $attrib['id'];

      $out = sprintf('<iframe name="%s"%s></iframe>'."\n", $framename, $attrib_str);
    
      return $out;
    }
    
}

?>
