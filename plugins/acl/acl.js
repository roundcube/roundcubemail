/**
 * ACL plugin script
 *
 * @version 0.6.3
 * @author Aleksander Machniak <alec@alec.pl>
 */

if (window.rcmail) {
    rcmail.addEventListener('init', function() {
        if (rcmail.gui_objects.acltable) {
            rcmail.acl_list_init();
            // enable autocomplete on user input
            if (rcmail.env.acl_users_source) {
                rcmail.init_address_input_events($('#acluser'), {action:'settings/plugin.acl-autocomplete'});
                // fix inserted value
                rcmail.addEventListener('autocomplete_insert', function(e) {
                    if (e.field.id != 'acluser')
                        return;

                    var value = e.insert;
                    // get UID from the entry value
                    if (value.match(/\s*\(([^)]+)\)[, ]*$/))
                        value = RegExp.$1;
                    e.field.value = value;
                });
            }
        }

        rcmail.enable_command('acl-create', 'acl-save', 'acl-cancel', 'acl-mode-switch', true);
        rcmail.enable_command('acl-delete', 'acl-edit', false);
    });
}

// Display new-entry form
rcube_webmail.prototype.acl_create = function()
{
    this.acl_init_form();
}

// Display ACL edit form
rcube_webmail.prototype.acl_edit = function()
{
    // @TODO: multi-row edition
    var id = this.acl_list.get_single_selection();
    if (id)
        this.acl_init_form(id);
}

// ACL entry delete
rcube_webmail.prototype.acl_delete = function()
{
    var users = this.acl_get_usernames();

    if (users && users.length && confirm(this.get_label('acl.deleteconfirm'))) {
        this.http_request('settings/plugin.acl', '_act=delete&_user='+urlencode(users.join(','))
            + '&_mbox='+urlencode(this.env.mailbox),
            this.set_busy(true, 'acl.deleting'));
    }
}

// Save ACL data
rcube_webmail.prototype.acl_save = function()
{
    var user = $('#acluser').val(), rights = '', type;

    $(':checkbox', this.env.acl_advanced ? $('#advancedrights') : sim_ul = $('#simplerights')).map(function() {
        if (this.checked)
            rights += this.value;
    });

    if (type = $('input:checked[name=usertype]').val()) {
        if (type != 'user')
            user = type;
    }

    if (!user) {
        alert(this.get_label('acl.nouser'));
        return;
    }
    if (!rights) {
        alert(this.get_label('acl.norights'));
        return;
    }

    this.http_request('settings/plugin.acl', '_act=save'
        + '&_user='+urlencode(user)
        + '&_acl=' +rights
        + '&_mbox='+urlencode(this.env.mailbox)
        + (this.acl_id ? '&_old='+this.acl_id : ''),
        this.set_busy(true, 'acl.saving'));
}

// Cancel/Hide form
rcube_webmail.prototype.acl_cancel = function()
{
    this.ksearch_blur();
    this.acl_form.hide();
}

// Update data after save (and hide form)
rcube_webmail.prototype.acl_update = function(o)
{
    // delete old row
    if (o.old)
        this.acl_remove_row(o.old);
    // make sure the same ID doesn't exist
    else if (this.env.acl[o.id])
        this.acl_remove_row(o.id);

    // add new row
    this.acl_add_row(o, true);
    // hide autocomplete popup
    this.ksearch_blur();
    // hide form
    this.acl_form.hide();
}

// Switch table display mode
rcube_webmail.prototype.acl_mode_switch = function(elem)
{
    this.env.acl_advanced = !this.env.acl_advanced;
    this.enable_command('acl-delete', 'acl-edit', false);
    this.http_request('settings/plugin.acl', '_act=list'
        + '&_mode='+(this.env.acl_advanced ? 'advanced' : 'simple')
        + '&_mbox='+urlencode(this.env.mailbox),
        this.set_busy(true, 'loading'));
}

// ACL table initialization
rcube_webmail.prototype.acl_list_init = function()
{
    this.acl_list = new rcube_list_widget(this.gui_objects.acltable,
        {multiselect:true, draggable:false, keyboard:true, toggleselect:true});
    this.acl_list.addEventListener('select', function(o) { rcmail.acl_list_select(o); });
    this.acl_list.addEventListener('dblclick', function(o) { rcmail.acl_list_dblclick(o); });
    this.acl_list.addEventListener('keypress', function(o) { rcmail.acl_list_keypress(o); });
    this.acl_list.init();
}

// ACL table row selection handler
rcube_webmail.prototype.acl_list_select = function(list)
{
    rcmail.enable_command('acl-delete', list.selection.length > 0);
    rcmail.enable_command('acl-edit', list.selection.length == 1);
    list.focus();
}

// ACL table double-click handler
rcube_webmail.prototype.acl_list_dblclick = function(list)
{
    this.acl_edit();
}

// ACL table keypress handler
rcube_webmail.prototype.acl_list_keypress = function(list)
{
    if (list.key_pressed == list.ENTER_KEY)
        this.command('acl-edit');
    else if (list.key_pressed == list.DELETE_KEY || list.key_pressed == list.BACKSPACE_KEY)
        if (!this.acl_form || !this.acl_form.is(':visible'))
            this.command('acl-delete');
}

// Reloads ACL table
rcube_webmail.prototype.acl_list_update = function(html)
{
    $(this.gui_objects.acltable).html(html);
    this.acl_list_init();
}

// Returns names of users in selected rows
rcube_webmail.prototype.acl_get_usernames = function()
{
    var users = [], n, len, cell, row,
        list = this.acl_list,
        selection = list.get_selection();

    for (n=0, len=selection.length; n<len; n++) {
        if (this.env.acl_specials.length && $.inArray(selection[n], this.env.acl_specials) >= 0) {
            users.push(selection[n]);
        }
        else {
            row = list.rows[selection[n]].obj;
            cell = $('td.user', row);
            if (cell.length == 1)
                users.push(cell.text());
        }
    }

    return users;
}

// Removes ACL table row
rcube_webmail.prototype.acl_remove_row = function(id)
{
    this.acl_list.remove_row(id);
    // we don't need it anymore (remove id conflict)
    $('#rcmrow'+id).remove();
    this.env.acl[id] = null;
}

// Adds ACL table row
rcube_webmail.prototype.acl_add_row = function(o, sel)
{
    var n, len, ids = [], spec = [], id = o.id, list = this.acl_list,
        items = this.env.acl_advanced ? [] : this.env.acl_items,
        table = this.gui_objects.acltable,
        row = $('thead > tr', table).clone();

    // Update new row
    $('td', row).map(function() {
        var r, cl = this.className.replace(/^acl/, '');

        if (items && items[cl])
            cl = items[cl];

        if (cl == 'user')
            $(this).text(o.username);
        else
            $(this).addClass(rcmail.acl_class(o.acl, cl)).text('');
    });

    row.attr('id', 'rcmrow'+id);
    row = row.get(0);

    this.env.acl[id] = o.acl;

    // sorting... (create an array of user identifiers, then sort it)
    for (n in this.env.acl) {
        if (this.env.acl[n]) {
            if (this.env.acl_specials.length && $.inArray(n, this.env.acl_specials) >= 0)
                spec.push(n);
            else
                ids.push(n);
        }
    }
    ids.sort();
    // specials on the top
    ids = spec.concat(ids);

    // find current id
    for (n=0, len=ids.length; n<len; n++)
        if (ids[n] == id)
            break;

    // add row
    if (n && n < len) {
        $('#rcmrow'+ids[n-1]).after(row);
        list.init_row(row);
        list.rowcount++;
    }
    else
        list.insert_row(row);

    if (sel)
        list.select_row(o.id);
}

// Initializes and shows ACL create/edit form
rcube_webmail.prototype.acl_init_form = function(id)
{
    var ul, row, val = '', type = 'user', li_elements, body = $('body'),
        adv_ul = $('#advancedrights'), sim_ul = $('#simplerights'),
        name_input = $('#acluser');

    if (!this.acl_form) {
        var fn = function () { $('input[value=user]').prop('checked', true); };
        name_input.click(fn).keypress(fn);
    }

    this.acl_form = $('#aclform');

    // Hide unused items
    if (this.env.acl_advanced) {
        adv_ul.show();
        sim_ul.hide();
        ul = adv_ul;
    }
    else {
        sim_ul.show();
        adv_ul.hide();
        ul = sim_ul;
    }

    // initialize form fields
    li_elements = $(':checkbox', ul);
    li_elements.attr('checked', false);

    if (id) {
        row = this.acl_list.rows[id].obj;
        li_elements.map(function() {
            var val = this.value, td = $('td.'+this.id, row);
            if (td && td.hasClass('enabled'))
                this.checked = true;
        });

        if (!this.env.acl_specials.length || $.inArray(id, this.env.acl_specials) < 0)
            val = $('td.user', row).text();
        else
            type = id;
    }

    name_input.val(val);
    $('input[value='+type+']').prop('checked', true);

    this.acl_id = id;

    // position the form horizontally
    var bw = body.width(), mw = this.acl_form.width();

    if (bw >= mw)
        this.acl_form.css({left: parseInt((bw - mw)/2)+'px'});

    // display it
    this.acl_form.show();
    if (type == 'user')
        name_input.focus();

    // unfocus the list, make backspace key in name input field working
    this.acl_list.blur();
}

// Returns class name according to ACL comparision result
rcube_webmail.prototype.acl_class = function(acl1, acl2)
{
    var i, len, found = 0;

    acl1 = String(acl1);
    acl2 = String(acl2);

    for (i=0, len=acl2.length; i<len; i++)
        if (acl1.indexOf(acl2[i]) > -1)
            found++;

    if (found == len)
        return 'enabled';
    else if (found)
        return 'partial';

    return 'disabled';
}
