/**
 * Roundcube functions for default skin interface
 */

/**
 * Settings
 */

function rcube_init_settings_tabs()
{
  var el, cl, container = $('#tabsbar'),
    last_tab = $('span:last', container),
    tab = '#settingstabdefault',
    action = window.rcmail && rcmail.env.action ? rcmail.env.action : null;

  // move About tab to the end
  if (last_tab && last_tab.attr('id') != 'settingstababout' && (el = $('#settingstababout'))) {
    cl = el.clone(true);
    el.remove();
    last_tab.after(cl);
  }

  // get selected tab
  if (action)
    tab = '#settingstab' + (action == 'preferences' ? 'default' : (action.indexOf('identity')>0 ? 'identities' : action.replace(/\./g, '')));

  $(tab).addClass('tablink-selected');
  $('a', tab).removeAttr('onclick').click(function() { return false; });
}

// Fieldsets-to-tabs converter
// Warning: don't place "caller" <script> inside page element (id)
function rcube_init_tabs(id, current)
{
  var content = $('#'+id),
    fs = content.children('fieldset');

  if (!fs.length)
    return;

  current = current ? current : 0;

  // first hide not selected tabs
  fs.each(function(idx) { if (idx != current) $(this).hide(); });

  // create tabs container
  var tabs = $('<div>').addClass('tabsbar').appendTo(content);

  // convert fildsets into tabs
  fs.each(function(idx) {
    var tab, a, elm = $(this), legend = elm.children('legend');

    // create a tab
    a   = $('<a>').text(legend.text()).attr('href', '#');
    tab = $('<span>').attr({'id': 'tab'+idx, 'class': 'tablink'})
        .click(function() { rcube_show_tab(id, idx); return false })

    // remove legend
    legend.remove();
    // style fieldset
    elm.addClass('tabbed');
    // style selected tab
    if (idx == current)
      tab.addClass('tablink-selected');

    // add the tab to container
    tab.append(a).appendTo(tabs);
  });
}

function rcube_show_tab(id, index)
{
  var fs = $('#'+id).children('fieldset');

  fs.each(function(idx) {
    // Show/hide fieldset (tab content)
    $(this)[index==idx ? 'show' : 'hide']();
    // Select/unselect tab
    $('#tab'+idx).toggleClass('tablink-selected', idx==index);
  });
}

/**
 * Mail UI
 */

function rcube_mail_ui()
{
  this.popups = {
    markmenu:       {id:'markmessagemenu'},
    replyallmenu:   {id:'replyallmenu'},
    forwardmenu:    {id:'forwardmenu', editable:1},
    searchmenu:     {id:'searchmenu', editable:1},
    messagemenu:    {id:'messagemenu'},
    listmenu:       {id:'listmenu', editable:1},
    dragmessagemenu:{id:'dragmessagemenu', sticky:1},
    groupmenu:      {id:'groupoptionsmenu', above:1},
    mailboxmenu:    {id:'mailboxoptionsmenu', above:1},
    composemenu:    {id:'composeoptionsmenu', editable:1, overlap:1},
    spellmenu:      {id:'spellmenu'},
    // toggle: #1486823, #1486930
    uploadmenu:     {id:'attachment-form', editable:1, above:1, toggle:!bw.ie&&!bw.linux },
    uploadform:     {id:'upload-form', editable:1, toggle:!bw.ie&&!bw.linux }
  };

  var obj;
  for (var k in this.popups) {
    obj = $('#'+this.popups[k].id)
    if (obj.length)
      this.popups[k].obj = obj;
    else {
      delete this.popups[k];
    }
  }
}

rcube_mail_ui.prototype = {

show_popup: function(popup, show, config)
{
  var obj;
  // auto-register menu object
  if (!this.popups[popup] && (obj = $('#'+popup)) && obj.length)
    this.popups[popup] = $.extend(config, {id: popup, obj: obj});

  if (typeof this[popup] == 'function')
    return this[popup](show);
  else
    return this.show_popupmenu(popup, show);
},

show_popupmenu: function(popup, show)
{
  var obj = this.popups[popup].obj,
    above = this.popups[popup].above,
    ref = rcube_find_object(popup+'link');

  if (typeof show == 'undefined')
    show = obj.is(':visible') ? false : true;
  else if (this.popups[popup].toggle && show && this.popups[popup].obj.is(':visible') )
    show = false;

  if (show && ref) {
    var parent = $(ref).parent(),
      win = $(window),
      pos = parent.hasClass('dropbutton') ? parent.offset() : $(ref).offset();

    if (!above && pos.top + ref.offsetHeight + obj.height() > win.height())
      above = true;
    if (pos.left + obj.width() > win.width())
      pos.left = win.width() - obj.width() - 30;

    obj.css({ left:pos.left, top:(pos.top + (above ? -obj.height() : ref.offsetHeight)) });
  }

  obj[show?'show':'hide']();

  if (bw.ie6 && this.popups[popup].overlap) {
    $('select').css('visibility', show?'hidden':'inherit');
    $('select', obj).css('visibility', 'inherit');
  }
},

dragmessagemenu: function(show)
{
  this.popups.dragmessagemenu.obj[show?'show':'hide']();
},

forwardmenu: function(show)
{
  $("input[name='forwardtype'][value="+(rcmail.env.forward_attachment ? 1 : 0)+"]", this.popups.forwardmenu.obj)
    .prop('checked', true);
  this.show_popupmenu('forwardmenu', show);
},

uploadmenu: function(show)
{
  if (typeof show == 'object') // called as event handler
    show = false;

  // clear upload form
  if (!show) {
    try { $('#attachment-form form')[0].reset(); }
    catch(e){}  // ignore errors
  }

  this.show_popupmenu('uploadmenu', show);

  if (!document.all && this.popups.uploadmenu.obj.is(':visible'))
    $('#attachment-form input[type=file]').click();
},

searchmenu: function(show)
{
  var obj = this.popups.searchmenu.obj,
    ref = rcube_find_object('searchmenulink');

  if (typeof show == 'undefined')
    show = obj.is(':visible') ? false : true;

  if (show && ref) {
    var pos = $(ref).offset();
    obj.css({left:pos.left, top:(pos.top + ref.offsetHeight + 2)});

    if (rcmail.env.search_mods) {
      var n, all,
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
  obj[show?'show':'hide']();
},

set_searchmod: function(elem)
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
},

listmenu: function(show)
{
  var obj = this.popups.listmenu.obj,
    ref = rcube_find_object('listmenulink');

  if (typeof show == 'undefined')
    show = obj.is(':visible') ? false : true;

  if (show && ref) {
    var pos = $(ref).offset(),
      menuwidth = obj.width(),
      pagewidth = $(document).width();

    if (pagewidth - pos.left < menuwidth && pos.left > menuwidth)
      pos.left = pos.left - menuwidth;

    obj.css({ left:pos.left, top:(pos.top + ref.offsetHeight + 2)});

    // set form values
    $('input[name="sort_col"][value="'+rcmail.env.sort_col+'"]').prop('checked', true);
    $('input[name="sort_ord"][value="DESC"]').prop('checked', rcmail.env.sort_order == 'DESC');
    $('input[name="sort_ord"][value="ASC"]').prop('checked', rcmail.env.sort_order != 'DESC');
    $('input[name="view"][value="thread"]').prop('checked', rcmail.env.threading ? true : false);
    $('input[name="view"][value="list"]').prop('checked', rcmail.env.threading ? false : true);

    // set checkboxes
    $('input[name="list_col[]"]').each(function() {
      $(this).prop('checked', jQuery.inArray(this.value, rcmail.env.coltypes) != -1);
    });
  }

  obj[show?'show':'hide']();

  if (show) {
    var maxheight=0;
    $('#listmenu fieldset').each(function() {
      var height = $(this).height();
      if (height > maxheight) {
        maxheight = height;
      }
    });
    $('#listmenu fieldset').css("min-height", maxheight+"px")
    // IE6 complains if you set this attribute using either method:
    //$('#listmenu fieldset').css({'height':'auto !important'});
    //$('#listmenu fieldset').css("height","auto !important");
      .height(maxheight);
  };
},

open_listmenu: function(e)
{
  this.listmenu();
},

save_listmenu: function()
{
  this.listmenu();

  var sort = $('input[name="sort_col"]:checked').val(),
    ord = $('input[name="sort_ord"]:checked').val(),
    thread = $('input[name="view"]:checked').val(),
    cols = $('input[name="list_col[]"]:checked')
      .map(function(){ return this.value; }).get();

  rcmail.set_list_options(cols, sort, ord, thread == 'thread' ? 1 : 0);
},

spellmenu: function(show)
{
  var link, li,
    lang = rcmail.spellcheck_lang(),
    menu = this.popups.spellmenu.obj,
    ul = $('ul', menu);

  if (!ul.length) {
    ul = $('<ul>');

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

  this.show_popupmenu('spellmenu', show);
},

body_mouseup: function(evt, p)
{
  var i, target = rcube_event.get_target(evt);

  for (i in this.popups) {
    if (this.popups[i].obj.is(':visible') && target != rcube_find_object(i+'link')
      && !this.popups[i].toggle
      && (!this.popups[i].editable || !this.target_overlaps(target, this.popups[i].id))
      && (!this.popups[i].sticky || !rcube_mouse_is_over(evt, rcube_find_object(this.popups[i].id)))
    ) {
      window.setTimeout('rcmail_ui.show_popup("'+i+'",false);', 50);
    }
  }
},

target_overlaps: function (target, elementid)
{
  var element = rcube_find_object(elementid);
  while (target.parentNode) {
    if (target.parentNode == element)
      return true;
    target = target.parentNode;
  }
  return false;
},

body_keydown: function(evt, p)
{
  if (rcube_event.get_keycode(evt) == 27) {
    for (var k in this.popups) {
      if (this.popups[k].obj.is(':visible'))
        this.show_popup(k, false);
    }
  }
},

switch_preview_pane: function(elem)
{
  var uid, prev_frm = $('#mailpreviewframe');

  if (elem.checked) {
    rcmail.env.contentframe = 'messagecontframe';
    if (mailviewsplit.layer) {
      mailviewsplit.resize();
      mailviewsplit.layer.elm.style.display = '';
    }
    else
      mailviewsplit.init();

    if (bw.opera) {
      $('#messagelistcontainer').css({height: ''});
    }
    prev_frm.show();

    if (uid = rcmail.message_list.get_single_selection())
      rcmail.show_message(uid, false, true);
  }
  else {
    prev_frm.hide();
    if (bw.ie6 || bw.ie7) {
      var fr = document.getElementById('mailcontframe');
      fr.style.bottom = 0;
      fr.style.height = parseInt(fr.parentNode.offsetHeight)+'px';
    }
    else {
      $('#mailcontframe').css({height: 'auto', bottom: 0});
      if (bw.opera)
        $('#messagelistcontainer').css({height: 'auto'});
    }
    if (mailviewsplit.layer)
      mailviewsplit.layer.elm.style.display = 'none';

    rcmail.env.contentframe = null;
    rcmail.show_contentframe(false);
  }

  rcmail.command('save-pref', {name: 'preview_pane', value: (elem.checked?1:0)});
},

/* Message composing */
init_compose_form: function()
{
  var f, field, fields = ['cc', 'bcc', 'replyto', 'followupto'],
    div = document.getElementById('compose-div'),
    headers_div = document.getElementById('compose-headers-div');

  // Show input elements with non-empty value
  for (f=0; f<fields.length; f++) {
    if ((field = $('#_'+fields[f])) && field.length && field.val() != '')
      rcmail_ui.show_header_form(fields[f]);
  }

  // prevent from form data loss when pressing ESC key in IE
  if (bw.ie) {
    var form = rcube_find_object('form');
    form.onkeydown = function (e) {
      if (rcube_event.get_keycode(e) == 27)
        rcube_event.cancel(e);
    };
  }

  $(window).resize(function() {
    rcmail_ui.resize_compose_body();
  });

  $('#compose-container').resize(function() {
    rcmail_ui.resize_compose_body();
  });

  div.style.top = (parseInt(headers_div.offsetHeight, 10) + 3) + 'px';
  $(window).resize();

  // fixes contacts-table position when there's more than one addressbook
  $('#contacts-table').css('top', $('#directorylist').height() + 24 + 'px');

  // contacts search submit
  $('#quicksearchbox').keydown(function(e) {
    if (rcube_event.get_keycode(e) == 13)
      rcmail.command('search');
  });
},

resize_compose_body: function()
{
  var div = $('#compose-div .boxlistcontent'),
    w = div.width() - 2, h = div.height(),
    x = bw.ie || bw.opera ? 4 : 0;

  $('#compose-body_tbl').width((w+3)+'px').height('');
  $('#compose-body_ifr').width((w+3)+'px').height((h-54)+'px');
  $('#compose-body').width((w-x)+'px').height(h+'px');
  $('#googie_edit_layer').height(h+'px');
},

resize_compose_body_ev: function()
{
  window.setTimeout(function(){rcmail_ui.resize_compose_body();}, 100);
},

show_header_form: function(id)
{
  var row, s,
    link = document.getElementById(id + '-link');

  if ((s = this.next_sibling(link)))
    s.style.display = 'none';
  else if ((s = this.prev_sibling(link)))
    s.style.display = 'none';

  link.style.display = 'none';

  if ((row = document.getElementById('compose-' + id))) {
    var div = document.getElementById('compose-div'),
      headers_div = document.getElementById('compose-headers-div');
    $(row).show();
    div.style.top = (parseInt(headers_div.offsetHeight, 10) + 3) + 'px';
    this.resize_compose_body();
  }

  return false;
},

hide_header_form: function(id)
{
  var row, ns,
    link = document.getElementById(id + '-link'),
    parent = link.parentNode,
    links = parent.getElementsByTagName('a');

  link.style.display = '';

  for (var i=0; i<links.length; i++)
    if (links[i].style.display != 'none')
      for (var j=i+1; j<links.length; j++)
        if (links[j].style.display != 'none')
          if ((ns = this.next_sibling(links[i]))) {
            ns.style.display = '';
            break;
          }

  document.getElementById('_' + id).value = '';

  if ((row = document.getElementById('compose-' + id))) {
    var div = document.getElementById('compose-div'),
      headers_div = document.getElementById('compose-headers-div');
    row.style.display = 'none';
    div.style.top = (parseInt(headers_div.offsetHeight, 10) + 1) + 'px';
    this.resize_compose_body();
  }

  return false;
},

next_sibling: function(elm)
{
  var ns = elm.nextSibling;
  while (ns && ns.nodeType == 3)
    ns = ns.nextSibling;
  return ns;
},

prev_sibling: function(elm)
{
  var ps = elm.previousSibling;
  while (ps && ps.nodeType == 3)
    ps = ps.previousSibling;
  return ps;
}

};

/**
 * Scroller
 */

function rcmail_scroller(list, top, bottom)
{
  var ref = this;

  this.list = $(list);
  this.top = $(top);
  this.bottom = $(bottom);
  this.step_size = 6;
  this.step_time = 20;
  this.delay = 500;

  this.top
    .mouseenter(function() { ref.ts = window.setTimeout(function() { ref.scroll('down'); }, ref.delay); })
    .mouseout(function() { if (ref.ts) window.clearTimeout(ref.ts); });

  this.bottom
    .mouseenter(function() { ref.ts = window.setTimeout(function() { ref.scroll('up'); }, ref.delay); })
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


// Events handling in iframes (eg. preview pane)
function iframe_events()
{
  // this==iframe
  var doc = this.contentDocument ? this.contentDocument : this.contentWindow ? this.contentWindow.document : null;
  rcube_event.add_listener({ element: doc, object:rcmail_ui, method:'body_mouseup', event:'mouseup' });
};

// Abbreviate mailbox names to fit width of the container
function rcube_render_mailboxlist()
{
  var list = $('#mailboxlist > li a, #mailboxlist ul:visible > li a');

  // it's too slow with really big number of folders, especially on IE
  if (list.length > (bw.ie ? 25 : 100))
    return;

  list.each(function(){
    var elem = $(this),
      text = elem.data('text');

    if (!text) {
      text = elem.text().replace(/\s+\(.+$/, '');
      elem.data('text', text);
    }
    if (text.length < 6)
      return;

    var abbrev = fit_string_to_size(text, elem, elem.width() - elem.children('span.unreadcount').width());
    if (abbrev != text)
      elem.attr('title', text);
    elem.contents().filter(function(){ return (this.nodeType == 3); }).get(0).data = abbrev;
  });
};

// inspired by https://gist.github.com/24261/7fdb113f1e26111bd78c0c6fe515f6c0bf418af5
function fit_string_to_size(str, elem, len)
{
  var w, span, result = str, ellip = '...';

  if (!rcmail.env.tmp_span) {
    // it should be appended to elem to use the same css style
    // but for performance reasons we'll append it to body (once)
    span = $('<b>').css({visibility: 'hidden', padding: '0px'})
      .appendTo($('body', document)).get(0);
    rcmail.env.tmp_span = span;
  }
  else {
    span = rcmail.env.tmp_span;
  }
  span.innerHTML = result;

  // on first run, check if string fits into the length already.
  w = span.offsetWidth;
  if (w > len) {
    var cut = Math.max(1, Math.floor(str.length * ((w - len) / w) / 2)),
      mid = Math.floor(str.length / 2),
      offLeft = mid,
      offRight = mid;

    while (true) {
      offLeft = mid - cut;
      offRight = mid + cut;
      span.innerHTML = str.substring(0,offLeft) + ellip + str.substring(offRight);

      // break loop if string fits size
      if (offLeft < 3 || span.offsetWidth)
        break;

      cut++;
    }

    // build resulting string
    result = str.substring(0,offLeft) + ellip + str.substring(offRight);
  }

  return result;
};

function update_quota(data)
{
  percent_indicator(rcmail.gui_objects.quotadisplay, data);
};

// percent (quota) indicator
function percent_indicator(obj, data)
{
  if (!data || !obj)
    return false;

  var limit_high = 80,
    limit_mid  = 55,
    width = data.width ? data.width : rcmail.env.indicator_width ? rcmail.env.indicator_width : 100,
    height = data.height ? data.height : rcmail.env.indicator_height ? rcmail.env.indicator_height : 14,
    quota = data.percent ? Math.abs(parseInt(data.percent)) : 0,
    quota_width = parseInt(quota / 100 * width),
    pos = $(obj).position();

  // workarounds for Opera and Webkit bugs
  pos.top = Math.max(0, pos.top);
  pos.left = Math.max(0, pos.left);

  rcmail.env.indicator_width = width;
  rcmail.env.indicator_height = height;

  // overlimit
  if (quota_width > width) {
    quota_width = width;
    quota = 100;
  }

  if (data.title)
    data.title = rcmail.get_label('quota') + ': ' +  data.title;

  // main div
  var main = $('<div>');
  main.css({position: 'absolute', top: pos.top, left: pos.left,
      width: width + 'px', height: height + 'px', zIndex: 100, lineHeight: height + 'px'})
    .attr('title', data.title).addClass('quota_text').html(quota + '%');
  // used bar
  var bar1 = $('<div>');
  bar1.css({position: 'absolute', top: pos.top + 1, left: pos.left + 1,
      width: quota_width + 'px', height: height + 'px', zIndex: 99});
  // background
  var bar2 = $('<div>');
  bar2.css({position: 'absolute', top: pos.top + 1, left: pos.left + 1,
      width: width + 'px', height: height + 'px', zIndex: 98})
	.addClass('quota_bg');

  if (quota >= limit_high) {
    main.addClass(' quota_text_high');
    bar1.addClass('quota_high');
  }
  else if(quota >= limit_mid) {
    main.addClass(' quota_text_mid');
    bar1.addClass('quota_mid');
  }
  else {
    main.addClass(' quota_text_low');
    bar1.addClass('quota_low');
  }

  // replace quota image
  $(obj).html('').append(bar1).append(bar2).append(main);
  // update #quotaimg title
  $('#quotaimg').attr('title', data.title);
};

// Optional parameters used by TinyMCE
var rcmail_editor_settings = {
  skin : "default", // "default", "o2k7"
  skin_variant : "" // "", "silver", "black"
};

var rcmail_ui;

function rcube_init_mail_ui()
{
  rcmail_ui = new rcube_mail_ui();
  rcube_event.add_listener({ object:rcmail_ui, method:'body_mouseup', event:'mouseup' });
  rcube_event.add_listener({ object:rcmail_ui, method:'body_keydown', event:'keydown' });

  if (rcmail.env.quota_content)
    update_quota(rcmail.env.quota_content);
  rcmail.addEventListener('setquota', update_quota);

  $('iframe').load(iframe_events)
    .contents().mouseup(function(e){rcmail_ui.body_mouseup(e)});

  if (rcmail.env.task == 'mail') {
    rcmail.addEventListener('menu-open', 'open_listmenu', rcmail_ui);
    rcmail.addEventListener('menu-save', 'save_listmenu', rcmail_ui);
    rcmail.addEventListener('aftersend-attachment', 'uploadmenu', rcmail_ui);
    rcmail.addEventListener('aftertoggle-editor', 'resize_compose_body_ev', rcmail_ui);
    rcmail.gui_object('message_dragmenu', 'dragmessagemenu');

    if (rcmail.gui_objects.mailboxlist) {
      rcmail.addEventListener('responseaftermark', rcube_render_mailboxlist);
      rcmail.addEventListener('responseaftergetunread', rcube_render_mailboxlist);
      rcmail.addEventListener('responseaftercheck-recent', rcube_render_mailboxlist);
      rcmail.addEventListener('aftercollapse-folder', rcube_render_mailboxlist);

      new rcmail_scroller('#mailboxlist-content', '#mailboxlist-title', '#mailboxlist-footer');
    }

    if (rcmail.env.action == 'compose')
      rcmail_ui.init_compose_form();
  }
  else if (rcmail.env.task == 'addressbook') {
    rcmail.addEventListener('afterupload-photo', function(){ rcmail_ui.show_popup('uploadform', false); });

    if (rcmail.gui_objects.folderlist)
      new rcmail_scroller('#directorylist-content', '#directorylist-title', '#directorylist-footer');
  }
  else if (rcmail.env.task == 'settings') {
    if (rcmail.gui_objects.subscriptionlist)
      new rcmail_scroller('#folderlist-content', '#folderlist-title', '#folderlist-footer');
  }
}
