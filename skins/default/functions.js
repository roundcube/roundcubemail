/**
 * RoundCube functions for default skin interface
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
  $(tab + '> a').removeAttr('onclick').unbind('click').bind('click', function(){return false;});
}

function rcube_show_advanced(visible)
{
  $('tr.advanced').css('display', (visible ? (bw.ie ? 'block' : 'table-row') : 'none'));
}

/**
 * Mail UI
 */

function rcube_mail_ui()
{
  this.popupmenus = {
    markmenu:'markmessagemenu',
    searchmenu:'searchmenu',
    messagemenu:'messagemenu',
    listmenu:'listmenu',
    dragmessagemenu:'dragmessagemenu',
    groupmenu:'groupoptionsmenu',
    mailboxmenu:'mailboxoptionsmenu',
    composemenu:'composeoptionsmenu',
    uploadform:'attachment-form'
  };

  var obj;
  for (var k in this.popupmenus) {
    obj = $('#'+this.popupmenus[k])
    if (obj.length)
      this[k] = obj;
  }
}

rcube_mail_ui.prototype = {

show_popupmenu: function(obj, refname, show, above)
{
  if (typeof show == 'undefined')
    show = obj.is(':visible') ? false : true;

  var ref = rcube_find_object(refname);
  if (show && ref) {
    var pos = $(ref).offset();
    obj.css({ left:pos.left, top:(pos.top + (above ? -obj.height() : ref.offsetHeight)) });
  }

  obj[show?'show':'hide']();
},

show_markmenu: function(show)
{
  this.show_popupmenu(this.markmenu, 'markreadbutton', show);
},

show_messagemenu: function(show)
{
  this.show_popupmenu(this.messagemenu, 'messagemenulink', show);
},

show_groupmenu: function(show)
{
  this.show_popupmenu(this.groupmenu, 'groupactionslink', show, true);
},

show_mailboxmenu: function(show)
{
  this.show_popupmenu(this.mailboxmenu, 'mboxactionslink', show, true);
},

show_composemenu: function(show)
{
  this.show_popupmenu(this.composemenu, 'composemenulink', show, true);
},

show_uploadform: function(show)
{
  if (typeof show == 'object') // called as event handler
    show = false;
  if (!show)
    $('input[type=file]').val('');
  this.show_popupmenu(this.uploadform, 'uploadformlink', show, true);
},

show_searchmenu: function(show)
{
  if (typeof show == 'undefined')
    show = this.searchmenu.is(':visible') ? false : true;

  var ref = rcube_find_object('searchmod');
  if (show && ref) {
    var pos = $(ref).offset();
    this.searchmenu.css({ left:pos.left, top:(pos.top + ref.offsetHeight + 2)});
    this.searchmenu.find(":checked").attr('checked', false);

    if (rcmail.env.search_mods) {
      var search_mods = rcmail.env.search_mods[rcmail.env.mailbox] ? rcmail.env.search_mods[rcmail.env.mailbox] : rcmail.env.search_mods['*'];
      for (var n in search_mods)
        $('#s_mod_' + n).attr('checked', true);
    }
  }
  this.searchmenu[show?'show':'hide']();
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

show_listmenu: function(show)
{
  if (typeof show == 'undefined')
    show = this.listmenu.is(':visible') ? false : true;

  var ref = rcube_find_object('listmenulink');
  if (show && ref) {
    var pos = $(ref).offset(),
      menuwidth = this.listmenu.width(),
      pagewidth = $(document).width();

    if (pagewidth - pos.left < menuwidth && pos.left > menuwidth)
      pos.left = pos.left - menuwidth;

    this.listmenu.css({ left:pos.left, top:(pos.top + ref.offsetHeight + 2)});
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

  this.listmenu[show?'show':'hide']();

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
  this.show_listmenu();
},

save_listmenu: function()
{
  this.show_listmenu();

  var sort = $('input[name="sort_col"]:checked').val(),
    ord = $('input[name="sort_ord"]:checked').val(),
    thread = $('input[name="view"]:checked').val(),
    cols = $('input[name="list_col[]"]:checked')
      .map(function(){ return this.value; }).get();

  rcmail.set_list_options(cols, sort, ord, thread == 'thread' ? 1 : 0);
},

body_mouseup: function(evt, p)
{
  var target = rcube_event.get_target(evt);

  if (this.markmenu && this.markmenu.is(':visible') && target != rcube_find_object('markreadbutton'))
    this.show_markmenu(false);
  else if (this.messagemenu && this.messagemenu.is(':visible') && target != rcube_find_object('messagemenulink'))
    this.show_messagemenu(false);
  else if (this.dragmessagemenu && this.dragmessagemenu.is(':visible') && !rcube_mouse_is_over(evt, rcube_find_object('dragmessagemenu')))
    this.dragmessagemenu.hide();
  else if (this.groupmenu &&  this.groupmenu.is(':visible') && target != rcube_find_object('groupactionslink'))
    this.show_groupmenu(false);
  else if (this.mailboxmenu &&  this.mailboxmenu.is(':visible') && target != rcube_find_object('mboxactionslink'))
    this.show_mailboxmenu(false);
  else if (this.composemenu &&  this.composemenu.is(':visible') && target != rcube_find_object('composemenulink')
    && !this.target_overlaps(target, this.popupmenus.composemenu)) {
    this.show_composemenu(false);
  }
  else if (this.uploadform &&  this.uploadform.is(':visible') && target != rcube_find_object('uploadformlink')
    && !this.target_overlaps(target, this.popupmenus.uploadform)) {
    this.show_uploadform(false);
  }
  else if (this.listmenu && this.listmenu.is(':visible') && target != rcube_find_object('listmenulink')
    && !this.target_overlaps(target, this.popupmenus.listmenu)) {
    this.show_listmenu(false);
  }
  else if (this.searchmenu && this.searchmenu.is(':visible') && target != rcube_find_object('searchmod')
    && !this.target_overlaps(target, this.popupmenus.searchmenu)) {
    this.show_searchmenu(false);
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

body_keypress: function(evt, p)
{
  if (rcube_event.get_keycode(evt) == 27) {
    for (var k in this.popupmenus) {
      if (this[k] && this[k].is(':visible'))
        this[k].hide();
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
    rcmail.http_post('save-pref', '_name=preview_pane&_value=1');
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
    rcmail.http_post('save-pref', '_name=preview_pane&_value=0');
  }
},

/* Message composing */
init_compose_form: function()
{
  var cc_field = document.getElementById('_cc'),
    bcc_field = document.getElementById('_bcc'),
    div = document.getElementById('compose-div'),
    headers_div = document.getElementById('compose-headers-div');

  if (cc_field && cc_field.value != '')
    rcmail_show_header_form('cc');

  if (bcc_field && bcc_field.value != '')
    rcmail_show_header_form('bcc');

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

  div.style.top = (parseInt(headers_div.offsetHeight, 10) + 1) + 'px';
  $(window).resize();
},

resize_compose_body: function()
{
  var ed, div = $('#compose-div'), w = div.width(), h = div.height();
  w = w-4;
  h = h-25;

  $('#compose-body').width(w-(bw.ie || bw.opera || bw.safari ? 2 : 0)+'px').height(h+'px');

  if (window.tinyMCE && tinyMCE.get('compose-body')) {
    $('#compose-body_tbl').width((w+4)+'px').height('');
    $('#compose-body_ifr').width((w+2)+'px').height((h-54)+'px');
  }
  else {
    $('#googie_edit_layer').width(w-(bw.ie || bw.opera || bw.safari ? 2 : 0)+'px').height(h+'px');
  }
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
    div.style.top = (parseInt(headers_div.offsetHeight, 10) + 1) + 'px';
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
  rcube_event.add_listener({ object:rcmail_ui, method:'body_keypress', event:'keypress' });

  $('iframe').load(iframe_events)
    .contents().mouseup(function(e){parent.rcmail_ui.body_mouseup(e)});

  if (rcmail.env.task == 'mail') {
    rcmail.addEventListener('menu-open', 'open_listmenu', rcmail_ui);
    rcmail.addEventListener('menu-save', 'save_listmenu', rcmail_ui);
    rcmail.addEventListener('aftersend-attachment', 'show_uploadform', rcmail_ui);
    rcmail.addEventListener('aftertoggle-editor', 'resize_compose_body', rcmail_ui);
    rcmail.gui_object('message_dragmenu', 'dragmessagemenu');

    if (rcmail.env.action == 'compose')
      rcmail_ui.init_compose_form();
  }
}

// Events handling in iframes (eg. preview pane)
function iframe_events()
{
  // this==iframe
  var doc = this.contentDocument ? this.contentDocument : this.contentWindow ? this.contentWindow.document : null;
  parent.rcube_event.add_listener({ element: doc, object:rcmail_ui, method:'body_mouseup', event:'mouseup' });
}

