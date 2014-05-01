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
      selector: '.mce_editor',
      theme: 'modern',
      language: config.lang,
      content_css: config.skin_path + '/editor_content.css',
      menubar: false,
      statusbar: false,
      extended_valid_elements: 'font[face|size|color|style],span[id|class|align|style]',
      relative_urls: false,
      remove_script_host: false,
      convert_urls: false, // #1486944
      image_list: window.rcmail_editor_images,
      image_description: false,
      paste_webkit_style: "color font-size font-family",
      paste_data_images: true
    };

  if (config.mode == 'identity')
    $.extend(conf, {
      plugins: ['charmap code hr link paste tabfocus textcolor'],
      toolbar: 'bold italic underline alignleft aligncenter alignright alignjustify'
        + ' | outdent indent charmap hr link unlink code forecolor'
        + ' | fontselect fontsizeselect'
    });
  else { // mail compose
    $.extend(conf, {
      plugins: ['charmap code directionality emoticons link image media nonbreaking'
        + ' paste table tabfocus textcolor searchreplace' + (config.spellcheck ? ' spellchecker' : '')],
      toolbar: 'bold italic underline | alignleft aligncenter alignright alignjustify'
        + ' | bullist numlist outdent indent ltr rtl blockquote | forecolor backcolor | fontselect fontsizeselect'
        + ' | link unlink table | emoticons charmap image media | code searchreplace undo redo',
      spellchecker_rpc_url: '../../../../../?_task=utils&_action=spell_html&_remote=1',
      spellchecker_enable_learn_rpc: config.spelldict, //TODO
      accessibility_focus: false
    });

    conf.setup = function(ed) {
      ed.on('init', rcmail_editor_callback);
      // add handler for spellcheck button state update
      ed.on('ProgressState', function(args) {
        if (!args.state)
          rcmail.spellcheck_state();
      });
      ed.on('keypress', function() {
        rcmail.compose_type_activity++;
      });
    }
  }

  // support external configuration settings e.g. from skin
  if (window.rcmail_editor_settings)
    $.extend(conf, window.rcmail_editor_settings);

  tinymce.init(conf);
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
    $(tinymce.get(rcmail.env.composebody).getBody()).css(css);

  if (elem && elem.type == 'select-one') {
    // insert signature (only for the first time)
    if (!rcmail.env.identities_initialized)
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
  // Trigger resize (needed for proper editor resizing in some browsers)
  window.setTimeout(function() { $(window).resize(); }, 100);
}

// set tabIndex on tinymce editor
function rcmail_editor_tabindex(focus)
{
  if (rcmail.env.task == 'mail') {
    var editor = tinymce.get(rcmail.env.composebody);
    if (editor) {
      var textarea = editor.getElement(),
        node = editor.getContentAreaContainer().childNodes[0];

      if (textarea && node)
        node.tabIndex = textarea.tabIndex;
      if (focus)
        editor.getBody().focus();
    }
  }
}

// switch html/plain mode
function rcmail_toggle_editor(select, textAreaId)
{
  var ishtml = select.tagName != 'SELECT' ? select.checked : select.value == 'html',
    res = rcmail.command('toggle-editor', {id: textAreaId, mode: ishtml ? 'html' : 'plain'});

  if (!res) {
    if (select.tagName == 'SELECT')
      select.value = 'html';
    else if (select.tagName == 'INPUT')
      select.checked = true;
  }
  else if (ishtml) {
    // #1486593
    setTimeout("rcmail_editor_tabindex(true);", 500);
  }
  else if (rcmail.env.composebody) {
    rcube_find_object(rcmail.env.composebody).focus();
  }
}

// editor callback for images listing
function rcmail_editor_images(callback)
{
  var i, file, list = [];

  for (i in rcmail.env.attachments) {
    file = rcmail.env.attachments[i];
    if (file.complete && file.mimetype.startsWith('image/')) {
      list.push({title: file.name, value: rcmail.env.comm_path+'&_id='+rcmail.env.compose_id+'&_action=display-attachment&_file='+i});
    }
  }

  callback(list);
}
