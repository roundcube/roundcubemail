/*
 +-----------------------------------------------------------------------+
 | Roundcube List Widget                                                 |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2006-2009, The Roundcube Dev Team                       |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Authors: Thomas Bruederli <roundcube@gmail.com>                       |
 |          Charles McNulty <charles@charlesmcnulty.com>                 |
 +-----------------------------------------------------------------------+
 | Requires: common.js                                                   |
 +-----------------------------------------------------------------------+

  $Id$
*/


/**
 * Roundcube List Widget class
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
  this.colcount = 0;

  this.subject_col = -1;
  this.shiftkey = false;
  this.multiselect = false;
  this.multiexpand = false;
  this.multi_selecting = false;
  this.draggable = false;
  this.column_movable = false;
  this.keyboard = false;
  this.toggleselect = false;

  this.dont_select = false;
  this.drag_active = false;
  this.col_drag_active = false;
  this.column_fixed = null;
  this.last_selected = 0;
  this.shift_start = 0;
  this.in_selection_before = false;
  this.focused = false;
  this.drag_mouse_start = null;
  this.dblclick_time = 600;
  this.row_init = function(){};

  // overwrite default paramaters
  if (p && typeof p === 'object')
    for (var n in p)
      this[n] = p[n];
};


rcube_list_widget.prototype = {


/**
 * get all message rows from HTML table and init each row
 */
init: function()
{
  if (this.list && this.list.tBodies[0]) {
    this.rows = [];
    this.rowcount = 0;

    var r, len, rows = this.list.tBodies[0].rows;

    for (r=0, len=rows.length; r<len; r++) {
      this.init_row(rows[r]);
      this.rowcount++;
    }

    this.init_header();
    this.frame = this.list.parentNode;

    // set body events
    if (this.keyboard) {
      rcube_event.add_listener({event:bw.opera?'keypress':'keydown', object:this, method:'key_press'});
      rcube_event.add_listener({event:'keydown', object:this, method:'key_down'});
    }
  }
},


/**
 * Init list row and set mouse events on it
 */
init_row: function(row)
{
  // make references in internal array and set event handlers
  if (row && String(row.id).match(/rcmrow([a-z0-9\-_=\+\/]+)/i)) {
    var self = this,
      uid = RegExp.$1;
    row.uid = uid;
    this.rows[uid] = {uid:uid, id:row.id, obj:row};

    // set eventhandlers to table row
    row.onmousedown = function(e){ return self.drag_row(e, this.uid); };
    row.onmouseup = function(e){ return self.click_row(e, this.uid); };

    if (bw.iphone || bw.ipad) {
      row.addEventListener('touchstart', function(e) {
        if (e.touches.length == 1) {
          if (!self.drag_row(rcube_event.touchevent(e.touches[0]), this.uid))
            e.preventDefault();
        }
      }, false);
      row.addEventListener('touchend', function(e) {
        if (e.changedTouches.length == 1)
          if (!self.click_row(rcube_event.touchevent(e.changedTouches[0]), this.uid))
            e.preventDefault();
      }, false);
    }

    if (document.all)
      row.onselectstart = function() { return false; };

    this.row_init(this.rows[uid]);
  }
},


/**
 * Init list column headers and set mouse events on them
 */
init_header: function()
{
  if (this.list && this.list.tHead) {
    this.colcount = 0;

    var col, r, p = this;
    // add events for list columns moving
    if (this.column_movable && this.list.tHead && this.list.tHead.rows) {
      for (r=0; r<this.list.tHead.rows[0].cells.length; r++) {
        if (this.column_fixed == r)
          continue;
        col = this.list.tHead.rows[0].cells[r];
        col.onmousedown = function(e){ return p.drag_column(e, this); };
        this.colcount++;
      }
    }
  }
},


/**
 * Remove all list rows
 */
clear: function(sel)
{
  var tbody = document.createElement('tbody');

  this.list.insertBefore(tbody, this.list.tBodies[0]);
  this.list.removeChild(this.list.tBodies[1]);
  this.rows = [];
  this.rowcount = 0;

  if (sel)
    this.clear_selection();

  // reset scroll position (in Opera)
  if (this.frame)
    this.frame.scrollTop = 0;
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

  delete this.rows[uid];
  this.rowcount--;
},


/**
 * Add row to the list and initialize it
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
 * Set focus to the list
 */
focus: function(e)
{
  var n, id;
  this.focused = true;

  for (n in this.selection) {
    id = this.selection[n];
    if (this.rows[id] && this.rows[id].obj) {
      $(this.rows[id].obj).addClass('selected').removeClass('unfocused');
    }
  }

  // Un-focus already focused elements
  $('*:focus', window).blur();
  $('iframe').each(function() { this.blur(); });

  if (e || (e = window.event))
    rcube_event.cancel(e);
},


/**
 * remove focus from the list
 */
blur: function()
{
  var n, id;
  this.focused = false;
  for (n in this.selection) {
    id = this.selection[n];
    if (this.rows[id] && this.rows[id].obj) {
      $(this.rows[id].obj).removeClass('selected').addClass('unfocused');
    }
  }
},


/**
 * onmousedown-handler of message list column
 */
drag_column: function(e, col)
{
  if (this.colcount > 1) {
    this.drag_start = true;
    this.drag_mouse_start = rcube_event.get_mouse_pos(e);

    rcube_event.add_listener({event:'mousemove', object:this, method:'column_drag_mouse_move'});
    rcube_event.add_listener({event:'mouseup', object:this, method:'column_drag_mouse_up'});

    // enable dragging over iframes
    this.add_dragfix();

    // find selected column number
    for (var i=0; i<this.list.tHead.rows[0].cells.length; i++) {
      if (col == this.list.tHead.rows[0].cells[i]) {
        this.selected_column = i;
        break;
      }
    }
  }

  return false;
},


/**
 * onmousedown-handler of message list row
 */
drag_row: function(e, id)
{
  // don't do anything (another action processed before)
  var evtarget = rcube_event.get_target(e),
    tagname = evtarget.tagName.toLowerCase();

  if (this.dont_select || (evtarget && (tagname == 'input' || tagname == 'img')))
    return true;

  // accept right-clicks
  if (rcube_event.get_button(e) == 2)
    return true;

  this.in_selection_before = this.in_selection(id) ? id : false;

  // selects currently unselected row
  if (!this.in_selection_before) {
    var mod_key = rcube_event.get_modifier(e);
    this.select_row(id, mod_key, false);
  }

  if (this.draggable && this.selection.length) {
    this.drag_start = true;
    this.drag_mouse_start = rcube_event.get_mouse_pos(e);
    rcube_event.add_listener({event:'mousemove', object:this, method:'drag_mouse_move'});
    rcube_event.add_listener({event:'mouseup', object:this, method:'drag_mouse_up'});
    if (bw.iphone || bw.ipad) {
      rcube_event.add_listener({event:'touchmove', object:this, method:'drag_mouse_move'});
      rcube_event.add_listener({event:'touchend', object:this, method:'drag_mouse_up'});
    }

    // enable dragging over iframes
    this.add_dragfix();
  }

  return false;
},


/**
 * onmouseup-handler of message list row
 */
click_row: function(e, id)
{
  var now = new Date().getTime(),
    mod_key = rcube_event.get_modifier(e),
    evtarget = rcube_event.get_target(e),
    tagname = evtarget.tagName.toLowerCase();

  if ((evtarget && (tagname == 'input' || tagname == 'img')))
    return true;

  // don't do anything (another action processed before)
  if (this.dont_select) {
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
    this.triggerEvent('dblclick');
  else
    this.triggerEvent('click');

  if (!this.drag_active) {
    // remove temp divs
    this.del_dragfix();
    rcube_event.cancel(e);
  }

  this.rows[id].clicked = now;
  return false;
},


/*
 * Returns thread root ID for specified row ID
 */
find_root: function(uid)
{
   var r = this.rows[uid];

   if (r && r.parent_uid)
     return this.find_root(r.parent_uid);
   else
     return uid;
},


expand_row: function(e, id)
{
  var row = this.rows[id],
    evtarget = rcube_event.get_target(e),
    mod_key = rcube_event.get_modifier(e);

  // Don't select this message
  this.dont_select = true;
  // Don't treat double click on the expando as double click on the message.
  row.clicked = 0;

  if (row.expanded) {
    evtarget.className = 'collapsed';
    if (mod_key == CONTROL_KEY || this.multiexpand)
      this.collapse_all(row);
    else
      this.collapse(row);
  }
  else {
    evtarget.className = 'expanded';
    if (mod_key == CONTROL_KEY || this.multiexpand)
      this.expand_all(row);
    else
     this.expand(row);
  }
},

collapse: function(row)
{
  row.expanded = false;
  this.triggerEvent('expandcollapse', { uid:row.uid, expanded:row.expanded });
  var depth = row.depth;
  var new_row = row ? row.obj.nextSibling : null;
  var r;

  while (new_row) {
    if (new_row.nodeType == 1) {
      var r = this.rows[new_row.uid];
      if (r && r.depth <= depth)
        break;
      $(new_row).css('display', 'none');
      if (r.expanded) {
        r.expanded = false;
        this.triggerEvent('expandcollapse', { uid:r.uid, expanded:r.expanded });
      }
    }
    new_row = new_row.nextSibling;
  }

  return false;
},

expand: function(row)
{
  var r, p, depth, new_row, last_expanded_parent_depth;

  if (row) {
    row.expanded = true;
    depth = row.depth;
    new_row = row.obj.nextSibling;
    this.update_expando(row.uid, true);
    this.triggerEvent('expandcollapse', { uid:row.uid, expanded:row.expanded });
  }
  else {
    var tbody = this.list.tBodies[0];
    new_row = tbody.firstChild;
    depth = 0;
    last_expanded_parent_depth = 0;
  }

  while (new_row) {
    if (new_row.nodeType == 1) {
      r = this.rows[new_row.uid];
      if (r) {
        if (row && (!r.depth || r.depth <= depth))
          break;

        if (r.parent_uid) {
          p = this.rows[r.parent_uid];
          if (p && p.expanded) {
            if ((row && p == row) || last_expanded_parent_depth >= p.depth - 1) {
              last_expanded_parent_depth = p.depth;
              $(new_row).css('display', '');
              r.expanded = true;
              this.triggerEvent('expandcollapse', { uid:r.uid, expanded:r.expanded });
            }
          }
          else
            if (row && (! p || p.depth <= depth))
              break;
        }
      }
    }
    new_row = new_row.nextSibling;
  }

  return false;
},


collapse_all: function(row)
{
  var depth, new_row, r;

  if (row) {
    row.expanded = false;
    depth = row.depth;
    new_row = row.obj.nextSibling;
    this.update_expando(row.uid);
    this.triggerEvent('expandcollapse', { uid:row.uid, expanded:row.expanded });

    // don't collapse sub-root tree in multiexpand mode 
    if (depth && this.multiexpand)
      return false;
  }
  else {
    new_row = this.list.tBodies[0].firstChild;
    depth = 0;
  }

  while (new_row) {
    if (new_row.nodeType == 1) {
      if (r = this.rows[new_row.uid]) {
        if (row && (!r.depth || r.depth <= depth))
          break;

        if (row || r.depth)
          $(new_row).css('display', 'none');
        if (r.has_children && r.expanded) {
          r.expanded = false;
          this.update_expando(r.uid, false);
          this.triggerEvent('expandcollapse', { uid:r.uid, expanded:r.expanded });
        }
      }
    }
    new_row = new_row.nextSibling;
  }

  return false;
},

expand_all: function(row)
{
  var depth, new_row, r;

  if (row) {
    row.expanded = true;
    depth = row.depth;
    new_row = row.obj.nextSibling;
    this.update_expando(row.uid, true);
    this.triggerEvent('expandcollapse', { uid:row.uid, expanded:row.expanded });
  }
  else {
    new_row = this.list.tBodies[0].firstChild;
    depth = 0;
  }

  while (new_row) {
    if (new_row.nodeType == 1) {
      if (r = this.rows[new_row.uid]) {
        if (row && r.depth <= depth)
          break;

        $(new_row).css('display', '');
        if (r.has_children && !r.expanded) {
          r.expanded = true;
          this.update_expando(r.uid, true);
          this.triggerEvent('expandcollapse', { uid:r.uid, expanded:r.expanded });
        }
      }
    }
    new_row = new_row.nextSibling;
  }
  return false;
},

update_expando: function(uid, expanded)
{
  var expando = document.getElementById('rcmexpando' + uid);
  if (expando)
    expando.className = expanded ? 'expanded' : 'collapsed';
},


/**
 * get first/next/previous/last rows that are not hidden
 */
get_next_row: function()
{
  if (!this.rows)
    return false;

  var last_selected_row = this.rows[this.last_selected],
    new_row = last_selected_row ? last_selected_row.obj.nextSibling : null;

  while (new_row && (new_row.nodeType != 1 || new_row.style.display == 'none'))
    new_row = new_row.nextSibling;

  return new_row;
},

get_prev_row: function()
{
  if (!this.rows)
    return false;

  var last_selected_row = this.rows[this.last_selected],
    new_row = last_selected_row ? last_selected_row.obj.previousSibling : null;

  while (new_row && (new_row.nodeType != 1 || new_row.style.display == 'none'))
    new_row = new_row.previousSibling;

  return new_row;
},

get_first_row: function()
{
  if (this.rowcount) {
    var i, len, rows = this.list.tBodies[0].rows;

    for (i=0, len=rows.length-1; i<len; i++)
      if (rows[i].id && String(rows[i].id).match(/rcmrow([a-z0-9\-_=\+\/]+)/i) && this.rows[RegExp.$1] != null)
	    return RegExp.$1;
  }

  return null;
},

get_last_row: function()
{
  if (this.rowcount) {
    var i, rows = this.list.tBodies[0].rows;

    for (i=rows.length-1; i>=0; i--)
      if (rows[i].id && String(rows[i].id).match(/rcmrow([a-z0-9\-_=\+\/]+)/i) && this.rows[RegExp.$1] != null)
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

  if (!mod_key) {
    this.shift_start = id;
    this.highlight_row(id, false);
    this.multi_selecting = false;
  }
  else {
    switch (mod_key) {
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
    this.triggerEvent('select');

  if (this.last_selected != 0 && this.rows[this.last_selected])
    $(this.rows[this.last_selected].obj).removeClass('focused');

  // unselect if toggleselect is active and the same row was clicked again
  if (this.toggleselect && this.last_selected == id) {
    this.clear_selection();
    id = null;
  }
  else
    $(this.rows[id].obj).addClass('focused');

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
  var next_row = this.get_next_row(),
    prev_row = this.get_prev_row(),
    new_row = (next_row) ? next_row : prev_row;

  if (new_row)
    this.select_row(new_row.uid, false, false);
},


/**
 * Select first row 
 */
select_first: function(mod_key)
{
  var row = this.get_first_row();
  if (row) {
    if (mod_key) {
      this.shift_select(row, mod_key);
      this.triggerEvent('select');
      this.scrollto(row);
    }
    else {
      this.select(row);
    }
  }
},


/**
 * Select last row 
 */
select_last: function(mod_key)
{
  var row = this.get_last_row();
  if (row) {
    if (mod_key) {
      this.shift_select(row, mod_key);
      this.triggerEvent('select');
      this.scrollto(row);
    }
    else {
      this.select(row);
    }
  }
},


/**
 * Add all childs of the given row to selection
 */
select_childs: function(uid)
{
  if (!this.rows[uid] || !this.rows[uid].has_children)
    return;

  var depth = this.rows[uid].depth,
    row = this.rows[uid].obj.nextSibling;

  while (row) {
    if (row.nodeType == 1) {
      if ((r = this.rows[row.uid])) {
        if (!r.depth || r.depth <= depth)
          break;
        if (!this.in_selection(r.uid))
          this.select_row(r.uid, CONTROL_KEY);
      }
    }
    row = row.nextSibling;
  }
},


/**
 * Perform selection when shift key is pressed
 */
shift_select: function(id, control)
{
  if (!this.rows[this.shift_start] || !this.selection.length)
    this.shift_start = id;

  var n, from_rowIndex = this.rows[this.shift_start].obj.rowIndex,
    to_rowIndex = this.rows[id].obj.rowIndex,
    i = ((from_rowIndex < to_rowIndex)? from_rowIndex : to_rowIndex),
    j = ((from_rowIndex > to_rowIndex)? from_rowIndex : to_rowIndex);

  // iterate through the entire message list
  for (n in this.rows) {
    if (this.rows[n].obj.rowIndex >= i && this.rows[n].obj.rowIndex <= j) {
      if (!this.in_selection(n)) {
        this.highlight_row(n, true);
      }
    }
    else {
      if (this.in_selection(n) && !control) {
        this.highlight_row(n, true);
      }
    }
  }
},


/**
 * Check if given id is part of the current selection
 */
in_selection: function(id)
{
  for (var n in this.selection)
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
  var n, select_before = this.selection.join(',');
  this.selection = [];

  for (n in this.rows) {
    if (!filter || this.rows[n][filter] == true) {
      this.last_selected = n;
      this.highlight_row(n, true);
    }
    else {
      $(this.rows[n].obj).removeClass('selected').removeClass('unfocused');
    }
  }

  // trigger event if selection changed
  if (this.selection.join(',') != select_before)
    this.triggerEvent('select');

  this.focus();

  return true;
},


/**
 * Invert selection
 */
invert_selection: function()
{
  if (!this.rows || !this.rows.length)
    return false;

  // remember old selection
  var n, select_before = this.selection.join(',');

  for (n in this.rows)
    this.highlight_row(n, true);

  // trigger event if selection changed
  if (this.selection.join(',') != select_before)
    this.triggerEvent('select');

  this.focus();

  return true;
},


/**
 * Unselect selected row(s)
 */
clear_selection: function(id)
{
  var n, num_select = this.selection.length;

  // one row
  if (id) {
    for (n in this.selection)
      if (this.selection[n] == id) {
        this.selection.splice(n,1);
        break;
      }
  }
  // all rows
  else {
    for (n in this.selection)
      if (this.rows[this.selection[n]]) {
        $(this.rows[this.selection[n]].obj).removeClass('selected').removeClass('unfocused');
      }

    this.selection = [];
  }

  if (num_select && !this.selection.length)
    this.triggerEvent('select');
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
  if (this.rows[id] && !multiple) {
    if (this.selection.length > 1 || !this.in_selection(id)) {
      this.clear_selection();
      this.selection[0] = id;
      $(this.rows[id].obj).addClass('selected');
    }
  }
  else if (this.rows[id]) {
    if (!this.in_selection(id)) { // select row
      this.selection[this.selection.length] = id;
      $(this.rows[id].obj).addClass('selected');
    }
    else { // unselect row
      var p = $.inArray(id, this.selection),
        a_pre = this.selection.slice(0, p),
        a_post = this.selection.slice(p+1, this.selection.length);

      this.selection = a_pre.concat(a_post);
      $(this.rows[id].obj).removeClass('selected').removeClass('unfocused');
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

  var keyCode = rcube_event.get_keycode(e),
    mod_key = rcube_event.get_modifier(e);

  switch (keyCode) {
    case 40:
    case 38: 
    case 63233: // "down", in safari keypress
    case 63232: // "up", in safari keypress
      // Stop propagation so that the browser doesn't scroll
      rcube_event.cancel(e);
      return this.use_arrow_key(keyCode, mod_key);
    case 61:
    case 107: // Plus sign on a numeric keypad (fc11 + firefox 3.5.2)
    case 109:
    case 32:
      // Stop propagation
      rcube_event.cancel(e);
      var ret = this.use_plusminus_key(keyCode, mod_key);
      this.key_pressed = keyCode;
      this.triggerEvent('keypress');
      return ret;
    case 36: // Home
      this.select_first(mod_key);
      return rcube_event.cancel(e);
    case 35: // End
      this.select_last(mod_key);
      return rcube_event.cancel(e);
    default:
      this.shiftkey = e.shiftKey;
      this.key_pressed = keyCode;
      this.triggerEvent('keypress');

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
  switch (rcube_event.get_keycode(e)) {
    case 27:
      if (this.drag_active)
	    return this.drag_mouse_up(e);
      if (this.col_drag_active) {
        this.selected_column = null;
	    return this.column_drag_mouse_up(e);
      }

    case 40:
    case 38: 
    case 63233:
    case 63232:
    case 61:
    case 107:
    case 109:
    case 32:
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

  if (new_row) {
    this.select_row(new_row.uid, mod_key, true);
    this.scrollto(new_row.uid);
  }

  return false;
},


/**
 * Special handling method for +/- keys
 */
use_plusminus_key: function(keyCode, mod_key)
{
  var selected_row = this.rows[this.last_selected];
  if (!selected_row)
    return;

  if (keyCode == 32)
    keyCode = selected_row.expanded ? 109 : 61;
  if (keyCode == 61 || keyCode == 107)
    if (mod_key == CONTROL_KEY || this.multiexpand)
      this.expand_all(selected_row);
    else
     this.expand(selected_row);
  else
    if (mod_key == CONTROL_KEY || this.multiexpand)
      this.collapse_all(selected_row);
    else
      this.collapse(selected_row);

  this.update_expando(selected_row.uid, selected_row.expanded);

  return false;
},


/**
 * Try to scroll the list to make the specified row visible
 */
scrollto: function(id)
{
  var row = this.rows[id].obj;
  if (row && this.frame) {
    var scroll_to = Number(row.offsetTop);

    // expand thread if target row is hidden (collapsed)
    if (!scroll_to && this.rows[id].parent_uid) {
      var parent = this.find_root(this.rows[id].uid);
      this.expand_all(this.rows[parent]);
      scroll_to = Number(row.offsetTop);
    }

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
  // convert touch event
  if (e.type == 'touchmove') {
    if (e.changedTouches.length == 1)
      e = rcube_event.touchevent(e.changedTouches[0]);
    else
      return rcube_event.cancel(e);
  }
  
  if (this.drag_start) {
    // check mouse movement, of less than 3 pixels, don't start dragging
    var m = rcube_event.get_mouse_pos(e);

    if (!this.drag_mouse_start || (Math.abs(m.x - this.drag_mouse_start.x) < 3 && Math.abs(m.y - this.drag_mouse_start.y) < 3))
      return false;

    if (!this.draglayer)
      this.draglayer = $('<div>').attr('id', 'rcmdraglayer')
        .css({ position:'absolute', display:'none', 'z-index':2000 })
        .appendTo(document.body);

    // also select childs of (collapsed) threads for dragging
    var n, uid, selection = $.merge([], this.selection);
    for (n in selection) {
      uid = selection[n];
      if (this.rows[uid].has_children && !this.rows[uid].expanded)
        this.select_childs(uid);
    }

    // reset content
    this.draglayer.html('');

    // get subjects of selected messages
    var c, i, n, subject, obj;
    for (n=0; n<this.selection.length; n++) {
      // only show 12 lines
      if (n>12) {
        this.draglayer.append('...');
        break;
      }

      if (obj = this.rows[this.selection[n]].obj) {
        subject = '';

        for (c=0, i=0; i<obj.childNodes.length; i++) {
	      if (obj.childNodes[i].nodeName == 'TD') {
            if (n == 0)
	          this.drag_start_pos = $(obj.childNodes[i]).offset();

	        if (this.subject_col < 0 || (this.subject_col >= 0 && this.subject_col == c)) {
	          var entry, node, tmp_node, nodes = obj.childNodes[i].childNodes;
	          // find text node
	          for (m=0; m<nodes.length; m++) {
	            if ((tmp_node = obj.childNodes[i].childNodes[m]) && (tmp_node.nodeType==3 || tmp_node.nodeName=='A'))
	              node = tmp_node;
	          }

	          if (!node)
	            break;

              subject = $(node).text();
	          // remove leading spaces
              subject = $.trim(subject);
              // truncate line to 50 characters
              subject = (subject.length > 50 ? subject.substring(0, 50) + '...' : subject);

              entry = $('<div>').text(subject);
	          this.draglayer.append(entry);
              break;
            }
            c++;
          }
        }
      }
    }

    this.draglayer.show();
    this.drag_active = true;
    this.triggerEvent('dragstart');
  }

  if (this.drag_active && this.draglayer) {
    var pos = rcube_event.get_mouse_pos(e);
    this.draglayer.css({ left:(pos.x+20)+'px', top:(pos.y-5 + (bw.ie ? document.documentElement.scrollTop : 0))+'px' });
    this.triggerEvent('dragmove', e?e:window.event);
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
  
  if (e.type == 'touchend') {
    if (e.changedTouches.length != 1)
      return rcube_event.cancel(e);
  }

  if (this.draglayer && this.draglayer.is(':visible')) {
    if (this.drag_start_pos)
      this.draglayer.animate(this.drag_start_pos, 300, 'swing').hide(20);
    else
      this.draglayer.hide();
  }

  if (this.drag_active)
    this.focus();
  this.drag_active = false;

  rcube_event.remove_listener({event:'mousemove', object:this, method:'drag_mouse_move'});
  rcube_event.remove_listener({event:'mouseup', object:this, method:'drag_mouse_up'});
  
  if (bw.iphone || bw.ipad) {
    rcube_event.remove_listener({event:'touchmove', object:this, method:'drag_mouse_move'});
    rcube_event.remove_listener({event:'touchend', object:this, method:'drag_mouse_up'});
  }

  // remove temp divs
  this.del_dragfix();

  this.triggerEvent('dragend');

  return rcube_event.cancel(e);
},


/**
 * Handler for mouse move events for dragging list column
 */
column_drag_mouse_move: function(e)
{
  if (this.drag_start) {
    // check mouse movement, of less than 3 pixels, don't start dragging
    var i, m = rcube_event.get_mouse_pos(e);

    if (!this.drag_mouse_start || (Math.abs(m.x - this.drag_mouse_start.x) < 3 && Math.abs(m.y - this.drag_mouse_start.y) < 3))
      return false;

    if (!this.col_draglayer) {
      var lpos = $(this.list).offset(),
        cells = this.list.tHead.rows[0].cells;

      // create dragging layer
      this.col_draglayer = $('<div>').attr('id', 'rcmcoldraglayer')
        .css(lpos).css({ position:'absolute', 'z-index':2001,
           'background-color':'white', opacity:0.75,
           height: (this.frame.offsetHeight-2)+'px', width: (this.frame.offsetWidth-2)+'px' })
        .appendTo(document.body)
        // ... and column position indicator
       .append($('<div>').attr('id', 'rcmcolumnindicator')
          .css({ position:'absolute', 'border-right':'2px dotted #555', 
          'z-index':2002, height: (this.frame.offsetHeight-2)+'px' }));

      this.cols = [];
      this.list_pos = this.list_min_pos = lpos.left;
      // save columns positions
      for (i=0; i<cells.length; i++) {
        this.cols[i] = cells[i].offsetWidth;
        if (this.column_fixed !== null && i <= this.column_fixed) {
          this.list_min_pos += this.cols[i];
        }
      }
    }

    this.col_draglayer.show();
    this.col_drag_active = true;
    this.triggerEvent('column_dragstart');
  }

  // set column indicator position
  if (this.col_drag_active && this.col_draglayer) {
    var i, cpos = 0, pos = rcube_event.get_mouse_pos(e);

    for (i=0; i<this.cols.length; i++) {
      if (pos.x >= this.cols[i]/2 + this.list_pos + cpos)
        cpos += this.cols[i];
      else
        break;
    }

    // handle fixed columns on left
    if (i == 0 && this.list_min_pos > pos.x)
      cpos = this.list_min_pos - this.list_pos;
    // empty list needs some assignment
    else if (!this.list.rowcount && i == this.cols.length)
      cpos -= 2;
    $('#rcmcolumnindicator').css({ width: cpos+'px'});
    this.triggerEvent('column_dragmove', e?e:window.event);
  }

  this.drag_start = false;

  return false;
},


/**
 * Handler for mouse up events for dragging list columns
 */
column_drag_mouse_up: function(e)
{
  document.onmousemove = null;

  if (this.col_draglayer) {
    (this.col_draglayer).remove();
    this.col_draglayer = null;
  }

  if (this.col_drag_active)
    this.focus();
  this.col_drag_active = false;

  rcube_event.remove_listener({event:'mousemove', object:this, method:'column_drag_mouse_move'});
  rcube_event.remove_listener({event:'mouseup', object:this, method:'column_drag_mouse_up'});
  // remove temp divs
  this.del_dragfix();

  if (this.selected_column !== null && this.cols && this.cols.length) {
    var i, cpos = 0, pos = rcube_event.get_mouse_pos(e);

    // find destination position
    for (i=0; i<this.cols.length; i++) {
      if (pos.x >= this.cols[i]/2 + this.list_pos + cpos)
        cpos += this.cols[i];
      else
        break;
    }

    if (i != this.selected_column && i != this.selected_column+1) {
      this.column_replace(this.selected_column, i);
    }
  }

  this.triggerEvent('column_dragend');

  return rcube_event.cancel(e);
},


/**
 * Creates a layer for drag&drop over iframes
 */
add_dragfix: function()
{
  $('iframe').each(function() {
    $('<div class="iframe-dragdrop-fix"></div>')
      .css({background: '#fff',
        width: this.offsetWidth+'px', height: this.offsetHeight+'px',
        position: 'absolute', opacity: '0.001', zIndex: 1000
      })
      .css($(this).offset())
      .appendTo(document.body);
  });
},


/**
 * Removes the layer for drag&drop over iframes
 */
del_dragfix: function()
{
  $('div.iframe-dragdrop-fix').each(function() { this.parentNode.removeChild(this); });
},


/**
 * Replaces two columns
 */
column_replace: function(from, to)
{
  var len, cells = this.list.tHead.rows[0].cells,
    elem = cells[from],
    before = cells[to],
    td = document.createElement('td');

  // replace header cells
  if (before)
    cells[0].parentNode.insertBefore(td, before);
  else
    cells[0].parentNode.appendChild(td);
  cells[0].parentNode.replaceChild(elem, td);

  // replace list cells
  for (r=0, len=this.list.tBodies[0].rows.length; r<len; r++) {
    row = this.list.tBodies[0].rows[r];

    elem = row.cells[from];
    before = row.cells[to];
    td = document.createElement('td');

    if (before)
      row.insertBefore(td, before);
    else
      row.appendChild(td);
    row.replaceChild(elem, td);
  }

  // update subject column position
  if (this.subject_col == from)
    this.subject_col = to > from ? to - 1 : to;
  else if (this.subject_col < from && to <= this.subject_col)
    this.subject_col++;
  else if (this.subject_col > from && to >= this.subject_col)
    this.subject_col--;

  this.triggerEvent('column_replace');
}

};

rcube_list_widget.prototype.addEventListener = rcube_event_engine.prototype.addEventListener;
rcube_list_widget.prototype.removeEventListener = rcube_event_engine.prototype.removeEventListener;
rcube_list_widget.prototype.triggerEvent = rcube_event_engine.prototype.triggerEvent;
