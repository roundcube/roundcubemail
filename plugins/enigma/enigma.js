/* Enigma Plugin */

if (window.rcmail)
{
    rcmail.addEventListener('init', function(evt)
    {
        if (rcmail.env.task == 'settings') {
            rcmail.register_command('plugin.enigma', function() { rcmail.goto_url('plugin.enigma') }, true);
            rcmail.register_command('plugin.enigma-key-import', function() { rcmail.enigma_key_import() }, true);
            rcmail.register_command('plugin.enigma-key-export', function() { rcmail.enigma_key_export() }, true);

            if (rcmail.gui_objects.keyslist)
            {
                var p = rcmail;
                rcmail.keys_list = new rcube_list_widget(rcmail.gui_objects.keyslist,
                    {multiselect:false, draggable:false, keyboard:false});
                rcmail.keys_list.addEventListener('select', function(o){ p.enigma_key_select(o); });
                rcmail.keys_list.init();
                rcmail.keys_list.focus();

                rcmail.enigma_list();

                rcmail.register_command('firstpage', function(props) {return rcmail.enigma_list_page('first'); });
                rcmail.register_command('previouspage', function(props) {return rcmail.enigma_list_page('previous'); });
                rcmail.register_command('nextpage', function(props) {return rcmail.enigma_list_page('next'); });
                rcmail.register_command('lastpage', function(props) {return rcmail.enigma_list_page('last'); });
            }

            if (rcmail.env.action == 'edit-prefs') {
                rcmail.register_command('search', function(props) {return rcmail.enigma_search(props); }, true);
                rcmail.register_command('reset-search', function(props) {return rcmail.enigma_search_reset(props); }, true);
            }
            else if (rcmail.env.action == 'plugin.enigma') {
                rcmail.register_command('plugin.enigma-import', function() { rcmail.enigma_import() }, true);
                rcmail.register_command('plugin.enigma-export', function() { rcmail.enigma_export() }, true);
            }
        }
    });
}

/*********************************************************/
/*********    Enigma Settings/Keys/Certs UI      *********/
/*********************************************************/

// Display key(s) import form
rcube_webmail.prototype.enigma_key_import = function()
{
    this.enigma_loadframe(null, '&_a=keyimport');
};

// Submit key(s) form
rcube_webmail.prototype.enigma_import = function()
{
    var form, file;
    if (form = this.gui_objects.importform) {
        file = document.getElementById('rcmimportfile');
        if (file && !file.value) {
            alert(this.get_label('selectimportfile'));
            return;
        }
        form.submit();
        this.set_busy(true, 'importwait');
        this.lock_form(form, true);
   }
};

// list row selection handler
rcube_webmail.prototype.enigma_key_select = function(list)
{
    var id;
    if (id = list.get_single_selection())
        this.enigma_loadframe(id);
};

// load key frame
rcube_webmail.prototype.enigma_loadframe = function(id, url)
{
    var frm, win;
    if (this.env.contentframe && window.frames && (frm = window.frames[this.env.contentframe])) {
        if (!id && !url && (win = window.frames[this.env.contentframe])) {
            if (win.location && win.location.href.indexOf(this.env.blankpage)<0)
                win.location.href = this.env.blankpage;
            return;
        }
        this.set_busy(true);
        if (!url)
            url = '&_a=keyinfo&_id='+id;
        frm.location.href = this.env.comm_path+'&_action=plugin.enigma&_framed=1' + url;
    }
};

// Search keys/certs
rcube_webmail.prototype.enigma_search = function(props)
{
    if (!props && this.gui_objects.qsearchbox)
        props = this.gui_objects.qsearchbox.value;

    if (props || this.env.search_request) {
        var params = {'_a': 'keysearch', '_q': urlencode(props)},
          lock = this.set_busy(true, 'searching');
//        if (this.gui_objects.search_filter)
  //          addurl += '&_filter=' + this.gui_objects.search_filter.value;
        this.env.current_page = 1;  
        this.enigma_loadframe();
        this.enigma_clear_list();
        this.http_post('plugin.enigma', params, lock);
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
    var params = {'_a': 'keylist'},
      lock = this.set_busy(true, 'loading');

    this.env.current_page = page ? page : 1;

    if (this.env.search_request)
        params._q = this.env.search_request;
    if (page)
        params._p = page;

    this.enigma_clear_list();
    this.http_post('plugin.enigma', params, lock);
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

// Import attached keys/certs file
rcube_webmail.prototype.enigma_import_attachment = function(mime_id)
{
    var lock = this.set_busy(true, 'loading');
    this.http_post('plugin.enigmaimport', '_uid='+this.env.uid+'&_mbox='
        +urlencode(this.env.mailbox)+'&_part='+urlencode(mime_id), lock);

    return false;
};

