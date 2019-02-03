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
 * @author Aleksander Machniak <alec@alec.pl>
 */

/**
 * Roundcube Text Editor Widget class
 * @constructor
 */
function rcube_text_editor(config, id)
{
  var ref = this,
    abs_url = location.href.replace(/[?#].*$/, '').replace(/\/$/, ''),
    conf = {
      selector: '#' + ($('#' + id).is('.mce_editor') ? id : 'fake-editor-id'),
      cache_suffix: 's=4080200',
      theme: 'modern',
      language: config.lang,
      content_css: rcmail.assets_path(config.content_css),
      menubar: false,
      statusbar: false,
      toolbar_items_size: 'small',
      extended_valid_elements: 'font[face|size|color|style],span[id|class|align|style]',
      fontsize_formats: '8pt 9pt 10pt 11pt 12pt 14pt 18pt 24pt 36pt',
      valid_children: '+body[style]',
      relative_urls: false,
      remove_script_host: false,
      convert_urls: false, // #1486944
      image_description: false,
      paste_webkit_style: "color font-size font-family",
      paste_data_images: true,
      browser_spellcheck: true,
      anchor_bottom: false,
      anchor_top: false
    };

  // register spellchecker for plain text editor
  this.spellcheck_observer = function() {};
  if (config.spellchecker) {
    this.spellchecker = config.spellchecker;
    if (config.spellcheck_observer) {
      this.spellchecker.spelling_state_observer = this.spellcheck_observer = config.spellcheck_observer;
    }
  }

  // secure spellchecker requests with Roundcube token
  // Note: must be registered only once (#1490311)
  if (!tinymce.registered_request_token) {
    tinymce.registered_request_token = true;
    tinymce.util.XHR.on('beforeSend', function(e) {
      e.xhr.setRequestHeader('X-Roundcube-Request', rcmail.env.request_token);
    });
  }

  // minimal editor
  if (config.mode == 'identity') {
    $.extend(conf, {
      plugins: 'autolink charmap code colorpicker hr image link paste tabfocus textcolor',
      toolbar: 'bold italic underline alignleft aligncenter alignright alignjustify'
        + ' | outdent indent charmap hr link unlink image code forecolor'
        + ' | fontselect fontsizeselect',
      file_browser_callback: function(name, url, type, win) { ref.file_browser_callback(name, url, type); },
      file_browser_callback_types: 'image'
    });
  }
  // full-featured editor
  else {
    $.extend(conf, {
      plugins: 'autolink charmap code colorpicker directionality link lists image media nonbreaking'
        + ' paste table tabfocus textcolor searchreplace spellchecker',
      toolbar: 'bold italic underline | alignleft aligncenter alignright alignjustify'
        + ' | bullist numlist outdent indent ltr rtl blockquote | forecolor backcolor | fontselect fontsizeselect'
        + ' | link unlink table | $extra charmap image media | code searchreplace undo redo',
      spellchecker_rpc_url: abs_url + '/?_task=utils&_action=spell_html&_remote=1',
      spellchecker_language: rcmail.env.spell_lang,
      accessibility_focus: false,
      file_browser_callback: function(name, url, type, win) { ref.file_browser_callback(name, url, type); },
      // @todo: support more than image (types: file, image, media)
      file_browser_callback_types: 'image media'
    });
  }

  // add TinyMCE plugins/buttons from Roundcube plugin
  $.each(config.extra_plugins || [], function() {
    if (conf.plugins.indexOf(this) < 0)
      conf.plugins = conf.plugins + ' ' + this;
  });
  $.each(config.extra_buttons || [], function() {
    if (conf.toolbar.indexOf(this) < 0)
      conf.toolbar = conf.toolbar.replace('$extra', '$extra ' + this);
  });

  // disable TinyMCE plugins/buttons from Roundcube plugin
  $.each(config.disabled_plugins || [], function() {
    conf.plugins = conf.plugins.replace(this, '');
  });
  $.each(config.disabled_buttons || [], function() {
    conf.toolbar = conf.toolbar.replace(this, '');
  });

  conf.toolbar = conf.toolbar.replace('$extra', '').replace(/\|\s+\|/g, '|');

  // support external configuration settings e.g. from skin
  if (window.rcmail_editor_settings)
    $.extend(conf, window.rcmail_editor_settings);

  conf.setup = function(ed) {
    ed.on('init', function(ed) { ref.init_callback(ed); });
    // add handler for spellcheck button state update
    ed.on('SpellcheckStart SpellcheckEnd', function(args) {
      ref.spellcheck_active = args.type == 'spellcheckstart';
      ref.spellcheck_observer();
    });
    ed.on('keypress', function() {
      rcmail.compose_type_activity++;
    });
    // make links open on shift-click
    ed.on('click', function(e) {
      var link = $(e.target).closest('a');
      if (link.length && e.shiftKey) {
        window.open(link.get(0).href, '_blank');
        return false;
      }
    });
    ed.on('focus blur', function(e) {
      $(ed.getContainer()).toggleClass('focused');
    });

    if (conf.setup_callback)
      conf.setup_callback(ed);
  };

  rcmail.triggerEvent('editor-init', {config: conf, ref: ref});

  // textarea identifier
  this.id = id;
  // reference to active editor (if in HTML mode)
  this.editor = null;

  tinymce.init(conf);

  // react to real individual tinyMCE editor init
  this.init_callback = function(event)
  {
    this.editor = event.target;

    rcmail.triggerEvent('editor-load', {config: conf, ref: ref});

    if (rcmail.env.action == 'compose') {
      var area = $('#' + this.id),
        height = $('div.mce-toolbar-grp:first', area.parent()).height();

      // the editor might be still not fully loaded, making the editing area
      // inaccessible, wait and try again (#1490310)
      if (height > 200 || height > area.height()) {
        return setTimeout(function () { ref.init_callback(event); }, 300);
      }

      var css = {},
        elem = rcube_find_object('_from'),
        fe = rcmail.env.compose_focus_elem;

      if (rcmail.env.default_font)
        css['font-family'] = rcmail.env.default_font;

      if (rcmail.env.default_font_size)
        css['font-size'] = rcmail.env.default_font_size;

      if (css['font-family'] || css['font-size'])
        $(this.editor.getBody()).css(css);

      if (elem && elem.type == 'select-one') {
        // insert signature (only for the first time)
        if (!rcmail.env.identities_initialized)
          rcmail.change_identity(elem);

        // Focus previously focused element
        if (fe && fe.id != this.id && fe.nodeName != 'BODY') {
          window.focus(); // for WebKit (#1486674)
          fe.focus();
          rcmail.env.compose_focus_elem = null;
        }
      }
    }

    // set tabIndex and set focus to element that was focused before
    ref.tabindex(ref.force_focus || (fe && fe.id == ref.id));

    // Trigger resize (needed for proper editor resizing in some browsers)
    $(window).resize();
  };

  // set tabIndex on tinymce editor
  this.tabindex = function(focus)
  {
    if (rcmail.env.task == 'mail' && this.editor) {
      var textarea = this.editor.getElement(),
        node = this.editor.getContentAreaContainer().childNodes[0];

      if (textarea && node)
        node.tabIndex = textarea.tabIndex;

      // find :prev and :next elements to get focus when tabbing away
      if (textarea.tabIndex > 0) {
        var x = null,
          tabfocus_elements = [':prev',':next'],
          el = tinymce.DOM.select('*[tabindex='+textarea.tabIndex+']:not(iframe)');

        tinymce.each(el, function(e, i) { if (e.id == ref.id) { x = i; return false; } });
        if (x !== null) {
          if (el[x-1] && el[x-1].id) {
            tabfocus_elements[0] = el[x-1].id;
          }
          if (el[x+1] && el[x+1].id) {
            tabfocus_elements[1] = el[x+1].id;
          }
          this.editor.settings.tabfocus_elements = tabfocus_elements.join(',');
        }
      }

      // ContentEditable reset fixes invisible cursor issue in Firefox < 25
      if (bw.mz && bw.vendver < 25)
        $(this.editor.getBody()).prop('contenteditable', false).prop('contenteditable', true);
    }

    if (focus)
      this.focus();
  };

  // focus the editor
  this.focus = function()
  {
    $(this.editor || ('#' + this.id)).focus();
    this.force_focus = false;
  };

  // switch html/plain mode
  this.toggle = function(ishtml, noconvert)
  {
    var curr, content, result,
      // these non-printable chars are not removed on text2html and html2text
      // we can use them as temp signature replacement
      sig_mark = "\u0002\u0003",
      input = $('#' + this.id),
      signature = rcmail.env.identity ? rcmail.env.signatures[rcmail.env.identity] : null,
      is_sig = signature && signature.text && signature.text.length > 1;

    // apply spellcheck changes if spell checker is active
    this.spellcheck_stop();

    if (ishtml) {
      content = input.val();

      // replace current text signature with temp mark
      if (is_sig) {
        content = content.replace(/\r\n/, "\n");
        content = content.replace(signature.text.replace(/\r\n/, "\n"), sig_mark);
      }

      var init_editor = function(data) {
        // replace signature mark with html version of the signature
        if (is_sig)
          data = data.replace(sig_mark, '<div id="_rc_sig">' + signature.html + '</div>');

        ref.force_focus = true;
        input.val(data);
        tinymce.execCommand('mceAddEditor', false, ref.id);
      };

      // convert to html
      if (!noconvert) {
        result = rcmail.plain2html(content, init_editor);
      }
      else {
        init_editor(content);
        result = true;
      }
    }
    else if (this.editor) {
      if (is_sig) {
        // get current version of signature, we'll need it in
        // case of html2text conversion abort
        if (curr = this.editor.dom.get('_rc_sig'))
          curr = curr.innerHTML;

        // replace current signature with some non-printable characters
        // we use non-printable characters, because this replacement
        // is visible to the user
        // doing this after getContent() would be hard
        this.editor.dom.setHTML('_rc_sig', sig_mark);
      }

      // get html content
      content = this.editor.getContent();

      var init_plaintext = function(data) {
        tinymce.execCommand('mceRemoveEditor', false, ref.id);
        ref.editor = null;

        // replace signture mark with text version of the signature
        if (is_sig)
          data = data.replace(sig_mark, "\n" + signature.text);

        input.val(data).focus();
        rcmail.set_caret_pos(input.get(0), 0);
      };

      // convert html to text
      if (!noconvert) {
        result = rcmail.html2plain(content, init_plaintext);
      }
      else {
        init_plaintext(input.val());
        result = true;
      }

      // bring back current signature
      if (!result && curr)
        this.editor.dom.setHTML('_rc_sig', curr);
    }

    return result;
  };

  // start spellchecker
  this.spellcheck_start = function()
  {
    if (this.editor) {
      tinymce.execCommand('mceSpellCheck', true);
      this.spellcheck_observer();
    }
    else if (this.spellchecker && this.spellchecker.spellCheck) {
      this.spellchecker.spellCheck();
    }
  };

  // stop spellchecker
  this.spellcheck_stop = function()
  {
    var ed = this.editor;

    if (ed) {
      if (ed.plugins && ed.plugins.spellchecker && this.spellcheck_active) {
        ed.execCommand('mceSpellCheck', false);
        this.spellcheck_observer();
      }
    }
    else if (ed = this.spellchecker) {
      if (ed.state && ed.state != 'ready' && ed.state != 'no_error_found')
        $(ed.spell_span).trigger('click');
    }
  };

  // spellchecker state
  this.spellcheck_state = function()
  {
    var ed;

    if (this.editor)
      return this.spellcheck_active;
    else if ((ed = this.spellchecker) && ed.state)
      return ed.state != 'ready' && ed.state != 'no_error_found';
  };

  // resume spellchecking, highlight provided mispellings without a new ajax request
  this.spellcheck_resume = function(data)
  {
    var ed = this.editor;

    if (ed) {
      ed.plugins.spellchecker.markErrors(data);
    }
    else if (ed = this.spellchecker) {
      ed.prepare(false, true);
      ed.processData(data);
    }
  };

  // get selected (spellchecker) language
  this.get_language = function()
  {
    if (this.editor) {
      return this.editor.settings.spellchecker_language || rcmail.env.spell_lang;
    }
    else if (this.spellchecker) {
      return GOOGIE_CUR_LANG;
    }
  };

  // set language for spellchecking
  this.set_language = function(lang)
  {
    var ed = this.editor;

    if (ed) {
      ed.settings.spellchecker_language = lang;
    }
    if (ed = this.spellchecker) {
      ed.setCurrentLanguage(lang);
    }
  };

  // replace selection with text snippet
  // input can be a string or object with 'text' and 'html' properties
  this.replace = function(input)
  {
    var format, ed = this.editor;

    if (!input)
      return false;

    // insert into tinymce editor
    if (ed) {
      ed.getWin().focus(); // correct focus in IE & Chrome

      if ($.type(input) == 'object' && ('html' in input)) {
        input = input.html;
        format = 'html';
      }
      else {
        if ($.type(input) == 'object')
          input = input.text || '';

        input = rcmail.quote_html(input).replace(/\r?\n/g, '<br/>');
        format = 'text';
      }

      ed.selection.setContent(input, {format: format});
    }
    // replace selection in compose textarea
    else if (ed = rcube_find_object(this.id)) {
      var selection = $(ed).is(':focus') ? rcmail.get_input_selection(ed) : {start: 0, end: 0},
        value = ed.value,
        pre = value.substring(0, selection.start),
        end = value.substring(selection.end, value.length);

      if ($.type(input) == 'object')
        input = input.text || '';

      // insert response text
      ed.value = pre + input + end;

      // set caret after inserted text
      rcmail.set_caret_pos(ed, selection.start + input.length);
      ed.focus();
    }
  };

  // Fill the editor with specified content
  // TODO: support format conversion
  this.set_content = function(content)
  {
    if (this.editor) {
      this.editor.setContent(content);
      this.editor.getWin().focus();
    }
    else if (ed = rcube_find_object(this.id)) {
      $(ed).val(content).focus();
    }
  };

  // get selected text (if no selection returns all text) from the editor
  this.get_content = function(args)
  {
    var sigstart, ed = this.editor, text = '', strip = false,
      defaults = {refresh: true, selection: false, nosig: false, format: 'html'};

    if (!args)
      args = defaults;
    else
      args = $.extend(defaults, args);

    // apply spellcheck changes if spell checker is active
    if (args.refresh) {
      this.spellcheck_stop();
    }

    // get selected text from tinymce editor
    if (ed) {
      if (args.selection)
        text = ed.selection.getContent({format: args.format});

      if (!text) {
        text = ed.getContent({format: args.format});
        // @todo: strip signature in html mode
        strip = args.format == 'text';
      }
    }
    // get selected text from compose textarea
    else if (ed = rcube_find_object(this.id)) {
      if (args.selection && $(ed).is(':focus')) {
        text = rcmail.get_input_selection(ed).text;
      }

      if (!text) {
        text = ed.value;
        strip = true;
      }
    }

    // strip off signature
    // @todo: make this optional
    if (strip && args.nosig) {
      sigstart = text.indexOf('-- \n');
      if (sigstart > 0) {
        text = text.substring(0, sigstart);
      }
    }

    return text;
  };

  // change user signature text
  this.change_signature = function(id, show_sig)
  {
    var position_element, cursor_pos, p = -1,
      input_message = $('#' + this.id),
      message = input_message.val(),
      sig = rcmail.env.identity;

    if (!this.editor) { // plain text mode
      // remove the 'old' signature
      if (show_sig && sig && rcmail.env.signatures && rcmail.env.signatures[sig]) {
        sig = rcmail.env.signatures[sig].text;
        sig = sig.replace(/\r\n/g, '\n');

        p = rcmail.env.top_posting ? message.indexOf(sig) : message.lastIndexOf(sig);
        if (p >= 0)
          message = message.substring(0, p) + message.substring(p+sig.length, message.length);
      }

      // add the new signature string
      if (show_sig && rcmail.env.signatures && rcmail.env.signatures[id]) {
        sig = rcmail.env.signatures[id].text;
        sig = sig.replace(/\r\n/g, '\n');

        // in place of removed signature
        if (p >= 0) {
          message = message.substring(0, p) + sig + message.substring(p, message.length);
          cursor_pos = p - 1;
        }
        // empty message or new-message mode
        else if (!message || !rcmail.env.compose_mode) {
          cursor_pos = message.length;
          message += '\n\n' + sig;
        }
        else if (rcmail.env.top_posting && !rcmail.env.sig_below) {
          // at cursor position
          if (pos = rcmail.get_caret_pos(input_message.get(0))) {
            message = message.substring(0, pos) + '\n' + sig + '\n\n' + message.substring(pos, message.length);
            cursor_pos = pos;
          }
          // on top
          else {
            message = '\n\n' + sig + '\n\n' + message.replace(/^[\r\n]+/, '');
            cursor_pos = 0;
          }
        }
        else {
          message = message.replace(/[\r\n]+$/, '');
          cursor_pos = !rcmail.env.top_posting && message.length ? message.length + 1 : 0;
          message += '\n\n' + sig;
        }
      }
      else {
        cursor_pos = rcmail.env.top_posting ? 0 : message.length;
      }

      input_message.val(message);

      // move cursor before the signature
      rcmail.set_caret_pos(input_message.get(0), cursor_pos);
    }
    else if (show_sig && rcmail.env.signatures) {  // html
      var sigElem = this.editor.dom.get('_rc_sig');

      // Append the signature as a div within the body
      if (!sigElem) {
        var body = this.editor.getBody();

        sigElem = $('<div id="_rc_sig"></div>').get(0);

        // insert at start or at cursor position in top-posting mode
        // (but not if the content is empty and not in new-message mode)
        if (rcmail.env.top_posting && !rcmail.env.sig_below
          && rcmail.env.compose_mode && (body.childNodes.length > 1 || $(body).text())
        ) {
          this.editor.getWin().focus(); // correct focus in IE & Chrome

          var node = this.editor.selection.getNode();

          $(sigElem).insertBefore(node.nodeName == 'BODY' ? body.firstChild : node.nextSibling);
          $('<p>').append($('<br>')).insertBefore(sigElem);
        }
        else {
          body.appendChild(sigElem);
          position_element = rcmail.env.top_posting && rcmail.env.compose_mode ? body.firstChild : $(sigElem).prev();
        }
      }

      sigElem.innerHTML = rcmail.env.signatures[id] ? rcmail.env.signatures[id].html : '';
    }
    else if (!rcmail.env.top_posting) {
      position_element = $(this.editor.getBody()).children().last();
    }

    // put cursor before signature and scroll the window
    if (this.editor && position_element && position_element.length) {
      this.editor.selection.setCursorLocation(position_element.get(0));
      this.editor.getWin().scroll(0, position_element.offset().top);
    }
  };

  // trigger content save
  this.save = function()
  {
    if (this.editor) {
      this.editor.save();
    }
  };

  // focus the editing area
  this.focus = function()
  {
    (this.editor || rcube_find_object(this.id)).focus();
  };

  // image selector
  this.file_browser_callback = function(field_name, url, type)
  {
    var i, button, elem, cancel, dialog, fn, hint, list = [],
      form = $('.upload-form').clone();

    // open image selector dialog
    this.editor.windowManager.open({
      title: rcmail.get_label('select' + type),
      width: 500,
      html: '<div id="image-selector" class="image-selector file-upload"><ul id="image-selector-list" class="attachmentslist"></ul></div>',
      buttons: [{text: 'Cancel', onclick: function() { ref.file_browser_close(); }}]
    });

    rcmail.env.file_browser_field = field_name;
    rcmail.env.file_browser_type = type;

    dialog = $('#image-selector');

    if (!form.length)
      form = this.file_upload_form(rcmail.gui_objects.uploadform);
    else
      form.find('button,a.button').slice(1).remove(); // need only the first button

    button = dialog.prepend(form).find('button,a.button')
      .text(rcmail.get_label('add' + type))
      .focus();

    // fill images list with available images
    for (i in rcmail.env.attachments) {
      if (elem = ref.file_browser_entry(i, rcmail.env.attachments[i])) {
        list.push(elem);
      }
    }

    cancel = dialog.parent().parent().find('button:last').parent();

    // Add custom Tab key handlers, tabindex does not work
    list = $('#image-selector-list').append(list).on('keydown', 'li', function(e) {
        if (e.which == 9) {
          if (rcube_event.get_modifier(e) == SHIFT_KEY) {
            if (!$(this).prev().focus().length) {
              button.focus();
            }
          }
          else if (!$(this).next().focus().length) {
            cancel.focus();
          }

          return false;
        }
      });

    button.keydown(function(e) {
      if (e.which == 9) { // Tab
        if (rcube_event.get_modifier(e) == SHIFT_KEY || !list.find('li:first').focus().length) {
          cancel.focus();
        }

        return false;
      }

      if (e.which == 13) { // Enter
        this.click();
      }
    });

    cancel.keydown(function(e) {
      if (e.which == 9) {
        if (rcube_event.get_modifier(e) != SHIFT_KEY || !list.find('li:last').focus().length) {
          button.focus();
        }

        return false;
      }
    });

    // enable drag-n-drop area
    if (window.FormData) {
      if (!rcmail.env.filedrop) {
        rcmail.env.filedrop = {};
      }
      if (rcmail.gui_objects.filedrop) {
        rcmail.env.old_file_drop = rcmail.gui_objects.filedrop;
      }

      rcmail.gui_objects.filedrop = $('#image-selector');
      rcmail.gui_objects.filedrop.addClass('droptarget')
        .on('dragover dragleave', function(e) {
          e.preventDefault();
          e.stopPropagation();
          $(this)[(e.type == 'dragover' ? 'addClass' : 'removeClass')]('hover');
        })
        .get(0).addEventListener('drop', function(e) { return rcmail.file_dropped(e); }, false);
    }

    // register handler for successful file upload
    if (!rcmail.env['file_dialog_event_' + type]) {
      rcmail.env['file_dialog_event+' + type] = true;
      rcmail.addEventListener('fileuploaded', function(attr) {
        var elem;
        if (elem = ref.file_browser_entry(attr.name, attr.attachment)) {
          list.prepend(elem);
          elem.focus();
        }
      });
    }

    // @todo: upload progress indicator
  };

  // close file browser window
  this.file_browser_close = function(url)
  {
    var input = $('#' + rcmail.env.file_browser_field);

    if (url)
      input.val(url);

    this.editor.windowManager.close();

    input.focus();

    if (rcmail.env.old_file_drop)
      rcmail.gui_objects.filedrop = rcmail.env.old_file_drop;
  };

  // creates file browser entry
  this.file_browser_entry = function(file_id, file)
  {
    if (!file.complete || !file.mimetype) {
      return;
    }

    if (rcmail.file_upload_id) {
      rcmail.set_busy(false, null, rcmail.file_upload_id);
    }

    var rx, img_src;

    switch (rcmail.env.file_browser_type) {
      case 'image':
        rx = /^image\//i;
        break;

      case 'media':
        rx = /^video\//i;
        img_src = rcmail.assets_path('program/resources/tinymce/video.png');
        break;

      default:
        return;
    }

    if (rx.test(file.mimetype)) {
      var path = rcmail.env.comm_path + '&_from=' + rcmail.env.action,
        action = rcmail.env.compose_id ? '&_id=' + rcmail.env.compose_id + '&_action=display-attachment' : '&_action=upload-display',
        href = path + action + '&_file=' + file_id,
        img = $('<img>').attr({title: file.name, src: img_src ? img_src : href + '&_thumbnail=1'});

      return $('<li>').attr({tabindex: 0})
        .data('url', href)
        .append($('<span class="img">').append(img))
        .append($('<span class="name">').text(file.name))
        .click(function() { ref.file_browser_close($(this).data('url')); })
        .keydown(function(e) {
          if (e.which == 13) {
            ref.file_browser_close($(this).data('url'));
          }
        });
    }
  };

  this.file_upload_form = function(clone_form)
  {
    var hint = clone_form ? $(clone_form).find('.hint').text() : '',
      form = $('<form id="imageuploadform">').attr({method: 'post', enctype: 'multipart/form-data'});
      file = $('<input>').attr({name: '_file[]', type: 'file', multiple: true, style: 'opacity:0;height:1px;width:1px'})
        .change(function() { rcmail.upload_file(form, 'upload'); }),
      wrapper = $('<div class="upload-form">')
        .append($('<button>').attr({'class': 'btn btn-secondary attach', href: '#', onclick: "rcmail.upload_input('imageuploadform')"}));

    if (hint)
      wrapper.prepend($('<div class="hint">').text(hint));

    // clone existing upload form
    if (clone_form) {
      file.attr('name', $('input[type="file"]', clone_form).attr('name'));
      form.attr('action', $(clone_form).attr('action'));
    }

    form.append(file).append($('<input>').attr({type: 'hidden', name: '_token', value: rcmail.env.request_token}));

    return wrapper.append(form);
  };
}
