/**
 * Roundcube editor js library
 *
 * This file is part of the Roundcube Webmail client
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (c) 2006-2014, The Roundcube Dev Team
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 *
 * @author Eric Stadtherr <estadtherr@gmail.com>
 */

// Initialize HTML editor
function rcmail_editor_init(config)
{
  var ret, conf = {
      selector: '.mce_editor',
      theme: 'modern',
      language: config.lang,
      content_css: config.skin_path + '/editor_content.css?v2',
      menubar: false,
      statusbar: false,
      toolbar_items_size: 'small',
      extended_valid_elements: 'font[face|size|color|style],span[id|class|align|style]',
      relative_urls: false,
      remove_script_host: false,
      convert_urls: false, // #1486944
      image_description: false,
      paste_webkit_style: "color font-size font-family",
      paste_data_images: true
    };

  if (config.mode == 'identity')
    $.extend(conf, {
      plugins: ['autolink charmap code hr link paste tabfocus textcolor'],
      toolbar: 'bold italic underline alignleft aligncenter alignright alignjustify'
        + ' | outdent indent charmap hr link unlink code forecolor'
        + ' | fontselect fontsizeselect'
    });
  else { // mail compose
    $.extend(conf, {
      plugins: ['autolink charmap code directionality emoticons link image media nonbreaking'
        + ' paste table tabfocus textcolor searchreplace' + (config.spellcheck ? ' spellchecker' : '')],
      toolbar: 'bold italic underline | alignleft aligncenter alignright alignjustify'
        + ' | bullist numlist outdent indent ltr rtl blockquote | forecolor backcolor | fontselect fontsizeselect'
        + ' | link unlink table | emoticons charmap image media | code searchreplace undo redo',
      spellchecker_rpc_url: '../../../../../?_task=utils&_action=spell_html&_remote=1',
      spellchecker_language: rcmail.env.spell_lang,
      accessibility_focus: false,
      file_browser_callback: rcmail_file_browser_callback,
      // @todo: support more than image (types: file, image, media)
      file_browser_callback_types: 'image'
    });

    conf.setup = function(ed) {
      ed.on('init', rcmail_editor_callback);
      // add handler for spellcheck button state update
      ed.on('SpellcheckStart SpellcheckEnd', function(args) {
        rcmail.env.spellcheck_active = args.type == 'spellcheckstart';
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

// image selector
function rcmail_file_browser_callback(field_name, url, type, win)
{
  var i, elem, dialog, list = [], editor = tinyMCE.activeEditor;

  // open image selector dialog
  dialog = editor.windowManager.open({
    title: rcmail.gettext('select' + type),
    width: 500,
    height: 300,
    html: '<div id="image-selector-list"><ul></ul></div>'
      + '<div id="image-selector-form"><div id="image-upload-button" class="mce-widget mce-btn" role="button"></div></div>',
    buttons: [{text: 'Cancel', onclick: function() { rcmail_file_browser_close(); }}]
  });

  rcmail.env.file_browser_field = field_name;
  rcmail.env.file_browser_type = type;

  // fill images list with available images
  for (i in rcmail.env.attachments) {
    if (elem = rcmail_file_browser_entry(i, rcmail.env.attachments[i])) {
      list.push(elem);
    }
  }

  if (list.length) {
    $('#image-selector-list > ul').append(list);
  }

  // add hint about max file size (in dialog footer)
  $('div.mce-abs-end', dialog.getEl()).append($('<div class="hint">').text($('div.hint', rcmail.gui_objects.uploadform).text()));

  // enable (smart) upload button
  elem = $('#image-upload-button').append($('<span>').text(rcmail.gettext('add' + type)));
  hack_file_input(elem, rcmail.gui_objects.uploadform);

  // enable drag-n-drop area
  if (rcmail.gui_objects.filedrop && rcmail.env.filedrop && ((window.XMLHttpRequest && XMLHttpRequest.prototype && XMLHttpRequest.prototype.sendAsBinary) || window.FormData)) {
    rcmail.env.old_file_drop = rcmail.gui_objects.filedrop;
    rcmail.gui_objects.filedrop = $('#image-selector-form');
    rcmail.gui_objects.filedrop.addClass('droptarget')
      .bind('dragover dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this)[(e.type == 'dragover' ? 'addClass' : 'removeClass')]('hover');
      })
      .get(0).addEventListener('drop', function(e) { return rcmail.file_dropped(e); }, false);
  }

  // register handler for successful file upload
  if (!rcmail.env.file_dialog_event) {
    rcmail.env.file_dialog_event = true;
    rcmail.addEventListener('fileuploaded', function(attr) {
      var elem;
      if (elem = rcmail_file_browser_entry(attr.name, attr.attachment)) {
        $('#image-selector-list > ul').prepend(elem);
      }
    });
  }
}

// close file browser window
function rcmail_file_browser_close(url)
{
  if (url)
    $('#' + rcmail.env.file_browser_field).val(url);

  tinyMCE.activeEditor.windowManager.close();

  if (rcmail.env.old_file_drop)
    rcmail.gui_objects.filedrop = rcmail.env.old_file_drop;
}

// creates file browser entry
function rcmail_file_browser_entry(file_id, file)
{
  if (!file.complete || !file.mimetype) {
    return;
  }

  if (file.mimetype.startsWith('image/')) {
    var href = rcmail.env.comm_path+'&_id='+rcmail.env.compose_id+'&_action=display-attachment&_file='+file_id,
      img = $('<img>').attr({title: file.name, src: href + '&_thumbnail=1'});

    return $('<li>').data('url', href)
      .append($('<span class="img">').append(img))
      .append($('<span class="name">').text(file.name))
      .click(function() { rcmail_file_browser_close($(this).data('url')); });
  }
}

// create smart files upload button
function hack_file_input(elem, clone_form)
{
  var link = $(elem),
    file = $('<input>'),
    form = $('<form>').attr({method: 'post', enctype: 'multipart/form-data'}),
    offset = link.offset();

  // clone existing upload form
  if (clone_form) {
    file.attr('name', $('input[type="file"]', clone_form).attr('name'));
    form.attr('action', $(clone_form).attr('action'))
      .append($('<input>').attr({type: 'hidden', name: '_token', value: rcmail.env.request_token}));
  }

  function move_file_input(e) {
    file.css({top: (e.pageY - offset.top - 10) + 'px', left: (e.pageX - offset.left - 10) + 'px'});
  }

  file.attr({type: 'file', multiple: 'multiple', size: 5, title: ''})
    .change(function() { rcmail.upload_file(form, 'upload'); })
    .click(function() { setTimeout(function() { link.mouseleave(); }, 20); })
    // opacity:0 does the trick, display/visibility doesn't work
    .css({opacity: 0, cursor: 'pointer', position: 'relative', outline: 'none'})
    .appendTo(form);

  // In FF and IE we need to move the browser file-input's button under the cursor
  // Thanks to the size attribute above we know the length of the input field
  if (navigator.userAgent.match(/Firefox|MSIE/))
    file.css({marginLeft: '-80px'});

  // Note: now, I observe problem with cursor style on FF < 4 only
  link.css({overflow: 'hidden', cursor: 'pointer'})
    .mouseenter(function() { this.__active = true; })
    // place button under the cursor
    .mousemove(function(e) {
      if (this.__active)
        move_file_input(e);
      // move the input away if button is disabled
      else
        $(this).mouseleave();
    })
    .mouseleave(function() {
      file.css({top: '-10000px', left: '-10000px'});
      this.__active = false;
    })
    .click(function(e) {
      // forward click if mouse-enter event was missed
      if (!this.__active) {
        this.__active = true;
        move_file_input(e);
        file.trigger(e);
      }
    })
    .mouseleave()
    .append(form);
}
