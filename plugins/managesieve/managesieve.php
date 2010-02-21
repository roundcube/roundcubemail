<?php

/**
 * Managesieve (Sieve Filters)
 *
 * Plugin that adds a possibility to manage Sieve filters in Thunderbird's style.
 * It's clickable interface which operates on text scripts and communicates
 * with server using managesieve protocol. Adds Filters tab in Settings.
 *
 * @version 2.2
 * @author Aleksander 'A.L.E.C' Machniak <alec@alec.pl>
 *
 * Configuration (see config.inc.php.dist)
 *
 * $Id$
 */

class managesieve extends rcube_plugin
{
  public $task = 'settings';

  private $rc;
  private $sieve;
  private $errors;
  private $form;
  private $script = array();
  private $exts = array();
  private $headers = array(
    'subject' => 'Subject',
    'sender' => 'From',
    'recipient' => 'To',
  );

  function init()
  {
    // add Tab label/title
    $this->add_texts('localization/', array('filters','managefilters'));

    // register actions
    $this->register_action('plugin.managesieve', array($this, 'managesieve_actions'));
    $this->register_action('plugin.managesieve-save', array($this, 'managesieve_save'));

    // include main js script
    $this->include_script('managesieve.js');
  }
  
  function managesieve_start()
  {
    $rcmail = rcmail::get_instance();
    $this->rc = &$rcmail;

    $this->load_config();

    // register UI objects
    $this->rc->output->add_handlers(array(
	'filterslist' => array($this, 'filters_list'),
	'filtersetslist' => array($this, 'filtersets_list'),
	'filterframe' => array($this, 'filter_frame'),
	'filterform' => array($this, 'filter_form'),
	'filtersetform' => array($this, 'filterset_form'),
    ));

    require_once($this->home . '/lib/Net/Sieve.php');
    require_once($this->home . '/lib/rcube_sieve.php');

    $host = str_replace('%h', $_SESSION['imap_host'], $this->rc->config->get('managesieve_host', 'localhost'));
    $port = $this->rc->config->get('managesieve_port', 2000);

    // try to connect to managesieve server and to fetch the script
    $this->sieve = new rcube_sieve($_SESSION['username'],
	$this->rc->decrypt($_SESSION['password']), 
	$host, $port,
	$this->rc->config->get('managesieve_usetls', false),
	$this->rc->config->get('managesieve_disabled_extensions'),
	$this->rc->config->get('managesieve_debug', false)
    );

    if (!($error = $this->sieve->error())) {
      
      $list = $this->sieve->get_scripts();
      $active = $this->sieve->get_active();
      $_SESSION['managesieve_active'] = $active;
      
      if (!empty($_GET['_sid'])) {
        $script_name = get_input_value('_sid', RCUBE_INPUT_GET);
      } else if (!empty($_SESSION['managesieve_current'])) {
        $script_name = $_SESSION['managesieve_current'];
      } else {
        // get active script
	if ($active) {
	  $script_name = $active;
        } else if ($list) {
          $script_name = $list[0];
        // create a new (initial) script
        } else {
          // if script not exists build default script contents
          $script_file = $this->rc->config->get('managesieve_default');
	  $script_name = 'roundcube';
          if ($script_file && is_readable($script_file))
	    $content = file_get_contents($script_file);

	  // add script and set it active    
	  if ($this->sieve->save_script($script_name, $content)) 
            if ($this->sieve->activate($script_name))
	      $_SESSION['managesieve_active'] = $script_name;
	}
      }

      if ($script_name)
        $this->sieve->load($script_name);

      $error = $this->sieve->error();
    }
    
    // finally set script objects
    if ($error)
    {
      switch ($error) {
	case SIEVE_ERROR_CONNECTION:
	case SIEVE_ERROR_LOGIN:
          $this->rc->output->show_message('managesieve.filterconnerror', 'error');  
	break;
	default:
          $this->rc->output->show_message('managesieve.filterunknownerror', 'error');
	break;
      }

      raise_error(array('code' => 403, 'type' => 'php', 'message' => "Unable to connect to managesieve on $host:$port"), true, false);

      // to disable 'Add filter' button set env variable
      $this->rc->output->set_env('filterconnerror', true);
      $this->script = array();
    }
    else
    {
      $this->script = $this->sieve->script->as_array();
      $this->exts = $this->sieve->get_extensions();
      $this->rc->output->set_env('active_set', $_SESSION['managesieve_active']);
      $_SESSION['managesieve_current'] = $this->sieve->current;
    }
    
    return $error;
  }

  function managesieve_actions()
  {
    // Init plugin and handle managesieve connection
    $error = $this->managesieve_start();

    // Handle user requests
    if ($action = get_input_value('_act', RCUBE_INPUT_GPC))
    {
      $fid = (int) get_input_value('_fid', RCUBE_INPUT_GET);

      if ($action=='up' && !$error)
      {
        if ($fid && isset($this->script[$fid]) && isset($this->script[$fid-1]))
        {
          if ($this->sieve->script->update_rule($fid, $this->script[$fid-1]) !== false
    		&& $this->sieve->script->update_rule($fid-1, $this->script[$fid]) !== false)
	    $result = $this->sieve->save();
      
          if ($result) {
//          $this->rc->output->show_message('managesieve.filtersaved', 'confirmation');
	    $this->rc->output->command('managesieve_updatelist', 'up', '', $fid);
          } else
            $this->rc->output->show_message('managesieve.filtersaveerror', 'error');
        }
      }
      else if ($action=='down' && !$error)
      {
        if (isset($this->script[$fid]) && isset($this->script[$fid+1]))
        {
          if ($this->sieve->script->update_rule($fid, $this->script[$fid+1]) !== false
    		&& $this->sieve->script->update_rule($fid+1, $this->script[$fid]) !== false)
	    $result = $this->sieve->save();
      
          if ($result === true) {
//          $this->rc->output->show_message('managesieve.filtersaved', 'confirmation');
	    $this->rc->output->command('managesieve_updatelist', 'down', '', $fid);
          } else {
            $this->rc->output->show_message('managesieve.filtersaveerror', 'error');
          }
	}
      }
      else if ($action=='delete' && !$error)
      {
        if (isset($this->script[$fid]))
        {
          if ($this->sieve->script->delete_rule($fid))
            $result = $this->sieve->save();

          if ($result === true) {
	    $this->rc->output->show_message('managesieve.filterdeleted', 'confirmation');
	    $this->rc->output->command('managesieve_updatelist', 'delete', '', $fid);
          } else {
            $this->rc->output->show_message('managesieve.filterdeleteerror', 'error');
          }
	}
      }
      else if ($action=='setact' && !$error)
      {
        $script_name = get_input_value('_set', RCUBE_INPUT_GPC);
	$result = $this->sieve->activate($script_name);
	
	if ($result === true) {
          $this->rc->output->set_env('active_set', $script_name);
	  $this->rc->output->show_message('managesieve.setactivated', 'confirmation');
	  $this->rc->output->command('enable_command', 'plugin.managesieve-setact', false);
	  $this->rc->output->command('managesieve_reset', $script_name);
	  $_SESSION['managesieve_active'] = $script_name;
	} else {
          $this->rc->output->show_message('managesieve.setactivateerror', 'error');
	}
      }
      else if ($action=='setdel' && !$error)
      {
        $script_name = get_input_value('_set', RCUBE_INPUT_GPC);
	$result = $this->sieve->remove($script_name);
	
	if ($result === true) {
	  $this->rc->output->show_message('managesieve.setdeleted', 'confirmation');
	  $this->rc->output->command('managesieve_reload');
	  rcube_sess_unset('managesieve_current');
	} else {
          $this->rc->output->show_message('managesieve.setdeleteerror', 'error');
	}
      }
      elseif ($action=='ruleadd')
      {
        $rid = get_input_value('_rid', RCUBE_INPUT_GPC);
        $id = $this->genid();
        $content = $this->rule_div($fid, $id, false);

        $this->rc->output->command('managesieve_rulefill', $content, $id, $rid);
      }
      elseif ($action=='actionadd')
      {
        $aid = get_input_value('_aid', RCUBE_INPUT_GPC);
        $id = $this->genid();
        $content = $this->action_div($fid, $id, false);
    
        $this->rc->output->command('managesieve_actionfill', $content, $id, $aid);
      }

      $this->rc->output->send();
    }

    $this->managesieve_send();
  }

  function managesieve_save()
  {
    // Init plugin and handle managesieve connection
    $error = $this->managesieve_start();

    // filters set add action
    if (!empty($_POST['_newset']))
    {
      $name = get_input_value('_name', RCUBE_INPUT_GPC);
      $copy = get_input_value('_copy', RCUBE_INPUT_GPC);

      if (!$name)
	$error = 'managesieve.emptyname';
      else if (mb_strlen($name)>128)
	$error = 'managesieve.nametoolong';
      else if (!$this->sieve->copy($name, $copy))
	$error = 'managesieve.setcreateerror';
		
      if (!$error) {
	$this->rc->output->show_message('managesieve.setcreated', 'confirmation');
	$this->rc->output->command('parent.managesieve_reload', $name);
//	rcube_sess_unset('managesieve_current');
      } else {
        $this->rc->output->show_message($error, 'error');
      }
    }
    // filter add/edit action
    else if (isset($_POST['_name']))
    {
      $name = trim(get_input_value('_name', RCUBE_INPUT_POST, true));
      $fid = trim(get_input_value('_fid', RCUBE_INPUT_POST));
      $join = trim(get_input_value('_join', RCUBE_INPUT_POST));
  
      // and arrays
      $headers = $_POST['_header'];
      $cust_headers = $_POST['_custom_header'];
      $ops = $_POST['_rule_op'];
      $sizeops = $_POST['_rule_size_op'];
      $sizeitems = $_POST['_rule_size_item'];
      $sizetargets = $_POST['_rule_size_target'];
      $targets = $_POST['_rule_target'];
      $act_types = $_POST['_action_type'];
      $mailboxes = $_POST['_action_mailbox'];
      $act_targets = $_POST['_action_target'];
      $area_targets = $_POST['_action_target_area'];
      $reasons = $_POST['_action_reason'];
      $addresses = $_POST['_action_addresses'];
      $days = $_POST['_action_days'];

      // we need a "hack" for radiobuttons
      foreach ($sizeitems as $item)
	$items[] = $item;

      $this->form['join'] = $join=='allof' ? true : false;
      $this->form['name'] = $name;
      $this->form['tests'] = array();
      $this->form['actions'] = array();

      if ($name == '')
	$this->errors['name'] = $this->gettext('cannotbeempty');
      else
	foreach($this->script as $idx => $rule)
          if($rule['name'] == $name && $idx != $fid) {
	    $this->errors['name'] = $this->gettext('ruleexist');
    	      break;
          }
      
      $i = 0;
      // rules
      if ($join == 'any')
      {
	$this->form['tests'][0]['test'] = 'true';
      }
      else foreach($headers as $idx => $header)
      {
	$header = $this->strip_value($header);
	$target = $this->strip_value($targets[$idx], true);
	$op = $this->strip_value($ops[$idx]);

	// normal header
	if (in_array($header, $this->headers))
	{
          if(preg_match('/^not/', $op))
    	    $this->form['tests'][$i]['not'] = true;
          $type = preg_replace('/^not/', '', $op);

          if ($type == 'exists')
    	  {
	    $this->form['tests'][$i]['test'] = 'exists';
    	    $this->form['tests'][$i]['arg'] = $header;
	  }
          else
          {	
	    $this->form['tests'][$i]['type'] = $type;
    	    $this->form['tests'][$i]['test'] = 'header';
    	    $this->form['tests'][$i]['arg1'] = $header;
    	    $this->form['tests'][$i]['arg2'] = $target;

    	    if ($target == '')
              $this->errors['tests'][$i]['target'] = $this->gettext('cannotbeempty');
	  }
	}
	else
          switch ($header)
          {
    	    case 'size':
	      $sizeop = $this->strip_value($sizeops[$idx]);
	      $sizeitem = $this->strip_value($items[$idx]);
	      $sizetarget = $this->strip_value($sizetargets[$idx]);

              $this->form['tests'][$i]['test'] = 'size';
              $this->form['tests'][$i]['type'] = $sizeop;
              $this->form['tests'][$i]['arg'] = $sizetarget.$sizeitem;

	      if (!preg_match('/^[0-9]+(K|M|G)*$/i', $sizetarget))
		$this->errors['tests'][$i]['sizetarget'] = $this->gettext('wrongformat');
	      break;
	    case '...':
              $cust_header = $headers = $this->strip_value($cust_headers[$idx]);

              if(preg_match('/^not/', $op))
		$this->form['tests'][$i]['not'] = true;
    	      $type = preg_replace('/^not/', '', $op);

              if ($cust_header == '')
    		$this->errors['tests'][$i]['header'] = $this->gettext('cannotbeempty');
              else {
	        $headers = preg_split('/[\s,]+/', $cust_header, -1, PREG_SPLIT_NO_EMPTY);
	        
		if (!count($headers))
    		  $this->errors['tests'][$i]['header'] = $this->gettext('cannotbeempty');
		else {
		  foreach ($headers as $hr)
		    if (!preg_match('/^[a-z0-9-]+$/i', $hr))
    		      $this->errors['tests'][$i]['header'] = $this->gettext('forbiddenchars');
		}
	      }
	      
	      if (empty($this->errors['tests'][$i]['header']))
		$cust_header = (is_array($headers) && count($headers) == 1) ? $headers[0] : $headers;
              
	      if ($type == 'exists')
    	      {
		$this->form['tests'][$i]['test'] = 'exists';
    		$this->form['tests'][$i]['arg'] = $cust_header;
	      }
              else
    	      {	
    		$this->form['tests'][$i]['test'] = 'header';
		$this->form['tests'][$i]['type'] = $type;
        	$this->form['tests'][$i]['arg1'] = $cust_header;
    		$this->form['tests'][$i]['arg2'] = $target;

        	if ($target == '')
    	          $this->errors['tests'][$i]['target'] = $this->gettext('cannotbeempty');
	      }
	    break;
          }
	$i++;
      }
  
      $i = 0;
      // actions
      foreach($act_types as $idx => $type)
      {
	$type = $this->strip_value($type);
	$target = $this->strip_value($act_targets[$idx]);
  
	$this->form['actions'][$i]['type'] = $type;
    
	switch ($type)
	{
          case 'fileinto':
	    $mailbox = $this->strip_value($mailboxes[$idx]);
	    $this->form['actions'][$i]['target'] = $mailbox;
          break;
          case 'reject':
          case 'ereject':
	    $target = $this->strip_value($area_targets[$idx]);
	    $this->form['actions'][$i]['target'] = str_replace("\r\n", "\n", $target);

 //         if ($target == '')
//    	  	$this->errors['actions'][$i]['targetarea'] = $this->gettext('cannotbeempty');
          break;
          case 'redirect':
	    $this->form['actions'][$i]['target'] = $target;

    	    if ($this->form['actions'][$i]['target'] == '')
    	      $this->errors['actions'][$i]['target'] = $this->gettext('cannotbeempty');
    	    else if (!$this->check_email($this->form['actions'][$i]['target']))
    	      $this->errors['actions'][$i]['target'] = $this->gettext('noemailwarning');
    	  break;
          case 'vacation':
    	    $reason = $this->strip_value($reasons[$idx]);
    	    $this->form['actions'][$i]['reason'] = str_replace("\r\n", "\n", $reason);
	    $this->form['actions'][$i]['days'] = $days[$idx];
	    $this->form['actions'][$i]['addresses'] = explode(',', $addresses[$idx]);
// @TODO: vacation :subject, :mime, :from, :handle

	    if ($this->form['actions'][$i]['addresses']) {
	      foreach($this->form['actions'][$i]['addresses'] as $aidx => $address) {
		$address = trim($address);
		if (!$address)
	          unset($this->form['actions'][$i]['addresses'][$aidx]);
		else if(!$this->check_email($address)) {
	          $this->errors['actions'][$i]['addresses'] = $this->gettext('noemailwarning');
	          break;
		} else
	          $this->form['actions'][$i]['addresses'][$aidx] = $address;
	      }
	    }
        
	    if ($this->form['actions'][$i]['reason'] == '')
    	      $this->errors['actions'][$i]['reason'] = $this->gettext('cannotbeempty');
    	    if ($this->form['actions'][$i]['days'] && !preg_match('/^[0-9]+$/', $this->form['actions'][$i]['days']))
    	      $this->errors['actions'][$i]['days'] = $this->gettext('forbiddenchars');
          break;
	}
  
        $i++;
      }

      if (!$this->errors)
      {
        // zapis skryptu
        if (!isset($this->script[$fid])) {
	  $fid = $this->sieve->script->add_rule($this->form);
          $new = true;
	} else
          $fid = $this->sieve->script->update_rule($fid, $this->form);

	if ($fid !== false)
          $save = $this->sieve->save();

	if ($save && $fid !== false)
	{
	  $this->rc->output->show_message('managesieve.filtersaved', 'confirmation');
	  $this->rc->output->add_script(sprintf("rcmail.managesieve_updatelist('%s', '%s', %d);",
	    isset($new) ? 'add' : 'update', Q($this->form['name']), $fid), 'foot');
	}
	else
	{
	  $this->rc->output->show_message('managesieve.filtersaveerror', 'error');
//	  $this->rc->output->send();
	}
      }
    }

    $this->managesieve_send();
  }

  private function managesieve_send()
  {
    // Handle form action 
    if (isset($_GET['_framed']) || isset($_POST['_framed'])) {
      if (isset($_GET['_newset']) || isset($_POST['_newset']))
        $this->rc->output->send('managesieve.setedit');
      else
        $this->rc->output->send('managesieve.filteredit');
    } else {
      $this->rc->output->set_pagetitle($this->gettext('filters'));
      $this->rc->output->send('managesieve.managesieve');
    }
  }
  
  // return the filters list as HTML table
  function filters_list($attrib)
  {
    // add id to message list table if not specified
    if (!strlen($attrib['id']))
      $attrib['id'] = 'rcmfilterslist';
  
    // define list of cols to be displayed
    $a_show_cols = array('managesieve.filtername');

    foreach($this->script as $idx => $filter)
      $result[] = array('managesieve.filtername' => $filter['name'], 'id' => $idx);
    
    // create XHTML table
    $out = rcube_table_output($attrib, $result, $a_show_cols, 'id');

    // set client env
    $this->rc->output->add_gui_object('filterslist', $attrib['id']);
    $this->rc->output->include_script('list.js');
  
    // add some labels to client
    $this->rc->output->add_label('managesieve.filterdeleteconfirm');
  
    return $out;
  }

  // return the filters list as <SELECT>
  function filtersets_list($attrib)
  {
    // add id to message list table if not specified
    if (!strlen($attrib['id']))
      $attrib['id'] = 'rcmfiltersetslist';

    $list = $this->sieve->get_scripts();
    $active = $this->sieve->get_active();
  
    $select = new html_select(array('name' => '_set', 'id' => $attrib['id'], 'onchange' => 'rcmail.managesieve_set()'));

    if ($list) {
      asort($list, SORT_LOCALE_STRING);

      foreach($list as $set)
        $select->add($set . ($set == $active ? ' ('.$this->gettext('active').')' : ''), $set);
    }
    
    $out = $select->show($this->sieve->current);
    
    // set client env
    $this->rc->output->add_gui_object('filtersetslist', $attrib['id']);
    $this->rc->output->add_label('managesieve.setdeleteconfirm');
    $this->rc->output->add_label('managesieve.active');
  
    return $out;
  }

  function filter_frame($attrib)
  {
    if (!$attrib['id'])
      $attrib['id'] = 'rcmfilterframe';
    
    $attrib['name'] = $attrib['id'];

    $this->rc->output->set_env('contentframe', $attrib['name']);
    $this->rc->output->set_env('blankpage', $attrib['src'] ? 
    $this->rc->output->abs_url($attrib['src']) : 'program/blank.gif');

    return html::tag('iframe', $attrib);
  }


  function filterset_form($attrib)
  {
    if (!$attrib['id'])
      $attrib['id'] = 'rcmfiltersetform';

    $out = '<form name="filtersetform" action="./" method="post">'."\n";

    $hiddenfields = new html_hiddenfield(array('name' => '_task', 'value' => $this->rc->task));
    $hiddenfields->add(array('name' => '_action', 'value' => 'plugin.managesieve-save'));
    $hiddenfields->add(array('name' => '_framed', 'value' => ($_POST['_framed'] || $_GET['_framed'] ? 1 : 0)));
    $hiddenfields->add(array('name' => '_newset', 'value' => 1));

    $out .= $hiddenfields->show();

    $name = get_input_value('_name', RCUBE_INPUT_GPC);
    $copy = get_input_value('_copy', RCUBE_INPUT_GPC);

    $table = new html_table(array('cols' => 2));

    // filter set name input
    $input_name = new html_inputfield(array('name' => '_name', 'id' => '_name', 'size' => 30,
	'class' => ($this->errors['name'] ? 'error' : '')));

    $table->add('title', sprintf('<label for="%s"><b>%s:</b></label>', '_name', Q($this->gettext('filtersetname'))));
    $table->add(null, $input_name->show($name));

    // filters set list
    $list = $this->sieve->get_scripts();
    $active = $this->sieve->get_active();
  
    $select = new html_select(array('name' => '_copy', 'id' => '_copy'));

    asort($list, SORT_LOCALE_STRING);

    $select->add($this->gettext('none'), '');
    foreach($list as $set)
      $select->add($set . ($set == $active ? ' ('.$this->gettext('active').')' : ''), $set);
    
    $table->add('title', '<label>'.$this->gettext('copyfromset').':</label>');
    $table->add(null, $select->show($copy));

    $out .= $table->show();
    
    $this->rc->output->add_gui_object('sieveform', 'filtersetform');

    return $out;
  }


  function filter_form($attrib)
  {
    if (!$attrib['id'])
      $attrib['id'] = 'rcmfilterform';

    $fid = get_input_value('_fid', RCUBE_INPUT_GPC);
    $scr = isset($this->form) ? $this->form : $this->script[$fid];

    $hiddenfields = new html_hiddenfield(array('name' => '_task', 'value' => $this->rc->task));
    $hiddenfields->add(array('name' => '_action', 'value' => 'plugin.managesieve-save'));
    $hiddenfields->add(array('name' => '_framed', 'value' => ($_POST['_framed'] || $_GET['_framed'] ? 1 : 0)));
    $hiddenfields->add(array('name' => '_fid', 'value' => $fid));

    $out = '<form name="filterform" action="./" method="post">'."\n";
    $out .= $hiddenfields->show();

    // 'any' flag 
    if (sizeof($scr['tests']) == 1 && $scr['tests'][0]['test'] == 'true' && !$scr['tests'][0]['not'])
      $any = true; 

    // filter name input
    $field_id = '_name';
    $input_name = new html_inputfield(array('name' => '_name', 'id' => $field_id, 'size' => 30,
	'class' => ($this->errors['name'] ? 'error' : '')));

    if (isset($scr))
      $input_name = $input_name->show($scr['name']);
    else
      $input_name = $input_name->show();

    $out .= sprintf("\n<label for=\"%s\"><b>%s:</b></label> %s<br /><br />\n",
        	$field_id, Q($this->gettext('filtername')), $input_name);

    $out .= '<fieldset><legend>' . Q($this->gettext('messagesrules')) . "</legend>\n";

    // any, allof, anyof radio buttons
    $field_id = '_allof';
    $input_join = new html_radiobutton(array('name' => '_join', 'id' => $field_id, 'value' => 'allof',
	'onclick' => 'rule_join_radio(\'allof\')', 'class' => 'radio'));

    if (isset($scr) && !$any)
      $input_join = $input_join->show($scr['join'] ? 'allof' : '');
    else
      $input_join = $input_join->show();

    $out .= sprintf("%s<label for=\"%s\">%s</label>&nbsp;\n",
        	$input_join, $field_id, Q($this->gettext('filterallof')));

    $field_id = '_anyof';
    $input_join = new html_radiobutton(array('name' => '_join', 'id' => $field_id, 'value' => 'anyof',
	'onclick' => 'rule_join_radio(\'anyof\')', 'class' => 'radio'));

    if (isset($scr) && !$any)
      $input_join = $input_join->show($scr['join'] ? '' : 'anyof');
    else
      $input_join = $input_join->show('anyof'); // default

    $out .= sprintf("%s<label for=\"%s\">%s</label>\n",
        	$input_join, $field_id, Q($this->gettext('filteranyof')));

    $field_id = '_any';
    $input_join = new html_radiobutton(array('name' => '_join', 'id' => $field_id, 'value' => 'any',
  	'onclick' => 'rule_join_radio(\'any\')', 'class' => 'radio'));

    $input_join = $input_join->show($any ? 'any' : '');

    $out .= sprintf("%s<label for=\"%s\">%s</label>\n",
        	$input_join, $field_id, Q($this->gettext('filterany')));

    $rows_num = isset($scr) ? sizeof($scr['tests']) : 1;

    $out .= '<div id="rules"'.($any ? ' style="display: none"' : '').'>';
    for ($x=0; $x<$rows_num; $x++)
      $out .= $this->rule_div($fid, $x);
    $out .= "</div>\n";

    $out .= "</fieldset>\n";

    // actions
    $out .= '<fieldset><legend>' . Q($this->gettext('messagesactions')) . "</legend>\n";

    $rows_num = isset($scr) ? sizeof($scr['actions']) : 1;

    $out .= '<div id="actions">';
    for ($x=0; $x<$rows_num; $x++)
      $out .= $this->action_div($fid, $x);
    $out .= "</div>\n";

    $out .= "</fieldset>\n";

    $this->rc->output->add_label('managesieve.ruledeleteconfirm');
    $this->rc->output->add_label('managesieve.actiondeleteconfirm');
    $this->rc->output->add_gui_object('sieveform', 'filterform');

    return $out;
  }

  function rule_div($fid, $id, $div=true)
  {
    $rule = isset($this->form) ? $this->form['tests'][$id] : $this->script[$fid]['tests'][$id];
    $rows_num = isset($this->form) ? sizeof($this->form['tests']) : sizeof($this->script[$fid]['tests']);
  
    $out = $div ? '<div class="rulerow" id="rulerow' .$id .'">'."\n" : '';

    $out .= '<table><tr><td class="rowactions">';

    // headers select
    $select_header = new html_select(array('name' => "_header[]", 'id' => 'header'.$id,
	'onchange' => 'header_select(' .$id .')'));
    foreach($this->headers as $name => $val)
      $select_header->add(Q($this->gettext($name)), Q($val));
    $select_header->add(Q($this->gettext('size')), 'size');
    $select_header->add(Q($this->gettext('...')), '...');

    // TODO: list arguments

    if ((isset($rule['test']) && $rule['test'] == 'header')
	&& !is_array($rule['arg1']) && in_array($rule['arg1'], $this->headers))
      $out .= $select_header->show($rule['arg1']);
    elseif ((isset($rule['test']) && $rule['test'] == 'exists')
	&& !is_array($rule['arg']) && in_array($rule['arg'], $this->headers))
      $out .= $select_header->show($rule['arg']);
    elseif (isset($rule['test']) && $rule['test'] == 'size')
      $out .= $select_header->show('size');
    elseif (isset($rule['test']) && $rule['test'] != 'true')
      $out .= $select_header->show('...');
    else
      $out .= $select_header->show();

    $out .= '</td><td class="rowtargets">';

    if ((isset($rule['test']) && $rule['test'] == 'header')
	&& (is_array($rule['arg1']) || !in_array($rule['arg1'], $this->headers)))
      $custom = is_array($rule['arg1']) ? implode(', ', $rule['arg1']) : $rule['arg1'];
    elseif ((isset($rule['test']) && $rule['test'] == 'exists')
	&& (is_array($rule['arg']) || !in_array($rule['arg'], $this->headers)))
      $custom = is_array($rule['arg']) ? implode(', ', $rule['arg']) : $rule['arg'];
    
    $out .= '<div id="custom_header' .$id. '" style="display:' .(isset($custom) ? 'inline' : 'none'). '">
	<input type="text" name="_custom_header[]" '. $this->error_class($id, 'test', 'header')
	.' value="' .Q($custom). '" size="20" />&nbsp;</div>' . "\n";
  
    // matching type select (operator)
    $select_op = new html_select(array('name' => "_rule_op[]", 'id' => 'rule_op'.$id, 
	'style' => 'display:' .($rule['test']!='size' ? 'inline' : 'none'), 'onchange' => 'rule_op_select('.$id.')'));
    $select_op->add(Q($this->gettext('filtercontains')), 'contains');
    $select_op->add(Q($this->gettext('filternotcontains')), 'notcontains');
    $select_op->add(Q($this->gettext('filteris')), 'is');
    $select_op->add(Q($this->gettext('filterisnot')), 'notis');
    $select_op->add(Q($this->gettext('filterexists')), 'exists');
    $select_op->add(Q($this->gettext('filternotexists')), 'notexists');
//    $select_op->add(Q($this->gettext('filtermatches')), 'matches');
//    $select_op->add(Q($this->gettext('filternotmatches')), 'notmatches');

    // target input (TODO: lists)

    if ($rule['test'] == 'header')
    {
      $out .= $select_op->show(($rule['not'] ? 'not' : '').$rule['type']);
      $target = $rule['arg2'];
    }
    elseif ($rule['test'] == 'size')
    {
      $out .= $select_op->show();
      if(preg_match('/^([0-9]+)(K|M|G)*$/', $rule['arg'], $matches))
      {
	$sizetarget = $matches[1];
	$sizeitem = $matches[2];
      }
    }
    else
    {
      $out .= $select_op->show(($rule['not'] ? 'not' : '').$rule['test']);
      $target = '';
    }

    $out .= '<input type="text" name="_rule_target[]" id="rule_target' .$id. '" 
	value="' .Q($target). '" size="20" ' . $this->error_class($id, 'test', 'target') 
	. ' style="display:' . ($rule['test']!='size' && $rule['test'] != 'exists' ? 'inline' : 'none') . '" />'."\n";

    $select_size_op = new html_select(array('name' => "_rule_size_op[]", 'id' => 'rule_size_op'.$id));
    $select_size_op->add(Q($this->gettext('filterunder')), 'under');
    $select_size_op->add(Q($this->gettext('filterover')), 'over');

    $out .= '<div id="rule_size' .$id. '" style="display:' . ($rule['test']=='size' ? 'inline' : 'none') .'">';
    $out .= $select_size_op->show($rule['test']=='size' ? $rule['type'] : '');
    $out .= '<input type="text" name="_rule_size_target[]" value="'.$sizetarget.'" size="10" ' . $this->error_class($id, 'test', 'sizetarget') .' />
	<input type="radio" name="_rule_size_item['.$id.']" value=""'. (!$sizeitem ? ' checked="checked"' : '') .' class="radio" />B
	<input type="radio" name="_rule_size_item['.$id.']" value="K"'. ($sizeitem=='K' ? ' checked="checked"' : '') .' class="radio" />kB
	<input type="radio" name="_rule_size_item['.$id.']" value="M"'. ($sizeitem=='M' ? ' checked="checked"' : '') .' class="radio" />MB
	<input type="radio" name="_rule_size_item['.$id.']" value="G"'. ($sizeitem=='G' ? ' checked="checked"' : '') .' class="radio" />GB';
    $out .= '</div>';
    $out .= '</td>';
  
    // add/del buttons
    $out .= '<td class="rowbuttons">';
    $out .= '<input type="button" id="ruleadd' . $id .'" value="'. Q($this->gettext('add')). '" 
	onclick="rcmail.managesieve_ruleadd(' . $id .')" class="button" /> ';
    $out .= '<input type="button" id="ruledel' . $id .'" value="'. Q($this->gettext('del')). '"
	onclick="rcmail.managesieve_ruledel(' . $id .')" class="button' . ($rows_num<2 ? ' disabled' : '') .'"'
	. ($rows_num<2 ? ' disabled="disabled"' : '') .' />';
    $out .= '</td></tr></table>';

    $out .= $div ? "</div>\n" : '';
        
    return $out;
  }

  function action_div($fid, $id, $div=true)
  {
    $action = isset($this->form) ? $this->form['actions'][$id] : $this->script[$fid]['actions'][$id];
    $rows_num = isset($this->form) ? sizeof($this->form['actions']) : sizeof($this->script[$fid]['actions']);

    $out = $div ? '<div class="actionrow" id="actionrow' .$id .'">'."\n" : '';

    $out .= '<table><tr><td class="rowactions">';

    // action select
    $select_action = new html_select(array('name' => "_action_type[]", 'id' => 'action_type'.$id,
	'onchange' => 'action_type_select(' .$id .')'));
    if (in_array('fileinto', $this->exts))
      $select_action->add(Q($this->gettext('messagemoveto')), 'fileinto');
    $select_action->add(Q($this->gettext('messageredirect')), 'redirect');
    if (in_array('reject', $this->exts))
      $select_action->add(Q($this->gettext('messagediscard')), 'reject');
    elseif (in_array('ereject', $this->exts))
      $select_action->add(Q($this->gettext('messagediscard')), 'ereject');
    if (in_array('vacation', $this->exts))
      $select_action->add(Q($this->gettext('messagereply')), 'vacation');
    $select_action->add(Q($this->gettext('messagedelete')), 'discard');
    $select_action->add(Q($this->gettext('rulestop')), 'stop');

    $out .= $select_action->show($action['type']);
    $out .= '</td>';

    // actions target inputs
    $out .= '<td class="rowtargets">';
    // shared targets
    $out .= '<input type="text" name="_action_target[]" id="action_target' .$id. '" '
	.'value="' .($action['type']=='redirect' ? Q($action['target'], 'strict', false) : ''). '" size="40" '
	.'style="display:' .($action['type']=='redirect' ? 'inline' : 'none') .'" '
	. $this->error_class($id, 'action', 'target') .' />';
    $out .= '<textarea name="_action_target_area[]" id="action_target_area' .$id. '" '
	.'rows="3" cols="40" '. $this->error_class($id, 'action', 'targetarea')
	.'style="display:' .(in_array($action['type'], array('reject', 'ereject')) ? 'inline' : 'none') .'">'
	. (in_array($action['type'], array('reject', 'ereject')) ? Q($action['target'], 'strict', false) : '')
	. "</textarea>\n";

    // vacation
    $out .= '<div id="action_vacation' .$id.'" style="display:' .($action['type']=='vacation' ? 'inline' : 'none') .'">';
    $out .= '<span class="label">'. Q($this->gettext('vacationreason')) .'</span><br />'
	.'<textarea name="_action_reason[]" id="action_reason' .$id. '" '
	.'rows="3" cols="40" '. $this->error_class($id, 'action', 'reason') . '>'
	. Q($action['reason'], 'strict', false) . "</textarea>\n";
    $out .= '<br /><span class="label">' .Q($this->gettext('vacationaddresses')) . '</span><br />'
	.'<input type="text" name="_action_addresses[]" '
        .'value="' . (is_array($action['addresses']) ? Q(implode(', ', $action['addresses']), 'strict', false) : $action['addresses']) . '" size="40" '
        . $this->error_class($id, 'action', 'addresses') .' />';
    $out .= '<br /><span class="label">' . Q($this->gettext('vacationdays')) . '</span><br />'
	.'<input type="text" name="_action_days[]" '
        .'value="' .Q($action['days'], 'strict', false) . '" size="2" '
        . $this->error_class($id, 'action', 'days') .' />';
    $out .= '</div>';

    // mailbox select
    $out .= '<select id="action_mailbox' .$id. '" name="_action_mailbox[]" style="display:' 
	.(!isset($action) || $action['type']=='fileinto' ? 'inline' : 'none'). '">';

    $this->rc->imap_connect();

    $a_folders = $this->rc->imap->list_mailboxes();
    $delimiter = $this->rc->imap->get_hierarchy_delimiter();

    // set mbox encoding
    $mbox_encoding = $this->rc->config->get('managesieve_mbox_encoding', 'UTF7-IMAP'); 

    if ($action['type'] == 'fileinto')
      $mailbox = $action['target'];
    else
      $mailbox = '';

    foreach ($a_folders as $folder)
    {
      $utf7folder = $this->rc->imap->mod_mailbox($folder);
      $names = explode($delimiter, rcube_charset_convert($folder, 'UTF7-IMAP'));
      $name = $names[sizeof($names)-1];
    
      if ($replace_delimiter = $this->rc->config->get('managesieve_replace_delimiter'))
        $utf7folder = str_replace($delimiter, $replace_delimiter, $utf7folder);
    
      // convert to Sieve implementation encoding
      $utf7folder = $this->mbox_encode($utf7folder, $mbox_encoding);
    
      if ($folder_class = rcmail_folder_classname($name))
        $foldername = $this->gettext($folder_class);
      else
        $foldername = $name;

      $out .= sprintf('<option value="%s"%s>%s%s</option>'."\n",
                    htmlspecialchars($utf7folder),
		    ($mailbox == $utf7folder ? ' selected="selected"' : ''),
		    str_repeat('&nbsp;', 4 * (sizeof($names)-1)),
		    Q(abbreviate_string($foldername, 40 - (2 * sizeof($names)-1))));
    }
    $out .= '</select>';
    $out .= '</td>';

    // add/del buttons
    $out .= '<td class="rowbuttons">';
    $out .= '<input type="button" id="actionadd' . $id .'" value="'. Q($this->gettext('add')). '" 
	onclick="rcmail.managesieve_actionadd(' . $id .')" class="button" /> ';
    $out .= '<input type="button" id="actiondel' . $id .'" value="'. Q($this->gettext('del')). '"
        onclick="rcmail.managesieve_actiondel(' . $id .')" class="button' . ($rows_num<2 ? ' disabled' : '') .'"'
	. ($rows_num<2 ? ' disabled="disabled"' : '') .' />';
    $out .= '</td>';
  
    $out .= '</tr></table>';

    $out .= $div ? "</div>\n" : '';

    return $out;
  }

  private function genid()
  {
    $result = intval(rcube_timer());
    return $result;
  }

  private function strip_value($str, $allow_html=false)
  {
    if (!$allow_html)
      $str = strip_tags($str);
       
    return trim($str);
  }

  private function error_class($id, $type, $target, $name_only=false)
  {
    // TODO: tooltips
    if ($type == 'test' && isset($this->errors['tests'][$id][$target]))
      return ($name_only ? 'error' : ' class="error"');
    elseif ($type == 'action' && isset($this->errors['actions'][$id][$target]))
      return ($name_only ? 'error' : ' class="error"');

    return '';
  }

  private function check_email($email)
  {
    if (function_exists('check_email')); 
      return check_email($email);

    // Check for invalid characters
    if (preg_match('/[\x00-\x1F\x7F-\xFF]/', $email))
      return false;

    // Check that there's one @ symbol, and that the lengths are right
    if (!preg_match('/^[^@]{1,64}@[^@]{1,255}$/', $email))
      return false;

    // Split it into sections to make life easier
    $email_array = explode('@', $email);

    // Check local part
    $local_array = explode('.', $email_array[0]);
    foreach ($local_array as $local_part)
      if (!preg_match('/^(([A-Za-z0-9!#$%&\'*+\/=?^_`{|}~-]+)|("[^"]+"))$/', $local_part))
        return false;

    // Check domain part
    if (preg_match('/^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}$/', $email_array[1]) 
      || preg_match('/^\[(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}\]$/', $email_array[1]))
      return true; // If an IP address
    else
    { // If not an IP address
      $domain_array = explode('.', $email_array[1]);
      if (sizeof($domain_array) < 2)
        return false; // Not enough parts to be a valid domain

      foreach ($domain_array as $domain_part)
        if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]))$/', $domain_part))
	  return false;

      return true;
    }
  
    return false;
  }
 
  private function mbox_encode($text, $encoding)
  {
    return rcube_charset_convert($text, 'UTF7-IMAP', $encoding);
  }
}

?>
