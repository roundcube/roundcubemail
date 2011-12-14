/**
 * Roundcube functions for default skin interface
 */


function rcube_mail_ui()
{
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

  // export public methods
  this.init = init;
  this.show_popup = show_popup;
  this.set_searchmod = set_searchmod;

  /**
   *
   */
  function init()
  {
    if (rcmail.env.task == 'mail') {
      rcmail.addEventListener('menu-open', function(){ show_popup('listoptions'); });
      rcmail.addEventListener('menu-save', save_listoptions);
//      rcmail.addEventListener('aftersend-attachment', 'uploadmenu', rcmail_ui);
//      rcmail.addEventListener('aftertoggle-editor', 'resize_compose_body_ev', rcmail_ui);
      rcmail.gui_object('message_dragmenu', 'dragmessagemenu');
      $('#mailpreviewtoggle').click(function(e){ toggle_preview_pane(e); return false });
      $('#maillistmode').addClass(rcmail.env.threading ? '' : 'selected').click(function(e){ switch_view_mode('list'); return false });
      $('#mailthreadmode').addClass(rcmail.env.threading ? 'selected' : '').click(function(e){ switch_view_mode('thread'); return false });
      
      if (rcmail.env.action == 'show' || rcmail.env.action == 'preview') {
        layout_messageview();
      }
    }
    else if (rcmail.env.task == 'settings') {
      var tab = '#settingstabpreferences';
      if (rcmail.env.action)
        tab = '#settingstab' + (rcmail.env.action.indexOf('identity')>0 ? 'identities' : rcmail.env.action.replace(/\./g, ''));

      $(tab).addClass('selected')
        .children().first().removeAttr('onclick').click(function() { return false; });
    }

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
    
    $(window).resize(resize);
  }

  /**
   * Update UI on window resize
   */
  function resize()
  {
    if (rcmail.env.task == 'mail' && (rcmail.env.action == 'show' || rcmail.env.action == 'preview')) {
      layout_messageview();
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
      $('#messagecontent div.rightcol').hide();
      $('#messagecontent .leftcol').css('margin-right', '0');
    }
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


  function toggle_preview_pane(e)
  {
    var button = $(e.target);
    var visible = !button.hasClass('enabled');
    
    button.removeClass().addClass(visible ? 'enabled' : 'closed');

//    rcmail.command('save-pref', { name:'preview_pane', value:(visible?1:0) });
  }


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


  function save_listoptions()
  {
    show_popupmenu('listoptions', false);

    var sort = $('input[name="sort_col"]:checked').val(),
      ord = $('input[name="sort_ord"]:checked').val(),
      thread = $('input[name="view"]:checked').val(),
      cols = $('input[name="list_col[]"]:checked')
        .map(function(){ return this.value; }).get();

    rcmail.set_list_options(cols, sort, ord, thread == 'thread' ? 1 : 0);
  }


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
}

