/* (Manage)Sieve Filters */

if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    // add managesieve-create command to message_commands array,
    // so it's state will be updated on message selection/unselection
    if (rcmail.env.task == 'mail') {
      if (rcmail.env.action != 'show')
        rcmail.env.message_commands.push('managesieve-create');
      else
        rcmail.enable_command('managesieve-create', true);
    }
    else {
      var tab = $('<span>').attr('id', 'settingstabpluginmanagesieve').addClass('tablink'),
        button = $('<a>').attr('href', rcmail.env.comm_path+'&_action=plugin.managesieve')
          .attr('title', rcmail.gettext('managesieve.managefilters'))
          .html(rcmail.gettext('managesieve.filters'))
          .appendTo(tab);

      // add tab
      rcmail.add_element(tab, 'tabs');
    }

    if (rcmail.env.task == 'mail' || rcmail.env.action.indexOf('plugin.managesieve') != -1) {
      // Create layer for form tips
      if (!rcmail.env.framed) {
        rcmail.env.ms_tip_layer = $('<div id="managesieve-tip" class="popupmenu"></div>');
        rcmail.env.ms_tip_layer.appendTo(document.body);
      }
    }

    // register commands
    rcmail.register_command('plugin.managesieve-save', function() { rcmail.managesieve_save() });
    rcmail.register_command('plugin.managesieve-act', function() { rcmail.managesieve_act() });
    rcmail.register_command('plugin.managesieve-add', function() { rcmail.managesieve_add() });
    rcmail.register_command('plugin.managesieve-del', function() { rcmail.managesieve_del() });
    rcmail.register_command('plugin.managesieve-move', function() { rcmail.managesieve_move() });
    rcmail.register_command('plugin.managesieve-setadd', function() { rcmail.managesieve_setadd() });
    rcmail.register_command('plugin.managesieve-setdel', function() { rcmail.managesieve_setdel() });
    rcmail.register_command('plugin.managesieve-setact', function() { rcmail.managesieve_setact() });
    rcmail.register_command('plugin.managesieve-setget', function() { rcmail.managesieve_setget() });

    if (rcmail.env.action == 'plugin.managesieve' || rcmail.env.action == 'plugin.managesieve-save') {
      if (rcmail.gui_objects.sieveform) {
        rcmail.enable_command('plugin.managesieve-save', true);

        // small resize for header element
        $('select[name="_header[]"]', rcmail.gui_objects.sieveform).each(function() {
          if (this.value == '...') this.style.width = '40px';
        });

        // resize dialog window
        if (rcmail.env.action == 'plugin.managesieve' && rcmail.env.task == 'mail') {
          parent.rcmail.managesieve_dialog_resize(rcmail.gui_objects.sieveform);
        }

        $('input[type="text"]:first', rcmail.gui_objects.sieveform).focus();
      }
      else {
        rcmail.enable_command('plugin.managesieve-add', 'plugin.managesieve-setadd', !rcmail.env.sieveconnerror);
      }

      var i, p = rcmail, setcnt, set = rcmail.env.currentset;

      if (rcmail.gui_objects.filterslist) {
        rcmail.filters_list = new rcube_list_widget(rcmail.gui_objects.filterslist,
          {multiselect:false, draggable:true, keyboard:false});
        rcmail.filters_list.addEventListener('select', function(o){ p.managesieve_select(o); });
        rcmail.filters_list.addEventListener('dragstart', function(o){ p.managesieve_dragstart(o); });
        rcmail.filters_list.addEventListener('dragend', function(e){ p.managesieve_dragend(e); });
        rcmail.filters_list.row_init = function (row) {
          row.obj.onmouseover = function() { p.managesieve_focus_filter(row); };
          row.obj.onmouseout = function() { p.managesieve_unfocus_filter(row); };
        };
        rcmail.filters_list.init();
        rcmail.filters_list.focus();
      }

      if (rcmail.gui_objects.filtersetslist) {
        rcmail.filtersets_list = new rcube_list_widget(rcmail.gui_objects.filtersetslist, {multiselect:false, draggable:false, keyboard:false});
        rcmail.filtersets_list.addEventListener('select', function(o){ p.managesieve_setselect(o); });
        rcmail.filtersets_list.init();
        rcmail.filtersets_list.focus();

        if (set != null) {
          set = rcmail.managesieve_setid(set);
          rcmail.filtersets_list.shift_start = set;
          rcmail.filtersets_list.highlight_row(set, false);
        }

        setcnt = rcmail.filtersets_list.rowcount;
        rcmail.enable_command('plugin.managesieve-set', true);
        rcmail.enable_command('plugin.managesieve-setact', 'plugin.managesieve-setget', setcnt);
        rcmail.enable_command('plugin.managesieve-setdel', setcnt > 1);
      }
    }
    if (rcmail.gui_objects.sieveform && rcmail.env.rule_disabled)
      $('#disabled').attr('checked', true);
  });
};

/*********************************************************/
/*********       Managesieve UI methods          *********/
/*********************************************************/

rcube_webmail.prototype.managesieve_add = function()
{
  this.load_managesieveframe();
  this.filters_list.clear_selection();
};

rcube_webmail.prototype.managesieve_del = function()
{
  var id = this.filters_list.get_single_selection();
  if (confirm(this.get_label('managesieve.filterdeleteconfirm'))) {
    var lock = this.set_busy(true, 'loading');
    this.http_post('plugin.managesieve',
      '_act=delete&_fid='+this.filters_list.rows[id].uid, lock);
  }
};

rcube_webmail.prototype.managesieve_act = function()
{
  var id = this.filters_list.get_single_selection(),
    lock = this.set_busy(true, 'loading');

  this.http_post('plugin.managesieve',
    '_act=act&_fid='+this.filters_list.rows[id].uid, lock);
};

// Filter selection
rcube_webmail.prototype.managesieve_select = function(list)
{
  var id = list.get_single_selection();
  if (id != null)
    this.load_managesieveframe(list.rows[id].uid);
};

// Set selection
rcube_webmail.prototype.managesieve_setselect = function(list)
{
  this.show_contentframe(false);
  this.filters_list.clear(true);
  this.enable_command('plugin.managesieve-setdel', list.rowcount > 1);
  this.enable_command( 'plugin.managesieve-setact', 'plugin.managesieve-setget', true);

  var id = list.get_single_selection();
  if (id != null)
    this.managesieve_list(this.env.filtersets[id]);
};

rcube_webmail.prototype.managesieve_rowid = function(id)
{
  var i, rows = this.filters_list.rows;

  for (i=0; i<rows.length; i++)
    if (rows[i] != null && rows[i].uid == id)
      return i;
};

// Returns set's identifier
rcube_webmail.prototype.managesieve_setid = function(name)
{
  for (var i in this.env.filtersets)
    if (this.env.filtersets[i] == name)
      return i;
};

// Filters listing request
rcube_webmail.prototype.managesieve_list = function(script)
{
  var lock = this.set_busy(true, 'loading');

  this.http_post('plugin.managesieve', '_act=list&_set='+urlencode(script), lock);
};

// Script download request
rcube_webmail.prototype.managesieve_setget = function()
{
  var id = this.filtersets_list.get_single_selection(),
    script = this.env.filtersets[id];

  location.href = this.env.comm_path+'&_action=plugin.managesieve&_act=setget&_set='+urlencode(script);
};

// Set activate/deactivate request
rcube_webmail.prototype.managesieve_setact = function()
{
  var id = this.filtersets_list.get_single_selection(),
   lock = this.set_busy(true, 'loading'),
    script = this.env.filtersets[id],
    action = $('#rcmrow'+id).hasClass('disabled') ? 'setact' : 'deact';

  this.http_post('plugin.managesieve', '_act='+action+'&_set='+urlencode(script), lock);
};

// Set delete request
rcube_webmail.prototype.managesieve_setdel = function()
{
  if (!confirm(this.get_label('managesieve.setdeleteconfirm')))
    return false;

  var id = this.filtersets_list.get_single_selection(),
    lock = this.set_busy(true, 'loading'),
    script = this.env.filtersets[id];

  this.http_post('plugin.managesieve', '_act=setdel&_set='+urlencode(script), lock);
};

// Set add request
rcube_webmail.prototype.managesieve_setadd = function()
{
  this.filters_list.clear_selection();
  this.enable_command('plugin.managesieve-act', 'plugin.managesieve-del', false);

  if (this.env.contentframe && window.frames && window.frames[this.env.contentframe]) {
    var lock = this.set_busy(true, 'loading');
    target = window.frames[this.env.contentframe];
    target.location.href = this.env.comm_path+'&_action=plugin.managesieve&_framed=1&_newset=1&_unlock='+lock;
  }
};

rcube_webmail.prototype.managesieve_updatelist = function(action, o)
{
  this.set_busy(true);

  switch (action) {

    // Delete filter row
    case 'del':
      var i, list = this.filters_list, rows = list.rows;

      list.remove_row(this.managesieve_rowid(o.id));
      list.clear_selection();
      this.show_contentframe(false);
      this.enable_command('plugin.managesieve-del', 'plugin.managesieve-act', false);

      // re-numbering filters
      for (i=0; i<rows.length; i++) {
        if (rows[i] != null && rows[i].uid > o.id)
          rows[i].uid = rows[i].uid-1;
      }

      break;

    // Update filter row
    case 'update':
      var i, row = $('#rcmrow'+o.id);

      if (o.name)
        $('td', row).html(o.name);
      if (o.disabled)
        row.addClass('disabled');
      else
        row.removeClass('disabled');

      $('#disabled', $('iframe').contents()).prop('checked', o.disabled);

      break;

    // Add filter row to the list
    case 'add':
      var list = this.filters_list,
        row = $('<tr><td class="name"></td></tr>');

      $('td', row).html(o.name);
      row.attr('id', 'rcmrow'+o.id);
      if (o.disabled)
        row.addClass('disabled');

      list.insert_row(row.get(0));
      list.highlight_row(o.id);

      this.enable_command('plugin.managesieve-del', 'plugin.managesieve-act', true);

      break;

    // Filling rules list
    case 'list':
      var i, tr, td, el, list = this.filters_list;

      if (o.clear)
        list.clear();

      for (i in o.list) {
        el = o.list[i];
        tr = document.createElement('TR');
        td = document.createElement('TD');

        td.innerHTML = el.name;
        td.className = 'name';
        tr.id = 'rcmrow' + el.id;
        if (el.class)
            tr.className = el.class
        tr.appendChild(td);

        list.insert_row(tr);
      }

      if (o.set)
        list.highlight_row(o.set);
      else
        this.enable_command('plugin.managesieve-del', 'plugin.managesieve-act', false);

      break;

    // Sactivate/deactivate set
    case 'setact':
      var id = this.managesieve_setid(o.name), row = $('#rcmrow' + id);
      if (o.active) {
        if (o.all)
          $('tr', this.gui_objects.filtersetslist).addClass('disabled');
        row.removeClass('disabled');
      }
      else
        row.addClass('disabled');

      break;

    // Delete set row
    case 'setdel':
      var id = this.managesieve_setid(o.name);

      this.filtersets_list.remove_row(id);
      this.filters_list.clear();
      this.show_contentframe(false);
      this.enable_command('plugin.managesieve-setdel', 'plugin.managesieve-setact', 'plugin.managesieve-setget', false);

      delete this.env.filtersets[id];

      break;

    // Create set row
    case 'setadd':
      var id = 'S' + new Date().getTime(),
        list = this.filtersets_list,
        row = $('<tr class="disabled"><td class="name"></td></tr>');

      $('td', row).html(o.name);
      row.attr('id', 'rcmrow'+id);

      this.env.filtersets[id] = o.name;
      list.insert_row(row.get(0));

      // move row into its position on the list
      if (o.index != list.rowcount-1) {
        row.detach();
        var elem = $('tr:visible', list.list).get(o.index);
        row.insertBefore(elem);
      }

      list.select(id);

      break;
  }

  this.set_busy(false);
};

// load filter frame
rcube_webmail.prototype.load_managesieveframe = function(id)
{
  var has_id = typeof(id) != 'undefined' && id != null;
  this.enable_command('plugin.managesieve-act', 'plugin.managesieve-del', has_id);

  if (this.env.contentframe && window.frames && window.frames[this.env.contentframe]) {
    target = window.frames[this.env.contentframe];
    var msgid = this.set_busy(true, 'loading');
    target.location.href = this.env.comm_path+'&_action=plugin.managesieve&_framed=1'
      +(id ? '&_fid='+id : '')+'&_unlock='+msgid;
  }
};

// load filter frame
rcube_webmail.prototype.managesieve_dragstart = function(list)
{
  var id = this.filters_list.get_single_selection();

  this.drag_active = true;
  this.drag_filter = id;
};

rcube_webmail.prototype.managesieve_dragend = function(e)
{
  if (this.drag_active) {
    if (this.drag_filter_target) {
      var lock = this.set_busy(true, 'loading');

      this.show_contentframe(false);
      this.http_post('plugin.managesieve', '_act=move&_fid='+this.drag_filter
        +'&_to='+this.drag_filter_target, lock);
    }
    this.drag_active = false;
  }
};

rcube_webmail.prototype.managesieve_focus_filter = function(row)
{
  var id = row.id.replace(/^rcmrow/, '');
  if (this.drag_active && id != this.drag_filter) {
    this.drag_filter_target = id;
    $(row.obj).addClass(id < this.drag_filter ? 'filtermoveup' : 'filtermovedown');
  }
};

rcube_webmail.prototype.managesieve_unfocus_filter = function(row)
{
  if (this.drag_active) {
    $(row.obj).removeClass('filtermoveup filtermovedown');
    this.drag_filter_target = null;
  }
};

/*********************************************************/
/*********          Filter Form methods          *********/
/*********************************************************/

// Form submition
rcube_webmail.prototype.managesieve_save = function()
{
  if (parent.rcmail && parent.rcmail.filters_list && this.gui_objects.sieveform.name != 'filtersetform') {
    var id = parent.rcmail.filters_list.get_single_selection();
    if (id != null)
      this.gui_objects.sieveform.elements['_fid'].value = parent.rcmail.filters_list.rows[id].uid;
  }
  this.gui_objects.sieveform.submit();
};

// Operations on filters form
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
  if ($('#ruledel'+id).hasClass('disabled'))
    return;

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
  if ($('#actiondel'+id).hasClass('disabled'))
    return;

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
    }
    else {
      $(button).addClass('disabled');
    }
  }
};

function rule_header_select(id)
{
  var obj = document.getElementById('header' + id),
    size = document.getElementById('rule_size' + id),
    op = document.getElementById('rule_op' + id),
    target = document.getElementById('rule_target' + id),
    header = document.getElementById('custom_header' + id);

  if (obj.value == 'size') {
    size.style.display = 'inline';
    op.style.display = 'none';
    target.style.display = 'none';
    header.style.display = 'none';
  }
  else {
    header.style.display = obj.value != '...' ? 'none' : 'inline';
    size.style.display = 'none';
    op.style.display = 'inline';
    rule_op_select(id);
  }

  obj.style.width = obj.value == '...' ? '40px' : '';
};

function rule_op_select(id)
{
  var obj = document.getElementById('rule_op' + id),
    target = document.getElementById('rule_target' + id);

  target.style.display = obj.value == 'exists' || obj.value == 'notexists' ? 'none' : 'inline';
};

function rule_join_radio(value)
{
  $('#rules').css('display', value == 'any' ? 'none' : 'block');
};

function action_type_select(id)
{
  var obj = document.getElementById('action_type' + id),
    enabled = {},
    elems = {
      mailbox: document.getElementById('action_mailbox' + id),
      target: document.getElementById('action_target' + id),
      target_area: document.getElementById('action_target_area' + id),
      flags: document.getElementById('action_flags' + id),
      vacation: document.getElementById('action_vacation' + id)
    };

  if (obj.value == 'fileinto' || obj.value == 'fileinto_copy') {
    enabled.mailbox = 1;
  }
  else if (obj.value == 'redirect' || obj.value == 'redirect_copy') {
    enabled.target = 1;
  }
  else if (obj.value.match(/^reject|ereject$/)) {
    enabled.target_area = 1;
  }
  else if (obj.value.match(/^(add|set|remove)flag$/)) {
    enabled.flags = 1;
  }
  else if (obj.value == 'vacation') {
    enabled.vacation = 1;
  }

  for (var x in elems) {
    elems[x].style.display = !enabled[x] ? 'none' : 'inline';
  }
};

// Register onmouse(leave/enter) events for tips on specified form element
rcube_webmail.prototype.managesieve_tip_register = function(tips)
{
  var n, framed = parent.rcmail,
    tip = framed ? parent.rcmail.env.ms_tip_layer : rcmail.env.ms_tip_layer;

  for (var n in tips) {
    $('#'+tips[n][0])
      .bind('mouseenter', {str: tips[n][1]},
        function(e) {
          var offset = $(this).offset(),
            left = offset.left,
            top = offset.top - 12;

          if (framed) {
            offset = $((rcmail.env.task == 'mail'  ? '#sievefilterform > iframe' : '#filter-box'), parent.document).offset();
            top  += offset.top;
            left += offset.left;
          }

          tip.html(e.data.str)
          top -= tip.height();

          tip.css({left: left, top: top}).show();
        })
      .bind('mouseleave', function(e) { tip.hide(); });
  }
};

/*********************************************************/
/*********           Mail UI methods             *********/
/*********************************************************/

rcube_webmail.prototype.managesieve_create = function()
{
  if (!rcmail.env.sieve_headers || !rcmail.env.sieve_headers.length)
    return;

  var i, html, buttons = {}, dialog = $("#sievefilterform");

  // create dialog window
  if (!dialog.length) {
    dialog = $('<div id="sievefilterform"></div>');
    $('body').append(dialog);
  }

  // build dialog window content
  html = '<fieldset><legend>'+this.gettext('managesieve.usedata')+'</legend><ul>';
  for (i in rcmail.env.sieve_headers)
    html += '<li><input type="checkbox" name="headers[]" id="sievehdr'+i+'" value="'+i+'" checked="checked" />'
      +'<label for="sievehdr'+i+'">'+rcmail.env.sieve_headers[i][0]+':</label> '+rcmail.env.sieve_headers[i][1]+'</li>';
  html += '</ul></fieldset>';

  dialog.html(html);

  // [Next Step] button action
  buttons[this.gettext('managesieve.nextstep')] = function () {
    // check if there's at least one checkbox checked
    var hdrs = $('input[name="headers[]"]:checked', dialog);
    if (!hdrs.length) {
      alert(rcmail.gettext('managesieve.nodata'));
      return;
    }

    // build frame URL
    var url = rcmail.get_task_url('mail');
    url = rcmail.add_url(url, '_action', 'plugin.managesieve');
    url = rcmail.add_url(url, '_framed', 1);

    hdrs.map(function() {
      var val = rcmail.env.sieve_headers[this.value];
      url = rcmail.add_url(url, 'r['+this.value+']', val[0]+':'+val[1]);
    });

    // load form in the iframe
    var frame = $('<iframe>').attr({src: url, frameborder: 0})
    frame.height(dialog.height()); // temp. 
    dialog.empty().append(frame);
    dialog.dialog('dialog').resize();

    // Change [Next Step] button with [Save] button
    buttons = {};
    buttons[rcmail.gettext('save')] = function() {  
      var win = $('iframe', dialog).get(0).contentWindow;
      win.rcmail.managesieve_save();
    };
    dialog.dialog('option', 'buttons', buttons);
  };

  // show dialog window
  dialog.dialog({
    modal: false,
    resizable: !bw.ie6,
    closeOnEscape: (!bw.ie6 && !bw.ie7),  // disable for performance reasons
    title: this.gettext('managesieve.newfilter'),
    close: function() { rcmail.managesieve_dialog_close(); },
    buttons: buttons,
    minWidth: 600,
    minHeight: 300
  }).show();

  this.env.managesieve_dialog = dialog;
}

rcube_webmail.prototype.managesieve_dialog_close = function()
{
  var dialog = this.env.managesieve_dialog;

  // BUG(?): if we don't remove the iframe first, it will be reloaded
  dialog.html('');
  dialog.dialog('destroy').hide();
}

rcube_webmail.prototype.managesieve_dialog_resize = function(o)
{
  var dialog = this.env.managesieve_dialog,
    win = $(window), form = $(o);
    width = form.width(), height = form.height(),
    w = win.width(), h = win.height();

  dialog.dialog('option', { height: Math.min(h-20, height+120), width: Math.min(w-20, width+65) })
    .dialog('option', 'position', ['center', 'center']);  // only works in a separate call (!?)
}
