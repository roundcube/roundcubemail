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
    conf = {
      selector: '#' + ($('#' + id).is('.mce_editor') ? id : 'fake-editor-id'),
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

  // register spellchecker for plain text editor
  this.spellcheck_observer = function() {};
  if (config.spellchecker) {
    this.spellchecker = config.spellchecker;
    if (config.spellcheck_observer) {
      this.spellchecker.spelling_state_observer = this.spellcheck_observer = config.spellcheck_observer;
    }
  }

  // minimal editor
  if (config.mode == 'identity') {
    $.extend(conf, {
      plugins: ['autolink charmap code hr link paste tabfocus textcolor'],
      toolbar: 'bold italic underline alignleft aligncenter alignright alignjustify'
        + ' | outdent indent charmap hr link unlink code forecolor'
        + ' | fontselect fontsizeselect'
    });
  }
  // full-featured editor
  else {
    $.extend(conf, {
      plugins: ['autolink charmap code directionality emoticons link image media nonbreaking'
        + ' paste table tabfocus textcolor searchreplace' + (config.spellcheck ? ' spellchecker' : '')],
      toolbar: 'bold italic underline | alignleft aligncenter alignright alignjustify'
        + ' | bullist numlist outdent indent ltr rtl blockquote | forecolor backcolor | fontselect fontsizeselect'
        + ' | link unlink table | emoticons charmap image media | code searchreplace undo redo',
      spellchecker_rpc_url: '../../../../../?_task=utils&_action=spell_html&_remote=1',
      spellchecker_language: rcmail.env.spell_lang,
      accessibility_focus: false,
      file_browser_callback: function(name, url, type, win) { ref.file_browser_callback(name, url, type); },
      // @todo: support more than image (types: file, image, media)
      file_browser_callback_types: 'image'
    });
  }

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
  };

  // textarea identifier
  this.id = id;
  // reference to active editor (if in HTML mode)
  this.editor = null;

  tinymce.init(conf);

  // react to real individual tinyMCE editor init
  this.init_callback = function(event)
  {
    this.editor = event.target;

    if (rcmail.env.action != 'compose') {
      return;
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
      if (fe && fe.id != this.id) {
        // use setTimeout() for IE9 (#1488541)
        window.setTimeout(function() {
          window.focus(); // for WebKit (#1486674)
          fe.focus();
        }, 10);
      }
    }

    // set tabIndex and set focus to element that was focused before
    this.tabindex(fe && fe.id == this.id);
    // Trigger resize (needed for proper editor resizing in some browsers)
    window.setTimeout(function() { $(window).resize(); }, 100);
  };

  // set tabIndex on tinymce editor
  this.tabindex = function(focus)
  {
    if (rcmail.env.task == 'mail' && this.editor) {
      var textarea = this.editor.getElement(),
        node = this.editor.getContentAreaContainer().childNodes[0];

      if (textarea && node)
        node.tabIndex = textarea.tabIndex;
      if (focus)
        this.editor.getBody().focus();
    }
  };

  // switch html/plain mode
  this.toggle = function(ishtml)
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
      if (is_sig)
        content = content.replace(signature.text, sig_mark);

      // convert to html
      result = rcmail.plain2html(content, function(data) {
        // replace signature mark with html version of the signature
        if (is_sig)
          data = data.replace(sig_mark, '<div id="_rc_sig">' + signature.html + '</div>');

        input.val(data);
        tinymce.execCommand('mceAddEditor', false, ref.id);

        setTimeout(function() {
          if (ref.editor) {
            if (rcmail.env.default_font)
              $(ref.editor.getBody()).css('font-family', rcmail.env.default_font);
            // #1486593
            ref.tabindex(true);
          }
        }, 500);
      });
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

      // convert html to text
      result = rcmail.html2plain(content, function(data) {
        tinymce.execCommand('mceRemoveEditor', false, ref.id);
        ref.editor = null;

        // replace signture mark with text version of the signature
        if (is_sig)
          data = data.replace(sig_mark, "\n" + signature.text);

        input.val(data).focus();
      });

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
      if (ed.plugins && ed.plugins.spellchecker && this.spellcheck_active)
        ed.execCommand('mceSpellCheck');
        this.spellcheck_observer();
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

  // resume spellchecking, highlight provided mispellings without new ajax request
  this.spellcheck_resume = function(data)
  {
    var ed = this.editor;

    if (ed) {
      ed.settings.spellchecker_callback = function(name, text, done, error) { done(data); };
      ed.execCommand('mceSpellCheck');
      ed.settings.spellchecker_callback = null;

      this.spellcheck_observer();
    }
    else if (ed = this.spellchecker) {
      ed.prepare(false, true);
      ed.processData(data);
    }
  };

  // get selected (spellcheker) language
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
  this.replace = function(text)
  {
    var ed = this.editor;

    // insert into tinymce editor
    if (ed) {
      ed.getWin().focus(); // correct focus in IE & Chrome
      ed.selection.setContent(rcmail.quote_html(text).replace(/\r?\n/g, '<br/>'), { format:'text' });
    }
    // replace selection in compose textarea
    else if (ed = rcube_find_object(this.id)) {
      var selection = $(ed).is(':focus') ? rcmail.get_input_selection(ed) : { start:0, end:0 },
        inp_value = ed.value;
        pre = inp_value.substring(0, selection.start),
        end = inp_value.substring(selection.end, inp_value.length);

      // insert response text
      ed.value = pre + text + end;

      // set caret after inserted text
      rcmail.set_caret_pos(ed, selection.start + text.length);
      ed.focus();
    }
  };

  // get selected text (if no selection returns all text) from the editor
  this.get_content = function(selected, plain)
  {
    // apply spellcheck changes if spell checker is active
    this.spellcheck_stop();

    var sigstart, ed = this.editor,
      format = plain ? 'text' : 'html',
      text = '', strip = false;

    // get selected text from tinymce editor
    if (ed) {
      ed.getWin().focus(); // correct focus in IE & Chrome
      if (selected)
        text = ed.selection.getContent({format: format});

      if (!text) {
        text = ed.getContent({format: format});
        strip = true;
      }
    }
    // get selected text from compose textarea
    else if (ed = rcube_find_object(this.id)) {
      if (selected && $(ed).is(':focus')) {
        text = rcmail.get_input_selection(ed).text;
      }

      if (!text) {
        text = ed.value;
        strip = true;
      }
    }

    // strip off signature
    if (strip) {
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
    var cursor_pos, p = -1,
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

        if (rcmail.env.top_posting) {
          if (p >= 0) { // in place of removed signature
            message = message.substring(0, p) + sig + message.substring(p, message.length);
            cursor_pos = p - 1;
          }
          else if (!message) { // empty message
            cursor_pos = 0;
            message = '\n\n' + sig;
          }
          else if (pos = rcmail.get_caret_pos(input_message.get(0))) { // at cursor position
            message = message.substring(0, pos) + '\n' + sig + '\n\n' + message.substring(pos, message.length);
            cursor_pos = pos;
          }
          else { // on top
            cursor_pos = 0;
            message = '\n\n' + sig + '\n\n' + message.replace(/^[\r\n]+/, '');
          }
        }
        else {
          message = message.replace(/[\r\n]+$/, '');
          cursor_pos = !rcmail.env.top_posting && message.length ? message.length+1 : 0;
          message += '\n\n' + sig;
        }
      }
      else
        cursor_pos = rcmail.env.top_posting ? 0 : message.length;

      input_message.val(message);

      // move cursor before the signature
      rcmail.set_caret_pos(input_message.get(0), cursor_pos);
    }
    else if (show_sig && rcmail.env.signatures) {  // html
      var sigElem = this.editor.dom.get('_rc_sig');

      // Append the signature as a div within the body
      if (!sigElem) {
        var body = this.editor.getBody(),
          doc = this.editor.getDoc();

        sigElem = doc.createElement('div');
        sigElem.setAttribute('id', '_rc_sig');

        if (rcmail.env.top_posting) {
          // if no existing sig and top posting then insert at caret pos
          this.editor.getWin().focus(); // correct focus in IE & Chrome

          var node = this.editor.selection.getNode();
          if (node.nodeName == 'BODY') {
            // no real focus, insert at start
            body.insertBefore(sigElem, body.firstChild);
            body.insertBefore(doc.createElement('br'), body.firstChild);
          }
          else {
            body.insertBefore(sigElem, node.nextSibling);
            body.insertBefore(doc.createElement('br'), node.nextSibling);
          }
        }
        else {
          body.appendChild(sigElem);
        }
      }

      if (rcmail.env.signatures[id]) {
        sigElem.innerHTML = rcmail.env.signatures[id].html;
      }
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
    var i, elem, dialog, list = [];

    // open image selector dialog
    dialog = this.editor.windowManager.open({
      title: rcmail.gettext('select' + type),
      width: 500,
      height: 300,
      html: '<div id="image-selector-list"><ul></ul></div>'
        + '<div id="image-selector-form"><div id="image-upload-button" class="mce-widget mce-btn" role="button"></div></div>',
      buttons: [{text: 'Cancel', onclick: function() { ref.file_browser_close(); }}]
    });

    rcmail.env.file_browser_field = field_name;
    rcmail.env.file_browser_type = type;

    // fill images list with available images
    for (i in rcmail.env.attachments) {
      if (elem = ref.file_browser_entry(i, rcmail.env.attachments[i])) {
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
    this.hack_file_input(elem, rcmail.gui_objects.uploadform);

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
        if (elem = ref.file_browser_entry(attr.name, attr.attachment)) {
          $('#image-selector-list > ul').prepend(elem);
        }
      });
    }
  };

  // close file browser window
  this.file_browser_close = function(url)
  {
    if (url)
      $('#' + rcmail.env.file_browser_field).val(url);

    this.editor.windowManager.close();

    if (rcmail.env.old_file_drop)
      rcmail.gui_objects.filedrop = rcmail.env.old_file_drop;
  };

  // creates file browser entry
  this.file_browser_entry = function(file_id, file)
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
        .click(function() { ref.file_browser_close($(this).data('url')); });
    }
  };

  // create smart files upload button
  this.hack_file_input = function(elem, clone_form)
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
  };
}
