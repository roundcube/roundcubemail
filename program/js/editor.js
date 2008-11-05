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
    tinyMCE.init({ mode : 'textareas',
      editor_selector : 'mce_editor',
      apply_source_formatting : true,
      theme : 'advanced',
      language : editor_lang,
      content_css : skin_path + '/editor_content.css',
      theme_advanced_toolbar_location : 'top',
      theme_advanced_toolbar_align : 'left',
      theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,separator,outdent,indent,charmap,hr,link,unlink,code,forecolor',
      theme_advanced_buttons2 : ',fontselect,fontsizeselect',
      theme_advanced_buttons3 : '',
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
      plugins : 'emotions,media,nonbreaking,table,searchreplace,visualchars,directionality' + (spellcheck ? ',spellchecker' : ''),
      theme_advanced_buttons1 : 'bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,outdent,indent,separator,link,unlink,emotions,charmap,code,forecolor,backcolor,fontselect,fontsizeselect, separator' + (spellcheck ? ',spellchecker' : '') + ',undo,redo,image,media,ltr,rtl',
      theme_advanced_buttons2 : '',
      theme_advanced_buttons3 : '',
      theme_advanced_toolbar_location : 'top',
      theme_advanced_toolbar_align : 'left',
      extended_valid_elements : 'font[face|size|color|style],span[id|class|align|style]',
      content_css : skin_path + '/editor_content.css',
      external_image_list_url : 'program/js/editor_images.js',
      spellchecker_languages : (rcmail.env.spellcheck_langs ? rcmail.env.spellcheck_langs : 'Dansk=da,Deutsch=de,+English=en,Espanol=es,Francais=fr,Italiano=it,Nederlands=nl,Polski=pl,Portugues=pt,Suomi=fi,Svenska=sv'),
      gecko_spellcheck : true,
      rc_client: rcube_webmail_client,
      oninit : 'rcmail_editor_callback'
    });
}

// react to real individual tinyMCE editor init
function rcmail_editor_callback(editor)
{
  var input_from = rcube_find_object('_from');
  if(input_from && input_from.type=='select-one')
    rcmail.change_identity(input_from);
}

// switch html/plain mode
function rcmail_toggle_editor(ishtml, textAreaId, flagElement)
{
  var composeElement = document.getElementById(textAreaId);
  var flag;

  if (ishtml)
    {
    var existingPlainText = composeElement.value;
    var htmlText = "<pre>" + existingPlainText + "</pre>";

    rcmail.display_spellcheck_controls(false);
    composeElement.value = htmlText;
    tinyMCE.execCommand('mceAddControl', true, textAreaId);
    if (flagElement && (flag = rcube_find_object(flagElement)))
      flag.value = '1';
    }
  else
    {
    if (!confirm(rcmail.get_label('editorwarning')))
      return false;

    var thisMCE = tinyMCE.get(textAreaId);
    var existingHtml = thisMCE.getContent();
    rcmail.html2plain(existingHtml, textAreaId);
    tinyMCE.execCommand('mceRemoveControl', true, textAreaId);
    rcmail.display_spellcheck_controls(true);
    if (flagElement && (flag = rcube_find_object(flagElement)))
      flag.value = '0';
    }
};
