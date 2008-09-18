/*
 +-----------------------------------------------------------------------+
 | RoundCube List Widget                                                 |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2006-2008, RoundCube Dev, - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Authors: Thomas Bruederli <roundcube@gmail.com>                       |
 |          Charles McNulty <charles@charlesmcnulty.com>                 |
 +-----------------------------------------------------------------------+
 | Requires: common.js                                                   |
 +-----------------------------------------------------------------------+

  $Id: list.js 344 2006-09-18 03:49:28Z thomasb $
*/


/**
 * RoundCube List Widget class
 * @contructor
 */
function rcube_list_widget(list, p)
  {
  // static contants
  this.ENTER_KEY = 13;
  this.DELETE_KEY = 46;
  this.BACKSPACE_KEY = 8;
  
  this.list = list ? list : null;
  this.frame = null;
  this.rows = [];
  this.selection = [];
  this.rowcount = 0;
  
  this.subject_col = -1;
  this.shiftkey = false;
  this.multiselect = false;
  this.multi_selecting = false;
  this.draggable = false;
  this.keyboard = false;
  this.toggleselect = false;
  
  this.dont_select = false;
  this.drag_active = false;
  this.last_selected = 0;
  this.shift_start = 0;
  this.in_selection_before = false;
  this.focused = false;
  this.drag_mouse_start = null;
  this.dblclick_time = 600;
  this.row_init = function(){};
  this.events = { click:[], dblclick:[], select:[], keypress:[], dragstart:[], dragmove:[], dragend:[] };
  
  // overwrite default paramaters
  if (p && typeof(p)=='object')
    for (var n in p)
      this[n] = p[n];
  }


rcube_list_widget.prototype = {


/**
 * get all message rows from HTML table and init each row
 */
init: function()
{
  if (this.list && this.list.tBodies[0])
  {
    this.rows = new Array();
    this.rowcount = 0;

    var row;
    for(var r=0; r<this.list.tBodies[0].childNodes.length; r++)
    {
      row = this.list.tBodies[0].childNodes[r];
      while (row && (row.nodeType != 1 || row.style.display == 'none'))
      {
        row = row.nextSibling;
        r++;
      }

      this.init_row(row);
      this.rowcount++;
    }

    this.frame = this.list.parentNode;

    // set body events
    if (this.keyboard) {
      rcube_event.add_listener({element:document, event:'keyup', object:this, method:'key_press'});
      rcube_event.add_listener({element:document, event:'keydown', object:this, method:'key_down'});
    }
  }
},


/**
 *
 */
init_row: function(row)
{
  // make references in internal array and set event handlers
  if (row && String(row.id).match(/rcmrow([a-z0-9\-_=]+)/i))
  {
    var p = this;
    var uid = RegExp.$1;
    row.uid = uid;
    this.rows[uid] = {uid:uid, id:row.id, obj:row, classname:row.className};

    // set eventhandlers to table row
    row.onmousedown = function(e){ return p.drag_row(e, this.uid); };
    row.onmouseup = function(e){ return p.click_row(e, this.uid); };

    if (document.all)
      row.onselectstart = function() { return false; };

    this.row_init(this.rows[uid]);
  }
},


/**
 *
 */
clear: function(sel)
{
  var tbody = document.createElement('TBODY');
  this.list.insertBefore(tbody, this.list.tBodies[0]);
  this.list.removeChild(this.list.tBodies[1]);
  this.rows = new Array();
  this.rowcount = 0;
  
  if (sel) this.clear_selection();
},


/**
 * 'remove' message row from list (just hide it)
 */
remove_row: function(uid, sel_next)
{
  if (this.rows[uid].obj)
    this.rows[uid].obj.style.display = 'none';

  if (sel_next)
    this.select_next();

  this.rows[uid] = null;
  this.rowcount--;
},


/**
 *
 */
insert_row: function(row, attop)
{
  var tbody = this.list.tBodies[0];

  if (attop && tbody.rows.length)
    tbody.insertBefore(row, tbody.firstChild);
  else
    tbody.appendChild(row);

  this.init_row(row);
  this.rowcount++;
},



/**
 * Set focur to the list
 */
focus: function(e)
{
  this.focused = true;
  for (var n=0; n<this.selection.length; n++)
  {
    id = this.selection[n];
    if (this.rows[id] && this.rows[id].obj)
    {
      this.set_classname(this.rows[id].obj, 'selected', true);
      this.set_classname(this.rows[id].obj, 'unfocused', false);
    }
  }

  if (e || (e = window.event))
    rcube_event.cancel(e);
},


/**
 * remove focus from the list
 */
blur: function()
{
  var id;
  this.focused = false;
  for (var n=0; n<this.selection.length; n++)
  {
    id = this.selection[n];
    if (this.rows[id] && this.rows[id].obj)
    {
      this.set_classname(this.rows[id].obj, 'selected', false);
      this.set_classname(this.rows[id].obj, 'unfocused', true);
    }
  }
},


/**
 * onmousedown-handler of message list row
 */
drag_row: function(e, id)
{
  // don't do anything (another action processed before)
  var evtarget = rcube_event.get_target(e);
  if (this.dont_select || (evtarget && (evtarget.tagName == 'INPUT' || evtarget.tagName == 'IMG')))
    return false;
    
  // accept right-clicks
  if (rcube_event.get_button(e) == 2)
    return true;
  
  this.in_selection_before = this.in_selection(id) ? id : false;

  // selects currently unselected row
  if (!this.in_selection_before)
  {
    var mod_key = rcube_event.get_modifier(e);
    this.select_row(id, mod_key, false);
  }

  if (this.draggable && this.selection.length)
  {
    this.drag_start = true;
    this.drag_mouse_start = rcube_event.get_mouse_pos(e);
    rcube_event.add_listener({element:document, event:'mousemove', object:this, method:'drag_mouse_move'});
    rcube_event.add_listener({element:document, event:'mouseup', object:this, method:'drag_mouse_up'});
  }

  return false;
},


/**
 * onmouseup-handler of message list row
 */
click_row: function(e, id)
{
  var now = new Date().getTime();
  var mod_key = rcube_event.get_modifier(e);
  var evtarget = rcube_event.get_target(e);
  
  if ((evtarget && (evtarget.tagName == 'INPUT' || evtarget.tagName == 'IMG')))
    return false;
  
  // don't do anything (another action processed before)
  if (this.dont_select)
    {
    this.dont_select = false;
    return false;
    }
    
  var dblclicked = now - this.rows[id].clicked < this.dblclick_time;

  // unselects currently selected row
  if (!this.drag_active && this.in_selection_before == id && !dblclicked)
    this.select_row(id, mod_key, false);

  this.drag_start = false;
  this.in_selection_before = false;

  // row was double clicked
  if (this.rows && dblclicked && this.in_selection(id))
    this.trigger_event('dblclick');
  else
    this.trigger_event('click');

  if (!this.drag_active)
    rcube_event.cancel(e);

  this.rows[id].clicked = now;
  return false;
},


/**
 * get next/previous/last rows that are not hidden
 */
get_next_row: function()
{
  if (!this.rows)
    return false;

  var last_selected_row = this.rows[this.last_selected];
  var new_row = last_selected_row ? last_selected_row.obj.nextSibling : null;
  while (new_row && (new_row.nodeType != 1 || new_row.style.display == 'none'))
    new_row = new_row.nextSibling;

  return new_row;
},

get_prev_row: function()
{
  if (!this.rows)
    return false;

  var last_selected_row = this.rows[this.last_selected];
  var new_row = last_selected_row ? last_selected_row.obj.previousSibling : null;
  while (new_row && (new_row.nodeType != 1 || new_row.style.display == 'none'))
    new_row = new_row.previousSibling;

  return new_row;
},

get_last_row: function()
{
  if (this.rowcount)
    {
    var rows = this.list.tBodies[0].rows;

    for(var i=rows.length-1; i>=0; i--)
      if(rows[i].id && String(rows[i].id).match(/rcmrow([a-z0-9\-_=]+)/i) && this.rows[RegExp.$1] != null)
	return RegExp.$1;
    }

  return null;
},


/**
 * selects or unselects the proper row depending on the modifier key pressed
 */
select_row: function(id, mod_key, with_mouse)
{
  var select_before = this.selection.join(',');
  if (!this.multiselect)
    mod_key = 0;
    
  if (!this.shift_start)
    this.shift_start = id

  if (!mod_key)
  {
    this.shift_start = id;
    this.highlight_row(id, false);
    this.multi_selecting = false;
  }
  else
  {
    switch (mod_key)
    {
      case SHIFT_KEY:
        this.shift_select(id, false);
        break;

      case CONTROL_KEY:
        if (!with_mouse)
          this.highlight_row(id, true);
        break; 

      case CONTROL_SHIFT_KEY:
        this.shift_select(id, true);
        break;

      default:
        this.highlight_row(id, false);
        break;
    }
    this.multi_selecting = true;
  }

  // trigger event if selection changed
  if (this.selection.join(',') != select_before)
    this.trigger_event('select');

  if (this.last_selected != 0 && this.rows[this.last_selected])
    this.set_classname(this.rows[this.last_selected].obj, 'focused', false);

  // unselect if toggleselect is active and the same row was clicked again
  if (this.toggleselect && this.last_selected == id)
  {
    this.clear_selection();
    id = null;
  }
  else
    this.set_classname(this.rows[id].obj, 'focused', true);

  if (!this.selection.length)
    this.shift_start = null;

  this.last_selected = id;
},


/**
 * Alias method for select_row
 */
select: function(id)
{
  this.select_row(id, false);
  this.scrollto(id);
},


/**
 * Select row next to the last selected one.
 * Either below or above.
 */
select_next: function()
{
  var next_row = this.get_next_row();
  var prev_row = this.get_prev_row();
  var new_row = (next_row) ? next_row : prev_row;
  if (new_row)
    this.select_row(new_row.uid, false, false);  
},


/**
 * Perform selection when shift key is pressed
 */
shift_select: function(id, control)
{
  if (!this.rows[this.shift_start] || !this.selection.length)
    this.shift_start = id;

  var from_rowIndex = this.rows[this.shift_start].obj.rowIndex;
  var to_rowIndex = this.rows[id].obj.rowIndex;

  var i = ((from_rowIndex < to_rowIndex)? from_rowIndex : to_rowIndex);
  var j = ((from_rowIndex > to_rowIndex)? from_rowIndex : to_rowIndex);

  // iterate through the entire message list
  for (var n in this.rows)
  {
    if ((this.rows[n].obj.rowIndex >= i) && (this.rows[n].obj.rowIndex <= j))
    {
      if (!this.in_selection(n))
        this.highlight_row(n, true);
    }
    else
    {
      if  (this.in_selection(n) && !control)
        this.highlight_row(n, true);
    }
  }
},


/**
 * Check if given id is part of the current selection
 */
in_selection: function(id)
{
  for(var n in this.selection)
    if (this.selection[n]==id)
      return true;

  return false;    
},


/**
 * Select each row in list
 */
select_all: function(filter)
{
  if (!this.rows || !this.rows.length)
    return false;

  // reset but remember selection first
  var select_before = this.selection.join(',');
  this.clear_selection();

  for (var n in this.rows)
  {
    if (!filter || this.rows[n][filter]==true)
    {
      this.last_selected = n;
      this.highlight_row(n, true);
    }
  }

  // trigger event if selection changed
  if (this.selection.join(',') != select_before)
    this.trigger_event('select');

  this.focus();

  return true;
},


/**
 * Unselect selected row(s)
 */
clear_selection: function(id)
{
  var num_select = this.selection.length;

  // one row
  if (id)
    {
    for (var n=0; n<this.selection.length; n++)
      if (this.selection[n] == id)
        {
	this.selection.splice(n,1);
    	break;
	}
    }
  // all rows
  else
    {
    for (var n=0; n<this.selection.length; n++)
      if (this.rows[this.selection[n]])
        {
        this.set_classname(this.rows[this.selection[n]].obj, 'selected', false);
        this.set_classname(this.rows[this.selection[n]].obj, 'unfocused', false);
        }
    
    this.selection = new Array();
    }

  if (num_select && !this.selection.length)
    this.trigger_event('select');
},


/**
 * Getter for the selection array
 */
get_selection: function()
{
  return this.selection;
},


/**
 * Return the ID if only one row is selected
 */
get_single_selection: function()
{
  if (this.selection.length == 1)
    return this.selection[0];
  else
    return null;
},


/**
 * Highlight/unhighlight a row
 */
highlight_row: function(id, multiple)
{
  if (this.rows[id] && !multiple)
  {
    if (this.selection.length > 1 || !this.in_selection(id))
    {
      this.clear_selection();
      this.selection[0] = id;
      this.set_classname(this.rows[id].obj, 'selected', true);
    }
  }
  else if (this.rows[id])
  {
    if (!this.in_selection(id))  // select row
    {
      this.selection[this.selection.length] = id;
      this.set_classname(this.rows[id].obj, 'selected', true);
    }
    else  // unselect row
    {
      var p = find_in_array(id, this.selection);
      var a_pre = this.selection.slice(0, p);
      var a_post = this.selection.slice(p+1, this.selection.length);
      this.selection = a_pre.concat(a_post);
      this.set_classname(this.rows[id].obj, 'selected', false);
      this.set_classname(this.rows[id].obj, 'unfocused', false);
    }
  }
},


/**
 * Handler for keyboard events
 */
key_press: function(e)
{
  if (this.focused != true)
    return true;

  var keyCode = rcube_event.get_keycode(e);
  var mod_key = rcube_event.get_modifier(e);
  switch (keyCode)
  {
    case 40:
    case 38: 
    case 63233: // "down", in safari keypress
    case 63232: // "up", in safari keypress
      // Stop propagation so that the browser doesn't scroll
      rcube_event.cancel(e);
      return this.use_arrow_key(keyCode, mod_key);
    default:
      this.shiftkey = e.shiftKey;
      this.key_pressed = keyCode;
      this.trigger_event('keypress');
      
      if (this.key_pressed == this.BACKSPACE_KEY)
        return rcube_event.cancel(e);
  }
  
  return true;
},

/**
 * Handler for keydown events
 */
key_down: function(e)
{
  switch (rcube_event.get_keycode(e))
  {
    case 40:
    case 38: 
    case 63233:
    case 63232:
      if (!rcube_event.get_modifier(e) && this.focused)
        return rcube_event.cancel(e);
        
    default:
  }
  
  return true;
},


/**
 * Special handling method for arrow keys
 */
use_arrow_key: function(keyCode, mod_key)
{
  var new_row;
  // Safari uses the nonstandard keycodes 63232/63233 for up/down, if we're
  // using the keypress event (but not the keydown or keyup event).
  if (keyCode == 40 || keyCode == 63233) // down arrow key pressed
    new_row = this.get_next_row();
  else if (keyCode == 38 || keyCode == 63232) // up arrow key pressed
    new_row = this.get_prev_row();

  if (new_row)
  {
    this.select_row(new_row.uid, mod_key, true);
    this.scrollto(new_row.uid);
  }

  return false;
},


/**
 * Try to scroll the list to make the specified row visible
 */
scrollto: function(id)
{
  var row = this.rows[id].obj;
  if (row && this.frame)
  {
    var scroll_to = Number(row.offsetTop);

    if (scroll_to < Number(this.frame.scrollTop))
      this.frame.scrollTop = scroll_to;
    else if (scroll_to + Number(row.offsetHeight) > Number(this.frame.scrollTop) + Number(this.frame.offsetHeight))
      this.frame.scrollTop = (scroll_to + Number(row.offsetHeight)) - Number(this.frame.offsetHeight);
  }
},


/**
 * Handler for mouse move events
 */
drag_mouse_move: function(e)
{
  if (this.drag_start)
  {
    // check mouse movement, of less than 3 pixels, don't start dragging
    var m = rcube_event.get_mouse_pos(e);
    if (!this.drag_mouse_start || (Math.abs(m.x - this.drag_mouse_start.x) < 3 && Math.abs(m.y - this.drag_mouse_start.y) < 3))
      return false;
  
    if (!this.draglayer)
      this.draglayer = new rcube_layer('rcmdraglayer', {x:0, y:0, width:300, vis:0, zindex:2000});
  
    // get subjects of selectedd messages
    var names = '';
    var c, i, node, subject, obj;
    for(var n=0; n<this.selection.length; n++)
    {
      if (n>12)  // only show 12 lines
      {
        names += '...';
        break;
      }

      if (this.rows[this.selection[n]].obj)
      {
        obj = this.rows[this.selection[n]].obj;
        subject = '';

        for(c=0, i=0; i<obj.childNodes.length; i++)
        {
          if (obj.childNodes[i].nodeName == 'TD')
          {
            if (((node = obj.childNodes[i].firstChild) && (node.nodeType==3 || node.nodeName=='A')) &&
              (this.subject_col < 0 || (this.subject_col >= 0 && this.subject_col == c)))
            {
              subject = node.nodeType==3 ? node.data : node.innerHTML;
              names += (subject.length > 50 ? subject.substring(0, 50)+'...' : subject) + '<br />';
              break;
            }
            c++;
          }
        }
      }
    }

    this.draglayer.write(names);
    this.draglayer.show(1);

    this.drag_active = true;
    this.trigger_event('dragstart');
  }

  if (this.drag_active && this.draglayer)
  {
    var pos = rcube_event.get_mouse_pos(e);
    this.draglayer.move(pos.x+20, pos.y-5);
    this.trigger_event('dragmove', e);
  }

  this.drag_start = false;

  return false;
},


/**
 * Handler for mouse up events
 */
drag_mouse_up: function(e)
{
  document.onmousemove = null;

  if (this.draglayer && this.draglayer.visible)
    this.draglayer.show(0);

  this.drag_active = false;
  this.trigger_event('dragend');

  rcube_event.remove_listener({element:document, event:'mousemove', object:this, method:'drag_mouse_move'});
  rcube_event.remove_listener({element:document, event:'mouseup', object:this, method:'drag_mouse_up'});

  this.focus();
  
  return rcube_event.cancel(e);
},



/**
 * set/unset a specific class name
 */
set_classname: function(obj, classname, set)
{
  var reg = new RegExp('\s*'+classname, 'i');
  if (!set && obj.className.match(reg))
    obj.className = obj.className.replace(reg, '');
  else if (set && !obj.className.match(reg))
    obj.className += ' '+classname;
},


/**
 * Setter for object event handlers
 *
 * @param {String}   Event name
 * @param {Function} Handler function
 * @return Listener ID (used to remove this handler later on)
 */
addEventListener: function(evt, handler)
{
  if (this.events[evt]) {
    var handle = this.events[evt].length;
    this.events[evt][handle] = handler;
    return handle;
  }
  else
    return false;
},


/**
 * Removes a specific event listener
 *
 * @param {String} Event name
 * @param {Int}    Listener ID to remove
 */
removeEventListener: function(evt, handle)
{
  if (this.events[evt] && this.events[evt][handle])
    this.events[evt][handle] = null;
},


/**
 * This will execute all registered event handlers
 * @private
 */
trigger_event: function(evt, p)
{
  if (this.events[evt] && this.events[evt].length) {
    for (var i=0; i<this.events[evt].length; i++)
      if (typeof(this.events[evt][i]) == 'function')
        this.events[evt][i](this, p);
  }
}


};

