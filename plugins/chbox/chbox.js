/*
 * Check box plugin
 * @version 0.2.4
 * @author Denis Sobolev
 */

if ( typeof rcube_mail_ui  === 'function' ) {
  rcube_mail_ui.prototype.selectmenu = function() {
    add = {
      selectmenu:     {id:'selectmenu'}
    };
    this.popups = $.extend(this.popups, add);
    var obj = $('#'+this.popups.selectmenu.id);
    if (obj.length)
      this.popups.selectmenu.obj = obj;
    else {
      delete this.popups.selectmenu;
    }
  }
}

function rcmail_selectmenu() {
  if (typeof rcmail_ui != 'undefined') {
    if (!rcmail_ui.popups.selectmenu)
      rcmail_ui.selectmenu();

    var obj = rcmail_ui.popups['selectmenu'].obj
    show = obj.is(':visible') ? false : true;
    if(show) {
      $('#selectmenu').mouseleave(function() { return rcmail.command('plugin.chbox.selectmenu')});
    } else {
      $('#selectmenu').unbind('mouseleave');
    }
    rcmail_ui.show_popupmenu('selectmenu');
  }else {
    UI.show_popup('selectmenu');
    var obj = $('#selectmenu');
    obj.mouseleave(function() { return  obj.hide();});
  }
  return false;
}

if (window.rcmail) {
  rcmail.addEventListener('init', function(evt) {
    rcmail.register_command('plugin.chbox.selectmenu', rcmail_selectmenu, true);
    // add event-listener to message list
    if (rcmail.message_list) {
      rcmail.message_list.addEventListener('select', function(list) {
        $('#messagelist input').filter(':checkbox').prop('checked',false);
        var selection = rcmail.message_list ? $.merge([], rcmail.message_list.get_selection()) : [];
        // exit if no mailbox specified or if selection is empty
        if (!rcmail.env.uid && !selection.length)
          return;
        for (var uid, i=0, len=selection.length; i<len; i++) {
            uid = selection[i];
            var select = document.getElementById('rcmselect'+uid);
            if (select) {
              select.checked = true;
      	    }
        }
      });
    }
  });

  rcmail.addEventListener('listupdate','chbox_menu');
  rcmail.addEventListener('insertrow', function(evt) {
    var row = evt.row
    if (found = (typeof(rcmail.env.coltypes.chbox) != "undefined" || $.inArray('chbox', rcmail.env.coltypes) >= 0)) {
      rcmail.set_env('chbox_col', found);
    }
    // set eventhandler to checkbox selection
    if (rcmail.env.chbox_col != null && (row.select = document.getElementById('rcmselect'+row.uid))) {
      if (rcmail.message_list.in_selection(row.uid)) {
        row.select.checked = true;
      }
      row.select._row = row.obj;
      row.select.onclick = function(e) {
        // don't include the non-selected checkbox in this
        rcmail.message_list.select_row(row.uid, CONTROL_KEY, true);
        $("#selectcount").html(rcmail.message_list.selection.length);
      };
    }
  });
}

