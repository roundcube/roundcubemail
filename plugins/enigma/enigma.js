/* Enigma Plugin */

window.rcmail && rcmail.addEventListener('init', function(evt) {
    if (rcmail.env.task == 'settings') {
        if (rcmail.gui_objects.keyslist) {
            rcmail.keys_list = new rcube_list_widget(rcmail.gui_objects.keyslist,
                {multiselect:true, draggable:false, keyboard:false});
            rcmail.keys_list
                .addEventListener('select', function(o) { rcmail.enigma_keylist_select(o); })
                .addEventListener('keypress', function(o) { rcmail.enigma_keylist_keypress(o); })
                .init()
                .focus();

            rcmail.enigma_list();

            rcmail.register_command('firstpage', function(props) { return rcmail.enigma_list_page('first'); });
            rcmail.register_command('previouspage', function(props) { return rcmail.enigma_list_page('previous'); });
            rcmail.register_command('nextpage', function(props) { return rcmail.enigma_list_page('next'); });
            rcmail.register_command('lastpage', function(props) { return rcmail.enigma_list_page('last'); });
        }

        if (rcmail.env.action == 'plugin.enigmakeys') {
            rcmail.register_command('search', function(props) {return rcmail.enigma_search(props); }, true);
            rcmail.register_command('reset-search', function(props) {return rcmail.enigma_search_reset(props); }, true);
            rcmail.register_command('plugin.enigma-import', function() { rcmail.enigma_import(); }, true);
            rcmail.register_command('plugin.enigma-key-export', function() { rcmail.enigma_export(); });
            rcmail.register_command('plugin.enigma-key-export-selected', function() { rcmail.enigma_export(true); });
            rcmail.register_command('plugin.enigma-key-import', function() { rcmail.enigma_key_import(); }, true);
            rcmail.register_command('plugin.enigma-key-delete', function(props) { return rcmail.enigma_delete(); });
            rcmail.register_command('plugin.enigma-key-create', function(props) { return rcmail.enigma_key_create(); }, true);
            rcmail.register_command('plugin.enigma-key-save', function(props) { return rcmail.enigma_key_create_save(); }, true);

            rcmail.addEventListener('responseafterplugin.enigmakeys', function() {
                rcmail.enable_command('plugin.enigma-key-export', rcmail.env.rowcount > 0);
            });
        }
    }
    else if (rcmail.env.task == 'mail') {
        if (rcmail.env.action == 'compose') {
            rcmail.addEventListener('beforesend', function(props) { rcmail.enigma_beforesend_handler(props); })
                .addEventListener('beforesavedraft', function(props) { rcmail.enigma_beforesavedraft_handler(props); });

            $('input,label', $('#enigmamenu')).mouseup(function(e) {
                // don't close the menu on mouse click inside
                e.stopPropagation();
            });
        }

        $.each(['encrypt', 'sign'], function() {
            if (rcmail.env['enigma_force_' + this])
                $('[name="_enigma_' + this + '"]').prop('checked', true);
        });

        if (rcmail.env.enigma_password_request) {
            rcmail.enigma_password_request(rcmail.env.enigma_password_request);
        }
    }
});


/*********************************************************/
/*********    Enigma Settings/Keys/Certs UI      *********/
/*********************************************************/

// Display key(s) import form
rcube_webmail.prototype.enigma_key_import = function()
{
    this.enigma_loadframe('&_action=plugin.enigmakeys&_a=import');
};

// Display key(s) generation form
rcube_webmail.prototype.enigma_key_create = function()
{
    this.enigma_loadframe('&_action=plugin.enigmakeys&_a=create');
};

// Generate key(s) and submit them
rcube_webmail.prototype.enigma_key_create_save = function()
{
    var options, lock,
        user = $('#key-ident > option').filter(':selected').text(),
        password = $('#key-pass').val(),
        confirm = $('#key-pass-confirm').val(),
        size = $('#key-size').val();

    // validate the form
    if (!password || !confirm)
        return alert(this.get_label('enigma.formerror'));

    if (password != confirm)
        return alert(this.get_label('enigma.passwordsdiffer'));

    if (user.match(/^<[^>]+>$/))
        return alert(this.get_label('enigma.nonameident'));

    // generate keys
    // use OpenPGP.js if browser supports required features
    if (window.openpgp && window.crypto && (window.crypto.getRandomValues || window.crypto.subtle)) {
        lock = this.set_busy(true, 'enigma.keygenerating');
        options = {
            numBits: size,
            userId: user,
            passphrase: password
        };

        openpgp.generateKeyPair(options).then(function(keypair) {
            // success
            var post = {_a: 'import', _keys: keypair.privateKeyArmored};

            // send request to server
            rcmail.http_post('plugin.enigmakeys', post, lock);
        }, function(error) {
            // failure
            rcmail.set_busy(false, null, lock);
            rcmail.display_message(rcmail.get_label('enigma.keygenerateerror'), 'error');
        });
    }
    // generate keys on the server
    else if (rcmail.env.enigma_keygen_server) {
        lock = this.set_busy(true, 'enigma.keygenerating');
        options = {_a: 'generate', _user: user, _password: password, _size: size};
        rcmail.http_post('plugin.enigmakeys', options, lock);
    }
    else {
        rcmail.display_message(rcmail.get_label('enigma.keygennosupport'), 'error');
    }
};

// Action executed after successful key generation and import
rcube_webmail.prototype.enigma_key_create_success = function()
{
    parent.rcmail.enigma_list(1);
};

// Delete key(s)
rcube_webmail.prototype.enigma_delete = function()
{
    var keys = this.keys_list.get_selection();

    if (!keys.length || !confirm(this.get_label('enigma.keyremoveconfirm')))
        return;

    var lock = this.display_message(this.get_label('enigma.keyremoving'), 'loading'),
        post = {_a: 'delete', _keys: keys};

    // send request to server
    this.http_post('plugin.enigmakeys', post, lock);
};

// Export key(s)
rcube_webmail.prototype.enigma_export = function(selected)
{
    var keys = selected ? this.keys_list.get_selection().join(',') : '*';

    if (!keys.length)
        return;

    this.goto_url('plugin.enigmakeys', {_a: 'export', _keys: keys}, false, true);
};

// Submit key(s) import form
rcube_webmail.prototype.enigma_import = function()
{
    var form, file;

    if (form = this.gui_objects.importform) {
        file = document.getElementById('rcmimportfile');
        if (file && !file.value) {
            alert(this.get_label('selectimportfile'));
            return;
        }

        var lock = this.set_busy(true, 'importwait');

        form.action = this.add_url(form.action, '_unlock', lock);
        form.submit();

        this.lock_form(form, true);
   }
};

// list row selection handler
rcube_webmail.prototype.enigma_keylist_select = function(list)
{
    var id = list.get_single_selection(), url;

    if (id)
        url = '&_action=plugin.enigmakeys&_a=info&_id=' + id;

    this.enigma_loadframe(url);
    this.enable_command('plugin.enigma-key-delete', 'plugin.enigma-key-export-selected', list.selection.length > 0);
};

rcube_webmail.prototype.enigma_keylist_keypress = function(list)
{
    if (list.modkey == CONTROL_KEY)
        return;

    if (list.key_pressed == list.DELETE_KEY || list.key_pressed == list.BACKSPACE_KEY)
        this.command('plugin.enigma-key-delete');
    else if (list.key_pressed == 33)
        this.command('previouspage');
    else if (list.key_pressed == 34)
        this.command('nextpage');
};

// load key frame
rcube_webmail.prototype.enigma_loadframe = function(url)
{
    var frm, win;

    if (this.env.contentframe && window.frames && (frm = window.frames[this.env.contentframe])) {
        if (!url && (win = window.frames[this.env.contentframe])) {
            if (win.location && win.location.href.indexOf(this.env.blankpage) < 0)
                win.location.href = this.env.blankpage;
            return;
        }

        this.env.frame_lock = this.set_busy(true, 'loading');
        frm.location.href = this.env.comm_path + '&_framed=1&' + url;
    }
};

// Search keys/certs
rcube_webmail.prototype.enigma_search = function(props)
{
    if (!props && this.gui_objects.qsearchbox)
        props = this.gui_objects.qsearchbox.value;

    if (props || this.env.search_request) {
        var params = {'_a': 'search', '_q': urlencode(props)},
          lock = this.set_busy(true, 'searching');
//        if (this.gui_objects.search_filter)
  //          addurl += '&_filter=' + this.gui_objects.search_filter.value;
        this.env.current_page = 1;
        this.enigma_loadframe();
        this.enigma_clear_list();
        this.http_post('plugin.enigmakeys', params, lock);
    }

    return false;
}

// Reset search filter and the list
rcube_webmail.prototype.enigma_search_reset = function(props)
{
    var s = this.env.search_request;
    this.reset_qsearch();

    if (s) {
        this.enigma_loadframe();
        this.enigma_clear_list();

        // refresh the list
        this.enigma_list();
    }

    return false;
}

// Keys/certs listing
rcube_webmail.prototype.enigma_list = function(page)
{
    var params = {'_a': 'list'},
      lock = this.set_busy(true, 'loading');

    this.env.current_page = page ? page : 1;

    if (this.env.search_request)
        params._q = this.env.search_request;
    if (page)
        params._p = page;

    this.enigma_clear_list();
    this.http_post('plugin.enigmakeys', params, lock);
}

// Change list page
rcube_webmail.prototype.enigma_list_page = function(page)
{
    if (page == 'next')
        page = this.env.current_page + 1;
    else if (page == 'last')
        page = this.env.pagecount;
    else if (page == 'prev' && this.env.current_page > 1)
        page = this.env.current_page - 1;
    else if (page == 'first' && this.env.current_page > 1)
        page = 1;

    this.enigma_list(page);
}

// Remove list rows
rcube_webmail.prototype.enigma_clear_list = function()
{
    this.enigma_loadframe();
    if (this.keys_list)
        this.keys_list.clear(true);

    this.enable_command('plugin.enigma-key-delete', 'plugin.enigma-key-delete-selected', false);
}

// Adds a row to the list
rcube_webmail.prototype.enigma_add_list_row = function(r)
{
    if (!this.gui_objects.keyslist || !this.keys_list)
        return false;

    var list = this.keys_list,
        tbody = this.gui_objects.keyslist.tBodies[0],
        rowcount = tbody.rows.length,
        even = rowcount%2,
        css_class = 'message'
            + (even ? ' even' : ' odd'),
        // for performance use DOM instead of jQuery here
        row = document.createElement('tr'),
        col = document.createElement('td');

    row.id = 'rcmrow' + r.id;
    row.className = css_class;

    col.innerHTML = r.name;
    row.appendChild(col);
    list.insert_row(row);
}


/*********************************************************/
/*********        Enigma Message methods         *********/
/*********************************************************/

// handle message send/save action
rcube_webmail.prototype.enigma_beforesend_handler = function(props)
{
    this.env.last_action = 'send';
    this.enigma_compose_handler(props);
}

rcube_webmail.prototype.enigma_beforesavedraft_handler = function(props)
{
    this.env.last_action = 'savedraft';
    this.enigma_compose_handler(props);
}

rcube_webmail.prototype.enigma_compose_handler = function(props)
{
    var form = this.gui_objects.messageform;

    // copy inputs from enigma menu to the form
    $('#enigmamenu input').each(function() {
        var id = this.id + '_cpy', input = $('#' + id);

        if (!input.length) {
            input = $(this).clone();
            input.prop({id: id, type: 'hidden'}).appendTo(form);
        }

        input.val(this.checked ? '1' : '');
    });

    // disable signing when saving drafts
    if (this.env.last_action == 'savedraft') {
        $('input[name="_enigma_sign"]', form).val(0);
    }
}

// Import attached keys/certs file
rcube_webmail.prototype.enigma_import_attachment = function(mime_id)
{
    var lock = this.set_busy(true, 'loading'),
        post = {_uid: this.env.uid, _mbox: this.env.mailbox, _part: mime_id};

    this.http_post('plugin.enigmaimport', post, lock);

    return false;
}

// password request popup
rcube_webmail.prototype.enigma_password_request = function(data)
{
    if (!data || !data.keyid) {
        return;
    }

    var ref = this,
        msg = this.get_label('enigma.enterkeypass'),
        myprompt = $('<div class="prompt">'),
        myprompt_content = $('<div class="message">')
            .appendTo(myprompt),
        myprompt_input = $('<input>').attr({type: 'password', size: 30})
            .keypress(function(e) {
                if (e.which == 13)
                    (ref.is_framed() ? window.parent.$ : $)('.ui-dialog-buttonpane button.mainaction:visible').click();
            })
            .appendTo(myprompt);

    data.key = data.keyid;
    if (data.keyid.length > 8)
        data.keyid = data.keyid.substr(data.keyid.length - 8);

    $.each(['keyid', 'user'], function() {
        msg = msg.replace('$' + this, data[this]);
    });

    myprompt_content.text(msg);

    this.show_popup_dialog(myprompt, this.get_label('enigma.enterkeypasstitle'),
        [{
            text: this.get_label('save'),
            'class': 'mainaction',
            click: function(e) {
                e.stopPropagation();

                var jq = ref.is_framed() ? window.parent.$ : $;

                data.password = myprompt_input.val();

                if (!data.password) {
                    myprompt_input.focus();
                    return;
                }

                ref.enigma_password_submit(data);
                jq(this).remove();
            }
        },
        {
            text: this.get_label('cancel'),
            click: function(e) {
                var jq = ref.is_framed() ? window.parent.$ : $;
                e.stopPropagation();
                jq(this).remove();
            }
        }], {width: 400});

    if (this.is_framed() && parent.rcmail.message_list) {
        // this fixes bug when pressing Enter on "Save" button in the dialog
        parent.rcmail.message_list.blur();
    }
}

// submit entered password
rcube_webmail.prototype.enigma_password_submit = function(data)
{
    if (this.env.action == 'compose' && !data['compose-init']) {
        return this.enigma_password_compose_submit(data);
    }

    var lock = this.set_busy(true, 'loading');

    // message preview
    var form = $('<form>').attr({method: 'post', action: location.href, style: 'display:none'})
        .append($('<input>').attr({type: 'hidden', name: '_keyid', value: data.key}))
        .append($('<input>').attr({type: 'hidden', name: '_passwd', value: data.password}))
        .append($('<input>').attr({type: 'hidden', name: '_token', value: this.env.request_token}))
        .append($('<input>').attr({type: 'hidden', name: '_unlock', value: lock}))
        .appendTo(document.body);

    form.submit();
}

// submit entered password - in mail compose page
rcube_webmail.prototype.enigma_password_compose_submit = function(data)
{
    var form = this.gui_objects.messageform;

    if (!$('input[name="_keyid"]', form).length) {
        $(form).append($('<input>').attr({type: 'hidden', name: '_keyid', value: data.key}))
            .append($('<input>').attr({type: 'hidden', name: '_passwd', value: data.password}));
    }
    else {
        $('input[name="_keyid"]', form).val(data.key);
        $('input[name="_passwd"]', form).val(data.password);
    }

    this.submit_messageform(this.env.last_action == 'savedraft');
}
