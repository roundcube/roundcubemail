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

// Initialize the message editor

function rcmail_editor_init(skin_path, editor_lang, spellcheck)
{
  tinyMCE.init({ 
    mode : "textareas",
    editor_selector : "mce_editor",
    accessibility_focus : false,
    apply_source_formatting : true,
    theme : "advanced",
    language : editor_lang,
    plugins : "emotions,media,nonbreaking,table,searchreplace,visualchars,directionality" + (spellcheck ? ",spellchecker" : ""),
    theme_advanced_buttons1 : "bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,outdent,indent,separator,link,unlink,emotions,charmap,code,forecolor,backcolor,fontselect,fontsizeselect, separator" + (spellcheck ? ",spellchecker" : "") + ",undo,redo,image,media,ltr,rtl",
    theme_advanced_buttons2 : "",
    theme_advanced_buttons3 : "",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "left",
    extended_valid_elements : "font[face|size|color|style],span[id|class|align|style]",
    content_css : skin_path + "/editor_content.css",
    external_image_list_url : "program/js/editor_images.js",
    spellchecker_languages : (rcmail.env.spellcheck_langs ? rcmail.env.spellcheck_langs : "Dansk=da,Deutsch=de,+English=en,Espanol=es,Francais=fr,Italiano=it,Nederlands=nl,Polski=pl,Portugues=pt,Suomi=fi,Svenska=sv"),
    gecko_spellcheck : true,
    rc_client: rcube_webmail_client
  });
}

// Toggle between the HTML and Plain Text editors

function rcmail_toggle_editor(toggler)
  {
  var selectedEditor = toggler.value;

  // determine the currently displayed editor
  var htmlFlag = document.getElementsByName('_is_html')[0];
  var isHtml = htmlFlag.value;

  if (((selectedEditor == 'plain') && (isHtml == "0")) ||
      ((selectedEditor == 'html') && (isHtml == "1")))
    {
    return;
    }

  // do the appropriate conversion
  if (selectedEditor == 'html')
    {
    rcmail.display_spellcheck_controls(false);
    var composeElement = document.getElementById('compose-body');
    var htmlText = "<pre>" + composeElement.value + "</pre>";
    composeElement.value = htmlText;
    tinyMCE.execCommand('mceAddControl', true, 'compose-body');
    htmlFlag.value = "1";
    }
  else
    {
    var thisMCE = tinyMCE.get('compose-body');
    var existingHtml = thisMCE.getContent();
    rcmail.html2plain(existingHtml, 'compose-body');
    tinyMCE.execCommand('mceRemoveControl', true, 'compose-body');
    htmlFlag.value = "0";
    rcmail.display_spellcheck_controls(true);
    }
  }
