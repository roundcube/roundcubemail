<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/edit_identity.inc                              |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Show edit form for a identity record or to add a new one            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

define('IDENTITIES_LEVEL', intval($RCMAIL->config->get('identities_level', 0)));

// edit-identity
if (($_GET['_iid'] || $_POST['_iid']) && $RCMAIL->action=='edit-identity') {
  $IDENTITY_RECORD = $USER->get_identity(get_input_value('_iid', RCUBE_INPUT_GPC));
  
  if (is_array($IDENTITY_RECORD))
    $OUTPUT->set_env('iid', $IDENTITY_RECORD['identity_id']);
  else {
    $OUTPUT->show_message('opnotpermitted', 'error');
    // go to identities page
    rcmail_overwrite_action('identities');
    return;
  }
}
// add-identity
else {
  if (IDENTITIES_LEVEL > 1) {
    $OUTPUT->show_message('opnotpermitted', 'error');
    // go to identities page
    rcmail_overwrite_action('identities');
    return;
  }
  else if (IDENTITIES_LEVEL == 1)
    $IDENTITY_RECORD['email'] = rcmail_get_email();
}


function rcube_identity_form($attrib)
  {
  global $IDENTITY_RECORD, $RCMAIL, $OUTPUT;

  $tinylang = substr($_SESSION['language'], 0, 2);
  if (!file_exists('program/js/tiny_mce/langs/'.$tinylang.'.js'))
    {
      $tinylang = 'en';
    }

  $OUTPUT->include_script('tiny_mce/tiny_mce.js');
  $OUTPUT->add_script("tinyMCE.init({ mode : 'textareas'," .
                                    "editor_selector : 'mce_editor'," .
                                    "apply_source_formatting : true," .
                                    "language : '$tinylang'," .
                                    "content_css : '\$__skin_path' + '/editor_content.css'," .
                                    "theme : 'advanced'," .
                                    "theme_advanced_toolbar_location : 'top'," .
                                    "theme_advanced_toolbar_align : 'left'," .
                                    "theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,separator,outdent,indent,charmap,hr,link,unlink,code,forecolor'," .
                                    "theme_advanced_buttons2 : ',fontselect,fontsizeselect'," .
                                    "theme_advanced_buttons3 : '',".
				    "gecko_spellcheck : true });");

  // add some labels to client
  $OUTPUT->add_label('noemailwarning', 'nonamewarning', 'converting', 'editorwarning');

  $i_size = !empty($attrib['size']) ? $attrib['size'] : 40;
  $t_rows = !empty($attrib['textarearows']) ? $attrib['textarearows'] : 6;
  $t_cols = !empty($attrib['textareacols']) ? $attrib['textareacols'] : 40;

  list($form_start, $form_end) = get_form_tags($attrib, 'save-identity', array('name' => '_iid', 'value' => $IDENTITY_RECORD['identity_id']));
  unset($attrib['form']);


  // list of available cols
  $a_show_cols = array('name'         => array('type' => 'text', 'size' => $i_size),
                       'email'        => array('type' => 'text', 'size' => $i_size),
                       'organization' => array('type' => 'text', 'size' => $i_size),
                       'reply-to'     => array('type' => 'text', 'label' => 'reply-to', 'size' => $i_size),
                       'bcc'          => array('type' => 'text', 'size' => $i_size),
                       'signature'	  => array('type' => 'textarea', 'size' => $t_cols, 'rows' => $t_rows),
                       'html_signature'=>array('type' => 'checkbox', 'label' => 'htmlsignature', 'onclick' => 'return rcmail.toggle_editor(this.checked, \'rcmfd_signature\');'),
                       'standard'     => array('type' => 'checkbox', 'label' => 'setdefault'));

  // disable some field according to access level
  if (IDENTITIES_LEVEL == 1 || IDENTITIES_LEVEL == 3) {
    $a_show_cols['email']['disabled'] = true;
    $a_show_cols['email']['class'] = 'disabled';
  }
  
  // a specific part is requested
  if ($attrib['part'])
    {
    $colprop = $a_show_cols[$attrib['part']];
    if (is_array($colprop))
      {
      $out = $form_start;
      $out .= rcmail_get_edit_field($attrib['part'], $IDENTITY_RECORD[$attrib['part']], $attrib, $colprop['type']); 
      return $out;
      }
    else
      return '';
    }


  // return the complete edit form as table
  $out = "$form_start<table>\n\n";

  foreach ($a_show_cols as $col => $colprop)
    {
    $colprop['id'] = 'rcmfd_'.$col;

    if ($col == 'signature')
      {
      $colprop['spellcheck'] = true;
      if ($IDENTITY_RECORD['html_signature'])
        {
        $colprop['class'] = 'mce_editor';
        }
      }

    $label = strlen($colprop['label']) ? $colprop['label'] : $col;
    $value = rcmail_get_edit_field($col, $IDENTITY_RECORD[$col], $colprop, $colprop['type']);

    $out .= sprintf("<tr><td class=\"title\"><label for=\"%s\">%s</label></td><td>%s</td></tr>\n",
                    $colprop['id'],
                    Q(rcube_label($label)),
                    $value);
    }

  $out .= "\n</table>$form_end";

  return $out;  
  }

$OUTPUT->include_script('list.js');
$OUTPUT->add_handler('identityform', 'rcube_identity_form');
$OUTPUT->set_env('identities_level', IDENTITIES_LEVEL);

$OUTPUT->set_pagetitle(rcube_label(($RCMAIL->action=='add-identity' ? 'newidentity' : 'edititem')));

if ($RCMAIL->action=='add-identity' && $OUTPUT->template_exists('addidentity'))
  $OUTPUT->send('addidentity');

$OUTPUT->send('editidentity');

?>
