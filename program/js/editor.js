/*
 +-----------------------------------------------------------------------+
 | Roundcube editor js library                                           |
 |                                                                       |
 | This file is part of the Roundcube web development suite              |
 | Copyright (C) 2006, The Roundcube Dev Team                            |
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
  var ret, conf = {
      mode: 'textareas',
      editor_selector: 'mce_editor',
      apply_source_formatting: true,
      theme: 'advanced',
      language: editor_lang,
      content_css: skin_path + '/editor_content.css',
      theme_advanced_toolbar_location: 'top',
      theme_advanced_toolbar_align: 'left',
      theme_advanced_buttons3: '',
      extended_valid_elements: 'font[face|size|color|style],span[id|class|align|style]',
      relative_urls: false,
      remove_script_host: false,
      gecko_spellcheck: true,
      convert_urls: false, // #1486944
      external_image_list_url: 'program/js/editor_images.js',
      rc_client: rcmail
    };

  if (mode == 'identity')
    $.extend(conf, {
      plugins: 'paste,tabfocus',
      theme_advanced_buttons1: 'bold,italic,underline,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,separator,outdent,indent,charmap,hr,link,unlink,code,forecolor',
      theme_advanced_buttons2: ',fontselect,fontsizeselect'
    });
  else // mail compose
    $.extend(conf, {
      plugins: 'paste,emotions,media,nonbreaking,table,searchreplace,visualchars,directionality,tabfocus' + (spellcheck ? ',spellchecker' : ''),
      theme_advanced_buttons1: 'bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,outdent,indent,ltr,rtl,blockquote,|,forecolor,backcolor,fontselect,fontsizeselect',
      theme_advanced_buttons2: 'link,unlink,table,|,emotions,charmap,image,media,|,code,search' + (spellcheck ? ',spellchecker' : '') + ',undo,redo',
      spellchecker_languages: (rcmail.env.spellcheck_langs ? rcmail.env.spellcheck_langs : 'Dansk=da,Deutsch=de,+English=en,Espanol=es,Francais=fr,Italiano=it,Nederlands=nl,Polski=pl,Portugues=pt,Suomi=fi,Svenska=sv'),
      spellchecker_rpc_url: '?_task=utils&_action=spell&tiny=1',
      accessibility_focus: false,
      oninit: 'rcmail_editor_callback'
    });

  // support external configuration settings e.g. from skin
  if (window.rcmail_editor_settings)
    $.extend(conf, window.rcmail_editor_settings);

  tinyMCE.init(conf);
}

// react to real individual tinyMCE editor init
function rcmail_editor_callback()
{
  var elem = rcube_find_object('_from'),
    fe = rcmail.env.compose_focus_elem;

  if (elem && elem.type == 'select-one') {
    rcmail.change_identity(elem);
    // Focus previously focused element
    if (fe && fe.id != rcmail.env.composebody) {
      window.focus(); // for WebKit (#1486674)
      fe.focus();
    }
  }

  // set tabIndex and set focus to element that was focused before
  rcmail_editor_tabindex(fe && fe.id == rcmail.env.composebody);
  // Trigger resize (needed for proper editor resizing in some browsers using default skin)
  $(window).resize();
}

// set tabIndex on tinyMCE editor
function rcmail_editor_tabindex(focus)
{
  if (rcmail.env.task == 'mail') {
    var editor = tinyMCE.get(rcmail.env.composebody);
    if (editor) {
      var textarea = editor.getElement();
      var node = editor.getContentAreaContainer().childNodes[0];
      if (textarea && node)
        node.tabIndex = textarea.tabIndex;
      if (focus)
        editor.getWin().focus();
    }
  }
}

// switch html/plain mode
function rcmail_toggle_editor(select, textAreaId, flagElement)
{
  var flag, ishtml;

  if (select.tagName != 'SELECT')
    ishtml = select.checked;
  else
    ishtml = select.value == 'html';

  var res = rcmail.command('toggle-editor', {id:textAreaId, mode:ishtml?'html':'plain'});

  if (ishtml) {
    // #1486593
    setTimeout("rcmail_editor_tabindex(true);", 500);
    if (flagElement && (flag = rcube_find_object(flagElement)))
      flag.value = '1';
  }
  else {
    if (!res && select.tagName == 'SELECT')
      select.value = 'html';
    if (flagElement && (flag = rcube_find_object(flagElement)))
      flag.value = '0';

    if (rcmail.env.composebody)
      rcube_find_object(rcmail.env.composebody).focus();
  }
}
