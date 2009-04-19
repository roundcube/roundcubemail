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
}

function rcube_show_advanced(visible)
{
  $('tr.advanced').css('display', (visible ? (bw.ie ? 'block' : 'table-row') : 'none'));
}

/**
 * Mail Composing
 */

function rcmail_show_header_form(id)
{
  var link, row, parent, ns, ps;
  
  link = document.getElementById(id + '-link');
  parent = link.parentNode;

  if ((ns = rcmail_next_sibling(link)))
    ns.style.display = 'none';
  else if ((ps = rcmail_prev_sibling(link)))
    ps.style.display = 'none';
    
  link.style.display = 'none';

  if (row = document.getElementById('compose-' + id))
    {
    var div = document.getElementById('compose-div');
    var headers_div = document.getElementById('compose-headers-div');
    row.style.display = (document.all && !window.opera) ? 'block' : 'table-row';
    div.style.top = (parseInt(headers_div.offsetHeight)) + 'px';
    }

  return false;
}

function rcmail_hide_header_form(id)
{
  var row, parent, ns, ps, link, links;

  link = document.getElementById(id + '-link');
  link.style.display = '';
  
  parent = link.parentNode;
  links = parent.getElementsByTagName('A');

  for (var i=0; i<links.length; i++)
    if (links[i].style.display != 'none')
      for (var j=i+1; j<links.length; j++)
	if (links[j].style.display != 'none')
          if ((ns = rcmail_next_sibling(links[i]))) {
	    ns.style.display = '';
	    break;
	  }

  document.getElementById('_' + id).value = '';

  if (row = document.getElementById('compose-' + id))
    {
    var div = document.getElementById('compose-div');
    var headers_div = document.getElementById('compose-headers-div');
    row.style.display = 'none';
    div.style.top = (parseInt(headers_div.offsetHeight)) + 'px';
    }

  return false;
}

function rcmail_next_sibling(elm)
{
  var ns = elm.nextSibling;
  while (ns && ns.nodeType == 3)
    ns = ns.nextSibling;
  return ns;
}

function rcmail_prev_sibling(elm)
{
  var ps = elm.previousSibling;
  while (ps && ps.nodeType == 3)
    ps = ps.previousSibling;
  return ps;
}

function rcmail_init_compose_form()
{
  var cc_field = document.getElementById('_cc');
  if (cc_field && cc_field.value!='')
    rcmail_show_header_form('cc');

  var bcc_field = document.getElementById('_bcc');
  if (bcc_field && bcc_field.value!='')
    rcmail_show_header_form('bcc');

  // prevent from form data loss when pressing ESC key in IE
  if (bw.ie) {
    var form = rcube_find_object('form');
    form.onkeydown = function (e) { if (rcube_event.get_keycode(e) == 27) rcube_event.cancel(e); };
  }
}

/**
 * Mailbox view
 */

function rcube_mail_ui()
{
  this.markmenu = $('#markmessagemenu');
}

rcube_mail_ui.prototype = {

show_markmenu: function(show)
{
  if (typeof show == 'undefined')
    show = this.markmenu.is(':visible') ? false : true;
  
  var ref = rcube_find_object('markreadbutton');
  if (show && ref)
    this.markmenu.css({ left:ref.offsetLeft, top:(ref.offsetTop + ref.offsetHeight) });
  
  this.markmenu[show?'show':'hide']();
},

body_mouseup: function(evt, p)
{
  if (this.markmenu && this.markmenu.is(':visible') && rcube_event.get_target(evt) != rcube_find_object('markreadbutton'))
    this.show_markmenu(false);
},

body_keypress: function(evt, p)
{
  if (rcube_event.get_keycode(evt) == 27 && this.markmenu && this.markmenu.is(':visible'))
    this.show_markmenu(false);
}

};

var rcmail_ui;

function rcube_init_mail_ui()
{
  rcmail_ui = new rcube_mail_ui();
  rcube_event.add_listener({ object:rcmail_ui, method:'body_mouseup', event:'mouseup' });
  rcube_event.add_listener({ object:rcmail_ui, method:'body_keypress', event:'keypress' });
}
