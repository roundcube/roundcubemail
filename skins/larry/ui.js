/**
 * Roundcube functions for default skin interface
 *
 * Copyright (c) 2013, The Roundcube Dev Team
 *
 * The contents are subject to the Creative Commons Attribution-ShareAlike
 * License. It is allowed to copy, distribute, transmit and to adapt the work
 * by keeping credits to the original autors in the README file.
 * See http://creativecommons.org/licenses/by-sa/3.0/ for details.
 */


function rcube_mail_ui()
{
  var env = {};
  var popups = {};
  var popupconfig = {
    forwardmenu:        { editable:1 },
    searchmenu:         { editable:1, callback:searchmenu },
    attachmentmenu:     { },
    listoptions:        { editable:1 },
    dragmenu:           { sticky:1 },
    groupmenu:          { above:1 },
    mailboxmenu:        { above:1 },
    spellmenu:          { callback: spellmenu },
    // toggle: #1486823, #1486930
    'attachment-form':  { editable:1, above:1, toggle:!bw.ie&&!bw.linux },
    'upload-form':      { editable:1, toggle:!bw.ie&&!bw.linux }
  };

  var me = this;
  var mailviewsplit;
  var compose_headers = {};
  var prefs;

  // export public methods
  this.set = setenv;
  this.init = init;
  this.init_tabs = init_tabs;
  this.show_about = show_about;
  this.show_popup = show_popup;
  this.add_popup = add_popup;
  this.set_searchmod = set_searchmod;
  this.show_uploadform = show_uploadform;
  this.show_header_row = show_header_row;
  this.hide_header_row = hide_header_row;
  this.update_quota = update_quota;
  this.get_pref = get_pref;
  this.save_pref = save_pref;


  // set minimal mode on small screens (don't wait for document.ready)
  if (window.$ && document.body) {
    var minmode = get_pref('minimalmode');
    if (parseInt(minmode) || (minmode === null && $(window).height() < 850)) {
      $(document.body).addClass('minimal');
    }

    if (bw.tablet) {
      $('#viewport').attr('content', "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0");
    }
  }


  /**
   *
   */
  function setenv(key, val)
  {
    env[key] = val;
  }

  /**
   * Get preference stored in browser
   */
  function get_pref(key)
  {
    if (!prefs) {
      prefs = rcmail.local_storage_get_item('prefs.larry', {});
    }

    // fall-back to cookies
    if (prefs[key] == null) {
      var cookie = rcmail.get_cookie(key);
      if (cookie != null) {
        prefs[key] = cookie;

        // copy value to local storage and remove cookie (if localStorage is supported)
        if (rcmail.local_storage_set_item('prefs.larry', prefs)) {
          rcmail.set_cookie(key, cookie, new Date());  // expire cookie
        }
      }
    }

    return prefs[key];
  }

  /**
   * Saves preference value to browser storage
   */
  function save_pref(key, val)
  {
    prefs[key] = val;

    // write prefs to local storage (if supported)
    if (!rcmail.local_storage_set_item('prefs.larry', prefs)) {
      // store value in cookie
      var exp = new Date();
      exp.setYear(exp.getFullYear() + 1);
      rcmail.set_cookie(key, val, exp);
    }
  }

  /**
   * Initialize UI
   * Called on document.ready
   */
  function init()
  {
    rcmail.addEventListener('message', message_displayed);

    /*** prepare minmode functions ***/
    $('#taskbar a').each(function(i,elem){
      $(elem).append('<span class="tooltip">' + $('.button-inner', this).html() + '</span>')
    });

    $('#taskbar .minmodetoggle').click(function(e){
      var ismin = $(document.body).toggleClass('minimal').hasClass('minimal');
      save_pref('minimalmode', ismin?1:0);
      $(window).resize();
    });

    /***  mail task  ***/
    if (rcmail.env.task == 'mail') {
      rcmail.addEventListener('menu-open', menu_open)
        .addEventListener('menu-save', menu_save)
        .addEventListener('responseafterlist', function(e){ switch_view_mode(rcmail.env.threading ? 'thread' : 'list') });

      var dragmenu = $('#dragmessagemenu');
      if (dragmenu.length) {
        rcmail.gui_object('dragmenu', 'dragmessagemenu');
        popups.dragmenu = dragmenu;
      }

      if (rcmail.env.action == 'show' || rcmail.env.action == 'preview') {
        rcmail.addEventListener('enable-command', enable_command)
          .addEventListener('aftershow-headers', function() { layout_messageview(); })
          .addEventListener('afterhide-headers', function() { layout_messageview(); });
        $('#previewheaderstoggle').click(function(e){ toggle_preview_headers(); return false });

        // add menu link for each attachment
        $('#attachment-list > li').each(function() {
          $(this).append($('<a class="drop"></a>').click(function() { attachmentmenu(this); }));
        });

        if (get_pref('previewheaders') == '1') {
          toggle_preview_headers();
        }
      }
      else if (rcmail.env.action == 'compose') {
        rcmail.addEventListener('aftersend-attachment', show_uploadform)
          .addEventListener('add-recipient', function(p){ show_header_row(p.field, true); })
          .addEventListener('aftertoggle-editor', function(e){
            window.setTimeout(function(){ layout_composeview() }, 200);
            if (e && e.mode)
              $("select[name='editorSelector']").val(e.mode);
          });

        // Show input elements with non-empty value
        var f, v, field, fields = ['cc', 'bcc', 'replyto', 'followupto'];
        for (f=0; f < fields.length; f++) {
          v = fields[f]; field = $('#_'+v);
          if (field.length) {
            field.on('change', {v: v}, function(e) { if (this.value) show_header_row(e.data.v, true); });
            if (field.val() != '')
              show_header_row(v, true);
          }
        }

        $('#composeoptionstoggle').click(function(){
          $('#composeoptionstoggle').toggleClass('remove');
          $('#composeoptions').toggle();
          layout_composeview();
          save_pref('composeoptions', $('#composeoptions').is(':visible') ? '1' : '0');
          return false;
        }).css('cursor', 'pointer');

        if (get_pref('composeoptions') !== '0') {
          $('#composeoptionstoggle').click();
        }

        // adjust hight when textarea starts to scroll
        $("textarea[name='_to'], textarea[name='_cc'], textarea[name='_bcc']").change(function(e){ adjust_compose_editfields(this); }).change();
        rcmail.addEventListener('autocomplete_insert', function(p){ adjust_compose_editfields(p.field); });

        // toggle compose options if opened in new window and they were visible before
        var opener_rc = rcmail.opener();
        if (opener_rc && opener_rc.env.action == 'compose' && $('#composeoptionstoggle', opener.document).hasClass('remove'))
          $('#composeoptionstoggle').click();

        new rcube_splitter({ id:'composesplitterv', p1:'#composeview-left', p2:'#composeview-right',
          orientation:'v', relative:true, start:248, min:170, size:12, render:layout_composeview }).init();
      }
      else if (rcmail.env.action == 'list' || !rcmail.env.action) {
        var previewframe = $('#mailpreviewframe').is(':visible');
        $('#mailpreviewtoggle').addClass(previewframe ? 'enabled' : 'closed').click(function(e){ toggle_preview_pane(e); return false });
        $('#maillistmode').addClass(rcmail.env.threading ? '' : 'selected').click(function(e){ switch_view_mode('list'); return false });
        $('#mailthreadmode').addClass(rcmail.env.threading ? 'selected' : '').click(function(e){ switch_view_mode('thread'); return false });

        mailviewsplit = new rcube_splitter({ id:'mailviewsplitter', p1:'#mailview-top', p2:'#mailview-bottom',
          orientation:'h', relative:true, start:310, min:150, size:12, offset:4 });
        if (previewframe)
          mailviewsplit.init();

        rcmail.addEventListener('setquota', update_quota)
          .addEventListener('enable-command', enable_command)
          .addEventListener('afterimport-messages', show_uploadform);
      }
      else if (rcmail.env.action == 'get') {
        new rcube_splitter({ id:'mailpartsplitterv', p1:'#messagepartheader', p2:'#messagepartcontainer',
          orientation:'v', relative:true, start:226, min:150, size:12}).init();
      }

      if ($('#mailview-left').length) {
        new rcube_splitter({ id:'mailviewsplitterv', p1:'#mailview-left', p2:'#mailview-right',
          orientation:'v', relative:true, start:226, min:150, size:12, callback:render_mailboxlist, render:resize_leftcol }).init();
      }
    }
    /***  settings task  ***/
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
          orientation:'v', relative:true, start:266, min:180, size:12 }).init();

        new rcube_scroller('#folderslist-content', '#folderslist-header', '#folderslist-footer');

        rcmail.addEventListener('setquota', update_quota);
      }
      else if (rcmail.env.action == 'identities') {
        new rcube_splitter({ id:'identviewsplitter', p1:'#identitieslist', p2:'#identity-details',
          orientation:'v', relative:true, start:266, min:180, size:12 }).init();
      }
      else if (rcmail.env.action == 'responses') {
        new rcube_splitter({ id:'responseviewsplitter', p1:'#identitieslist', p2:'#identity-details',
          orientation:'v', relative:true, start:266, min:180, size:12 }).init();
      }
      else if (rcmail.env.action == 'preferences' || !rcmail.env.action) {
        new rcube_splitter({ id:'prefviewsplitter', p1:'#sectionslist', p2:'#preferences-box',
          orientation:'v', relative:true, start:266, min:180, size:12 }).init();
      }
      else if (rcmail.env.action == 'edit-prefs') {
        $('<a href="#toggle">&#9660;</a>')
            .addClass('advanced-toggle')
            .appendTo('#preferences-details fieldset.advanced legend');

          $('#preferences-details fieldset.advanced legend').click(function(e){
            var collapsed = $(this).hasClass('collapsed'),
              toggle = $('.advanced-toggle', this).html(collapsed ? '&#9650;' : '&#9660;');
            $(this)
              .toggleClass('collapsed')
              .closest('fieldset').children('.propform').toggle()
          }).addClass('collapsed')
      }
    }
    /***  addressbook task  ***/
    else if (rcmail.env.task == 'addressbook') {
      rcmail.addEventListener('afterupload-photo', show_uploadform)
        .addEventListener('beforepushgroup', push_contactgroup)
        .addEventListener('beforepopgroup', pop_contactgroup);

      if (rcmail.env.action == '') {
        new rcube_splitter({ id:'addressviewsplitterd', p1:'#addressview-left', p2:'#addressview-right',
          orientation:'v', relative:true, start:226, min:150, size:12, render:resize_leftcol }).init();
        new rcube_splitter({ id:'addressviewsplitter', p1:'#addresslist', p2:'#contacts-box',
          orientation:'v', relative:true, start:286, min:270, size:12 }).init();
      }

      var dragmenu = $('#dragcontactmenu');
      if (dragmenu.length) {
        rcmail.gui_object('dragmenu', 'dragcontactmenu');
        popups.dragmenu = dragmenu;
      }
    }

    // turn a group of fieldsets into tabs
    $('.tabbed').each(function(idx, elem){ init_tabs(elem); })

    // decorate select elements
    $('select.decorated').each(function(){
      if (bw.opera) {
        $(this).removeClass('decorated');
        return;
      }

      var select = $(this),
        parent = select.parent(),
        height = Math.max(select.height(), 26) - 2,
        width = select.width() - 22,
        title = $('option', this).first().text();

      if ($('option:selected', this).val() != '')
        title = $('option:selected', this).text();

      var overlay = $('<a class="menuselector"><span class="handle">' + title + '</span></a>')
        .css('position', 'absolute')
        .offset(select.position())
        .insertAfter(select);

      overlay.children().width(width).height(height).css('line-height', (height - 1) + 'px');

      if (parent.css('position') != 'absolute')
        parent.css('position', 'relative');

      // re-set original select width to fix click action and options width in some browsers
      select.width(overlay.width())
        .change(function() {
          var val = $('option:selected', this).text();
          $(this).next().children().text(val);
        });
    });

    // set min-width to show all toolbar buttons
    var screen = $('body > div.minwidth');
    if (screen.length) {
      screen.css('min-width', $('.toolbar').width() + $('#quicksearchbar').width() + $('#searchfilter').width() + 30);
    }

    $(document.body)
      .bind('mouseup', body_mouseup)
      .bind('keyup', function(e){
        if (e.keyCode == 27) {
          for (var id in popups) {
            if (popups[id].is(':visible'))
              show_popup(id, false);
          }
        }
      });

    $('iframe').load(function(e){
      // this = iframe
      try {
        var doc = this.contentDocument ? this.contentDocument : this.contentWindow ? this.contentWindow.document : null;
        $(doc).mouseup(body_mouseup);
      }
      catch (e) {
        // catch possible "Permission denied" error in IE
      };
    })
    .contents().mouseup(body_mouseup);

    // don't use $(window).resize() due to some unwanted side-effects
    window.onresize = resize;
    resize();
  }

  /**
   * Handler for mouse-up events on the document body.
   * This will close all open popup menus
   */
  function body_mouseup(e)
  {
    var config, obj, target = e.target;

    if (target.className == 'inner')
        target = e.target.parentNode;

    for (var id in popups) {
      obj = popups[id];
      config = popupconfig[id];
      if (obj.is(':visible')
        && target.id != id+'link'
        && target != obj.get(0)  // check if scroll bar was clicked (#1489832)
        && !config.toggle
        && (!config.editable || !target_overlaps(target, obj.get(0)))
        && (!config.sticky || !rcube_mouse_is_over(e, obj.get(0)))
        && !$(target).is('.folder-selector-link')
      ) {
        var myid = id+'';
        window.setTimeout(function() { show_popupmenu(myid, false); }, 10);
      }
    }
  }

  /**
   * Update UI on window resize
   */
  function resize(e)
  {
    // resize in intervals to prevent lags and double onresize calls in Chrome (#1489005)
    var interval = e ? 10 : 0;

    if (rcmail.resize_timeout)
      window.clearTimeout(rcmail.resize_timeout);

    rcmail.resize_timeout = window.setTimeout(function() {
      if (rcmail.env.task == 'mail') {
        if (rcmail.env.action == 'show' || rcmail.env.action == 'preview')
          layout_messageview();
        else if (rcmail.env.action == 'compose')
          layout_composeview();
      }

      // make iframe footer buttons float if scrolling is active
      $('body.iframe .footerleft').each(function(){
        var footer = $(this),
          body = $(document.body),
          floating = footer.hasClass('floating'),
          overflow = body.outerHeight(true) > $(window).height();

        if (overflow != floating) {
          var action = overflow ? 'addClass' : 'removeClass';
          footer[action]('floating');
          body[action]('floatingbuttons');
        }
      });
    }, interval);
  }

  /**
   * Triggered when a new user message is displayed
   */
  function message_displayed(p)
  {
    var siblings = $(p.object).siblings('div');
    if (siblings.length)
      $(p.object).insertBefore(siblings.first());

    // show a popup dialog on errors
    if (p.type == 'error' && rcmail.env.task != 'login') {
      // hide original message object, we don't want both
      rcmail.hide_message(p.object);

      if (me.message_timer) {
        window.clearTimeout(me.message_timer);
      }
      if (!me.messagedialog) {
        me.messagedialog = $('<div>').addClass('popupdialog').hide();
      }

      var msg = p.message,
        dialog_close = function() {
          // check if dialog is still displayed, to prevent from js error
          me.messagedialog.is(':visible') && me.messagedialog.dialog('destroy').hide();
        };

      if (me.messagedialog.is(':visible'))
        msg = me.messagedialog.html() + '<p>' + p.message + '</p>';

      me.messagedialog.html(msg)
        .dialog({
          resizable: false,
          closeOnEscape: true,
          dialogClass: 'popupmessage ' + p.type,
          title: env.errortitle,
          close: dialog_close,
          position: ['center', 'center'],
          hide: {effect: 'fadeOut'},
          width: 420,
          minHeight: 90
        }).show();

      if (p.timeout > 0)
        me.message_timer = window.setTimeout(dialog_close, p.timeout);
    }
  }


  /**
   * Adjust UI objects of the mail view screen
   */
  function layout_messageview()
  {
    $('#messagecontent').css('top', ($('#messageheader').outerHeight() + 1) + 'px');
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
    // STUB
  }

  function adjust_compose_editfields(elem)
  {
    if (elem.nodeName == 'TEXTAREA') {
      var $elem = $(elem), line_height = 14,  // hard-coded because some browsers only provide the outer height in elem.clientHeight
        content_height = elem.scrollHeight,
        rows = elem.value.length > 80 && content_height > line_height*1.5 ? 2 : 1;
      $elem.css('height', (line_height*rows) + 'px');
      layout_composeview();
    }
  }

  function layout_composeview()
  {
    var body = $('#composebody'),
      form = $('#compose-content'),
      bottom = $('#composeview-bottom'),
      w, h, bh, ovflw, btns = 0,
      minheight = 300,

    bh = (form.height() - bottom.position().top);
    ovflw = minheight - bh;
    btns = ovflw > -100 ? 0 : 40;
    bottom.css('height', Math.max(minheight, bh) + 'px');
    form.css('overflow', ovflw > 0 ? 'auto' : 'hidden');

    w = body.parent().width() - 5;
    h = body.parent().height() - 16;
    body.width(w).height(h);

    $('#composebody_tbl').width((w+8)+'px').height('').css('margin-top', '1px');
    $('#composebody_ifr').width((w+8)+'px').height((h-40)+'px');
    $('#googie_edit_layer').height(h+'px');
//    $('#composebodycontainer')[(btns ? 'addClass' : 'removeClass')]('buttons');
//    $('#composeformbuttons')[(btns ? 'show' : 'hide')]();

    var abooks = $('#directorylist');
    if (abooks.length)
      $('#compose-contacts .scroller').css('top', abooks.position().top + abooks.outerHeight());
  }


  function update_quota(p)
  {
    var step = 24, step_count = 20,
      y = p.total ? Math.ceil(p.percent / 100 * step_count) * step : 0;

    // never show full-circle if quota is close to 100% but below.
    if (p.total && y == step * step_count && p.percent < 100)
      y -= step;

    $('#quotadisplay').css('background-position', '0 -'+y+'px');
  }


  function enable_command(p)
  {
    if (p.command == 'reply-list' && rcmail.env.reply_all_mode == 1) {
      var label = rcmail.gettext(p.status ? 'replylist' : 'replyall');
      if (rcmail.env.action == 'preview')
        $('a.button.replyall').attr('title', label);
      else
        $('a.button.reply-all').text(label).attr('title', label);
    }
  }


  /**
   * Register a popup menu
   */
  function add_popup(popup, config)
  {
    var obj = popups[popup] = $('#'+popup);
    obj.appendTo(document.body);  // move it to top for proper absolute positioning

    if (obj.length)
      popupconfig[popup] = $.extend(popupconfig[popup] || {}, config || {});
  }

  /**
   * Trigger for popup menus
   */
  function show_popup(popup, show, config)
  {
    // auto-register menu object
    if (config || !popupconfig[popup])
      add_popup(popup, config);

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
      ref = $(config.link ? config.link : '#'+popup+'link'),
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

    if (show && ref.length) {
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
      splitter = mailviewsplit.pos || parseInt(get_pref('mailviewsplitter') || 320),
      topstyles, bottomstyles, uid;

    frame.toggle();
    button.removeClass().addClass(visible ? 'enabled' : 'closed');

    if (visible) {
      $('#mailview-top').removeClass('fullheight').css({ bottom:'auto' });
      $('#mailview-bottom').css({ height:'auto' }).show();

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

      $('#mailview-top').addClass('fullheight').css({ height:'auto', bottom:'0px' });
      $('#mailview-bottom').css({ top:'auto', height:'0px' }).hide();

      if (mailviewsplit.handle)
        mailviewsplit.handle.hide();
    }

    if (rcmail.message_list) {
      if (visible && uid)
          rcmail.message_list.scrollto(uid);
      rcmail.message_list.resize();
    }

    rcmail.command('save-pref', { name:'preview_pane', value:(visible?1:0) });
  }


  /**
   * Switch between short and full headers display in message preview
   */
  function toggle_preview_headers()
  {
    $('#preview-shortheaders').toggle();
    var full = $('#preview-allheaders').toggle(),
      button = $('a#previewheaderstoggle');

    // add toggle button to full headers table
    if (full.is(':visible'))
      button.attr('href', '#hide').removeClass('add').addClass('remove')
    else
      button.attr('href', '#details').removeClass('remove').addClass('add')

    save_pref('previewheaders', full.is(':visible') ? '1' : '0');
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

  function menu_open(p)
  {
    if (p && p.props && p.props.menu == 'attachmentmenu')
      show_popupmenu('attachmentmenu');
    else
      show_listoptions();
  }

  function menu_save(prop)
  {
    save_listoptions();
  }

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

  function attachmentmenu(elem)
  {
    var id = elem.parentNode.id.replace(/^attach/, '');

    $('#attachmenuopen').unbind('click').attr('onclick', '').click(function(e) {
      return rcmail.command('open-attachment', id, this);
    });

    $('#attachmenudownload').unbind('click').attr('onclick', '').click(function() {
      rcmail.command('download-attachment', id, this);
    });

    popupconfig.attachmentmenu.link = elem;
    rcmail.command('menu-open', {menu: 'attachmentmenu', id: id});
  }

  function spellmenu(show)
  {
    var link, li,
      lang = rcmail.spellcheck_lang(),
      menu = popups.spellmenu,
      ul = $('ul', menu);

    if (!ul.length) {
      ul = $('<ul class="toolbarmenu selectable">');

      for (i in rcmail.env.spell_langs) {
        li = $('<li>');
        link = $('<a href="#"></a>').text(rcmail.env.spell_langs[i])
          .addClass('active').data('lang', i)
          .click(function() {
            rcmail.spellcheck_lang_set($(this).data('lang'));
          });

        link.appendTo(li);
        li.appendTo(ul);
      }

      ul.appendTo(menu);
    }

    // select current language
    $('li', ul).each(function() {
      var el = $('a', this);
      if (el.data('lang') == lang)
        el.addClass('selected');
      else if (el.hasClass('selected'))
        el.removeClass('selected');
    });
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

    // set checkboxes
    $('input[name="list_col[]"]').each(function() {
      $(this).prop('checked', $.inArray(this.value, rcmail.env.coltypes) != -1);
    });

    $dialog.dialog({
      modal: true,
      resizable: false,
      closeOnEscape: true,
      title: null,
      close: function() {
        $dialog.dialog('destroy').hide();
      },
      minWidth: 500,
      width: $dialog.width()+25
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
      cols = $('input[name="list_col[]"]:checked')
        .map(function(){ return this.value; }).get();

    rcmail.set_list_options(cols, sort, ord, rcmail.env.threading);
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

  function push_contactgroup(p)
  {
    // lets the contacts list swipe to the left, nice!
    var table = $('#contacts-table'),
      scroller = table.parent().css('overflow', 'hidden');

    table.clone()
      .css({ position:'absolute', top:'0', left:'0', width:table.width()+'px', 'z-index':10 })
      .appendTo(scroller)
      .animate({ left: -(table.width()+5) + 'px' }, 300, 'swing', function(){
        $(this).remove();
        scroller.css('overflow', 'auto')
      });
  }

  function pop_contactgroup(p)
  {
    // lets the contacts list swipe to the left, nice!
    var table = $('#contacts-table'),
      scroller = table.parent().css('overflow', 'hidden'),
      clone = table.clone().appendTo(scroller);

      table.css({ position:'absolute', top:'0', left:-(table.width()+5) + 'px', width:table.width()+'px', height:table.height()+'px', 'z-index':10 })
        .animate({ left:'0' }, 300, 'linear', function(){
        clone.remove();
        $(this).css({ position:'relative', left:'0', width:'100%', height:'auto', 'z-index':1 });
        scroller.css('overflow', 'auto')
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
    var content = $(elem),
      id = content.get(0).id,
      fs = content.children('fieldset');

    if (!fs.length)
      return;

    if (!id) {
      id = 'rcmtabcontainer';
      content.attr('id', id);
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

    resize();
  }

  /**
   * Show about page as jquery UI dialog
   */
  function show_about(elem)
  {
    var frame = $('<iframe>').attr('id', 'aboutframe')
      .attr('src', rcmail.url('settings/about'))
      .attr('frameborder', '0')
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
 * Roundcube Scroller class
 */
function rcube_scroller(list, top, bottom)
{
  var ref = this;

  this.list = $(list);
  this.top = $(top);
  this.bottom = $(bottom);
  this.step_size = 6;
  this.step_time = 20;
  this.delay = 500;

  this.top
    .mouseenter(function() { if (rcmail.drag_active) ref.ts = window.setTimeout(function() { ref.scroll('down'); }, ref.delay); })
    .mouseout(function() { if (ref.ts) window.clearTimeout(ref.ts); });

  this.bottom
    .mouseenter(function() { if (rcmail.drag_active) ref.ts = window.setTimeout(function() { ref.scroll('up'); }, ref.delay); })
    .mouseout(function() { if (ref.ts) window.clearTimeout(ref.ts); });

  this.scroll = function(dir)
  {
    var ref = this, size = this.step_size;

    if (!rcmail.drag_active)
      return;

    if (dir == 'down')
      size *= -1;

    this.list.get(0).scrollTop += size;
    this.ts = window.setTimeout(function() { ref.scroll(dir); }, this.step_time);
  };
};


/**
 * Roundcube UI splitter class
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
  rcube_splitter._instances[this.id] = me;

  this.init = function()
  {
    this.p1 = $(this.p.p1);
    this.p2 = $(this.p.p2);

    // check if referenced elements exist, otherwise abort
    if (!this.p1.length || !this.p2.length)
      return;

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

    // listen to window resize on IE
    if (bw.ie)
      $(window).resize(onResize);

    // read saved position from cookie
    var cookie = this.get_cookie();
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
      this.p1.css('height', Math.floor(this.pos - this.p1pos.top - Math.floor(this.halfsize)) + 'px');
      this.p2.css('top', Math.ceil(this.pos + Math.ceil(this.halfsize) + 2) + 'px');
      this.handle.css('top', Math.round(this.pos - this.halfsize + this.offset)+'px');
      if (bw.ie) {
        var new_height = parseInt(this.p2.parent().outerHeight(), 10) - parseInt(this.p2.css('top'), 10) - (bw.ie8 ? 2 : 0);
        this.p2.css('height', (new_height > 0 ? new_height : 0) + 'px');
      }
    }
    else {
      this.p1.css('width', Math.floor(this.pos - this.p1pos.left - Math.floor(this.halfsize)) + 'px');
      this.p2.css('left', Math.ceil(this.pos + Math.ceil(this.halfsize)) + 'px');
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

    // with timing events dragging action is more responsive
    window.clearTimeout(me.ts);
    me.ts = window.setTimeout(function() { onDragAction(e); }, 1);

    return false;
  };

  /**
   * Dragging action (see onDrag())
   */
  function onDragAction(e)
  {
    var pos = rcube_event.get_mouse_pos(e);

    if (me.relative) {
      var parent = me.p1.parent().offset();
      pos.x -= parent.left;
      pos.y -= parent.top;
    }

    if (me.horizontal) {
      if (((pos.y - me.halfsize) > me.p1pos.top) && ((pos.y + me.halfsize) < (me.p2pos.top + me.p2.outerHeight()))) {
        me.pos = Math.max(me.min, pos.y - Math.max(0, me.offset));
        me.resize();
      }
    }
    else {
      if (((pos.x - me.halfsize) > me.p1pos.left) && ((pos.x + me.halfsize) < (me.p2pos.left + me.p2.outerWidth()))) {
        me.pos = Math.max(me.min, pos.x - Math.max(0, me.offset));
        me.resize();
      }
    }

    me.p1pos = me.relative ? me.p1.position() : me.p1.offset();
    me.p2pos = me.relative ? me.p2.position() : me.p2.offset();
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
   * Get saved splitter position from cookie
   */
  this.get_cookie = function()
  {
    return window.UI ? UI.get_pref(this.id) : null;
  };

  /**
   * Saves splitter position in cookie
   */
  this.set_cookie = function()
  {
    if (window.UI)
      UI.save_pref(this.id, this.pos);
  };

} // end class rcube_splitter


// static getter for splitter instances
rcube_splitter._instances = {};

rcube_splitter.get_instance = function(id)
{
  return rcube_splitter._instances[id];
};
