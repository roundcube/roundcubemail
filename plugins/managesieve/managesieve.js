/* Sieve Filters (tab) */

if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {

    var tab = $('<span>').attr('id', 'settingstabpluginmanagesieve').addClass('tablink');
    var button = $('<a>').attr('href', rcmail.env.comm_path+'&_action=plugin.managesieve')
      .attr('title', rcmail.gettext('managesieve.managefilters'))
      .html(rcmail.gettext('managesieve.filters'))
      .bind('click', function(e){ return rcmail.command('plugin.managesieve', this) })
      .appendTo(tab);

    // add button and register commands
    rcmail.add_element(tab, 'tabs');
    rcmail.register_command('plugin.managesieve', function() { rcmail.goto_url('plugin.managesieve') }, true);
    rcmail.register_command('plugin.managesieve-save', function() { rcmail.managesieve_save() }, true);
    rcmail.register_command('plugin.managesieve-add', function() { rcmail.managesieve_add() }, true);
    rcmail.register_command('plugin.managesieve-del', function() { rcmail.managesieve_del() }, true);
    rcmail.register_command('plugin.managesieve-up', function() { rcmail.managesieve_up() }, true);
    rcmail.register_command('plugin.managesieve-down', function() { rcmail.managesieve_down() }, true);
    rcmail.register_command('plugin.managesieve-set', function() { rcmail.managesieve_set() }, true);
    rcmail.register_command('plugin.managesieve-setadd', function() { rcmail.managesieve_setadd() }, true);
    rcmail.register_command('plugin.managesieve-setdel', function() { rcmail.managesieve_setdel() }, true);
    rcmail.register_command('plugin.managesieve-setact', function() { rcmail.managesieve_setact() }, true);
    rcmail.register_command('plugin.managesieve-setget', function() { rcmail.managesieve_setget() }, true);

    if (rcmail.env.action == 'plugin.managesieve') {
      if (rcmail.gui_objects.sieveform) {
        rcmail.enable_command('plugin.managesieve-save', true);
      }
      else {
        rcmail.enable_command('plugin.managesieve-del', 'plugin.managesieve-up',
          'plugin.managesieve-down', false);
        rcmail.enable_command('plugin.managesieve-add', 'plugin.managesieve-setadd', !rcmail.env.sieveconnerror);
      }

      // Create layer for form tips
      if (!rcmail.env.framed) {
        rcmail.env.ms_tip_layer = $('<div id="managesieve-tip" class="popupmenu"></div>');
        rcmail.env.ms_tip_layer.appendTo(document.body);
      }

      if (rcmail.gui_objects.filterslist) {
        var p = rcmail;
        rcmail.filters_list = new rcube_list_widget(rcmail.gui_objects.filterslist, {multiselect:false, draggable:false, keyboard:false});
        rcmail.filters_list.addEventListener('select', function(o){ p.managesieve_select(o); });
        rcmail.filters_list.init();
        rcmail.filters_list.focus();

        rcmail.enable_command('plugin.managesieve-set', true);
        rcmail.enable_command('plugin.managesieve-setact', 'plugin.managesieve-setget', rcmail.gui_objects.filtersetslist.length);
        rcmail.enable_command('plugin.managesieve-setdel', rcmail.gui_objects.filtersetslist.length > 1);

        $('#'+rcmail.buttons['plugin.managesieve-setact'][0].id).attr('title', rcmail.gettext('managesieve.filterset'
          + (rcmail.gui_objects.filtersetslist.value == rcmail.env.active_set ? 'deact' : 'act')));
      }
    }
    if (rcmail.gui_objects.sieveform && rcmail.env.rule_disabled)
      $('#disabled').attr('checked', true);
  });
};

/*********************************************************/
/*********     Managesieve filters methods       *********/
/*********************************************************/

rcube_webmail.prototype.managesieve_add = function()
{
  this.load_managesieveframe();
  this.filters_list.clear_selection();
};

rcube_webmail.prototype.managesieve_del = function()
{
  var id = this.filters_list.get_single_selection();
  if (confirm(this.get_label('managesieve.filterdeleteconfirm')))
    this.http_request('plugin.managesieve',
      '_act=delete&_fid='+this.filters_list.rows[id].uid, true);
};

rcube_webmail.prototype.managesieve_up = function()
{
  var id = this.filters_list.get_single_selection();
  this.http_request('plugin.managesieve',
    '_act=up&_fid='+this.filters_list.rows[id].uid, true);
};

rcube_webmail.prototype.managesieve_down = function()
{
  var id = this.filters_list.get_single_selection();
  this.http_request('plugin.managesieve',
    '_act=down&_fid='+this.filters_list.rows[id].uid, true);
};

rcube_webmail.prototype.managesieve_rowid = function(id)
{
  var i, rows = this.filters_list.rows;

  for (i=0; i<rows.length; i++)
    if (rows[i] != null && rows[i].uid == id)
      return i;
}

rcube_webmail.prototype.managesieve_updatelist = function(action, name, id, disabled)
{
  this.set_busy(true);

  switch (action) {
    case 'delete':
      this.filters_list.remove_row(this.managesieve_rowid(id));
      this.filters_list.clear_selection();
      this.enable_command('plugin.managesieve-del', 'plugin.managesieve-up', 'plugin.managesieve-down', false);
      this.show_contentframe(false);

      // re-numbering filters
      var i, rows = this.filters_list.rows;
      for (i=0; i<rows.length; i++) {
        if (rows[i] != null && rows[i].uid > id)
          rows[i].uid = rows[i].uid-1;
      }
      break;

    case 'down':
      var from, fromstatus, status, rows = this.filters_list.rows;

      // we need only to replace filter names...
      for (var i=0; i<rows.length; i++) {
        if (rows[i]==null) { // removed row
          continue;
        }
        else if (rows[i].uid == id) {
          from = rows[i].obj;
          fromstatus = $(from).hasClass('disabled');
        }
        else if (rows[i].uid == id+1) {
          name = rows[i].obj.cells[0].innerHTML;
          status = $(rows[i].obj).hasClass('disabled');
          rows[i].obj.cells[0].innerHTML = from.cells[0].innerHTML;
          from.cells[0].innerHTML = name;
          $(from)[status?'addClass':'removeClass']('disabled');
          $(rows[i].obj)[fromstatus?'addClass':'removeClass']('disabled');
          this.filters_list.highlight_row(i);
          break;
        }
      }
      // ... and disable/enable Down button
      this.filters_listbuttons();
      break;

    case 'up':
      var from, status, fromstatus, rows = this.filters_list.rows;

      // we need only to replace filter names...
      for (var i=0; i<rows.length; i++) {
        if (rows[i] == null) { // removed row
          continue;
        }
        else if (rows[i].uid == id-1) {
          from = rows[i].obj;
          fromstatus = $(from).hasClass('disabled');
          this.filters_list.highlight_row(i);
        }
        else if (rows[i].uid == id) {
          name = rows[i].obj.cells[0].innerHTML;
          status = $(rows[i].obj).hasClass('disabled');
          rows[i].obj.cells[0].innerHTML = from.cells[0].innerHTML;
          from.cells[0].innerHTML = name;
          $(from)[status?'addClass':'removeClass']('disabled');
          $(rows[i].obj)[fromstatus?'addClass':'removeClass']('disabled');
          break;
        }
      }
      // ... and disable/enable Up button
      this.filters_listbuttons();
      break;

    case 'update':
      var rows = parent.rcmail.filters_list.rows;
      for (var i=0; i<rows.length; i++)
        if (rows[i] && rows[i].uid == id) {
          rows[i].obj.cells[0].innerHTML = name;
          if (disabled)
            $(rows[i].obj).addClass('disabled');
          else
            $(rows[i].obj).removeClass('disabled');
          break;
        }
      break;

    case 'add':
      var row, new_row, td, list = parent.rcmail.filters_list;

      if (!list)
        break;

      for (var i=0; i<list.rows.length; i++)
        if (list.rows[i] != null && String(list.rows[i].obj.id).match(/^rcmrow/))
          row = list.rows[i].obj;

      if (row) {
        new_row = parent.document.createElement('tr');
        new_row.id = 'rcmrow'+id;
        td = parent.document.createElement('td');
        new_row.appendChild(td);
        list.insert_row(new_row, false);
        if (disabled)
          $(new_row).addClass('disabled');
          if (row.cells[0].className)
            td.className = row.cells[0].className;

           td.innerHTML = name;
        list.highlight_row(id);

        parent.rcmail.enable_command('plugin.managesieve-del', 'plugin.managesieve-up', true);
      }
      else // refresh whole page
        parent.rcmail.goto_url('plugin.managesieve');
      break;
  }

  this.set_busy(false);
};

rcube_webmail.prototype.managesieve_select = function(list)
{
  var id = list.get_single_selection();
  if (id != null)
    this.load_managesieveframe(list.rows[id].uid);
};

rcube_webmail.prototype.managesieve_save = function()
{
  if (parent.rcmail && parent.rcmail.filters_list && this.gui_objects.sieveform.name != 'filtersetform') {
    var id = parent.rcmail.filters_list.get_single_selection();
    if (id != null)
      this.gui_objects.sieveform.elements['_fid'].value = parent.rcmail.filters_list.rows[id].uid;
  }
  this.gui_objects.sieveform.submit();
};

// load filter frame
rcube_webmail.prototype.load_managesieveframe = function(id)
{
  if (typeof(id) != 'undefined' && id != null) {
    this.enable_command('plugin.managesieve-del', true);
    this.filters_listbuttons();
  }
  else
    this.enable_command('plugin.managesieve-up', 'plugin.managesieve-down', 'plugin.managesieve-del', false);

  if (this.env.contentframe && window.frames && window.frames[this.env.contentframe]) {
    target = window.frames[this.env.contentframe];
    var msgid = this.set_busy(true, 'loading');
    target.location.href = this.env.comm_path+'&_action=plugin.managesieve&_framed=1&_fid='+id+'&_unlock='+msgid;
  }
};

// enable/disable Up/Down buttons
rcube_webmail.prototype.filters_listbuttons = function()
{
  var id = this.filters_list.get_single_selection(),
    rows = this.filters_list.rows;

  for (var i=0; i<rows.length; i++) {
    if (rows[i] == null) { // removed row
    }
    else if (i == id) {
      this.enable_command('plugin.managesieve-up', false);
      break;
    }
    else {
      this.enable_command('plugin.managesieve-up', true);
      break;
    }
  }

  for (var i=rows.length-1; i>0; i--) {
    if (rows[i] == null) { // removed row
    }
    else if (i == id) {
      this.enable_command('plugin.managesieve-down', false);
      break;
    }
    else {
      this.enable_command('plugin.managesieve-down', true);
      break;
    }
  } 
};

// operations on filters form
rcube_webmail.prototype.managesieve_ruleadd = function(id)
{
  this.http_post('plugin.managesieve', '_act=ruleadd&_rid='+id);
};

rcube_webmail.prototype.managesieve_rulefill = function(content, id, after)
{
  if (content != '') {
    // create new element
    var div = document.getElementById('rules'),
      row = document.createElement('div');

    this.managesieve_insertrow(div, row, after);
    // fill row after inserting (for IE)
    row.setAttribute('id', 'rulerow'+id);
    row.className = 'rulerow';
    row.innerHTML = content;

    this.managesieve_formbuttons(div);
  }
};

rcube_webmail.prototype.managesieve_ruledel = function(id)
{
  if (confirm(this.get_label('managesieve.ruledeleteconfirm'))) {
    var row = document.getElementById('rulerow'+id);
    row.parentNode.removeChild(row);
    this.managesieve_formbuttons(document.getElementById('rules'));
  }
};

rcube_webmail.prototype.managesieve_actionadd = function(id)
{
  this.http_post('plugin.managesieve', '_act=actionadd&_aid='+id);
};

rcube_webmail.prototype.managesieve_actionfill = function(content, id, after)
{
  if (content != '') {
    var div = document.getElementById('actions'),
      row = document.createElement('div');

    this.managesieve_insertrow(div, row, after);
    // fill row after inserting (for IE)
    row.className = 'actionrow';
    row.setAttribute('id', 'actionrow'+id);
    row.innerHTML = content;

    this.managesieve_formbuttons(div);
  }
};

rcube_webmail.prototype.managesieve_actiondel = function(id)
{
  if (confirm(this.get_label('managesieve.actiondeleteconfirm'))) {
    var row = document.getElementById('actionrow'+id);
    row.parentNode.removeChild(row);
    this.managesieve_formbuttons(document.getElementById('actions'));
  }
};

// insert rule/action row in specified place on the list
rcube_webmail.prototype.managesieve_insertrow = function(div, row, after)
{
  for (var i=0; i<div.childNodes.length; i++) {
    if (div.childNodes[i].id == (div.id == 'rules' ? 'rulerow' : 'actionrow')  + after)
      break;
  }

  if (div.childNodes[i+1])
    div.insertBefore(row, div.childNodes[i+1]);
  else
    div.appendChild(row);
};

// update Delete buttons status
rcube_webmail.prototype.managesieve_formbuttons = function(div)
{
  var i, button, buttons = [];

  // count and get buttons
  for (i=0; i<div.childNodes.length; i++) {
    if (div.id == 'rules' && div.childNodes[i].id) {
      if (/rulerow/.test(div.childNodes[i].id))
        buttons.push('ruledel' + div.childNodes[i].id.replace(/rulerow/, ''));
    }
    else if (div.childNodes[i].id) {
      if (/actionrow/.test(div.childNodes[i].id))
        buttons.push( 'actiondel' + div.childNodes[i].id.replace(/actionrow/, ''));
    }
  }

  for (i=0; i<buttons.length; i++) {
    button = document.getElementById(buttons[i]);
    if (i>0 || buttons.length>1) {
      $(button).removeClass('disabled');
      button.removeAttribute('disabled');
    }
    else {
      $(button).addClass('disabled');
      button.setAttribute('disabled', true);
    }
  }
};

// Set change
rcube_webmail.prototype.managesieve_set = function()
{
  var script = $(this.gui_objects.filtersetslist).val();
  location.href = this.env.comm_path+'&_action=plugin.managesieve&_set='+script;
};

// Script download
rcube_webmail.prototype.managesieve_setget = function()
{
  var script = $(this.gui_objects.filtersetslist).val();
  location.href = this.env.comm_path+'&_action=plugin.managesieve&_act=setget&_set='+script;
};

// Set activate
rcube_webmail.prototype.managesieve_setact = function()
{
  if (!this.gui_objects.filtersetslist)
    return false;

  var script = this.gui_objects.filtersetslist.value,
    action = (script == rcmail.env.active_set ? 'deact' : 'setact');

  this.http_post('plugin.managesieve', '_act='+action+'&_set='+script);
};

// Set activate flag in sets list after set activation
rcube_webmail.prototype.managesieve_reset = function()
{
  if (!this.gui_objects.filtersetslist)
    return false;

  var list = this.gui_objects.filtersetslist,
    opts = list.getElementsByTagName('option'),
    label = ' (' + this.get_label('managesieve.active') + ')',
    regx = new RegExp(RegExp.escape(label)+'$');

  for (var x=0; x<opts.length; x++) {
    if (opts[x].value != rcmail.env.active_set && opts[x].innerHTML.match(regx))
      opts[x].innerHTML = opts[x].innerHTML.replace(regx, '');
    else if (opts[x].value == rcmail.env.active_set)
      opts[x].innerHTML = opts[x].innerHTML + label;
  }

  // change title of setact button
  $('#'+rcmail.buttons['plugin.managesieve-setact'][0].id).attr('title', rcmail.gettext('managesieve.filterset'
    + (list.value == rcmail.env.active_set ? 'deact' : 'act')));
};

// Set delete
rcube_webmail.prototype.managesieve_setdel = function()
{
  if (!this.gui_objects.filtersetslist)
    return false;

  if (!confirm(this.get_label('managesieve.setdeleteconfirm')))
    return false;

  var script = this.gui_objects.filtersetslist.value;
  this.http_post('plugin.managesieve', '_act=setdel&_set='+script);
};

// Set add
rcube_webmail.prototype.managesieve_setadd = function()
{
  this.filters_list.clear_selection();
  this.enable_command('plugin.managesieve-up', 'plugin.managesieve-down', 'plugin.managesieve-del', false);

  if (this.env.contentframe && window.frames && window.frames[this.env.contentframe]) {
    target = window.frames[this.env.contentframe];
    var msgid = this.set_busy(true, 'loading');
    target.location.href = this.env.comm_path+'&_action=plugin.managesieve&_framed=1&_newset=1&_unlock='+msgid;
  }
};

rcube_webmail.prototype.managesieve_reload = function(set)
{
  this.env.reload_set = set;
  window.setTimeout(function() {
    location.href = rcmail.env.comm_path + '&_action=plugin.managesieve'
      + (rcmail.env.reload_set ? '&_set=' + rcmail.env.reload_set : '')
  }, 500);
};

// Register onmouse(leave/enter) events for tips on specified form element
rcube_webmail.prototype.managesieve_tip_register = function(tips)
{
  for (var n in tips) {
    $('#'+tips[n][0])
      .bind('mouseenter', {str: tips[n][1]},
        function(e) {
          var offset = $(this).offset(),
            tip = rcmail.env.framed ? parent.rcmail.env.ms_tip_layer : rcmail.env.ms_tip_layer,
            left = offset.left,
            top = offset.top - 12;

          if (rcmail.env.framed) {
            offset = $(parent.document.getElementById('filter-box')).offset();
            top  += offset.top;
            left += offset.left;
          }

          tip.html(e.data.str)
          top -= tip.height();
          
          tip.css({left: left, top: top}).show();
        })
      .bind('mouseleave',
        function(e) {
          var tip = parent.rcmail && parent.rcmail.env.ms_tip_layer ? 
            parent.rcmail.env.ms_tip_layer : rcmail.env.ms_tip_layer;
          tip.hide();
      });
  }
};
