<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/edit_prefs.inc                                 |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2007, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide functionality for user's settings & preferences             |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id: func.inc 2822 2009-07-31 09:07:54Z alec $

*/

if (!$OUTPUT->ajax_call)
  $OUTPUT->set_pagetitle(rcube_label('preferences'));


$CURR_SECTION = get_input_value('_section', RCUBE_INPUT_GPC);
list($SECTIONS,) = rcmail_user_prefs($CURR_SECTION);

function rcmail_user_prefs_form($attrib)
{
  global $RCMAIL, $CURR_SECTION, $SECTIONS;

  // add some labels to client
  $RCMAIL->output->add_label('nopagesizewarning');

  unset($attrib['form']);

  list($form_start, $form_end) = get_form_tags($attrib, 'save-prefs', null,
    array('name' => '_section', 'value' => $CURR_SECTION));

  $out = $form_start;

  foreach ($SECTIONS[$CURR_SECTION]['blocks'] as $idx => $block) {
    if (!empty($block['options'])) {
      $table = new html_table(array('cols' => 2));

      foreach ($block['options'] as $option) {
        if ($option['advanced'])
	      $table->set_row_attribs('advanced');

        $table->add('title', $option['title']);
	    $table->add(null, $option['content']);
      }

      $out .= html::tag('fieldset', null, html::tag('legend', null, $block['name']) . $table->show($attrib));
    }
    else if (!empty($block['content'])) {
      $out .= html::tag('fieldset', null, html::tag('legend', null, $block['name']) . $block['content']);
    }
  }

  return $out . $form_end;
}

function rcmail_prefs_section_name()
{
  global $SECTIONS, $CURR_SECTION;
  
  return $SECTIONS[$CURR_SECTION]['section'];
}


// register UI objects
$OUTPUT->add_handlers(array(
  'userprefs' => 'rcmail_user_prefs_form',
  'sectionname' => 'rcmail_prefs_section_name',
));

$OUTPUT->send('settingsedit');


