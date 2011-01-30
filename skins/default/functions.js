/**
 * Roundcube functions for default skin interface
 */

/**
 * Settings
 */

function rcube_init_settings_tabs()
{
  var tab = '#settingstabdefault';
  if (window.rcmail && rcmail.env.action)
    tab = '#settingstab' + (rcmail.env.action=='preferences' ? 'default' : (rcmail.env.action.indexOf('identity')>0 ? 'identities' : rcmail.env.action.replace(/\./g, '')));

  $(tab).addClass('tablink-selected');
  $(tab + '> a').removeAttr('onclick').click(function() { return false; });
}

function rcube_show_advanced(visible)
{
  $('tr.advanced').css('display', (visible ? (bw.ie ? 'block' : 'table-row') : 'none'));
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
  var tabs = $('<div>').addClass('tabsbar').appendTo($(content));

  // convert fildsets into tabs
  fs.each(function(idx) {
    var tab, a, elm = $(this), legend = elm.children('legend');

    // create a tab
    a   = $('<a>').text(legend.text()).attr('href', '#');
    tab = $('<span>').attr({'id': 'tab'+idx, 'class': 'tablink'})
        .click(function() { return rcube_show_tab(id, idx); })

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
    searchmenu:     {id:'searchmenu', editable:1},
    messagemenu:    {id:'messagemenu'},
    listmenu:       {id:'listmenu', editable:1},
    dragmessagemenu:{id:'dragmessagemenu', sticky:1},
    groupmenu:      {id:'groupoptionsmenu', above:1},
    mailboxmenu:    {id:'mailboxoptionsmenu', above:1},
    composemenu:    {id:'composeoptionsmenu', editable:1},
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

show_popup: function(popup, show)
{
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
},

dragmessagemenu: function(show)
{
  this.popups.dragmessagemenu.obj[show?'show':'hide']();
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
    obj.css({ left:pos.left, top:(pos.top + ref.offsetHeight + 2)})
        .find(':checked').attr('checked', false);

    if (rcmail.env.search_mods) {
      var search_mods = rcmail.env.search_mods[rcmail.env.mailbox] ? rcmail.env.search_mods[rcmail.env.mailbox] : rcmail.env.search_mods['*'];
      for (var n in search_mods)
        $('#s_mod_' + n).attr('checked', true);
    }
  }
  obj[show?'show':'hide']();
},

set_searchmod: function(elem)
{
  if (!rcmail.env.search_mods)
    rcmail.env.search_mods = {};

  if (!rcmail.env.search_mods[rcmail.env.mailbox])
    rcmail.env.search_mods[rcmail.env.mailbox] = rcube_clone_object(rcmail.env.search_mods['*']);

  if (!elem.checked)
    delete(rcmail.env.search_mods[rcmail.env.mailbox][elem.value]);
  else
    rcmail.env.search_mods[rcmail.env.mailbox][elem.value] = elem.value;
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
    $('input[name="sort_col"][value="'+rcmail.env.sort_col+'"]').attr('checked', 1);
    $('input[name="sort_ord"][value="DESC"]').attr('checked', rcmail.env.sort_order=='DESC' ? 1 : 0);
    $('input[name="sort_ord"][value="ASC"]').attr('checked', rcmail.env.sort_order=='DESC' ? 0 : 1);
    $('input[name="view"][value="thread"]').attr('checked', rcmail.env.threading ? 1 : 0);
    $('input[name="view"][value="list"]').attr('checked', rcmail.env.threading ? 0 : 1);
    // list columns
    var cols = $('input[name="list_col[]"]');
    for (var i=0; i<cols.length; i++) {
      var found = 0;
      if (cols[i].value != 'from')
        found = jQuery.inArray(cols[i].value, rcmail.env.coltypes) != -1;
      else
        found = (jQuery.inArray('from', rcmail.env.coltypes) != -1
	    || jQuery.inArray('to', rcmail.env.coltypes) != -1);
      $(cols[i]).attr('checked',found ? 1 : 0);
    }
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

body_mouseup: function(evt, p)
{
  var i, target = rcube_event.get_target(evt);

  for (i in this.popups) {
    if (this.popups[i].obj.is(':visible') && target != rcube_find_object(i+'link')
      && !this.popups[i].toggle
      && (!this.popups[i].editable || !this.target_overlaps(target, this.popups[i].id))
      && (!this.popups[i].sticky || !rcube_mouse_is_over(evt, rcube_find_object(this.popups[i].id)))
    ) {
      window.setTimeout('$("#'+this.popups[i].id+'").hide()', 50);
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
  rcmail.http_post('save-pref', '_name=preview_pane&_value='+(elem.checked?1:0));
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
},

resize_compose_body: function()
{
  var div = $('#compose-div .boxlistcontent'), w = div.width(), h = div.height();
  w -= 8;  // 2 x 3px padding + 2 x 1px border
  h -= 4;

  $('#compose-body').width(w+'px').height(h+'px');

  if (window.tinyMCE && tinyMCE.get('compose-body')) {
    $('#compose-body_tbl').width((w+6)+'px').height('');
    $('#compose-body_ifr').width((w+6)+'px').height((h-54)+'px');
  }
  else {
    $('#googie_edit_layer').height(h+'px');
  }
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
    row.style.display = (document.all && !window.opera) ? 'block' : 'table-row';
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


var rcmail_ui;

function rcube_init_mail_ui()
{
  rcmail_ui = new rcube_mail_ui();
  rcube_event.add_listener({ object:rcmail_ui, method:'body_mouseup', event:'mouseup' });
  rcube_event.add_listener({ object:rcmail_ui, method:'body_keydown', event:'keydown' });

  $('iframe').load(iframe_events)
    .contents().mouseup(function(e){rcmail_ui.body_mouseup(e)});

  if (rcmail.env.task == 'mail') {
    rcmail.addEventListener('menu-open', 'open_listmenu', rcmail_ui);
    rcmail.addEventListener('menu-save', 'save_listmenu', rcmail_ui);
    rcmail.addEventListener('aftersend-attachment', 'uploadmenu', rcmail_ui);
    rcmail.addEventListener('aftertoggle-editor', 'resize_compose_body_ev', rcmail_ui);
    rcmail.gui_object('message_dragmenu', 'dragmessagemenu');

    if (rcmail.env.action == 'compose')
      rcmail_ui.init_compose_form();
  }
  else if (rcmail.env.task == 'addressbook') {
    rcmail.addEventListener('afterupload-photo', function(){ rcmail_ui.show_popup('uploadform', false); });
  }
}

// Events handling in iframes (eg. preview pane)
function iframe_events()
{
  // this==iframe
  var doc = this.contentDocument ? this.contentDocument : this.contentWindow ? this.contentWindow.document : null;
  rcube_event.add_listener({ element: doc, object:rcmail_ui, method:'body_mouseup', event:'mouseup' });
}

