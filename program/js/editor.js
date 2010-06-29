/*
 +-----------------------------------------------------------------------+
 | RoundCube editor js library                                           |
 |                                                                       |
 | This file is part of the RoundCube web development suite              |
 | Copyright (C) 2006, RoundCube Dev, - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Eric Stadtherr <estadtherr@gmail.com>                         |
 +-----------------------------------------------------------------------+

 $Id: editor.js 000 2006-05-18 19:12:28Z roundcube $
*/

// Initialize HTML editor
function rcmail_editor_init(skin_path, editor_lang, spellcheck, mode)
{
  if (mode == 'identity')
    tinyMCE.init({
      mode : 'textareas',
      editor_selector : 'mce_editor',
      apply_source_formatting : true,
      theme : 'advanced',
      language : editor_lang,
      content_css : skin_path + '/editor_content.css',
      plugins: 'paste',
      theme_advanced_toolbar_location : 'top',
      theme_advanced_toolbar_align : 'left',
      theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,separator,outdent,indent,charmap,hr,link,unlink,code,forecolor',
      theme_advanced_buttons2 : ',fontselect,fontsizeselect',
      theme_advanced_buttons3 : '',
      relative_urls : false,
      remove_script_host : false,
      gecko_spellcheck : true
    });
  else // mail compose
    tinyMCE.init({ 
      mode : 'textareas',
      editor_selector : 'mce_editor',
      accessibility_focus : false,
      apply_source_formatting : true,
      theme : 'advanced',
      language : editor_lang,
      plugins : 'paste,emotions,media,nonbreaking,table,searchreplace,visualchars,directionality' + (spellcheck ? ',spellchecker' : ''),
      theme_advanced_buttons1 : 'bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,outdent,indent,ltr,rtl,blockquote,|,forecolor,backcolor,fontselect,fontsizeselect',
      theme_advanced_buttons2 : 'link,unlink,code,|,emotions,charmap,image,media,|,search' + (spellcheck ? ',spellchecker' : '') + ',undo,redo',
      theme_advanced_buttons3 : '',
      theme_advanced_toolbar_location : 'top',
      theme_advanced_toolbar_align : 'left',
      extended_valid_elements : 'font[face|size|color|style],span[id|class|align|style]',
      content_css : skin_path + '/editor_content.css',
      external_image_list_url : 'program/js/editor_images.js',
      spellchecker_languages : (rcmail.env.spellcheck_langs ? rcmail.env.spellcheck_langs : 'Dansk=da,Deutsch=de,+English=en,Espanol=es,Francais=fr,Italiano=it,Nederlands=nl,Polski=pl,Portugues=pt,Suomi=fi,Svenska=sv'),
      spellchecker_rpc_url : '?_task=utils&_action=spell&tiny=1',
      gecko_spellcheck : true,
      relative_urls : false,
      remove_script_host : false,
      rc_client : rcmail,
      oninit : 'rcmail_editor_callback'
    });
}

// react to real individual tinyMCE editor init
function rcmail_editor_callback(editor)
{
  var editor, elem = rcube_find_object('_from');
  if (elem && elem.type=='select-one')
    rcmail.change_identity(elem);
  // set tabIndex
  rcmail_editor_tabindex();
  // set focus to element that was focused before
  if (elem = rcmail.env.compose_focus_elem) {
    if (elem.id == rcmail.env.composebody && (editor = tinyMCE.get(rcmail.env.composebody)))
      editor.getWin().focus();
    else
      elem.focus();
  }
}

// set tabIndex on tinyMCE editor
function rcmail_editor_tabindex()
{
  if (rcmail.env.task == 'mail') {
    var editor = tinyMCE.get(rcmail.env.composebody);
    if (editor) {
      var textarea = editor.getElement();
      var node = editor.getContentAreaContainer().childNodes[0];
      if (textarea && node)
        node.tabIndex = textarea.tabIndex;
    }
  }
}

// switch html/plain mode
function rcmail_toggle_editor(select, textAreaId, flagElement)
{
  var composeElement = document.getElementById(textAreaId);
  var flag, ishtml;

  if (select.tagName != 'SELECT')
    ishtml = select.checked;
  else
    ishtml = select.value == 'html';

  if (ishtml) {
    rcmail.display_spellcheck_controls(false);

    rcmail.plain2html(composeElement.value, textAreaId);
    tinyMCE.execCommand('mceAddControl', false, textAreaId);
    // #1486593
    setTimeout("rcmail_editor_tabindex();", 500);
    if (flagElement && (flag = rcube_find_object(flagElement)))
      flag.value = '1';
  }
  else {
    var thisMCE = tinyMCE.get(textAreaId);
    var existingHtml = thisMCE.getContent();

    if (existingHtml) {
      if (!confirm(rcmail.get_label('editorwarning'))) {
        if (select.tagName == 'SELECT')
	      select.value = 'html';
        return false;
	  }

      rcmail.html2plain(existingHtml, textAreaId);
    }

    tinyMCE.execCommand('mceRemoveControl', false, textAreaId);
    rcmail.display_spellcheck_controls(true);
    if (flagElement && (flag = rcube_find_object(flagElement)))
      flag.value = '0';
  }
}
