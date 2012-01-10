/**
 * Roundcube functions for default skin interface
 *
 * Copyright (c) 2011, The Roundcube Dev Team
 *
 * The contents are subject to the Creative Commons Attribution-ShareAlike
 * License. It is allowed to copy, distribute, transmit and to adapt the work
 * by keeping credits to the original autors in the README file.
 * See http://creativecommons.org/licenses/by-sa/3.0/ for details.
 *
 * $Id$
 */


function rcube_mail_ui()
{
  var env = {};
  var popups = {};
  var popupconfig = {
    forwardmenu:        { editable:1 },
    searchmenu:         { editable:1, callback:searchmenu },
    listoptions:        { editable:1 },
    dragmessagemenu:    { sticky:1 },
    groupmenu:          { above:1 },
    mailboxmenu:        { above:1 },
    composeoptionsmenu: { editable:1, overlap:1 },
    // toggle: #1486823, #1486930
    'attachment-form':  { editable:1, above:1, toggle:!bw.ie&&!bw.linux },
    'upload-form':      { editable:1, toggle:!bw.ie&&!bw.linux }
  };

  var me = this;
  var mailviewsplit;
  var compose_headers = {};

  // export public methods
  this.set = setenv;
  this.init = init;
  this.init_tabs = init_tabs;
  this.show_about = show_about;
  this.show_popup = show_popup;
  this.set_searchmod = set_searchmod;
  this.show_uploadform = show_uploadform;
  this.show_header_row = show_header_row;
  this.hide_header_row = hide_header_row;


  /**
   *
   */
  function setenv(key, val)
  {
    env[key] = val;
  }

  /**
   *
   */
  function init()
  {
    rcmail.addEventListener('message', message_displayed);
    
    if (rcmail.env.task == 'mail') {
      rcmail.addEventListener('menu-open', show_listoptions);
      rcmail.addEventListener('menu-save', save_listoptions);
      rcmail.addEventListener('responseafterlist', function(e){ switch_view_mode(rcmail.env.threading ? 'thread' : 'list') });

      var dragmenu = $('#dragmessagemenu');
      if (dragmenu.length) {
        rcmail.gui_object('message_dragmenu', 'dragmessagemenu');
        popups.dragmessagemenu = dragmenu;
      }

      var previewframe = $('#mailpreviewframe').is(':visible');
      $('#mailpreviewtoggle').addClass(previewframe ? 'enabled' : 'closed').click(function(e){ toggle_preview_pane(e); return false });
      $('#maillistmode').addClass(rcmail.env.threading ? '' : 'selected').click(function(e){ switch_view_mode('list'); return false });
      $('#mailthreadmode').addClass(rcmail.env.threading ? 'selected' : '').click(function(e){ switch_view_mode('thread'); return false });

      if (rcmail.env.action == 'show' || rcmail.env.action == 'preview') {
        layout_messageview();
        $("#all-headers").resizable({ handles: 's', minHeight: 50 });
        $('#previewheaderstoggle').click(function(e){ toggle_preview_headers(this); return false });
      }
      else if (rcmail.env.action == 'compose') {
        rcmail.addEventListener('aftertoggle-editor', function(){ window.setTimeout(function(){ layout_composeview() }, 100); });
        rcmail.addEventListener('aftersend-attachment', show_uploadform);
        rcmail.addEventListener('add-recipient', function(p){ show_header_row(p.field, true); });
        layout_composeview();

        $('#composeoptionstoggle').parent().click(function(){
          $('#composeoptionstoggle').toggleClass('enabled');
          $('#composeoptions').toggle();
          layout_composeview();
          return false;
        }).css('cursor', 'pointer');

        new rcube_splitter({ id:'composesplitterv', p1:'#composeview-left', p2:'#composeview-right',
          orientation:'v', relative:true, start:248, min:170, size:12 }).init();
      }
      else if (rcmail.env.action == 'list' || !rcmail.env.action) {
          mailviewsplit = new rcube_splitter({ id:'mailviewsplitter', p1:'#mailview-top', p2:'#mailview-bottom',
            orientation:'h', relative:true, start:310, min:150, size:0, offset:-22 });
          if (previewframe)
            mailviewsplit.init();

          rcmail.addEventListener('setquota', update_quota);
      }

      if ($('#mailview-left').length) {
        new rcube_splitter({ id:'mailviewsplitterv', p1:'#mailview-left', p2:'#mailview-right',
          orientation:'v', relative:true, start:248, min:150, size:12, callback:render_mailboxlist, render:resize_leftcol }).init();
      }
    }
    else if (rcmail.env.task == 'settings') {
      rcmail.addEventListener('init', function(){
        var tab = '#settingstabpreferences';
        if (rcmail.env.action)
          tab = '#settingstab' + (rcmail.env.action.indexOf('identity')>0 ? 'identities' : rcmail.env.action.replace(/\./g, ''));

        $(tab).addClass('selected')
          .children().first().removeAttr('onclick').click(function() { return false; });
      });

      if (rcmail.env.action == 'folders') {
        new rcube_splitter({ id:'folderviewsplitter', p1:'#folderslist', p2:'#folder-details',
          orientation:'v', relative:true, start:305, min:150, size:12 }).init();
      }
      else if (rcmail.env.action == 'identities') {
        new rcube_splitter({ id:'identviewsplitter', p1:'#identitieslist', p2:'#identity-details',
          orientation:'v', relative:true, start:305, min:150, size:12 }).init();
      }
    }
    else if (rcmail.env.task == 'addressbook') {
      rcmail.addEventListener('afterupload-photo', show_uploadform);

      if (rcmail.env.action == '') {
        new rcube_splitter({ id:'addressviewsplitterd', p1:'#addressview-left', p2:'#addressview-right',
          orientation:'v', relative:true, start:226, min:150, size:12, render:resize_leftcol }).init();
        new rcube_splitter({ id:'addressviewsplitter', p1:'#addresslist', p2:'#contacts-box',
          orientation:'v', relative:true, start:296, min:220, size:12 }).init();
      }
    }
    else if (rcmail.env.task == 'login') {
      if (bw.ie && bw.vendver < 9) {
        var popup = $('<div>')
          .addClass('readtext')
          .html("Roundcube will not work well with the crappy browser ya' using. Get yourself a new internet browsing software and don't come back without!<p>Sincerly,<br/>the Roundcube Dev Team</p>")
          .appendTo(document.body)
          .dialog({
            dialogClass: 'alert',
            closeOnEscape: true,
            title: "No way, are you serious?",
            close: function() {
              popup.dialog('destroy').remove();
            },
            width: 450
          });
      }
    }

    // turn a group of fieldsets into tabs
    $('.tabbed').each(function(idx, elem){ init_tabs(elem); })

    $(document.body).bind('mouseup', function(e){
      var config, obj, target = e.target;
      for (var id in popups) {
        obj = popups[id];
        config = popupconfig[id];
        if (obj.is(':visible')
          && target.id != id+'link'
          && !config.toggle
          && (!config.editable || !target_overlaps(target, obj.get(0)))
          && (!config.sticky || !rcube_mouse_is_over(e, obj.get(0)))
        ) {
          var myid = id+'';
          window.setTimeout(function(){ show_popupmenu(myid, false) }, 10);
        }
      }
    })
    .bind('keyup', function(e){
      if (e.keyCode == 27) {
        for (var id in popups) {
          if (popups[id].is(':visible'))
            show_popup(id, false);
        }
      }
    });

    $(window).resize(function(e) {
      // check target due to bugs in jquery
      // http://bugs.jqueryui.com/ticket/7514
      // http://bugs.jquery.com/ticket/9841
      if (e.target == window) resize();
    });
  }

  /**
   * Update UI on window resize
   */
  function resize()
  {
    if (rcmail.env.task == 'mail' && (rcmail.env.action == 'show' || rcmail.env.action == 'preview')) {
      layout_messageview();
    }
    if (rcmail.env.task == 'mail' && rcmail.env.action == 'compose') {
      layout_composeview();
    }
  }

  /**
   * Triggered when a new user message is displayed
   */
  function message_displayed(p)
  {
    // show a popup dialog on errors
    if (p.type == 'error') {
      if (!me.messagedialog) {
        me.messagedialog = $('<div>').addClass('popupdialog');
      }

      var pos = $(p.object).offset();
      me.messagedialog.dialog('close');
      me.messagedialog.html(p.message)
        .dialog({
          resizable: false,
          closeOnEscape: true,
          dialogClass: 'popupmessage ' + p.type,
          title: null,
          close: function() {
            me.messagedialog.dialog('destroy').hide();
          },
          position: ['center', pos.top - 160],
          hide: { effect:'drop', direction:'down' },
          width: 420,
          minHeight: 90
        }).show();
        
      window.setTimeout(function(){ me.messagedialog.dialog('close'); }, p.timeout / 2);
    }
  }


  /**
   * Adjust UI objects of the mail view screen
   */
  function layout_messageview()
  {
    $('#messagecontent').css('top', ($('#messageheader').outerHeight() + 10) + 'px');
    $('#message-objects div a').addClass('button');
    
    if (!$('#attachment-list li').length) {
      $('div.rightcol').hide();
      $('div.leftcol').css('margin-right', '0');
    }
  }


  function render_mailboxlist(splitter)
  {
    // TODO: implement smart shortening of long folder names
  }


  function resize_leftcol(splitter)
  {
    if (splitter)
      $('#quicksearchbar input').css('width', (splitter.pos - 70) + 'px');
  }


  function layout_composeview()
  {
    var body = $('#composebody'),
      bottom = $('#composeview-bottom'),
      w, h;

    bottom.css('height', (bottom.parent().height() - bottom.position().top) + 'px');

    w = body.parent().width() - 8;
    h = body.parent().height() - 36;
    body.width(w).height(h);

    if (window.tinyMCE && tinyMCE.get('composebody')) {
      $('#composebody_tbl').width((w+12)+'px').height('').css('margin-top', '1px');
      $('#composebody_ifr').width((w+12)+'px').height((h-22)+'px');
    }
    else {
      $('#googie_edit_layer').height(h+'px');
    }
  }


  function update_quota(p)
  {
    var y = p.total ? Math.ceil(p.percent / 100 * 20) * 24 : 0;
    $('#quotadisplay').css('background-position', '0 -'+y+'px');
  }


  /**
   * Trigger for popup menus
   */
  function show_popup(popup, show, config)
  {
    // auto-register menu object
    if (config || !popupconfig[popup])
      popupconfig[popup] = $.extend(popupconfig[popup] || {}, config);

    var visible = show_popupmenu(popup, show),
      config = popupconfig[popup];
    if (typeof config.callback == 'function')
      config.callback(visible);
  }

  /**
   * Show/hide a specific popup menu
   */
  function show_popupmenu(popup, show)
  {
    var obj = popups[popup],
      config = popupconfig[popup],
      ref = $('#'+popup+'link'),
      above = config.above;

    if (!obj) {
      obj = popups[popup] = $('#'+popup);
      obj.appendTo(document.body);  // move them to top for proper absolute positioning
    }

    if (!obj || !obj.length)
      return false;

    if (typeof show == 'undefined')
      show = obj.is(':visible') ? false : true;
    else if (config.toggle && show && obj.is(':visible'))
      show = false;

    if (show && ref) {
      var parent = ref.parent(),
        win = $(window),
        pos;

      if (parent.hasClass('dropbutton'))
        ref = parent;

      pos = ref.offset();
      ref.offsetHeight = ref.outerHeight();
      if (!above && pos.top + ref.offsetHeight + obj.height() > win.height())
        above = true;
      if (pos.left + obj.width() > win.width())
        pos.left = win.width() - obj.width() - 12;

      obj.css({ left:pos.left, top:(pos.top + (above ? -obj.height() : ref.offsetHeight)) });
    }

    obj[show?'show':'hide']();

    // hide drop-down elements on buggy browsers
    if (bw.ie6 && config.overlap) {
      $('select').css('visibility', show?'hidden':'inherit');
      $('select', obj).css('visibility', 'inherit');
    }
    
    return show;
  }

  /**
   *
   */
  function target_overlaps(target, elem)
  {
    while (target.parentNode) {
      if (target.parentNode == elem)
        return true;
      target = target.parentNode;
    }
    return false;
  }


  /**
   * Show/hide the preview pane
   */
  function toggle_preview_pane(e)
  {
    var button = $(e.target),
      frame = $('#mailpreviewframe'),
      visible = !frame.is(':visible'),
      splitter = mailviewsplit.pos || parseInt(bw.get_cookie('mailviewsplitter') || 320),
      topstyles, bottomstyles, uid;

    frame.toggle();
    button.removeClass().addClass(visible ? 'enabled' : 'closed');

    if (visible) {
      $('#mailview-top').css({ bottom:'auto' });
      $('#mailview-bottom').css({ height:'auto' });

      rcmail.env.contentframe = 'messagecontframe';
      if (uid = rcmail.message_list.get_single_selection())
        rcmail.show_message(uid, false, true);

      // let the splitter set the correct size and position
      if (mailviewsplit.handle) {
        mailviewsplit.handle.show();
        mailviewsplit.resize();
      }
      else
        mailviewsplit.init();
    }
    else {
      rcmail.env.contentframe = null;
      rcmail.show_contentframe(false);

      $('#mailview-top').css({ height:'auto', bottom:'28px' });
      $('#mailview-bottom').css({ top:'auto', height:'26px' });

      if (mailviewsplit.handle)
        mailviewsplit.handle.hide();
    }

    if (visible && uid && rcmail.message_list)
      rcmail.message_list.scrollto(uid);

    rcmail.command('save-pref', { name:'preview_pane', value:(visible?1:0) });
  }


  /**
   * Switch between short and full headers display in message preview
   */
  function toggle_preview_headers(button)
  {
    $('#preview-shortheaders').toggle();
    var full = $('#preview-allheaders').toggle();
    
    // add toggle button to full headers table
    if (!full.data('mod')) {
      $('<a>').attr('href', '#hide')
        .addClass('iconlink remove')
        .html('Hide')
        .appendTo($('<td>').appendTo($('tr:first', full)))
        .click(function(){ toggle_preview_headers(this);return false });
      full.data('mod', true);
    }
  }


  /**
   *
   */
  function switch_view_mode(mode)
  {
    if (rcmail.env.threading != (mode == 'thread'))
      rcmail.set_list_options(null, undefined, undefined, mode == 'thread' ? 1 : 0);

    $('#maillistmode, #mailthreadmode').removeClass('selected');
    $('#mail'+mode+'mode').addClass('selected');
  }


  /**** popup callbacks ****/

  function searchmenu(show)
  {
    if (show && rcmail.env.search_mods) {
      var n, all,
        obj = popups['searchmenu'],
        list = $('input:checkbox[name="s_mods[]"]', obj),
        mbox = rcmail.env.mailbox,
        mods = rcmail.env.search_mods;

      if (rcmail.env.task == 'mail') {
        mods = mods[mbox] ? mods[mbox] : mods['*'];
        all = 'text';
      }
      else {
        all = '*';
      }

      if (mods[all])
        list.map(function() {
          this.checked = true;
          this.disabled = this.value != all;
        });
      else {
        list.prop('disabled', false).prop('checked', false);
        for (n in mods)
          $('#s_mod_' + n).prop('checked', true);
      }
    }
  }


  /**
   *
   */
  function show_listoptions()
  {
    var $dialog = $('#listoptions');

    // close the dialog
    if ($dialog.is(':visible')) {
      $dialog.dialog('close');
      return;
    }

    // set form values
    $('input[name="sort_col"][value="'+rcmail.env.sort_col+'"]').prop('checked', true);
    $('input[name="sort_ord"][value="DESC"]').prop('checked', rcmail.env.sort_order == 'DESC');
    $('input[name="sort_ord"][value="ASC"]').prop('checked', rcmail.env.sort_order != 'DESC');
    $('input[name="view"][value="thread"]').prop('checked', rcmail.env.threading ? true : false);
    $('input[name="view"][value="list"]').prop('checked', rcmail.env.threading ? false : true);

    // list columns
    var found, cols = $('input[name="list_col[]"]');
    for (var i=0; i < cols.length; i++) {
      if (cols[i].value != 'from') {
        found = $.inArray(cols[i].value, rcmail.env.coltypes) != -1;
      }
      else {
        found = ($.inArray('from', rcmail.env.coltypes) != -1
          || $.inArray('to', rcmail.env.coltypes) != -1);
      }
      $(cols[i]).prop('checked', found);
    }

    $dialog.dialog({
      modal: true,
      resizable: false,
      closeOnEscape: true,
      title: null,
      close: function() {
        $dialog.dialog('destroy').hide();
      },
      width: 650
    }).show();
  }


  /**
   *
   */
  function save_listoptions()
  {
    $('#listoptions').dialog('close');

    var sort = $('input[name="sort_col"]:checked').val(),
      ord = $('input[name="sort_ord"]:checked').val(),
      thread = $('input[name="view"]:checked').val(),
      cols = $('input[name="list_col[]"]:checked')
        .map(function(){ return this.value; }).get();

    rcmail.set_list_options(cols, sort, ord, thread == 'thread' ? 1 : 0);
  }


  /**
   *
   */
  function set_searchmod(elem)
  {
    var all, m, task = rcmail.env.task,
      mods = rcmail.env.search_mods,
      mbox = rcmail.env.mailbox;

    if (!mods)
      mods = {};

    if (task == 'mail') {
      if (!mods[mbox])
        mods[mbox] = rcube_clone_object(mods['*']);
      m = mods[mbox];
      all = 'text';
    }
    else { //addressbook
      m = mods;
      all = '*';
    }

    if (!elem.checked)
      delete(m[elem.value]);
    else
      m[elem.value] = 1;

    // mark all fields
    if (elem.value != all)
      return;

    $('input:checkbox[name="s_mods[]"]').map(function() {
      if (this == elem)
        return;

      this.checked = true;
      if (elem.checked) {
        this.disabled = true;
        delete m[this.value];
      }
      else {
        this.disabled = false;
        m[this.value] = 1;
      }
    });
  }


  function show_uploadform()
  {
    var $dialog = $('#upload-dialog');

    // close the dialog
    if ($dialog.is(':visible')) {
      $dialog.dialog('close');
      return;
    }
    
    // add icons to clone file input field
    if (rcmail.env.action == 'compose' && !$dialog.data('extended')) {
      $('<a>')
        .addClass('iconlink add')
        .attr('href', '#add')
        .html('Add')
        .appendTo($('input[type="file"]', $dialog).parent())
        .click(add_uploadfile);
      $dialog.data('extended', true);
    }

    $dialog.dialog({
      modal: true,
      resizable: false,
      closeOnEscape: true,
      title: $dialog.attr('title'),
      close: function() {
        try { $('#upload-dialog form').get(0).reset(); }
        catch(e){ }  // ignore errors

        $dialog.dialog('destroy').hide();
        $('div.addline', $dialog).remove();
      },
      width: 480
    }).show();

    if (!document.all)
      $('input[type=file]', $dialog).first().click();
  }

  function add_uploadfile(e)
  {
    var div = $(this).parent();
    var clone = div.clone().addClass('addline').insertAfter(div);
    clone.children('.iconlink').click(add_uploadfile);
    clone.children('input').val('');

    if (!document.all)
      $('input[type=file]', clone).click();
  }


  /**
   *
   */
  function show_header_row(which, updated)
  {
    var row = $('#compose-' + which);
    if (row.is(':visible'))
      return;  // nothing to be done here

    if (compose_headers[which] && !updated)
      $('#_' + which).val(compose_headers[which]);

    row.show();
    $('#' + which + '-link').hide();
    layout_composeview();
    return false;
  }

  /**
   *
   */
  function hide_header_row(which)
  {
    // copy and clear field value
    var field = $('#_' + which);
    compose_headers[which] = field.val();
    field.val('');

    $('#compose-' + which).hide();
    $('#' + which + '-link').show();
    layout_composeview();
    return false;
  }


  /**
   * Fieldsets-to-tabs converter
   */
  function init_tabs(elem, current)
  {
    var id = elem.id,
      content = $(elem),
      fs = content.children('fieldset');

    if (!fs.length)
      return;

    if (!id) {
      id = 'rcmtabcontainer';
      elem.attr('id', id);
    }

    // first hide not selected tabs
    current = current || 0;
    fs.each(function(idx) { if (idx != current) $(this).hide(); });

    // create tabs container
    var tabs = $('<div>').addClass('tabsbar').prependTo(content);

    // convert fildsets into tabs
    fs.each(function(idx) {
      var tab, a, elm = $(this), legend = elm.children('legend');

      // create a tab
      a   = $('<a>').text(legend.text()).attr('href', '#');
      tab = $('<span>').attr({'id': 'tab'+idx, 'class': 'tablink'})
        .click(function() { show_tab(id, idx); return false })

      // remove legend
      legend.remove();
      // style fieldset
      elm.addClass('tab');
      // style selected tab
      if (idx == current)
        tab.addClass('selected');

      // add the tab to container
      tab.append(a).appendTo(tabs);
    });
  }

  function show_tab(id, index)
  {
    var fs = $('#'+id).children('fieldset');

    fs.each(function(idx) {
      // Show/hide fieldset (tab content)
      $(this)[index==idx ? 'show' : 'hide']();
      // Select/unselect tab
      $('#tab'+idx).toggleClass('selected', idx==index);
    });
  }

  /**
   * Show about page as jquery UI dialog
   */
  function show_about(elem)
  {
    var frame = $('<iframe>').attr('id', 'aboutframe')
      .attr('src', rcmail.url('settings/about'))
      .appendTo(document.body);

    var h = Math.floor($(window).height() * 0.75);
    var buttons = {};
    var supportln = $('#supportlink');
    if (supportln.length && (env.supporturl = supportln.attr('href')))
      buttons[supportln.html()] = function(e){ env.supporturl.indexOf('mailto:') < 0 ? window.open(env.supporturl) : location.href = env.supporturl };

    frame.dialog({
      modal: true,
      resizable: false,
      closeOnEscape: true,
      title: elem ? elem.title || elem.innerHTML : null,
      close: function() {
        frame.dialog('destroy').remove();
      },
      buttons: buttons,
      width: 640,
      height: h
    }).width(640);
  }
}



/**
 * Roundcube splitter GUI class
 *
 * @constructor
 */
function rcube_splitter(p)
{
  this.p = p;
  this.id = p.id;
  this.horizontal = (p.orientation == 'horizontal' || p.orientation == 'h');
  this.halfsize = (p.size !== undefined ? p.size : 10) / 2;
  this.pos = p.start || 0;
  this.min = p.min || 20;
  this.offset = p.offset || 0;
  this.relative = p.relative ? true : false;
  this.drag_active = false;
  this.render = p.render;
  this.callback = p.callback;

  var me = this;

  this.init = function()
  {
    this.p1 = $(this.p.p1);
    this.p2 = $(this.p.p2);

    // create and position the handle for this splitter
    this.p1pos = this.relative ? this.p1.position() : this.p1.offset();
    this.p2pos = this.relative ? this.p2.position() : this.p2.offset();
    this.handle = $('<div>')
      .attr('id', this.id)
      .attr('unselectable', 'on')
      .addClass('splitter ' + (this.horizontal ? 'splitter-h' : 'splitter-v'))
      .appendTo(this.p1.parent())
      .bind('mousedown', onDragStart);

    if (this.horizontal) {
      var top = this.p1pos.top + this.p1.outerHeight();
      this.handle.css({ left:'0px', top:top+'px' });
    }
    else {
      var left = this.p1pos.left + this.p1.outerWidth();
      this.handle.css({ left:left+'px', top:'0px' });
    }

    this.elm = this.handle.get(0);

    // listen to window resize on IE
    if (bw.ie)
      $(window).resize(function(e){ onResize(e) });

    // read saved position from cookie
    var cookie = bw.get_cookie(this.id);
    if (cookie && !isNaN(cookie)) {
      this.pos = parseFloat(cookie);
      this.resize();
    }
    else if (this.pos) {
      this.resize();
      this.set_cookie();
    }
  };

  /**
   * Set size and position of all DOM objects
   * according to the saved splitter position
   */
  this.resize = function()
  {
    if (this.horizontal) {
      this.p1.css('height', Math.floor(this.pos - this.p1pos.top - this.halfsize) + 'px');
      this.p2.css('top', Math.ceil(this.pos + this.halfsize + 2) + 'px');
      this.handle.css('top', Math.round(this.pos - this.halfsize + this.offset)+'px');
      if (bw.ie) {
        var new_height = parseInt(this.p2.parent().outerHeight(), 10) - parseInt(this.p2.css('top'), 10) - (bw.ie8 ? 2 : 0);
        this.p2.css('height', (new_height > 0 ? new_height : 0) + 'px');
      }
    }
    else {
      this.p1.css('width', Math.floor(this.pos - this.p1pos.left - this.halfsize) + 'px');
      this.p2.css('left', Math.ceil(this.pos + this.halfsize) + 'px');
      this.handle.css('left', Math.round(this.pos - this.halfsize + this.offset + 3)+'px');
      if (bw.ie) {
        var new_width = parseInt(this.p2.parent().outerWidth(), 10) - parseInt(this.p2.css('left'), 10) ;
        this.p2.css('width', (new_width > 0 ? new_width : 0) + 'px');
      }
    }

    this.p2.resize();
    this.p1.resize();

    // also resize iframe covers
    if (this.drag_active) {
      $('iframe').each(function(i, elem) {
        var pos = $(this).offset();
        $('#iframe-splitter-fix-'+i).css({ top: pos.top+'px', left: pos.left+'px', width:elem.offsetWidth+'px', height: elem.offsetHeight+'px' });
      });
    }

    if (typeof this.render == 'function')
      this.render(this);
  };

  /**
   * Handler for mousedown events
   */
  function onDragStart(e)
  {
    // disable text selection while dragging the splitter
    if (bw.konq || bw.chrome || bw.safari)
      document.body.style.webkitUserSelect = 'none';

    me.p1pos = me.relative ? me.p1.position() : me.p1.offset();
    me.p2pos = me.relative ? me.p2.position() : me.p2.offset();
    me.drag_active = true;

    // start listening to mousemove events
    $(document).bind('mousemove.'+this.id, onDrag).bind('mouseup.'+this.id, onDragStop);

    // enable dragging above iframes
    $('iframe').each(function(i, elem) {
      $('<div>')
        .attr('id', 'iframe-splitter-fix-'+i)
        .addClass('iframe-splitter-fix')
        .css({ background: '#fff',
          width: elem.offsetWidth+'px', height: elem.offsetHeight+'px',
          position: 'absolute', opacity: '0.001', zIndex: 1000
        })
        .css($(this).offset())
        .appendTo('body');
      });
  };

  /**
   * Handler for mousemove events
   */
  function onDrag(e)
  {
    if (!me.drag_active)
      return false;

    var pos = rcube_event.get_mouse_pos(e);

    if (me.relative) {
      var parent = me.p1.parent().offset();
      pos.x -= parent.left;
      pos.y -= parent.top;
    }

    if (me.horizontal) {
      if (((pos.y - me.halfsize) > me.p1pos.top) && ((pos.y + me.halfsize) < (me.p2pos.top + me.p2.outerHeight()))) {
        me.pos = Math.max(me.min, pos.y - me.offset);
        me.resize();
      }
    }
    else {
      if (((pos.x - me.halfsize) > me.p1pos.left) && ((pos.x + me.halfsize) < (me.p2pos.left + me.p2.outerWidth()))) {
        me.pos = Math.max(me.min, pos.x - me.offset);
        me.resize();
      }
    }

    me.p1pos = me.relative ? me.p1.position() : me.p1.offset();
    me.p2pos = me.relative ? me.p2.position() : me.p2.offset();
    return false;
  };

  /**
   * Handler for mouseup events
   */
  function onDragStop(e)
  {
    // resume the ability to highlight text
    if (bw.konq || bw.chrome || bw.safari)
      document.body.style.webkitUserSelect = 'auto';

    // cancel the listening for drag events
    $(document).unbind('.'+me.id);
    me.drag_active = false;

    // remove temp divs
    $('div.iframe-splitter-fix').remove();

    me.set_cookie();

    if (typeof me.callback == 'function')
      me.callback(me);

    return bw.safari ? true : rcube_event.cancel(e);
  };

  /**
   * Handler for window resize events
   */
  function onResize(e)
  {
    if (me.horizontal) {
      var new_height = parseInt(me.p2.parent().outerHeight(), 10) - parseInt(me.p2[0].style.top, 10) - (bw.ie8 ? 2 : 0);
      me.p2.css('height', (new_height > 0 ? new_height : 0) +'px');
    }
    else {
      var new_width = parseInt(me.p2.parent().outerWidth(), 10) - parseInt(me.p2[0].style.left, 10);
      me.p2.css('width', (new_width > 0 ? new_width : 0) + 'px');
    }
  };

  /**
   * Saves splitter position in cookie
   */
  this.set_cookie = function()
  {
    var exp = new Date();
    exp.setYear(exp.getFullYear() + 1);
    bw.set_cookie(this.id, this.pos, exp);
  };

} // end class rcube_splitter


