/*
 +-----------------------------------------------------------------------+
 | Roundcube List Widget                                                 |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2006-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Authors: Thomas Bruederli <roundcube@gmail.com>                       |
 |          Charles McNulty <charles@charlesmcnulty.com>                 |
 +-----------------------------------------------------------------------+
 | Requires: common.js                                                   |
 +-----------------------------------------------------------------------+
*/


/**
 * Roundcube List Widget class
 * @constructor
 */
function rcube_list_widget(list, p)
{
  // static contants
  this.ENTER_KEY = 13;
  this.DELETE_KEY = 46;
  this.BACKSPACE_KEY = 8;

  this.list = list ? list : null;
  this.tagname = this.list ? this.list.nodeName.toLowerCase() : 'table';
  this.id_regexp = /^rcmrow([a-z0-9\-_=\+\/]+)/i;
  this.rows = {};
  this.selection = [];
  this.rowcount = 0;
  this.colcount = 0;

  this.subject_col = -1;
  this.modkey = 0;
  this.multiselect = false;
  this.multiexpand = false;
  this.multi_selecting = false;
  this.draggable = false;
  this.column_movable = false;
  this.keyboard = false;
  this.toggleselect = false;

  this.drag_active = false;
  this.col_drag_active = false;
  this.column_fixed = null;
  this.last_selected = 0;
  this.shift_start = 0;
  this.focused = false;
  this.drag_mouse_start = null;
  this.dblclick_time = 500; // default value on MS Windows is 500
  this.row_init = function(){};  // @deprecated; use list.addEventListener('initrow') instead

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
  if (this.tagname == 'table' && this.list && this.list.tBodies[0]) {
    this.thead = this.list.tHead;
    this.tbody = this.list.tBodies[0];
  }
  else if (this.tagname != 'table' && this.list) {
    this.tbody = this.list;
  }

  if (this.tbody) {
    this.rows = {};
    this.rowcount = 0;

    var r, len, rows = this.tbody.childNodes;

    for (r=0, len=rows.length; r<len; r++) {
      this.rowcount += this.init_row(rows[r]) ? 1 : 0;
    }

    this.init_header();
    this.frame = this.list.parentNode;

    // set body events
    if (this.keyboard)
      rcube_event.add_listener({event:'keydown', object:this, method:'key_press'});
  }

  return this;
},


/**
 * Init list row and set mouse events on it
 */
init_row: function(row)
{
  // make references in internal array and set event handlers
  if (row && String(row.id).match(this.id_regexp)) {
    var self = this,
      uid = RegExp.$1;
    row.uid = uid;
    this.rows[uid] = {uid:uid, id:row.id, obj:row};

    // set eventhandlers to table row
    row.onmousedown = function(e){ return self.drag_row(e, this.uid); };
    row.onmouseup = function(e){ return self.click_row(e, this.uid); };

    if (bw.touch && row.addEventListener) {
      row.addEventListener('touchstart', function(e) {
        if (e.touches.length == 1) {
          self.touchmoved = false;
          self.drag_row(rcube_event.touchevent(e.touches[0]), this.uid)
        }
      }, false);
      row.addEventListener('touchend', function(e) {
        if (e.changedTouches.length == 1) {
          if (!self.touchmoved && !self.click_row(rcube_event.touchevent(e.changedTouches[0]), this.uid))
            e.preventDefault();
        }
      }, false);
      row.addEventListener('touchmove', function(e) {
        if (e.changedTouches.length == 1) {
          self.touchmoved = true;
          if (self.drag_active)
            e.preventDefault();
        }
      }, false);
    }

    if (document.all)
      row.onselectstart = function() { return false; };

    this.row_init(this.rows[uid]);  // legacy support
    this.triggerEvent('initrow', this.rows[uid]);

    return true;
  }
},


/**
 * Init list column headers and set mouse events on them
 */
init_header: function()
{
  if (this.thead) {
    this.colcount = 0;

    if (this.fixed_header) {  // copy (modified) fixed header back to the actual table
      $(this.list.tHead).replaceWith($(this.fixed_header).find('thead').clone());
      $(this.list.tHead).find('tr td').attr('style', '');  // remove fixed widths
    }
    else if (!bw.touch && this.list.className.indexOf('fixedheader') >= 0) {
      this.init_fixed_header();
    }

    var col, r, p = this;
    // add events for list columns moving
    if (this.column_movable && this.thead && this.thead.rows) {
      for (r=0; r<this.thead.rows[0].cells.length; r++) {
        if (this.column_fixed == r)
          continue;
        col = this.thead.rows[0].cells[r];
        col.onmousedown = function(e){ return p.drag_column(e, this); };
        this.colcount++;
      }
    }
  }
},

init_fixed_header: function()
{
  var clone = $(this.list.tHead).clone();

  if (!this.fixed_header) {
    this.fixed_header = $('<table>')
      .attr('class', this.list.className + ' fixedcopy')
      .css({ position:'fixed' })
      .append(clone)
      .append('<tbody></tbody>');
    $(this.list).before(this.fixed_header);

    var me = this;
    $(window).resize(function(){ me.resize() });
    $(window).scroll(function(){
      var w = $(window);
      me.fixed_header.css('marginLeft', (-w.scrollLeft()) + 'px');
      if (!bw.webkit)
        me.fixed_header.css('marginTop', (-w.scrollTop()) + 'px');
    });
  }
  else {
    $(this.fixed_header).find('thead').replaceWith(clone);
  }

  this.thead = clone.get(0);
  this.resize();
},

resize: function()
{
    if (!this.fixed_header)
      return;

    var column_widths = [];

    // get column widths from original thead
    $(this.tbody).parent().find('thead tr td').each(function(index) {
      column_widths[index] = $(this).width();
    });

    // apply fixed widths to fixed table header
    $(this.thead).parent().width($(this.tbody).parent().width());
    $(this.thead).find('tr td').each(function(index) {
      $(this).css('width', column_widths[index]);
    });

    $(window).scroll();
},

/**
 * Remove all list rows
 */
clear: function(sel)
{
  if (this.tagname == 'table') {
    var tbody = document.createElement('tbody');
    this.list.insertBefore(tbody, this.tbody);
    this.list.removeChild(this.list.tBodies[1]);
    this.tbody = tbody;
  }
  else {
    $(this.row_tagname() + ':not(.thead)', this.tbody).remove();
  }

  this.rows = {};
  this.rowcount = 0;

  if (sel)
    this.clear_selection();

  // reset scroll position (in Opera)
  if (this.frame)
    this.frame.scrollTop = 0;

  // fix list header after removing any rows
  this.resize();
},


/**
 * 'remove' message row from list (just hide it)
 */
remove_row: function(uid, sel_next)
{
  var self = this, node = this.rows[uid] ? this.rows[uid].obj : null;

  if (!node)
    return;

  node.style.display = 'none';

  if (sel_next)
    this.select_next();

  delete this.rows[uid];
  this.rowcount--;

  // fix list header after removing any rows
  clearTimeout(this.resize_timeout)
  this.resize_timeout = setTimeout(function() { self.resize(); }, 50);
},


/**
 * Add row to the list and initialize it
 */
insert_row: function(row, before)
{
  var self = this, tbody = this.tbody;

  // create a real dom node first
  if (row.nodeName === undefined) {
    // for performance reasons use DOM instead of jQuery here
    var domrow = document.createElement(this.row_tagname());
    if (row.id) domrow.id = row.id;
    if (row.className) domrow.className = row.className;
    if (row.style) $.extend(domrow.style, row.style);

    for (var e, domcell, col, i=0; row.cols && i < row.cols.length; i++) {
      col = row.cols[i];
      domcell = document.createElement(this.col_tagname());
      if (col.className) domcell.className = col.className;
      if (col.innerHTML) domcell.innerHTML = col.innerHTML;
      for (e in col.events)
        domcell['on' + e] = col.events[e];
      domrow.appendChild(domcell);
    }

    row = domrow;
  }

  if (before && tbody.childNodes.length)
    tbody.insertBefore(row, (typeof before == 'object' && before.parentNode == tbody) ? before : tbody.firstChild);
  else
    tbody.appendChild(row);

  this.init_row(row);
  this.rowcount++;

  // fix list header after adding any rows
  clearTimeout(this.resize_timeout)
  this.resize_timeout = setTimeout(function() { self.resize(); }, 50);
},

/**
 * 
 */
update_row: function(id, cols, newid, select)
{
  var row = this.rows[id];
  if (!row) return false;

  var domrow = row.obj;
  for (var domcell, col, i=0; cols && i < cols.length; i++) {
    this.get_cell(domrow, i).html(cols[i]);
  }

  if (newid) {
    delete this.rows[id];
    domrow.id = 'rcmrow' + newid;
    this.init_row(domrow);

    if (select)
      this.selection[0] = newid;
  }
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

  // Un-focus already focused elements (#1487123, #1487316, #1488600, #1488620)
  // It looks that window.focus() does the job for all browsers, but not Firefox (#1489058)
  $('iframe,:focus:not(body)').blur();
  window.focus();

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
      $(this.rows[id].obj).removeClass('selected focused').addClass('unfocused');
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
    for (var i=0; i<this.thead.rows[0].cells.length; i++) {
      if (col == this.thead.rows[0].cells[i]) {
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
  if (!this.is_event_target(e))
    return true;

  // accept right-clicks
  if (rcube_event.get_button(e) == 2)
    return true;

  this.in_selection_before = e && e.istouch || this.in_selection(id) ? id : false;

  // selects currently unselected row
  if (!this.in_selection_before) {
    var mod_key = rcube_event.get_modifier(e);
    this.select_row(id, mod_key, true);
  }

  if (this.draggable && this.selection.length && this.in_selection(id)) {
    this.drag_start = true;
    this.drag_mouse_start = rcube_event.get_mouse_pos(e);

    rcube_event.add_listener({event:'mousemove', object:this, method:'drag_mouse_move'});
    rcube_event.add_listener({event:'mouseup', object:this, method:'drag_mouse_up'});
    if (bw.touch) {
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
  // don't do anything (another action processed before)
  if (!this.is_event_target(e))
    return true;

  var now = new Date().getTime(),
    dblclicked = now - this.rows[id].clicked < this.dblclick_time;

  // unselects currently selected row
  if (!this.drag_active && !dblclicked && this.in_selection_before == id)
    this.select_row(id, rcube_event.get_modifier(e), true);

  this.drag_start = false;
  this.in_selection_before = false;

  // row was double clicked
  if (this.rowcount && dblclicked && this.in_selection(id)) {
    this.triggerEvent('dblclick');
    now = 0;
  }
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


/**
 * Check target of the current event
 */
is_event_target: function(e)
{
  var target = rcube_event.get_target(e),
    tagname = target.tagName.toLowerCase();

  return !(target && (tagname == 'input' || tagname == 'img' || (tagname != 'a' && target.onclick)));
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
  var r, depth = row.depth,
    new_row = row ? row.obj.nextSibling : null;

  row.expanded = false;
  this.triggerEvent('expandcollapse', { uid:row.uid, expanded:row.expanded, obj:row.obj });

  while (new_row) {
    if (new_row.nodeType == 1) {
      r = this.rows[new_row.uid];
      if (r && r.depth <= depth)
        break;

      $(new_row).css('display', 'none');
      if (r.expanded) {
        r.expanded = false;
        this.triggerEvent('expandcollapse', { uid:r.uid, expanded:r.expanded, obj:new_row });
      }
    }
    new_row = new_row.nextSibling;
  }

  this.resize();
  this.triggerEvent('listupdate');

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
    this.triggerEvent('expandcollapse', { uid:row.uid, expanded:row.expanded, obj:row.obj });
  }
  else {
    var tbody = this.tbody;
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
              this.triggerEvent('expandcollapse', { uid:r.uid, expanded:r.expanded, obj:new_row });
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

  this.resize();
  this.triggerEvent('listupdate');
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
    this.triggerEvent('expandcollapse', { uid:row.uid, expanded:row.expanded, obj:row.obj });

    // don't collapse sub-root tree in multiexpand mode 
    if (depth && this.multiexpand)
      return false;
  }
  else {
    new_row = this.tbody.firstChild;
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
          this.triggerEvent('expandcollapse', { uid:r.uid, expanded:r.expanded, obj:new_row });
        }
      }
    }
    new_row = new_row.nextSibling;
  }

  this.resize();
  this.triggerEvent('listupdate');
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
    this.triggerEvent('expandcollapse', { uid:row.uid, expanded:row.expanded, obj:row.obj });
  }
  else {
    new_row = this.tbody.firstChild;
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
          this.triggerEvent('expandcollapse', { uid:r.uid, expanded:r.expanded, obj:new_row });
        }
      }
    }
    new_row = new_row.nextSibling;
  }

  this.resize();
  this.triggerEvent('listupdate');
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
  if (!this.rowcount)
    return false;

  var last_selected_row = this.rows[this.last_selected],
    new_row = last_selected_row ? last_selected_row.obj.nextSibling : null;

  while (new_row && (new_row.nodeType != 1 || new_row.style.display == 'none'))
    new_row = new_row.nextSibling;

  return new_row;
},

get_prev_row: function()
{
  if (!this.rowcount)
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
    var i, len, rows = this.tbody.childNodes;

    for (i=0, len=rows.length-1; i<len; i++)
      if (rows[i].id && String(rows[i].id).match(this.id_regexp) && this.rows[RegExp.$1] != null)
        return RegExp.$1;
  }

  return null;
},

get_last_row: function()
{
  if (this.rowcount) {
    var i, rows = this.tbody.childNodes;

    for (i=rows.length-1; i>=0; i--)
      if (rows[i].id && String(rows[i].id).match(this.id_regexp) && this.rows[RegExp.$1] != null)
        return RegExp.$1;
  }

  return null;
},

row_tagname: function()
{
  var row_tagnames = { table:'tr', ul:'li', '*':'div' };
  return row_tagnames[this.tagname] || row_tagnames['*'];
},

col_tagname: function()
{
  var col_tagnames = { table:'td', '*':'span' };
  return col_tagnames[this.tagname] || col_tagnames['*'];
},

get_cell: function(row, index)
{
  return $(this.col_tagname(), row).eq(index);
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
        if (with_mouse) {
          this.shift_start = id;
          this.highlight_row(id, true);
        }
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
    this.select_row(row, mod_key, false);
    this.scrollto(row);
  }
},


/**
 * Select last row
 */
select_last: function(mod_key)
{
  var row = this.get_last_row();
  if (row) {
    this.select_row(row, mod_key, false);
    this.scrollto(row);
  }
},


/**
 * Add all childs of the given row to selection
 */
select_children: function(uid)
{
  var i, children = this.row_children(uid), len = children.length;

  for (i=0; i<len; i++)
    if (!this.in_selection(children[i]))
      this.select_row(children[i], CONTROL_KEY, true);
},


/**
 * Perform selection when shift key is pressed
 */
shift_select: function(id, control)
{
  if (!this.rows[this.shift_start] || !this.selection.length)
    this.shift_start = id;

  var n, i, j, to_row = this.rows[id],
    from_rowIndex = this._rowIndex(this.rows[this.shift_start].obj),
    to_rowIndex = this._rowIndex(to_row.obj);

  // if we're going down the list, and we hit a thread, and it's closed, select the whole thread
  if (from_rowIndex < to_rowIndex && !to_row.expanded && to_row.has_children)
    if (to_row = this.rows[(this.row_children(id)).pop()])
      to_rowIndex = this._rowIndex(to_row.obj);

  i = ((from_rowIndex < to_rowIndex) ? from_rowIndex : to_rowIndex),
  j = ((from_rowIndex > to_rowIndex) ? from_rowIndex : to_rowIndex);

  // iterate through the entire message list
  for (n in this.rows) {
    if (this._rowIndex(this.rows[n].obj) >= i && this._rowIndex(this.rows[n].obj) <= j) {
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
 * Helper method to emulate the rowIndex property of non-tr elements
 */
_rowIndex: function(obj)
{
  return (obj.rowIndex !== undefined) ? obj.rowIndex : $(obj).prevAll().length;
},

/**
 * Check if given id is part of the current selection
 */
in_selection: function(id)
{
  for (var n in this.selection)
    if (this.selection[n] == id)
      return true;

  return false;
},


/**
 * Select each row in list
 */
select_all: function(filter)
{
  if (!this.rowcount)
    return false;

  // reset but remember selection first
  var n, select_before = this.selection.join(',');
  this.selection = [];

  for (n in this.rows) {
    if (!filter || this.rows[n][filter] == true) {
      this.last_selected = n;
      this.highlight_row(n, true, true);
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
  if (!this.rowcount)
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
clear_selection: function(id, no_event)
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

  if (num_select && !this.selection.length && !no_event)
    this.triggerEvent('select');
},


/**
 * Getter for the selection array
 */
get_selection: function(deep)
{
  var res = $.merge([], this.selection);

  // return children of selected threads even if only root is selected
  if (deep !== false && res.length) {
    for (var uid, uids, i=0, len=res.length; i<len; i++) {
      uid = res[i];
      if (this.rows[uid] && this.rows[uid].has_children && !this.rows[uid].expanded) {
        uids = this.row_children(uid);
        for (var j=0, uids_len=uids.length; j<uids_len; j++) {
          uid = uids[j];
          if (!this.in_selection(uid))
            res.push(uid);
        }
      }
    }
  }

  return res;
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
highlight_row: function(id, multiple, norecur)
{
  if (!this.rows[id])
    return;

  if (!multiple) {
    if (this.selection.length > 1 || !this.in_selection(id)) {
      this.clear_selection(null, true);
      this.selection[0] = id;
      $(this.rows[id].obj).addClass('selected');
    }
  }
  else {
    if (!this.in_selection(id)) { // select row
      this.selection.push(id);
      $(this.rows[id].obj).addClass('selected');
      if (!norecur && !this.rows[id].expanded)
        this.highlight_children(id, true);
    }
    else { // unselect row
      var p = $.inArray(id, this.selection),
        a_pre = this.selection.slice(0, p),
        a_post = this.selection.slice(p+1, this.selection.length);

      this.selection = a_pre.concat(a_post);
      $(this.rows[id].obj).removeClass('selected').removeClass('unfocused');
      if (!norecur && !this.rows[id].expanded)
        this.highlight_children(id, false);
    }
  }
},


/**
 * Highlight/unhighlight all childs of the given row
 */
highlight_children: function(id, status)
{
  var i, selected,
    children = this.row_children(id), len = children.length;

  for (i=0; i<len; i++) {
    selected = this.in_selection(children[i]);
    if ((status && !selected) || (!status && selected))
      this.highlight_row(children[i], true, true);
  }
},


/**
 * Handler for keyboard events
 */
key_press: function(e)
{
  var target = e.target || {};

  if (this.focused != true || target.nodeName == 'INPUT' || target.nodeName == 'TEXTAREA' || target.nodeName == 'SELECT')
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

    case 32:
      rcube_event.cancel(e);
      return this.select_row(this.last_selected, mod_key, true);

    case 37: // Left arrow key
    case 39: // Right arrow key
      // Stop propagation
      rcube_event.cancel(e);
      var ret = this.use_arrow_key(keyCode, mod_key);
      this.key_pressed = keyCode;
      this.modkey = mod_key;
      this.triggerEvent('keypress');
      this.modkey = 0;
      return ret;

    case 36: // Home
      this.select_first(mod_key);
      return rcube_event.cancel(e);

    case 35: // End
      this.select_last(mod_key);
      return rcube_event.cancel(e);

    case 27:
      if (this.drag_active)
        return this.drag_mouse_up(e);

      if (this.col_drag_active) {
        this.selected_column = null;
        return this.column_drag_mouse_up(e);
      }

      return rcube_event.cancel(e);

    default:
      this.key_pressed = keyCode;
      this.modkey = mod_key;
      this.triggerEvent('keypress');
      this.modkey = 0;

      if (this.key_pressed == this.BACKSPACE_KEY)
        return rcube_event.cancel(e);
  }

  return true;
},


/**
 * Special handling method for arrow keys
 */
use_arrow_key: function(keyCode, mod_key)
{
  var new_row,
    selected_row = this.rows[this.last_selected];

  // Safari uses the nonstandard keycodes 63232/63233 for up/down, if we're
  // using the keypress event (but not the keydown or keyup event).
  if (keyCode == 40 || keyCode == 63233) // down arrow key pressed
    new_row = this.get_next_row();
  else if (keyCode == 38 || keyCode == 63232) // up arrow key pressed
    new_row = this.get_prev_row();
  else {
    if (!selected_row || !selected_row.has_children)
      return;

    // expand
    if (keyCode == 39) {
      if (selected_row.expanded)
        return;

      if (mod_key == CONTROL_KEY || this.multiexpand)
        this.expand_all(selected_row);
      else
        this.expand(selected_row);
    }
    // collapse
    else {
      if (!selected_row.expanded)
        return;

      if (mod_key == CONTROL_KEY || this.multiexpand)
        this.collapse_all(selected_row);
      else
        this.collapse(selected_row);
    }

    this.update_expando(selected_row.uid, selected_row.expanded);

    return false;
  }

  if (new_row) {
    this.select_row(new_row.uid, mod_key, false);
    this.scrollto(new_row.uid);
  }

  return false;
},


/**
 * Try to scroll the list to make the specified row visible
 */
scrollto: function(id)
{
  var row = this.rows[id] ? this.rows[id].obj : null;

  if (row && this.frame) {
    var scroll_to = Number(row.offsetTop),
      head_offset = 0;

    // expand thread if target row is hidden (collapsed)
    if (!scroll_to && this.rows[id].parent_uid) {
      var parent = this.find_root(this.rows[id].uid);
      this.expand_all(this.rows[parent]);
      scroll_to = Number(row.offsetTop);
    }

    if (this.fixed_header)
      head_offset = Number(this.thead.offsetHeight);

    // if row is above the frame (or behind header)
    if (scroll_to < Number(this.frame.scrollTop) + head_offset) {
      // scroll window so that row isn't behind header
      this.frame.scrollTop = scroll_to - head_offset;
    }
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
    if (e.touches.length == 1 && e.changedTouches.length == 1)
      e = rcube_event.touchevent(e.changedTouches[0]);
    else
      return rcube_event.cancel(e);
  }

  if (this.drag_start) {
    // check mouse movement, of less than 3 pixels, don't start dragging
    var m = rcube_event.get_mouse_pos(e),
      limit = 10, selection = [], self = this;

    if (!this.drag_mouse_start || (Math.abs(m.x - this.drag_mouse_start.x) < 3 && Math.abs(m.y - this.drag_mouse_start.y) < 3))
      return false;

    // remember dragging start position
    this.drag_start_pos = {left: m.x, top: m.y};

    // initialize drag layer
    if (!this.draglayer)
      this.draglayer = $('<div>').attr('id', 'rcmdraglayer')
        .css({position: 'absolute', display: 'none', 'z-index': 2000})
        .appendTo(document.body);
    else
      this.draglayer.html('');

    // get selected rows (in display order), don't use this.selection here
    $(this.row_tagname() + '.selected', this.tbody).each(function() {
      if (!String(this.id).match(self.id_regexp))
        return;

      var uid = RegExp.$1, row = self.rows[uid];

      if (!row || $.inArray(uid, selection) > -1)
        return;

      selection.push(uid);

      // also handle children of (collapsed) trees for dragging (they might be not selected)
      if (row.has_children && !row.expanded)
        $.each(self.row_children(uid), function() {
          if ($.inArray(this, selection) > -1)
            return;
          selection.push(this);
        });

      // break the loop asap
      if (selection.length > limit + 1)
        return false;
    });

    // append subject (of every row up to the limit) to the drag layer
    $.each(selection, function(i, uid) {
      if (i > limit) {
        self.draglayer.append('...');
        return false;
      }

      $('> ' + self.col_tagname(), self.rows[uid].obj).each(function(n, cell) {
        if (self.subject_col < 0 || (self.subject_col >= 0 && self.subject_col == n)) {
          var subject = $(cell).text();

          if (subject) {
            // remove leading spaces
            subject = $.trim(subject);
            // truncate line to 50 characters
            subject = (subject.length > 50 ? subject.substring(0, 50) + '...' : subject);

            self.draglayer.append($('<div>').text(subject));
            return false;
          }
        }
      });
    });

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

  if (bw.touch) {
    rcube_event.remove_listener({event:'touchmove', object:this, method:'drag_mouse_move'});
    rcube_event.remove_listener({event:'touchend', object:this, method:'drag_mouse_up'});
  }

  // remove temp divs
  this.del_dragfix();

  this.triggerEvent('dragend', e);

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
        cells = this.thead.rows[0].cells;

      // fix layer position when list is scrolled
      lpos.top += this.list.scrollTop + this.list.parentNode.scrollTop;

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

  this.triggerEvent('column_dragend', e);

  return rcube_event.cancel(e);
},


/**
 * Returns IDs of all rows in a thread (except root) for specified root
 */
row_children: function(uid)
{
  if (!this.rows[uid] || !this.rows[uid].has_children)
    return [];

  var res = [], depth = this.rows[uid].depth,
    row = this.rows[uid].obj.nextSibling;

  while (row) {
    if (row.nodeType == 1) {
      if (r = this.rows[row.uid]) {
        if (!r.depth || r.depth <= depth)
          break;
        res.push(r.uid);
      }
    }
    row = row.nextSibling;
  }

  return res;
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
  $('div.iframe-dragdrop-fix').remove();
},


/**
 * Replaces two columns
 */
column_replace: function(from, to)
{
  // only supported for <table> lists
  if (!this.thead || !this.thead.rows)
    return;

  var len, cells = this.thead.rows[0].cells,
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
  for (r=0, len=this.tbody.rows.length; r<len; r++) {
    row = this.tbody.rows[r];

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

  if (this.fixed_header)
    this.init_header();

  this.triggerEvent('column_replace');
}

};

rcube_list_widget.prototype.addEventListener = rcube_event_engine.prototype.addEventListener;
rcube_list_widget.prototype.removeEventListener = rcube_event_engine.prototype.removeEventListener;
rcube_list_widget.prototype.triggerEvent = rcube_event_engine.prototype.triggerEvent;
