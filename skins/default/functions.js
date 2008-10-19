/**
 * RoundCube functions for default skin interface
 */

/**
 * Settings
 */

function rcube_init_settings_tabs()
{
  if (window.rcmail && rcmail.env.action)
    {
    var action = rcmail.env.action=='preferences' ? 'default' : (rcmail.env.action.indexOf('identity')>0 ? 'identities' : rcmail.env.action);
    var tab = document.getElementById('settingstab'+action);
    }
  else 
    var tab = document.getElementById('settingstabdefault');
  
  if (tab)
    tab.className = 'tablink-selected';
}

function rcube_show_advanced(visible)
{
  var rows = document.getElementsByTagName('TR');
  for(var i=0; i<rows.length; i++)
    if(rows[i].className && rows[i].className.match(/advanced/))
      rows[i].style.display = visible ? (bw.ie ? 'block' : 'table-row') : 'none';
}

/**
 * Mail Composing
 */

function rcmail_show_header_form(id, link)
{
  var row, parent, ns, ps, links;

  if (link)
  {
    var parent = link.parentNode;

    if ((ns = rcmail_next_sibling(link)))
      parent.removeChild(ns);
    else if ((ps = rcmail_prev_sibling(link)))
      parent.removeChild(ps);
    
    parent.removeChild(link);

    if(!parent.getElementsByTagName('A').length)
      document.getElementById('compose-links').style.display = 'none';
  }

  if (row = document.getElementById(id))
    {
    var div = document.getElementById('compose-div');
    var headers_div = document.getElementById('compose-headers-div');
    row.style.display = (document.all && !window.opera) ? 'block' : 'table-row';
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
  var cc_field = document.getElementById('rcmcomposecc');
  if (cc_field && cc_field.value!='')
    rcmail_show_header_form('compose-cc', document.getElementById('addcclink'));
  var bcc_field = document.getElementById('rcmcomposebcc');
  if (bcc_field && bcc_field.value!='')
    rcmail_show_header_form('compose-bcc', document.getElementById('addbcclink'));

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
  this.markmenu = new rcube_layer('markmessagemenu');
}

rcube_mail_ui.prototype = {

show_markmenu: function(show)
{
  if (typeof show == 'undefined')
    show = this.markmenu.visible ? false : true;
  
  var ref = rcube_find_object('markreadbutton');
  if (show && ref)
    this.markmenu.move(ref.offsetLeft, ref.offsetTop + ref.offsetHeight);
  
  this.markmenu.show(show);
},

body_mouseup: function(evt, p)
{
  if (this.markmenu && this.markmenu.visible && evt.target != rcube_find_object('markreadbutton'))
    this.show_markmenu(false);
},

body_keypress: function(evt, p)
{
  if (rcube_event.get_keycode(evt) == 27 && this.markmenu && this.markmenu.visible)
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
