/*
 +-----------------------------------------------------------------------+
 | Roundcube editor js library                                           |
 |                                                                       |
 | This file is part of the Roundcube web development suite              |
 | Copyright (C) 2006-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Eric Stadtherr <estadtherr@gmail.com>                         |
 +-----------------------------------------------------------------------+
*/

// Initialize HTML editor
function rcmail_editor_init(config)
{
  var ret, conf = {
      mode: 'textareas',
      editor_selector: 'mce_editor',
      apply_source_formatting: true,
      theme: 'advanced',
      language: config.lang,
      content_css: config.skin_path + '/editor_content.css?v2',
      theme_advanced_toolbar_location: 'top',
      theme_advanced_toolbar_align: 'left',
      theme_advanced_buttons3: '',
      theme_advanced_statusbar_location: 'none',
      extended_valid_elements: 'font[face|size|color|style],span[id|class|align|style]',
      relative_urls: false,
      remove_script_host: false,
      gecko_spellcheck: true,
      convert_urls: false, // #1486944
      external_image_list: window.rcmail_editor_images,
      rc_client: rcmail
    };

  if (config.mode == 'identity')
    $.extend(conf, {
      plugins: 'paste,tabfocus',
      theme_advanced_buttons1: 'bold,italic,underline,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,separator,outdent,indent,charmap,hr,link,unlink,code,forecolor',
      theme_advanced_buttons2: 'fontselect,fontsizeselect'
    });
  else { // mail compose
    $.extend(conf, {
      plugins: 'paste,emotions,media,nonbreaking,table,searchreplace,visualchars,directionality,inlinepopups,tabfocus,contextmenu' + (config.spellcheck ? ',spellchecker' : ''),
      theme_advanced_buttons1: 'bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,outdent,indent,ltr,rtl,blockquote,|,forecolor,backcolor,fontselect,fontsizeselect',
      theme_advanced_buttons2: 'link,unlink,table,|,emotions,charmap,image,media,|,code,search,undo,redo',
      spellchecker_languages: (rcmail.env.spellcheck_langs ? rcmail.env.spellcheck_langs : 'Dansk=da,Deutsch=de,+English=en,Espanol=es,Francais=fr,Italiano=it,Nederlands=nl,Polski=pl,Portugues=pt,Suomi=fi,Svenska=sv'),
      spellchecker_rpc_url: '?_task=utils&_action=spell_html&_remote=1',
      spellchecker_enable_learn_rpc: config.spelldict,
      accessibility_focus: false,
      oninit: 'rcmail_editor_callback'
    });

    // add handler for spellcheck button state update
    conf.setup = function(ed) {
      ed.onSetProgressState.add(function(ed, active) {
        if (!active)
          rcmail.spellcheck_state();
      });
      ed.onKeyPress.add(function(ed, e) {
          rcmail.compose_type_activity++;
      });
    }
  }

  // support external configuration settings e.g. from skin
  if (window.rcmail_editor_settings)
    $.extend(conf, window.rcmail_editor_settings);

  tinyMCE.init(conf);
}

// react to real individual tinyMCE editor init
function rcmail_editor_callback()
{
  var css = {},
    elem = rcube_find_object('_from'),
    fe = rcmail.env.compose_focus_elem;

  if (rcmail.env.default_font)
    css['font-family'] = rcmail.env.default_font;

  if (rcmail.env.default_font_size)
    css['font-size'] = rcmail.env.default_font_size;

  if (css['font-family'] || css['font-size'])
    $(tinyMCE.get(rcmail.env.composebody).getBody()).css(css);

  if (elem && elem.type == 'select-one') {
    rcmail.change_identity(elem);
    // Focus previously focused element
    if (fe && fe.id != rcmail.env.composebody) {
      // use setTimeout() for IE9 (#1488541)
      window.setTimeout(function() {
        window.focus(); // for WebKit (#1486674)
        fe.focus();
      }, 10);
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
        editor.getBody().focus();
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
  else if (res) {
    if (flagElement && (flag = rcube_find_object(flagElement)))
      flag.value = '0';

    if (rcmail.env.composebody)
      rcube_find_object(rcmail.env.composebody).focus();
  }
  else { // !res
    if (select.tagName == 'SELECT')
      select.value = 'html';
    else if (select.tagName == 'INPUT')
      select.checked = true;
  }
}

// editor callbeck for images listing
function rcmail_editor_images()
{
  var i, files = rcmail.env.attachments, list = [];

  for (i in files) {
    att = files[i];
    if (att.complete && att.mimetype.startsWith('image/')) {
      list.push([att.name, rcmail.env.comm_path+'&_id='+rcmail.env.compose_id+'&_action=display-attachment&_file='+i]);
    }
  }

  return list;
};
